<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;



// this commment is to check git 
class UserController extends Controller
{
    /** FQCN stored in personal_access_tokens.tokenable_type */
    private const USER_TYPE = 'App\\Models\\User';

    /** Canonical roles for w3t */
    private const ROLES = ['super_admin','admin','instructor','student','author'];

    /** Short codes for roles */
    private const ROLE_SHORT = [
        'super_admin' => 'SA',
        'admin'       => 'ADM',
        'instructor'  => 'INS',
        'student'     => 'STD',
        'author'      => 'AUT',
    ];
/* =========================================================
 |                  ACTIVITY LOG HELPERS
 |=========================================================*/

private static ?array $AL_COLS_CACHE = null;

/**
 * Get column list for user_data_activity_log table (cached).
 * If table doesn't exist, returns empty array.
 */
private function alColumns(): array
{
    if (self::$AL_COLS_CACHE !== null) return self::$AL_COLS_CACHE;

    try {
        self::$AL_COLS_CACHE = DB::getSchemaBuilder()->getColumnListing('user_data_activity_log');
    } catch (\Throwable $e) {
        self::$AL_COLS_CACHE = [];
    }
    return self::$AL_COLS_CACHE;
}

/** Safe JSON encoder (never throws). */
private function safeJson($val): ?string
{
    try {
        if ($val === null) return null;
        return json_encode($val, JSON_UNESCAPED_UNICODE);
    } catch (\Throwable $e) {
        return null;
    }
}

/** Build a safe snapshot of user fields (no password). */
private function userSnapshot($user): array
{
    if (!$user) return [];

    // object -> array
    if (is_object($user)) $user = json_decode(json_encode($user), true);
    if (!is_array($user)) return [];

    $safeKeys = [
        'id','uuid','name','email','phone_number','alternative_email','alternative_phone_number',
        'whatsapp_number','address','role','role_short_form','slug','status','image',
        'last_login_at','last_login_ip','deleted_at','created_at','updated_at'
    ];

    $out = [];
    foreach ($safeKeys as $k) {
        if (array_key_exists($k, $user)) $out[$k] = $user[$k];
    }
    return $out;
}

/**
 * Resolve actor context.
 * - For login/register you can force actor id/role.
 * - Otherwise it tries token -> current user.
 */
private function actorContext(Request $request, ?int $forcedId = null, ?string $forcedRole = null): array
{
    $id = $forcedId ?? $this->currentUserId($request);

    $name = null;
    $role = $forcedRole;

    if ($id) {
        $u = DB::table('users')->select('id','name','role')->where('id', $id)->first();
        if ($u) {
            $name = $u->name ?? null;
            if (!$role) $role = $u->role ?? null;
        }
    }

    return [
        'id'   => $id ?: 0,
        'name' => $name,
        'role' => $role,
    ];
}

/**
 * Insert a log row into user_data_activity_log (best-effort).
 * Will only insert keys that exist in your table columns.
 */
private function logActivity(
    Request $request,
    string $module,
    string $activity,
    string $note,
    ?string $tableName = null,
    ?int $recordId = null,
    array $changedFields = [],
    $oldValues = null,
    $newValues = null,
    array $meta = [],
    ?int $forcedActorId = null,
    ?string $forcedActorRole = null
): void {
    try {
        $cols = $this->alColumns();
        if (empty($cols)) return;

        $actor = $this->actorContext($request, $forcedActorId, $forcedActorRole);

        $payload = [
            'performed_by'       => (int)($actor['id'] ?? 0),
            'performed_by_role'  => $actor['role'] ?? null,
            'performed_by_name'  => $actor['name'] ?? null,

            'module'             => $module,
            'activity'           => $activity,
            'log_note'           => $note,

            'table_name'         => $tableName,
            'record_id'          => $recordId,

            'endpoint'           => '/' . ltrim($request->path(), '/'),
            'method'             => $request->method(),
            'ip'                 => $request->ip(),
            'user_agent'         => substr((string)$request->userAgent(), 0, 500),

            'changed_fields'     => $this->safeJson($changedFields),
            'old_values'         => $this->safeJson($oldValues),
            'new_values'         => $this->safeJson($newValues),
            'meta_json'          => $this->safeJson($meta),

            'created_at'         => now(),
            'updated_at'         => now(), // will be filtered out if your table doesn't have it
        ];

        // Filter only existing columns (prevents SQL errors)
        $filtered = array_intersect_key($payload, array_flip($cols));

        DB::table('user_data_activity_log')->insert($filtered);
    } catch (\Throwable $e) {
        // never break main API
        Log::warning('[ActivityLog] insert failed', ['err' => $e->getMessage()]);
    }
}

    /* =========================================================
     |                       AUTH
     |=========================================================*/

    /**
     * POST /api/auth/login
     * Body: { email, password, remember?: bool }
     * Returns: { access_token, token_type, expires_at?, user: {...} }
     */
    public function login(Request $request)
{
    Log::info('[Auth Login] begin', ['ip' => $request->ip()]);

    $validated = $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
        'remember' => 'sometimes|boolean',
    ]);

    $user = DB::table('users')
        ->where('email', $validated['email'])
        ->whereNull('deleted_at')
        ->first();

    if (!$user) {
        $this->logActivity(
            $request,
            'users',
            'login_failed',
            'Login failed: user not found',
            'users',
            null,
            [],
            null,
            null,
            ['email' => $validated['email']],
            0,
            null
        );

        Log::warning('[Auth Login] user not found', ['email' => $validated['email']]);
        return response()->json(['status'=>'error','message'=>'Invalid credentials'], 401);
    }

    if (isset($user->status) && $user->status !== 'active') {
        $this->logActivity(
            $request,
            'users',
            'login_failed',
            'Login failed: inactive user',
            'users',
            (int)$user->id,
            [],
            null,
            null,
            ['status' => $user->status],
            (int)$user->id,
            (string)($user->role ?? null)
        );

        Log::warning('[Auth Login] inactive user', ['user_id'=>$user->id,'status'=>$user->status]);
        return response()->json(['status'=>'error','message'=>'Account is not active'], 403);
    }

    if (!Hash::check($validated['password'], $user->password)) {
        $this->logActivity(
            $request,
            'users',
            'login_failed',
            'Login failed: password mismatch',
            'users',
            (int)$user->id,
            [],
            null,
            null,
            ['email' => $validated['email']],
            (int)$user->id,
            (string)($user->role ?? null)
        );

        Log::warning('[Auth Login] password mismatch', ['user_id'=>$user->id]);
        return response()->json(['status'=>'error','message'=>'Invalid credentials'], 401);
    }

    $remember  = (bool)($validated['remember'] ?? false);
    $expiresAt = $remember ? now()->addDays(30) : now()->addHours(12);

    $plainToken = $this->issueToken((int)$user->id, $expiresAt);

    DB::table('users')->where('id', $user->id)->update([
        'last_login_at' => now(),
        'last_login_ip' => $request->ip(),
        'updated_at'    => now(),
    ]);

    // activity log: success
    $this->logActivity(
        $request,
        'users',
        'login',
        'Login successful',
        'users',
        (int)$user->id,
        ['last_login_at','last_login_ip'],
        null,
        null,
        ['remember' => $remember],
        (int)$user->id,
        (string)($user->role ?? null)
    );

    $payloadUser = $this->publicUserPayload($user);

    Log::info('[Auth Login] success', ['user_id'=>$user->id,'role'=>$payloadUser['role'] ?? null]);

    return response()->json([
        'status'       => 'success',
        'message'      => 'Login successful',
        'access_token' => $plainToken,
        'token_type'   => 'Bearer',
        'expires_at'   => $expiresAt->toIso8601String(),
        'user'         => $payloadUser,
    ]);
}

    /**
     * POST /api/auth/logout
     * Header: Authorization: Bearer <token>
     */
    public function logout(Request $request)
{
    Log::info('[Auth Logout] begin', ['ip' => $request->ip()]);

    $plain = $this->extractToken($request);
    if (!$plain) {
        $this->logActivity(
            $request,
            'users',
            'logout_failed',
            'Logout failed: token not provided',
            'personal_access_tokens',
            null
        );

        Log::warning('[Auth Logout] missing token');
        return response()->json(['status'=>'error','message'=>'Token not provided'], 401);
    }

    $deleted = DB::table('personal_access_tokens')
        ->where('token', hash('sha256', $plain))
        ->where('tokenable_type', self::USER_TYPE)
        ->delete();

    $this->logActivity(
        $request,
        'users',
        'logout',
        $deleted ? 'Logout successful' : 'Logout failed: invalid token',
        'personal_access_tokens',
        null,
        [],
        null,
        null,
        ['deleted' => (bool)$deleted]
    );

    Log::info('[Auth Logout] token removed', ['deleted'=>(bool)$deleted]);

    return response()->json([
        'status'  => $deleted ? 'success' : 'error',
        'message' => $deleted ? 'Logged out successfully' : 'Invalid token',
    ], $deleted ? 200 : 401);
}

    /**
     * GET /api/auth/check
     * Header: Authorization: Bearer <token>
     * Returns user if token valid (and not expired).
     */
    public function authenticateToken(Request $request)
    {
        $plain = $this->extractToken($request);
        if (!$plain) {
            return response()->json(['status'=>'error','message'=>'Token not provided'], 401);
        }

        $rec = DB::table('personal_access_tokens')
            ->where('token', hash('sha256', $plain))
            ->where('tokenable_type', self::USER_TYPE)
            ->first();

        if (!$rec) {
            return response()->json(['status'=>'error','message'=>'Invalid token'], 401);
        }

        // Expiration check (if set)
        if (!empty($rec->expires_at) && Carbon::parse($rec->expires_at)->isPast()) {
            // Token expired: delete it
            DB::table('personal_access_tokens')->where('id', $rec->id)->delete();
            return response()->json(['status'=>'error','message'=>'Token expired'], 401);
        }

        $user = DB::table('users')
            ->where('id', $rec->tokenable_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user || (isset($user->status) && $user->status !== 'active')) {
            return response()->json(['status'=>'error','message'=>'Unauthorized'], 401);
        }

        return response()->json([
            'status' => 'success',
            'user'   => $this->publicUserPayload($user),
        ]);
    }

    /* =========================================================
     |                       USERS CRUD
     |=========================================================*/

    /**
     * POST /api/users
     * Create user (with optional image). Stores image in /Public/UserProfileImage.
     */
    public function store(Request $request)
{
    $v = Validator::make($request->all(), [
        'name'                     => 'required|string|max:150',
        'email'                    => 'required|email|max:255',
        'password'                 => 'required|string|min:8',
        'phone_number'             => 'sometimes|nullable|string|max:32',
        'alternative_email'        => 'sometimes|nullable|email|max:255',
        'alternative_phone_number' => 'sometimes|nullable|string|max:32',
        'whatsapp_number'          => 'sometimes|nullable|string|max:32',
        'address'                  => 'sometimes|nullable|string',
        'role'                     => 'sometimes|nullable|string|max:50',
        'role_short_form'          => 'sometimes|nullable|string|max:10',
        'status'                   => 'sometimes|in:active,inactive',
        'image'                    => 'sometimes|file|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
    ]);
    if ($v->fails()) {
        return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
    }
    $data = $v->validated();

    if (DB::table('users')->where('email', $data['email'])->exists()) {
        return response()->json(['status'=>'error','message'=>'Email already exists'], 422);
    }
    if (!empty($data['phone_number']) &&
        DB::table('users')->where('phone_number', $data['phone_number'])->exists()) {
        return response()->json(['status'=>'error','message'=>'Phone number already exists'], 422);
    }

    do { $uuid = (string) Str::uuid(); }
    while (DB::table('users')->where('uuid', $uuid)->exists());

    $base = Str::slug($data['name']);
    do { $slug = $base . '-' . Str::lower(Str::random(24)); }
    while (DB::table('users')->where('slug', $slug)->exists());

    [$role, $roleShort] = $this->normalizeRole(
        $data['role'] ?? 'student',
        $data['role_short_form'] ?? null
    );

    $imageUrl = null;
    if ($request->hasFile('image')) {
        $imageUrl = $this->saveProfileImage($request->file('image'));
        if ($imageUrl === false) {
            return response()->json(['status'=>'error','message'=>'Invalid image upload'], 422);
        }
    }

    $createdBy = $this->currentUserId($request);

    try {
        $now = now();
        DB::table('users')->insert([
            'uuid'                     => $uuid,
            'name'                     => $data['name'],
            'email'                    => $data['email'],
            'phone_number'             => $data['phone_number'] ?? null,
            'alternative_email'        => $data['alternative_email'] ?? null,
            'alternative_phone_number' => $data['alternative_phone_number'] ?? null,
            'whatsapp_number'          => $data['whatsapp_number'] ?? null,
            'password'                 => Hash::make($data['password']),
            'image'                    => $imageUrl,
            'address'                  => $data['address'] ?? null,
            'role'                     => $role,
            'role_short_form'          => $roleShort,
            'slug'                     => $slug,
            'status'                   => $data['status'] ?? 'active',
            'remember_token'           => Str::random(60),
            'created_by'               => $createdBy,
            'created_at'               => $now,
            'created_at_ip'            => $request->ip(),
            'updated_at'               => $now,
            'metadata'                 => json_encode([
                'timezone' => 'Asia/Kolkata',
                'source'   => 'api_store',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $user = DB::table('users')->where('email', $data['email'])->first();

        // activity log
        $this->logActivity(
            $request,
            'users',
            'create',
            'User created: '.$data['name'].' ('.$data['email'].')',
            'users',
            (int)$user->id,
            array_keys($this->userSnapshot($user)),
            null,
            $this->userSnapshot($user),
            ['source' => 'api_store']
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'User created',
            'user'    => $this->publicUserPayload($user),
        ], 201);

    } catch (\Throwable $e) {
        if ($imageUrl) $this->deleteManagedProfileImage($imageUrl);
        Log::error('[Users Store] failed', ['error'=>$e->getMessage()]);
        return response()->json(['status'=>'error','message'=>'Could not create user'], 500);
    }
}

    /**
     * GET /api/users/all?q=&status=&limit=
     * Lightweight list (no pagination).
     */
    public function all(Request $request)
    {
        $q      = trim((string)$request->query('q', ''));
        $status = (string)$request->query('status', 'active'); // '' to disable filter
        $limit  = min(1000, max(1, (int)$request->query('limit', 1000)));

        $rows = DB::table('users')
            ->whereNull('deleted_at')
            ->when($status !== '', fn($w) => $w->where('status', $status))
            ->when($q !== '', function($w) use ($q){
                $like = "%{$q}%";
                $w->where(function($x) use ($like){
                    $x->where('name','LIKE',$like)->orWhere('email','LIKE',$like);
                });
            })
            ->select('id','name','email','image','role','role_short_form','status')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return response()->json([
            'status'=>'success',
            'data'  => $rows,
            'meta'  => ['count' => $rows->count()],
        ]);
    }

    /**
 * GET /api/users?page=&per_page=&q=&status=
 * Paginated list.
 */
public function index(Request $request)
{
    $page   = max(1, (int)$request->query('page', 1));
    $pp     = min(100, max(1, (int)$request->query('per_page', 20)));
    $q      = trim((string)$request->query('q', ''));

    // NEW: Default is 'all' -> show both active & inactive
    $status = $request->query('status', 'all');

    $base = DB::table('users')->whereNull('deleted_at');

    // Apply filter only if status is NOT 'all' and not empty
    if ($status !== 'all' && $status !== '') {
        $base->where('status', $status);
    }

    if ($q !== '') {
        $like = "%{$q}%";
        $base->where(function($w) use ($like){
            $w->where('name', 'LIKE', $like)
              ->orWhere('email', 'LIKE', $like);
        });
    }

    $total = (clone $base)->count();

    $rows  = $base->orderBy('name')
        ->offset(($page - 1) * $pp)
        ->limit($pp)
        ->select('id','name','email','image','role','role_short_form','status')
        ->get();

    return response()->json([
        'status' => 'success',
        'data'   => $rows,
        'meta'   => [
            'page'        => $page,
            'per_page'    => $pp,
            'total'       => $total,
            'total_pages' => (int)ceil($total / $pp),
        ],
    ]);
}

    /**
     * GET /api/users/{id}
     */
    public function show(Request $request, int $id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json(['status'=>'error','message'=>'User not found'], 404);
        }

        return response()->json([
            'status'=>'success',
            'user'  => [
                'id' => (int)$user->id,
                'uuid' => (string)($user->uuid ?? ''),
                'name' => (string)($user->name ?? ''),
                'email' => (string)($user->email ?? ''),
                'phone_number' => (string)($user->phone_number ?? ''),
                'alternative_email' => (string)($user->alternative_email ?? ''),
                'alternative_phone_number' => (string)($user->alternative_phone_number ?? ''),
                'whatsapp_number' => (string)($user->whatsapp_number ?? ''),
                'image' => (string)($user->image ?? ''),
                'address' => (string)($user->address ?? ''),
                'role' => (string)($user->role ?? ''),
                'role_short_form' => (string)($user->role_short_form ?? ''),
                'slug' => (string)($user->slug ?? ''),
                'status' => (string)($user->status ?? ''),
                'last_login_at' => (string)($user->last_login_at ?? ''),
                'last_login_ip' => (string)($user->last_login_ip ?? ''),
                'created_by' => $user->created_by,
                'created_at' => (string)$user->created_at,
                'updated_at' => (string)$user->updated_at,
                'deleted_at' => (string)($user->deleted_at ?? ''),
            ],
        ]);
    }

    /**
     * PUT/PATCH /api/users/{id}
     * Partial update. If name changes, slug is regenerated.
     */
    public function update(Request $request, int $id)
{
    $v = Validator::make($request->all(), [
        'name'                     => 'sometimes|string|max:150',
        'email'                    => 'sometimes|email|max:255',
        'phone_number'             => 'sometimes|nullable|string|max:32',
        'alternative_email'        => 'sometimes|nullable|email|max:255',
        'alternative_phone_number' => 'sometimes|nullable|string|max:32',
        'whatsapp_number'          => 'sometimes|nullable|string|max:32',
        'address'                  => 'sometimes|nullable|string',
        'role'                     => 'sometimes|nullable|string|max:50',
        'role_short_form'          => 'sometimes|nullable|string|max:10',
        'status'                   => 'sometimes|in:active,inactive',
        'image'                    => 'sometimes|file|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
    ]);
    if ($v->fails()) {
        return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
    }
    $data = $v->validated();

    $existing = DB::table('users')->where('id', $id)->whereNull('deleted_at')->first();
    if (!$existing) {
        return response()->json(['status'=>'error','message'=>'User not found'], 404);
    }

    $oldSnap = $this->userSnapshot($existing);

    if (array_key_exists('email', $data)) {
        if (DB::table('users')->where('email', $data['email'])->where('id','!=',$id)->exists()) {
            return response()->json(['status'=>'error','message'=>'Email already exists'], 422);
        }
    }
    if (array_key_exists('phone_number', $data) && !empty($data['phone_number'])) {
        if (DB::table('users')->where('phone_number', $data['phone_number'])->where('id','!=',$id)->exists()) {
            return response()->json(['status'=>'error','message'=>'Phone number already exists'], 422);
        }
    }

    $updates = [];
    foreach ([
        'name','email','phone_number','alternative_email','alternative_phone_number',
        'whatsapp_number','address','status'
    ] as $key) {
        if (array_key_exists($key, $data)) {
            $updates[$key] = $data[$key];
        }
    }

    if (array_key_exists('role', $data) || array_key_exists('role_short_form', $data)) {
        [$normRole, $normShort] = $this->normalizeRole(
            $data['role'] ?? $existing->role,
            $data['role_short_form'] ?? $existing->role_short_form
        );
        $updates['role'] = $normRole;
        $updates['role_short_form'] = $normShort;
    }

    if (array_key_exists('name', $updates) && $updates['name'] !== $existing->name) {
        $base = Str::slug($updates['name']);
        do { $slug = $base . '-' . Str::lower(Str::random(24)); }
        while (DB::table('users')->where('slug', $slug)->where('id','!=',$id)->exists());
        $updates['slug'] = $slug;
    }

    if ($request->hasFile('image')) {
        $newUrl = $this->saveProfileImage($request->file('image'));
        if ($newUrl === false) {
            return response()->json(['status'=>'error','message'=>'Invalid image upload'], 422);
        }
        $this->deleteManagedProfileImage($existing->image);
        $updates['image'] = $newUrl;
    }

    if (empty($updates)) {
        return response()->json(['status'=>'error','message'=>'Nothing to update'], 400);
    }

    $updates['updated_at'] = now();
    DB::table('users')->where('id', $id)->update($updates);

    $fresh = DB::table('users')->where('id', $id)->first();
    $newSnap = $this->userSnapshot($fresh);

    $changed = array_values(array_filter(array_keys($updates), fn($k)=>$k !== 'updated_at'));

    $this->logActivity(
        $request,
        'users',
        'update',
        'User updated: ID '.$id,
        'users',
        (int)$id,
        $changed,
        $oldSnap,
        $newSnap
    );

    return response()->json([
        'status'=>'success',
        'message'=>'User updated',
        'user'=>$this->publicUserPayload($fresh),
    ]);
}

    /**
     * DELETE /api/users/{id}
     * Soft delete (prevents self-delete).
     */
    public function destroy(Request $request, int $id)
{
    $actorId = $this->currentUserId($request);
    if ($actorId !== null && $actorId === $id) {
        return response()->json(['status'=>'error','message'=>"You can't delete your own account"], 422);
    }

    $user = DB::table('users')->where('id', $id)->whereNull('deleted_at')->first();
    if (!$user) {
        return response()->json(['status'=>'error','message'=>'User not found'], 404);
    }

    $oldSnap = $this->userSnapshot($user);

    DB::table('users')->where('id', $id)->update([
        'deleted_at' => now(),
        'status'     => 'inactive',
        'updated_at' => now(),
    ]);

    $fresh = DB::table('users')->where('id', $id)->first();
    $newSnap = $this->userSnapshot($fresh);

    $this->logActivity(
        $request,
        'users',
        'delete',
        'User soft-deleted: ID '.$id,
        'users',
        (int)$id,
        ['deleted_at','status'],
        $oldSnap,
        $newSnap
    );

    return response()->json(['status'=>'success','message'=>'User soft-deleted']);
}


    /**
     * POST /api/users/{id}/restore
     */
    public function restore(Request $request, int $id)
{
    $user = DB::table('users')->where('id', $id)->whereNotNull('deleted_at')->first();
    if (!$user) {
        return response()->json(['status'=>'error','message'=>'User not found or not deleted'], 404);
    }

    $oldSnap = $this->userSnapshot($user);

    DB::table('users')->where('id', $id)->update([
        'deleted_at' => null,
        'status'     => 'active',
        'updated_at' => now(),
    ]);

    $fresh = DB::table('users')->where('id', $id)->first();
    $newSnap = $this->userSnapshot($fresh);

    $this->logActivity(
        $request,
        'users',
        'restore',
        'User restored: ID '.$id,
        'users',
        (int)$id,
        ['deleted_at','status'],
        $oldSnap,
        $newSnap
    );

    return response()->json(['status'=>'success','message'=>'User restored']);
}


    /**
     * DELETE /api/users/{id}/force
     * Permanently delete (also removes managed profile image).
     */
    public function forceDelete(Request $request, int $id)
    {
        $actorId = $this->currentUserId($request);
        if ($actorId !== null && $actorId === $id) {
            return response()->json(['status'=>'error','message'=>"You can't delete your own account"], 422);
        }

        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return response()->json(['status'=>'error','message'=>'User not found'], 404);
        }

        $this->deleteManagedProfileImage($user->image);

        DB::table('users')->where('id', $id)->delete();

        return response()->json(['status'=>'success','message'=>'User permanently deleted']);
    }

    /**
     * PATCH /api/users/{id}/password
     * Body: { password }
     */
   public function updatePassword(Request $request, int $id)
{
    $v = Validator::make($request->all(), [
        'password' => 'required|string|min:8',
    ]);
    if ($v->fails()) {
        return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
    }

    $user = DB::table('users')->where('id', $id)->whereNull('deleted_at')->first();
    if (!$user) {
        return response()->json(['status'=>'error','message'=>'User not found'], 404);
    }

    DB::table('users')->where('id', $id)->update([
        'password'   => Hash::make($v->validated()['password']),
        'updated_at' => now(),
    ]);

    $this->logActivity(
        $request,
        'users',
        'update_password',
        'Password updated for user ID '.$id,
        'users',
        (int)$id,
        ['password'],
        null,
        null,
        ['note' => 'password not stored in logs']
    );

    return response()->json(['status'=>'success','message'=>'Password updated']);
}


    /**
     * POST /api/users/{id}/image
     * file: image (multipart/form-data)
     */
    public function updateImage(Request $request, int $id)
{
    $v = Validator::make($request->all(), [
        'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
    ]);
    if ($v->fails()) {
        return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
    }

    $user = DB::table('users')->where('id', $id)->whereNull('deleted_at')->first();
    if (!$user) {
        return response()->json(['status'=>'error','message'=>'User not found'], 404);
    }

    $oldSnap = $this->userSnapshot($user);

    $newUrl = $this->saveProfileImage($request->file('image'));
    if ($newUrl === false) {
        return response()->json(['status'=>'error','message'=>'Invalid image upload'], 422);
    }

    $this->deleteManagedProfileImage($user->image);

    DB::table('users')->where('id', $id)->update([
        'image'      => $newUrl,
        'updated_at' => now(),
    ]);

    $fresh = DB::table('users')->where('id', $id)->first();
    $newSnap = $this->userSnapshot($fresh);

    $this->logActivity(
        $request,
        'users',
        'update_image',
        'Profile image updated: ID '.$id,
        'users',
        (int)$id,
        ['image'],
        $oldSnap,
        $newSnap
    );

    return response()->json([
        'status'=>'success',
        'message'=>'Image updated',
        'user'=>$this->publicUserPayload($fresh),
    ]);
}

        /**
     * GET /api/auth/my-role
     * Header: Authorization: Bearer <token>
     *
     * Returns:
     * {
     *   "status": "success",
     *   "role": "admin",
     *   "role_short_form": "ADM",
     *   "user": { ... public payload ... }
     * }
     */
    public function getMyRole(Request $request)
    {
        $plain = $this->extractToken($request);
        if (!$plain) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token not provided',
            ], 401);
        }

        $rec = DB::table('personal_access_tokens')
            ->where('token', hash('sha256', $plain))
            ->where('tokenable_type', self::USER_TYPE)
            ->first();

        if (!$rec) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid token',
            ], 401);
        }

        // Check expiry (same logic as authenticateToken)
        if (!empty($rec->expires_at) && Carbon::parse($rec->expires_at)->isPast()) {
            DB::table('personal_access_tokens')->where('id', $rec->id)->delete();

            return response()->json([
                'status'  => 'error',
                'message' => 'Token expired',
            ], 401);
        }

        $user = DB::table('users')
            ->where('id', $rec->tokenable_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user || (isset($user->status) && $user->status !== 'active')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'status'          => 'success',
            'role'            => (string)($user->role ?? ''),
            'role_short_form' => (string)($user->role_short_form ?? ''),
            'user'            => $this->publicUserPayload($user),
        ]);
    }


    /* =========================================================
     |                     Helper methods
     |=========================================================*/

    /** Issue a personal access token; returns the plain token. */
    protected function issueToken(int $userId, ?Carbon $expiresAt = null): string
    {
        $plain = bin2hex(random_bytes(40));

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => self::USER_TYPE,
            'tokenable_id'   => $userId,
            'name'           => 'w3t_user_token',
            'token'          => hash('sha256', $plain),
            'abilities'      => json_encode(['*']),
            'last_used_at'   => null,
            'expires_at'     => $expiresAt ? $expiresAt->toDateTimeString() : null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return $plain;
    }

    /** Extract Bearer token from Authorization header. */
    protected function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $m)) {
            return null;
        }
        return $m[1];
    }

    /** Resolve current user id from the provided Bearer token. */
    protected function currentUserId(Request $request): ?int
    {
        $plain = $this->extractToken($request);
        if (!$plain) return null;

        $rec = DB::table('personal_access_tokens')
            ->where('token', hash('sha256', $plain))
            ->where('tokenable_type', self::USER_TYPE)
            ->first();

        return $rec ? (int)$rec->tokenable_id : null;
    }

    /** Public payload sent to FE (no sensitive fields). */
    protected function publicUserPayload(object $user): array
    {
        return [
            'id'              => (int)$user->id,
            'uuid'            => (string)($user->uuid ?? ''),
            'name'            => (string)($user->name ?? ''),
            'email'           => (string)($user->email ?? ''),
            'role'            => (string)($user->role ?? ''),
            'role_short_form' => (string)($user->role_short_form ?? ''),
            'slug'            => (string)($user->slug ?? ''),
            'image'           => (string)($user->image ?? ''),
            'status'          => (string)($user->status ?? ''),
        ];
    }

    /**
     * Normalize role & short code against allowed set.
     * Accepts synonyms like "super admin", "super-admin", "sa", "students" -> "student".
     */
    protected function normalizeRole(?string $role, ?string $short = null): array
    {
        $r = Str::of((string)$role)->lower()->trim()->toString();

        // common synonyms
        $map = [
            'super admin' => 'super_admin',
            'super-admin' => 'super_admin',
            'superadmin'  => 'super_admin',
            'sa'          => 'super_admin',
            'administrator'=> 'admin',
            'students'    => 'student',
            'std'         => 'student',
            'teacher'     => 'instructor',
            'writer'      => 'author',
        ];

        if (isset($map[$r])) $r = $map[$r];

        if (!in_array($r, self::ROLES, true)) {
            // fallback to student
            $r = 'student';
        }

        $short = $short ?: self::ROLE_SHORT[$r] ?? 'STD';
        return [$r, strtoupper($short)];
    }

    /** Save profile image into /Public/UserProfileImage and return absolute URL (or false on failure). */
    protected function saveProfileImage($uploadedFile)
    {
        if (!$uploadedFile || !$uploadedFile->isValid()) return false;

        $destDir = public_path('UserProfileImage');
        if (!File::isDirectory($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        $ext      = strtolower($uploadedFile->getClientOriginalExtension() ?: 'bin');
        $filename = 'usr_' . date('Ymd_His') . '_' . Str::lower(Str::random(16)) . '.' . $ext;

        $uploadedFile->move($destDir, $filename);

        return rtrim(config('app.url'), '/') . '/UserProfileImage/' . $filename;
    }

    /** Delete a managed profile image if it resides in /Public/UserProfileImage. */
    protected function deleteManagedProfileImage(?string $url): void
    {
        if (empty($url)) return;
        $pathUrl = parse_url($url, PHP_URL_PATH) ?? '';
        if (Str::startsWith($pathUrl, '/UserProfileImage/')) {
            $abs = public_path(ltrim($pathUrl, '/'));
            if (File::exists($abs)) {
                @File::delete($abs);
            }
        }
    }
    /**
 * POST /api/auth/register
 * Body: { name, email, phone_number, password, password_confirmation }
 * Always registers as role = student
 */
public function register(Request $request)
{
    $v = Validator::make($request->all(), [
        'name'              => 'required|string|max:150',
        'email'             => 'required|email|max:255',
        'phone_number'      => 'required|string|max:32',
        'password'          => 'required|string|min:8|confirmed',
    ]);

    if ($v->fails()) {
        return response()->json(['status' => 'error', 'errors' => $v->errors()], 422);
    }

    $data = $v->validated();

    if (DB::table('users')->where('email', $data['email'])->exists()) {
        return response()->json(['status'=>'error','message'=>'Email already exists'], 422);
    }
    if (!empty($data['phone_number']) &&
        DB::table('users')->where('phone_number', $data['phone_number'])->exists()) {
        return response()->json(['status'=>'error','message'=>'Phone number already exists'], 422);
    }

    do { $uuid = (string) Str::uuid(); }
    while (DB::table('users')->where('uuid', $uuid)->exists());

    $base = Str::slug($data['name']);
    do { $slug = $base . '-' . Str::lower(Str::random(24)); }
    while (DB::table('users')->where('slug', $slug)->exists());

    [$role, $roleShort] = $this->normalizeRole('student', null);

    try {
        $now = now();
        $userId = DB::table('users')->insertGetId([
            'uuid'                     => $uuid,
            'name'                     => $data['name'],
            'email'                    => $data['email'],
            'phone_number'             => $data['phone_number'],
            'password'                 => Hash::make($data['password']),
            'image'                    => null,
            'address'                  => null,
            'role'                     => $role,
            'role_short_form'          => $roleShort,
            'slug'                     => $slug,
            'status'                   => 'active',
            'remember_token'           => Str::random(60),
            'created_by'               => null,
            'created_at'               => $now,
            'created_at_ip'            => $request->ip(),
            'updated_at'               => $now,
            'metadata'                 => json_encode([
                'timezone' => 'Asia/Kolkata',
                'source'   => 'api_register',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $user = DB::table('users')->where('id', $userId)->first();

        $expiresAt = now()->addHours(12);
        $plainToken = $this->issueToken((int)$userId, $expiresAt);

        DB::table('users')->where('id', $userId)->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'updated_at'    => now(),
        ]);

        // activity log
        $this->logActivity(
            $request,
            'users',
            'register',
            'Registration successful',
            'users',
            (int)$userId,
            ['name','email','phone_number','role','role_short_form','slug','status'],
            null,
            $this->userSnapshot($user),
            ['source' => 'api_register'],
            (int)$userId,
            (string)($user->role ?? 'student')
        );

        return response()->json([
            'status'       => 'success',
            'message'      => 'Registration successful',
            'access_token' => $plainToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $expiresAt->toIso8601String(),
            'user'         => $this->publicUserPayload($user),
        ], 201);

    } catch (\Throwable $e) {
        Log::error('[Auth Register] failed', ['error' => $e->getMessage()]);
        return response()->json(['status'=>'error','message'=>'Could not register user'], 500);
    }
}

public function getProfile(Request $request)
{
    $userId = $this->currentUserId($request);

    if (!$userId) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized'
        ], 401);
    }

    $user = DB::table('users')->where('id', $userId)->first();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }

    // Role-based frontend permissions
    $isEditable = in_array($user->role, ['admin', 'super_admin','student','instructor']);

    $permissions = [
        'can_edit_profile'   => $isEditable,
        'can_change_image'   => $isEditable,
        'can_change_password'=> $isEditable,
        'can_view_profile'   => true
    ];

    // API endpoints to be used by frontend
    $endpoints = [
        'update_profile' => "/api/users/{$user->id}",
        'update_image'   => "/api/users/{$user->id}/image",
        'update_password'=> "/api/users/{$user->id}/password"
    ];

    return response()->json([
        'status' => 'success',
        'user' => [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'phone_number'    => $user->phone_number,
            'address'         => $user->address,
            'role'            => $user->role,
            'role_short_form' => $user->role_short_form,
            'image'           => $user->image,
            'status'          => $user->status,
        ],
        'permissions' => $permissions,
        'endpoints' => $endpoints
    ]);
}

}
