<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class BatchController extends Controller
{
    /* =========================================================
     |                       Helpers
     |=========================================================*/

    /** Extract user id from Bearer token (Sanctum-style hashed token) */
    protected function authUserIdFromToken(Request $request): ?int
    {
        $auth = $request->header('Authorization', '');
        if (!preg_match('/Bearer\s+(.+)/i', $auth, $m)) return null;
        $plain = trim($m[1]); $hash = hash('sha256', $plain);

        $tok = DB::table('personal_access_tokens')
            ->select('tokenable_id')
            ->where('token', $hash)
            ->first();

        return $tok ? (int)$tok->tokenable_id : null;
    }

    /** Build absolute app URL */
    protected function appUrl(Request $request): string
    {
        $cfg = rtrim(config('app.url', ''), '/');
        if ($cfg) return $cfg;
        return rtrim($request->getSchemeAndHttpHost(), '/');
    }

    /** Save feature image to public/batchFeatureImage and return absolute URL */
    protected function saveFeatureImage(Request $request, string $inputName = 'featured_image'): ?string
    {
        if (!$request->hasFile($inputName)) return null;
        $file = $request->file($inputName);
        if (!$file->isValid()) return null;

        $dir = public_path('batchFeatureImage');
        if (!File::exists($dir)) File::makeDirectory($dir, 0755, true);

        $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = 'batch_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $ext;

        $file->move($dir, $name);
        return $this->appUrl($request) . '/batchFeatureImage/' . $name;
    }

    /** Remove old feature image if it’s inside /batchFeatureImage */
    protected function deleteOldFeatureImage(?string $url): void
    {
        if (!$url) return;
        $prefix = '/batchFeatureImage/';
        $pos = strpos($url, $prefix);
        if ($pos === false) return; // Only delete our own files
        $relative = substr($url, $pos + 1); // drop first slash
        $path = public_path($relative);
        if (File::exists($path)) @unlink($path);
    }

    /** Format duration between two timestamps as “X months” or “Y year Z months” */
    protected function humanDuration(?string $start, ?string $end): ?string
    {
        if (!$start || !$end) return null;
        try {
            $s = Carbon::parse($start);
            $e = Carbon::parse($end);
            if ($e->lt($s)) return null;

            $months = $s->diffInMonths($e);
            if ($months < 12) {
                return $months . ' ' . ($months === 1 ? 'month' : 'months');
            }
            $years   = intdiv($months, 12);
            $rem     = $months % 12;
            $yearStr = $years . ' ' . ($years === 1 ? 'year' : 'years');
            return $rem > 0 ? ($yearStr . ' ' . $rem . ' ' . ($rem === 1 ? 'month' : 'months')) : $yearStr;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Fetch a batch by id or uuid (not soft deleted by default) */
    protected function findBatch($idOrUuid, bool $withTrashed = false)
    {
        $q = DB::table('batches');
        if (!$withTrashed) $q->whereNull('deleted_at');

        if (is_numeric($idOrUuid)) {
            $q->where('id', (int)$idOrUuid);
        } else {
            $q->where('uuid', (string)$idOrUuid);
        }
        return $q->first();
    }

    /** Normalize any date/datetime into 'Y-m-d H:i:s'. Clamp pure dates to day start/end. */
    protected function normDateToDb(?string $val, bool $isStart = true): ?string
    {
        if (!$val) return null;
        try {
            $dt = Carbon::parse($val);
            if (!preg_match('/\d{2}:\d{2}:\d{2}/', (string)$val)) {
                $dt = $isStart ? $dt->startOfDay() : $dt->endOfDay();
            }
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Same as normDateToDb but returns Carbon for comparisons */
    protected function normDateToCarbon(?string $val, bool $isStart = true): ?Carbon
    {
        $s = $this->normDateToDb($val, $isStart);
        return $s ? Carbon::parse($s) : null;
    }

    protected function daysDuration(?string $start, ?string $end, bool $inclusive = true): ?int
{
    if (!$start || !$end) return null;
    try {
        $s = \Carbon\Carbon::parse($start)->startOfDay();
        $e = \Carbon\Carbon::parse($end)->startOfDay();
        if ($e->lt($s)) return null;
        $days = $s->diffInDays($e);
        return $inclusive ? ($days + 1) : $days;
    } catch (\Throwable $e) {
        return null;
    }
}


    
    /* =========================================================
     |                       Batches
     |=========================================================*/

    /**
     * GET /api/batches?course_id=...&status=&q=&sort=-created_at&per_page=20&page=1&include_deleted=0&only_deleted=0
     * Requires course_id (per UX: select course first).
     */
    public function index(Request $request)
    {
        $v = Validator::make($request->all(), [
            'course_id'       => 'required|integer|exists:courses,id',
            'status'          => 'nullable|string|in:active,inactive,archived',
            'q'               => 'nullable|string|max:255',
            'sort'            => 'nullable|string|max:64',     // e.g., -created_at, badge_title
            'per_page'        => 'nullable|integer|min:1|max:200',
            'page'            => 'nullable|integer|min:1',
            'include_deleted' => 'nullable|boolean',
            'only_deleted'    => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $perPage = (int)($request->input('per_page', 20));
        $page    = (int)($request->input('page', 1));
        $sort    = (string)$request->input('sort', '-created_at');

        $allowedSorts = ['id','badge_title','created_at','starts_at','status','mode'];
        $dir = 'asc'; $col = ltrim($sort, '-');
        if (!in_array($col, $allowedSorts, true)) $col = 'created_at';
        if (str_starts_with($sort, '-')) $dir = 'desc';

        $onlyDeleted    = $request->boolean('only_deleted');
        $includeDeleted = $request->boolean('include_deleted');

        $q = DB::table('batches')->where('course_id', (int)$request->course_id);

        if ($onlyDeleted) {
            $q->whereNotNull('deleted_at');
        } else {
            if (!$includeDeleted) $q->whereNull('deleted_at');
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $like = '%' . str_replace('%', '\\%', $request->q) . '%';
            $q->where(function($qq) use ($like) {
                $qq->where('badge_title', 'like', $like)
                   ->orWhere('tagline', 'like', $like);
            });
        }

        $total = (clone $q)->count();
        $rows  = $q->orderBy($col, $dir)
                   ->offset(($page - 1) * $perPage)
                   ->limit($perPage)
                   ->get();

        // UI helpers
        $data = $rows->map(function($r) {
            $r->group_links    = $r->group_links ? json_decode($r->group_links, true) : null;
            $r->metadata       = $r->metadata ? json_decode($r->metadata, true) : null;
$r->duration_human = $this->humanDuration($r->starts_at ?? null, $r->ends_at ?? null);
$r->duration_days  = $this->daysDuration($r->starts_at ?? null, $r->ends_at ?? null);

            return $r;
        });

        return response()->json([
            'success'   => true,
            'data'      => $data,
            'pagination'=> [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int)ceil(max(1, $total) / max(1, $perPage)),
            ]
        ]);
    }

    /** GET /api/batches/{idOrUuid} */
    public function show(Request $request, $idOrUuid)
    {
        $row = $this->findBatch($idOrUuid);
        if (!$row) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

        $row->group_links    = $row->group_links ? json_decode($row->group_links, true) : null;
        $row->metadata       = $row->metadata ? json_decode($row->metadata, true) : null;
      $row->duration_human = $this->humanDuration($row->starts_at ?? null, $row->ends_at ?? null);
$row->duration_days  = $this->daysDuration($row->starts_at ?? null, $row->ends_at ?? null);


        return response()->json(['success' => true, 'data' => $row]);
    }

    /** POST /api/batches */
    public function store(Request $request)
    {
        $uid = $this->authUserIdFromToken($request);
        if (!$uid) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $v = Validator::make($request->all(), [
            'course_id'         => 'required|integer|exists:courses,id',
            'badge_title'       => 'required|string|max:255',
            'badge_description' => 'nullable|string',
            'tagline'           => 'nullable|string|max:255',
            'mode'              => 'nullable|string|in:online,offline,hybrid',
            'group_links'       => 'nullable|array',
            'group_links.*'     => 'nullable|string|max:512',
            'contact_number'    => 'nullable|string|max:32',
            'badge_note'        => 'nullable|string',
            'status'            => 'nullable|string|in:active,inactive,archived',

            // Accept either *_on (dates) or *_at (datetimes); both validated as "date"
            'starts_on'         => 'nullable|date',
            'ends_on'           => 'nullable|date',
            'starts_at'         => 'nullable|date',
            'ends_at'           => 'nullable|date',

            'metadata'          => 'nullable|array',
            'featured_image'    => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        // Prefer *_on; fallback to *_at
        $startInput = $request->input('starts_on', $request->input('starts_at'));
        $endInput   = $request->input('ends_on',   $request->input('ends_at'));

        $startDb = $this->normDateToDb($startInput, true);
        $endDb   = $this->normDateToDb($endInput, false);

        if ($startInput && !$startDb) return response()->json(['success' => false, 'message' => 'Invalid start date'], 422);
        if ($endInput   && !$endDb)   return response()->json(['success' => false, 'message' => 'Invalid end date'], 422);
        if ($startDb && $endDb && Carbon::parse($endDb)->lt(Carbon::parse($startDb))) {
            return response()->json(['success' => false, 'message' => 'ends_at cannot be before starts_at'], 422);
        }

        try {
            $uuid   = (string) Str::uuid();
            $imgUrl = $this->saveFeatureImage($request, 'featured_image'); // absolute URL or null
            $now    = Carbon::now();
            $ip     = $request->ip();

            $gl = null;
if ($request->has('group_links') && is_array($request->group_links)) {
    $norm = [];
    foreach ($request->group_links as $k => $v) {
        $k = trim((string)$k);
        $v = trim((string)$v);
        if ($k !== '' && $v !== '') $norm[$k] = $v;
    }
    $gl = $norm ?: null;
}


            $payload = [
                'uuid'              => $uuid,
                'course_id'         => (int)$request->course_id,
                'badge_title'       => $request->badge_title,
                'badge_description' => $request->input('badge_description'),
                'tagline'           => $request->input('tagline'),
                'featured_image'    => $imgUrl, // null if not uploaded
                'mode'              => $request->input('mode', 'online'),
               'group_links' => $gl ? json_encode($gl, JSON_UNESCAPED_UNICODE) : null,
                'contact_number'    => $request->input('contact_number'),
                'badge_note'        => $request->input('badge_note'),
                'status'            => $request->input('status', 'active'),
                'starts_at'         => $startDb,
                'ends_at'           => $endDb,
               'metadata' => $request->exists('metadata') && !empty($request->metadata)
    ? json_encode($request->metadata, JSON_UNESCAPED_UNICODE)
    : null,
                'created_by'        => $uid,
                'created_at'        => $now,
                'created_at_ip'     => $ip,
                'updated_at'        => $now,
            ];

            $id  = DB::table('batches')->insertGetId($payload);
            $row = DB::table('batches')->where('id', $id)->first();
            $row->group_links    = $row->group_links ? json_decode($row->group_links, true) : null;
            $row->metadata       = $row->metadata ? json_decode($row->metadata, true) : null;
           $row->duration_human = $this->humanDuration($row->starts_at ?? null, $row->ends_at ?? null);
$row->duration_days  = $this->daysDuration($row->starts_at ?? null, $row->ends_at ?? null); // add this

            return response()->json(['success' => true, 'data' => $row], 201);
        } catch (\Throwable $e) {
            Log::error('Batch store failed', ['ex' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to create batch'], 500);
        }
    }

    /** PUT/PATCH /api/batches/{idOrUuid} */
public function update(Request $request, $idOrUuid)
{
    $batch = $this->findBatch($idOrUuid, true); // allow editing even if soft-deleted
    if (!$batch) {
        return response()->json(['success' => false, 'message' => 'Batch not found'], 404);
    }

    // Validation (group_links now key => value map)
    $v = Validator::make($request->all(), [
        'course_id'         => 'sometimes|integer|exists:courses,id',
        'badge_title'       => 'sometimes|string|max:255',
        'badge_description' => 'sometimes|nullable|string',
        'tagline'           => 'sometimes|nullable|string|max:255',
        'mode'              => 'sometimes|in:online,offline,hybrid',
        'group_links'       => 'sometimes|array',
        'group_links.*'     => 'nullable|string|max:512', // use 'url' if you want strict URLs
        'contact_number'    => 'sometimes|nullable|string|max:32',
        'badge_note'        => 'sometimes|nullable|string',
        'status'            => 'sometimes|in:active,inactive,archived',

        // allow either *_on (date) or *_at (datetime)
        'starts_on'         => 'sometimes|date',
        'ends_on'           => 'sometimes|date',
        'starts_at'         => 'sometimes|date',
        'ends_at'           => 'sometimes|date',

        'metadata'          => 'sometimes|array',
        'featured_image'    => 'sometimes|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
    ]);
    if ($v->fails()) {
        return response()->json(['success' => false, 'errors' => $v->errors()], 422);
    }

    // Normalize dates (prefer *_on, fallback *_at). Use exists() so clearing/omitting is distinguishable.
    $startInput = $request->exists('starts_on')
        ? $request->input('starts_on')
        : ($request->exists('starts_at') ? $request->input('starts_at') : null);

    $endInput = $request->exists('ends_on')
        ? $request->input('ends_on')
        : ($request->exists('ends_at') ? $request->input('ends_at') : null);

    $startDb = $startInput !== null ? $this->normDateToDb($startInput, true)  : null;
    $endDb   = $endInput   !== null ? $this->normDateToDb($endInput,   false) : null;

    if ($startInput !== null && !$startDb) {
        return response()->json(['success' => false, 'message' => 'Invalid start date'], 422);
    }
    if ($endInput !== null && !$endDb) {
        return response()->json(['success' => false, 'message' => 'Invalid end date'], 422);
    }
    if ($request->exists('metadata')) {
    $m = (array)$request->input('metadata', []);
    $upd['metadata'] = $m ? json_encode($m, JSON_UNESCAPED_UNICODE) : null;
}

    // Enforce: end date must be after start date (even if only one side is edited)
    $startForCheck = $startDb ?? $batch->starts_at;
    $endForCheck   = $endDb   ?? $batch->ends_at;
    if ($startForCheck && $endForCheck && Carbon::parse($endForCheck)->lt(Carbon::parse($startForCheck))) {
        return response()->json(['success' => false, 'message' => 'ends_at cannot be before starts_at'], 422);
    }

    $upd = [];

    // Non-nullable/simple fields
    foreach (['course_id', 'badge_title', 'mode', 'status'] as $k) {
        if ($request->exists($k)) {
            $upd[$k] = $request->input($k);
        }
    }

    // Nullable text fields — allow clearing to NULL
    foreach (['badge_description', 'tagline', 'contact_number', 'badge_note'] as $k) {
        if ($request->exists($k)) {
            $val = $request->input($k);
            $upd[$k] = (is_string($val) && trim($val) === '') ? null : $val;
        }
    }

    // Dates (only when provided)
    if ($startInput !== null) $upd['starts_at'] = $startDb;
    if ($endInput   !== null) $upd['ends_at']   = $endDb;

    // Group links map (platform => URL). If empty array/object provided, clear to NULL.
    if ($request->exists('group_links')) {
        $incoming = (array)$request->input('group_links', []);
        $norm = [];
        foreach ($incoming as $k => $v) {
            $k = trim((string)$k);
            $v = trim((string)$v);
            if ($k !== '' && $v !== '') $norm[$k] = $v;
        }
        $upd['group_links'] = $norm ? json_encode($norm, JSON_UNESCAPED_UNICODE) : null;
    }

    // Metadata JSON (only when present)
    if ($request->exists('metadata')) {
        $upd['metadata'] = json_encode($request->input('metadata'), JSON_UNESCAPED_UNICODE);
    }

    // Featured image replacement (if uploaded)
    if ($request->hasFile('featured_image')) {
        $newUrl = $this->saveFeatureImage($request, 'featured_image');
        if ($newUrl) {
            $this->deleteOldFeatureImage($batch->featured_image ?? null); // only delete if ours
            $upd['featured_image'] = $newUrl;
        }
    }

    if (empty($upd)) {
        // nothing changed; still return fresh for FE to reconcile
        $fresh = DB::table('batches')->where('id', $batch->id)->first();
        $fresh->group_links    = $fresh->group_links ? json_decode($fresh->group_links, true) : null;
        $fresh->metadata       = $fresh->metadata ? json_decode($fresh->metadata, true) : null;
        $fresh->duration_human = $this->humanDuration($fresh->starts_at ?? null, $fresh->ends_at ?? null);
        if (method_exists($this, 'daysDuration')) {
            $fresh->duration_days = $this->daysDuration($fresh->starts_at ?? null, $fresh->ends_at ?? null);
        }
        return response()->json(['success' => true, 'message' => 'Nothing changed', 'batch' => $fresh], 200);
    }

    $upd['updated_at'] = now();
    DB::table('batches')->where('id', $batch->id)->update($upd);

    $fresh = DB::table('batches')->where('id', $batch->id)->first();
    $fresh->group_links    = $fresh->group_links ? json_decode($fresh->group_links, true) : null;
    $fresh->metadata       = $fresh->metadata ? json_decode($fresh->metadata, true) : null;
    $fresh->duration_human = $this->humanDuration($fresh->starts_at ?? null, $fresh->ends_at ?? null);
    if (method_exists($this, 'daysDuration')) {
        $fresh->duration_days = $this->daysDuration($fresh->starts_at ?? null, $fresh->ends_at ?? null);
    }

    return response()->json([
        'success' => true,
        'message' => 'Batch saved',
        'batch'   => $fresh,
    ]);
}



    /** DELETE /api/batches/{idOrUuid} (soft delete) */
    public function destroy(Request $request, $idOrUuid)
    {
        $row = $this->findBatch($idOrUuid);
        if (!$row) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

        DB::table('batches')->where('id', $row->id)->update(['deleted_at' => Carbon::now()]);
        return response()->json(['success' => true, 'message' => 'Batch deleted']);
    }

    /** POST /api/batches/{idOrUuid}/restore */
    public function restore(Request $request, $idOrUuid)
    {
        $row = $this->findBatch($idOrUuid, true);
        if (!$row) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

        DB::table('batches')->where('id', $row->id)->update(['deleted_at' => null, 'updated_at' => Carbon::now()]);
        return response()->json(['success' => true, 'message' => 'Batch restored']);
    }

    /** PATCH /api/batches/{idOrUuid}/archive -> status=archived */
    public function archive(Request $request, $idOrUuid)
    {
        $row = $this->findBatch($idOrUuid);
        if (!$row) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

        DB::table('batches')->where('id', $row->id)->update([
            'status'     => 'archived',
            'updated_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Batch archived']);
    }

    /* =========================================================
     |                Existing Students (role=student)
     |=========================================================*/

    /**
     * GET /api/batches/{idOrUuid}/students?q=&per_page=20&page=1
     * Returns students list with "assigned" flag for the given batch (for toggle UI).
     */
public function studentsIndex(Request $request, $idOrUuid)
{
    $batch = $this->findBatch($idOrUuid);
    if (!$batch) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

    $v = Validator::make($request->all(), [
        'q'        => 'nullable|string|max:255',
        'per_page' => 'nullable|integer|min:1|max:200',
        'page'     => 'nullable|integer|min:1',
    ]);
    if ($v->fails()) {
        return response()->json(['success' => false, 'errors' => $v->errors()], 422);
    }

    $perPage  = (int)($request->input('per_page', 20));
    $page     = (int)($request->input('page', 1));

    // Detect which phone column exists in DB
    $phoneCol = Schema::hasColumn('users','phone_number') ? 'phone_number'
              : (Schema::hasColumn('users','phone') ? 'phone' : null);

    $u = DB::table('users')->whereNull('deleted_at')->where('role', 'student');

    if ($request->filled('q')) {
        $like = '%' . str_replace('%', '\\%', $request->q) . '%';
        $u->where(function($qq) use ($like, $phoneCol) {
            $qq->where('name', 'like', $like)
               ->orWhere('email', 'like', $like);
            if ($phoneCol) {
                $qq->orWhere($phoneCol, 'like', $like);
            }
        });
    }

    $total = (clone $u)->count();

    // Select with alias so frontend can read as "phone" regardless of column name
    $select = ['id','name','email'];
    if ($phoneCol) $select[] = DB::raw("$phoneCol as phone");

    $rows = $u->select($select)
        ->orderBy('name', 'asc')
        ->offset(($page - 1) * $perPage)
        ->limit($perPage)
        ->get();

    // Assigned flags for current page
    $ids = $rows->pluck('id')->all();
    $assigned = DB::table('batch_students')
        ->where('batch_id', $batch->id)
        ->whereNull('deleted_at')
        ->whereIn('user_id', $ids)
        ->pluck('user_id')
        ->all();

    $data = $rows->map(function($r) use ($assigned) {
        $r->assigned = in_array($r->id, $assigned, true);
        return $r;
    });

    return response()->json([
        'success'   => true,
        'data'      => $data,
        'pagination'=> [
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil(max(1, $total) / max(1, $perPage)),
        ]
    ]);
}


    /**
     * POST /api/batches/{idOrUuid}/students/toggle
     * Body: { user_id: int, assigned: bool }
     */
    public function studentsToggle(Request $request, $idOrUuid)
    {
        $uid = $this->authUserIdFromToken($request);
        if (!$uid) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $batch = $this->findBatch($idOrUuid);
        if (!$batch) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

        $v = Validator::make($request->all(), [
            'user_id'  => 'required|integer|exists:users,id',
            'assigned' => 'required|boolean',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $now = Carbon::now();
        $ip  = $request->ip();

        if ($request->boolean('assigned')) {
            // If a row exists (even soft-deleted), revive it; else insert new
            $existing = DB::table('batch_students')
                ->where('batch_id', $batch->id)
                ->where('user_id', (int)$request->user_id)
                ->first();

            if ($existing) {
                DB::table('batch_students')
                    ->where('id', $existing->id)
                    ->update([
                        'deleted_at'        => null,
                        'updated_at'        => $now,
                        'enrollment_status' => 'enrolled',
                        'enrolled_at'       => $existing->enrolled_at ?: $now,
                    ]);
            } else {
                DB::table('batch_students')->insert([
                    'uuid'              => (string)Str::uuid(),
                    'batch_id'          => $batch->id,
                    'user_id'           => (int)$request->user_id,
                    'enrollment_status' => 'enrolled',
                    'enrolled_at'       => $now,
                    'completed_at'      => null,
                    'created_by'        => $uid,
                    'created_at'        => $now,
                    'created_at_ip'     => $ip,
                    'updated_at'        => $now,
                    'deleted_at'        => null,
                    'metadata'          => json_encode([]),
                ]);
            }
        } else {
            // Soft delete assignment
            DB::table('batch_students')
                ->where('batch_id', $batch->id)
                ->where('user_id', (int)$request->user_id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        return response()->json(['success' => true]);
    }

    /* =========================================================
     |                CSV Upload (create/update students)
     |=========================================================*/

    /**
     * POST /api/batches/{idOrUuid}/students/upload-csv
     * Form-Data: csv (file .csv)
     * Required columns per row: email, name, phone (others are ignored/nullable)
     * - Creates user with role='student' if not exists (case-insensitive email).
     * - Updates name/phone if provided.
     * - Enrolls each into the batch (if not already).
     */
public function studentsUploadCsv(Request $request, $idOrUuid)
{
    $uid = $this->authUserIdFromToken($request);
    if (!$uid) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $batch = $this->findBatch($idOrUuid);
    if (!$batch) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

    $v = Validator::make($request->all(), [
        'csv' => 'required|file|mimes:csv,txt|max:10240',
    ]);
    if ($v->fails()) {
        return response()->json(['success' => false, 'errors' => $v->errors()], 422);
    }

    $file = $request->file('csv');
    if (!$file->isValid()) {
        return response()->json(['success' => false, 'message' => 'Invalid CSV upload'], 422);
    }

    $createdUsers = 0;
    $updatedUsers = 0;
    $enrolled     = 0;
    $errors       = [];

    try {
        $fh = fopen($file->getRealPath(), 'r');
        if ($fh === false) throw new \RuntimeException('Unable to open CSV');

        // ----- header -----
        $header = fgetcsv($fh);
        if ($header === false) throw new \RuntimeException('Empty CSV');

        $map = [];
        foreach ($header as $i => $h) {
            // strip potential UTF-8 BOM & normalize
            $key = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $h)));
            $map[$key] = $i;
        }

        // accept phone synonyms
        $phoneKey = null;
        foreach (['phone','phone_number','mobile','mobile_number','contact','contact_number'] as $k) {
            if (array_key_exists($k, $map)) { $phoneKey = $k; break; }
        }
        if (!isset($map['email']) || !isset($map['name']) || !$phoneKey) {
            fclose($fh);
            return response()->json([
                'success' => false,
                'message' => 'Missing required columns: email, name, and one of phone/phone_number'
            ], 422);
        }

        // which DB column holds phone
        $phoneCol = Schema::hasColumn('users','phone_number') ? 'phone_number'
                  : (Schema::hasColumn('users','phone') ? 'phone' : null);

        $now = now();
        $ip  = $request->ip();

        // helper: make unique slug like your UserController::store
        $makeSlug = function (string $name): string {
            $base = \Illuminate\Support\Str::slug($name);
            // keep base reasonable so base + '-' + 24 chars < 255
            $base = \Illuminate\Support\Str::limit($base, 200, '');
            do {
                $slug = $base . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(24));
            } while (DB::table('users')->where('slug', $slug)->exists());
            return $slug;
        };

        $rownum = 1;
        while (($row = fgetcsv($fh)) !== false) {
            $rownum++;

            $email = strtolower(trim($row[$map['email']] ?? ''));
            $name  = trim($row[$map['name']] ?? '');
            $phone = trim($row[$map[$phoneKey]] ?? '');

            if ($email === '' || $name === '' || $phone === '') {
                $errors[] = "Row {$rownum}: email, name, phone required";
                continue;
            }

            // already present (active, not soft-deleted)?
            $user = DB::table('users')->whereNull('deleted_at')
                    ->whereRaw('LOWER(email) = ?', [$email])->first();

            if (!$user) {
                // create new student
                $insert = [
                    'uuid'            => (string) \Illuminate\Support\Str::uuid(),
                    'slug'            => $makeSlug($name),
                    'name'            => $name,
                    'email'           => $email,
                    'role'            => 'student',
                    'role_short_form' => 'STD',
                    'status'          => 'active',
                    'password'        => bcrypt(\Illuminate\Support\Str::random(16)),
                    'remember_token'  => \Illuminate\Support\Str::random(60),
                    'created_by'      => $uid,
                    'created_at_ip'   => $ip,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                    'deleted_at'      => null,
                ];
                if ($phoneCol) $insert[$phoneCol] = $phone;

                // if a unique index exists on email (without deleted_at), this will throw on dup
                $id = DB::table('users')->insertGetId($insert);

                $user = DB::table('users')->where('id', $id)->first();
                $createdUsers++;
            } else {
                // update changed fields
                $toUpd = [];
                if ($name !== ($user->name ?? '')) $toUpd['name'] = $name;
                if ($phoneCol && $phone !== ($user->{$phoneCol} ?? '')) $toUpd[$phoneCol] = $phone;

                // ensure slug exists (legacy rows could be null)
                if (empty($user->slug)) $toUpd['slug'] = $makeSlug($name);

                if (!empty($toUpd)) {
                    $toUpd['updated_at'] = $now;
                    DB::table('users')->where('id', $user->id)->update($toUpd);
                    $updatedUsers++;
                }
            }

            // enroll to batch if not yet
            $exists = DB::table('batch_students')
                ->where('batch_id', $batch->id)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$exists) {
                DB::table('batch_students')->insert([
                    'uuid'              => (string)\Illuminate\Support\Str::uuid(),
                    'batch_id'          => $batch->id,
                    'user_id'           => $user->id,
                    'enrollment_status' => 'enrolled',
                    'enrolled_at'       => $now,
                    'completed_at'      => null,
                    'created_by'        => $uid,
                    'created_at_ip'     => $ip,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                    'deleted_at'        => null,
                    'metadata'          => json_encode([]),
                ]);
                $enrolled++;
            }
        }
        fclose($fh);

        return response()->json([
            'success' => true,
            'summary' => [
                'created_users' => $createdUsers,
                'updated_users' => $updatedUsers,
                'enrolled'      => $enrolled,
                'errors'        => $errors,
            ]
        ]);
    } catch (\Throwable $e) {
        Log::error('CSV upload failed', ['ex' => $e]);
        return response()->json(['success' => false, 'message' => 'CSV processing failed'], 500);
    }
}


/**
 * GET /api/batches/{idOrUuid}/instructors?q=&assigned=&per_page=&page=
 * assigned: 'all' (default) | '1' | '0'
 */
public function instructorsIndex(Request $request, $idOrUuid)
{
    $batch = $this->findBatch($idOrUuid);
    if (!$batch) return response()->json(['success'=>false,'message'=>'Batch not found'], 404);

    $v = Validator::make($request->all(), [
        'q'        => 'nullable|string|max:255',
        'assigned' => 'nullable|in:all,0,1',
        'per_page' => 'nullable|integer|min:1|max:200',
        'page'     => 'nullable|integer|min:1',
    ]);
    if ($v->fails()) {
        return response()->json(['success'=>false,'errors'=>$v->errors()], 422);
    }

    $per  = (int)$request->input('per_page', 20);
    $page = (int)$request->input('page', 1);
    $assignedFilter = $request->input('assigned', 'all');

    // Detect phone column (same approach as students)
    $phoneCol = \Illuminate\Support\Facades\Schema::hasColumn('users','phone_number') ? 'phone_number'
              : (\Illuminate\Support\Facades\Schema::hasColumn('users','phone') ? 'phone' : null);

    $u = DB::table('users')
        ->whereNull('deleted_at')
        ->where('status','active')
        ->where('role','instructor');

    if ($request->filled('q')) {
        $like = '%'.str_replace('%','\\%',$request->q).'%';
        $u->where(function($qq) use ($like, $phoneCol){
            $qq->where('name','like',$like)
               ->orWhere('email','like',$like);
            if ($phoneCol) $qq->orWhere($phoneCol,'like',$like);
        });
    }

    $total = (clone $u)->count();

    $select = ['id','name','email'];
    if ($phoneCol) $select[] = DB::raw("$phoneCol as phone");

    $rows = $u->select($select)
        ->orderBy('name','asc')
        ->offset(($page-1)*$per)
        ->limit($per)
        ->get();

    // Pull assignments for these user ids (not soft-deleted)
    $ids = $rows->pluck('id')->all();
    $links = DB::table('batch_instructors')
        ->where('batch_id', $batch->id)
        ->whereNull('deleted_at')
        ->whereIn('user_id', $ids)
        ->get()
        ->keyBy('user_id');

    // If assigned filter requested, apply it server-side
    $data = [];
    foreach ($rows as $r) {
        $link = $links->get($r->id);
        $isAssigned = (bool)$link;

        if ($assignedFilter === '1' && !$isAssigned) continue;
        if ($assignedFilter === '0' &&  $isAssigned) continue;

        $data[] = [
            'id'            => (int)$r->id,
            'name'          => $r->name,
            'email'         => $r->email,
            'phone'         => $r->phone ?? null,
            'assigned'      => $isAssigned,
            'role_in_batch' => $link->role_in_batch ?? null,
            'assign_status' => $link->assign_status ?? null,
            'assigned_at'   => $link->assigned_at ?? null,
        ];
    }

    return response()->json([
        'success'    => true,
        'data'       => array_values($data),
        'pagination' => [
            'total'        => $total,
            'per_page'     => $per,
            'current_page' => $page,
            'last_page'    => (int)ceil(max(1,$total)/max(1,$per)),
        ],
    ]);
}

/**
 * POST /api/batches/{idOrUuid}/instructors/toggle
 * Body: { user_id:int, assigned:bool, role_in_batch?:instructor|tutor|TA|mentor }
 */
public function instructorsToggle(Request $request, $idOrUuid)
{
    $uid = $this->authUserIdFromToken($request);
    if (!$uid) return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);

    $batch = $this->findBatch($idOrUuid);
    if (!$batch) return response()->json(['success'=>false,'message'=>'Batch not found'], 404);

    $v = Validator::make($request->all(), [
        'user_id'       => 'required|integer|exists:users,id',
        'assigned'      => 'required|boolean',
        'role_in_batch' => 'nullable|in:instructor,tutor,TA,mentor',
    ]);
    if ($v->fails()) {
        return response()->json(['success'=>false,'errors'=>$v->errors()], 422);
    }

    $now  = now();
    $ip   = $request->ip();
    $roleInBatch = $request->input('role_in_batch', 'instructor');

    if ($request->boolean('assigned')) {
        // If a link (even soft-deleted) exists, revive; else insert
        $existing = DB::table('batch_instructors')
            ->where('batch_id', $batch->id)
            ->where('user_id', (int)$request->user_id)
            ->first();

        if ($existing) {
            DB::table('batch_instructors')
              ->where('id', $existing->id)
              ->update([
                  'deleted_at'    => null,
                  'assign_status' => 'active',
                  'role_in_batch' => $roleInBatch ?: ($existing->role_in_batch ?? 'instructor'),
                  'assigned_at'   => $existing->assigned_at ?: $now,
                  'unassigned_at' => null,
                  'updated_at'    => $now,
              ]);
        } else {
            DB::table('batch_instructors')->insert([
                'uuid'          => (string) Str::uuid(),
                'batch_id'      => $batch->id,
                'user_id'       => (int)$request->user_id,
                'role_in_batch' => $roleInBatch,
                'assign_status' => 'active',
                'assigned_at'   => $now,
                'unassigned_at' => null,
                'created_by'    => $uid,
                'created_at_ip' => $ip,
                'created_at'    => $now,
                'updated_at'    => $now,
                'deleted_at'    => null,
                'metadata'      => json_encode([]),
            ]);
        }
    } else {
        // Soft delete + mark removed
        DB::table('batch_instructors')
          ->where('batch_id', $batch->id)
          ->where('user_id', (int)$request->user_id)
          ->whereNull('deleted_at')
          ->update([
              'assign_status' => 'removed',
              'unassigned_at' => $now,
              'deleted_at'    => $now,
              'updated_at'    => $now,
          ]);
    }

    return response()->json(['success'=>true]);
}

/**
 * PATCH /api/batches/{idOrUuid}/instructors/update
 * Body: { user_id:int, role_in_batch?:..., assign_status?:active|standby|replaced|removed }
 * Note: Requires the link to exist (not soft-deleted).
 */
public function instructorsUpdate(Request $request, $idOrUuid)
{
    $uid = $this->authUserIdFromToken($request);
    if (!$uid) return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);

    $batch = $this->findBatch($idOrUuid);
    if (!$batch) return response()->json(['success'=>false,'message'=>'Batch not found'], 404);

    $v = Validator::make($request->all(), [
        'user_id'       => 'required|integer|exists:users,id',
        'role_in_batch' => 'sometimes|in:instructor,tutor,TA,mentor',
        'assign_status' => 'sometimes|in:active,standby,replaced,removed',
    ]);
    if ($v->fails()) {
        return response()->json(['success'=>false,'errors'=>$v->errors()], 422);
    }

    $link = DB::table('batch_instructors')
        ->where('batch_id', $batch->id)
        ->where('user_id', (int)$request->user_id)
        ->whereNull('deleted_at')
        ->first();

    if (!$link) {
        return response()->json(['success'=>false,'message'=>'Instructor is not assigned to this batch'], 404);
    }

    $upd = [];
    if ($request->has('role_in_batch')) $upd['role_in_batch'] = $request->role_in_batch;
    if ($request->has('assign_status')) $upd['assign_status'] = $request->assign_status;
    if (empty($upd)) return response()->json(['success'=>true,'message'=>'Nothing to update']);

    $upd['updated_at'] = now();
    DB::table('batch_instructors')->where('id',$link->id)->update($upd);

    return response()->json(['success'=>true,'message'=>'Instructor link updated']);
}
/**
 * GET /api/batches/{idOrUuid}/quizzes?q=&assigned=&per_page=&page=
 * assigned: 'all' (default) | '1' | '0'
 */
public function quizzIndex(Request $request, $idOrUuid)
{
    $batch = $this->findBatch($idOrUuid);
    if (!$batch) {
        return response()->json(['success' => false, 'message' => 'Batch not found'], 404);
    }

    $v = Validator::make($request->all(), [
        'q'        => 'nullable|string|max:255',
        'assigned' => 'nullable|in:all,0,1',
        'per_page' => 'nullable|integer|min:1|max:200',
        'page'     => 'nullable|integer|min:1',
    ]);
    if ($v->fails()) {
        return response()->json(['success' => false, 'errors' => $v->errors()], 422);
    }

    $per  = (int)$request->input('per_page', 20);
    $page = (int)$request->input('page', 1);
    $assignedFilter = $request->input('assigned', 'all');

    $qLike = $request->filled('q') ? ('%' . str_replace('%','\\%',$request->q) . '%') : null;

    $select = [
        'quizz.id',
        'quizz.uuid',
        DB::raw('quizz.quiz_name as title'),
        DB::raw('LEFT(quizz.quiz_description, 200) as excerpt'),
        DB::raw('CASE WHEN bq.id IS NULL THEN 0 ELSE 1 END as assigned'),
        DB::raw('bq.id as batch_quiz_id'),
        DB::raw('bq.display_order'),
        DB::raw('bq.status as batch_status'),
        DB::raw('bq.publish_to_students'),
        DB::raw('bq.available_from'),
        DB::raw('bq.available_until'),
        // assignment time
        DB::raw('bq.created_at as assigned_at'),
        // now read attempt_allowed from pivot
        DB::raw('bq.attempt_allowed as attempt_allowed'),
    ];

    $base = DB::table('quizz')
        ->leftJoin('batch_quizzes as bq', function($join) use ($batch) {
            $join->on('quizz.id', 'bq.quiz_id')
                 ->where('bq.batch_id', $batch->id)
                 ->whereNull('bq.deleted_at');
        })
        ->whereNull('quizz.deleted_at');

    if ($qLike) {
        $base->where(function($w) use ($qLike) {
            $w->where('quizz.quiz_name', 'like', $qLike)
              ->orWhere('quizz.quiz_description', 'like', $qLike);
        });
    }

    if ($assignedFilter === '1') {
        $base->whereNotNull('bq.id');
    } elseif ($assignedFilter === '0') {
        $base->whereNull('bq.id');
    }

    $totalRow = (clone $base)->select(DB::raw('COUNT(DISTINCT quizz.id) as cnt'))->first();
    $total = (int)($totalRow->cnt ?? 0);

    $rows = $base->select($select)
        ->orderByRaw('CASE WHEN bq.id IS NULL THEN 1 ELSE 0 END, quizz.quiz_name ASC')
        ->offset(($page - 1) * $per)
        ->limit($per)
        ->get();

    $data = [];
    foreach ($rows as $r) {
        $data[] = [
            'id'                 => (int)$r->id,
            'uuid'               => $r->uuid,
            'title'              => $r->title,
            'excerpt'            => $r->excerpt ?? null,
            'assigned'           => (bool)$r->assigned,
            'batch_quiz_id'      => $r->batch_quiz_id ? (int)$r->batch_quiz_id : null,
            'display_order'      => $r->display_order !== null ? (int)$r->display_order : null,
            'batch_status'       => $r->batch_status ?? null,
            'publish_to_students'=> isset($r->publish_to_students) ? (bool)$r->publish_to_students : false,
            'available_from'     => $r->available_from ?? null,
            'available_until'    => $r->available_until ?? null,
            'assigned_at'        => $r->assigned_at ?? null,
            // now comes from pivot
            'attempt_allowed'    => isset($r->attempt_allowed) ? (is_null($r->attempt_allowed) ? null : (int)$r->attempt_allowed) : null,
        ];
    }

    return response()->json([
        'success' => true,
        'data' => array_values($data),
        'pagination' => [
            'total' => $total,
            'per_page' => $per,
            'current_page' => $page,
            'last_page' => (int)ceil(max(1, $total) / max(1, $per)),
        ],
    ]);
}
public function quizzToggle(Request $request, $idOrUuid)
{
    $uid = $this->authUserIdFromToken($request);
    if (!$uid) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $batch = $this->findBatch($idOrUuid);
    if (!$batch) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

    $v = Validator::make($request->all(), [
        'quiz_id'            => 'required|integer|exists:quizz,id',
        'assigned'           => 'required|boolean',
        'display_order'      => 'sometimes|integer|min:0',
        'publish_to_students'=> 'sometimes|boolean',
        'available_from'     => 'nullable|date',
        'available_until'    => 'nullable|date|after_or_equal:available_from',
        'attempt_allowed'    => 'sometimes|nullable|integer|min:0',
    ]);
    if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

    $now    = now();
    $ip     = $request->ip();
    $quizId = (int) $request->quiz_id;

    // cache column presence once
    $hasAssignedAt = Schema::hasColumn('batch_quizzes', 'assigned_at');

    // fetch existing (include soft-deleted so we can revive)
    $existing = DB::table('batch_quizzes')
        ->where('batch_id', $batch->id)
        ->where('quiz_id', $quizId)
        ->first();

    // Use transaction to avoid races
    $pivotId = null;
    DB::transaction(function() use (
        $request, $batch, $quizId, $uid, $ip, $now, $existing, $hasAssignedAt, &$pivotId
    ) {
        if ($request->boolean('assigned')) {
            // build payload using optional() to avoid ->property on null
            $payload = [
                'assign_status'      => 1,
                'display_order'      => $request->filled('display_order') ? (int)$request->display_order : (optional($existing)->display_order ?? 1),
                'publish_to_students'=> $request->has('publish_to_students') ? (int)$request->publish_to_students : (optional($existing)->publish_to_students ?? 0),
                'available_from'     => $request->filled('available_from') ? $request->available_from : (optional($existing)->available_from ?? null),
                'available_until'    => $request->filled('available_until') ? $request->available_until : (optional($existing)->available_until ?? null),
                // attempt_allowed sourced from request if present, otherwise keep existing pivot value
                'attempt_allowed'    => $request->has('attempt_allowed') ? (is_null($request->attempt_allowed) ? null : (int)$request->attempt_allowed) : (optional($existing)->attempt_allowed ?? null),
                'unassigned_at'      => null,
                'updated_at'         => $now,
                'deleted_at'         => null,
            ];

            if ($hasAssignedAt) {
                // if existing had assigned_at keep it, otherwise set now
                $payload['assigned_at'] = optional($existing)->assigned_at ?? $now;
            }

            if ($existing) {
                DB::table('batch_quizzes')->where('id', $existing->id)->update($payload);
                $pivotId = $existing->id;
            } else {
                $insertData = array_merge($payload, [
                    'uuid'          => (string) Str::uuid(),
                    'batch_id'      => $batch->id,
                    'quiz_id'       => $quizId,
                    'created_by'    => $uid,
                    'created_at_ip' => $ip,
                    'created_at'    => $now,
                    'metadata'      => json_encode([]),
                ]);

                $pivotId = DB::table('batch_quizzes')->insertGetId($insertData);
            }
        } else {
            // unassign -> soft-delete + set assign_status = 0
            DB::table('batch_quizzes')
                ->where('batch_id', $batch->id)
                ->where('quiz_id', $quizId)
                ->whereNull('deleted_at')
                ->update([
                    'assign_status' => 0,
                    'unassigned_at' => $now,
                    'deleted_at'    => $now,
                    'updated_at'    => $now,
                ]);

            // keep pivotId null to indicate removal
            $pivotId = null;
        }
    });

    // if it was an unassign, return success + null data so client removes it
    if (is_null($pivotId)) {
        return response()->json(['success' => true, 'data' => null]);
    }

    // fetch pivot row to return (fresh)
    $pivot = DB::table('batch_quizzes')->where('id', $pivotId)->first();

    // attempt_allowed now comes from pivot
    $attemptAllowed = isset($pivot->attempt_allowed) ? (is_null($pivot->attempt_allowed) ? null : (int)$pivot->attempt_allowed) : null;

    $assignedAt = null;
    if ($pivot) {
        $assignedAt = $hasAssignedAt ? ($pivot->assigned_at ?? $pivot->created_at ?? null) : ($pivot->created_at ?? null);
    }

    $response = [
        'success' => true,
        'data'    => $pivot ? [
            'id'                 => (int)$pivot->id,
            'uuid'               => $pivot->uuid,
            'batch_id'           => (int)$pivot->batch_id,
            'quiz_id'            => (int)$pivot->quiz_id,
            'display_order'      => isset($pivot->display_order) ? (int)$pivot->display_order : null,
            'assign_status'      => isset($pivot->assign_status) ? (bool)$pivot->assign_status : false,
            'publish_to_students'=> isset($pivot->publish_to_students) ? (bool)$pivot->publish_to_students : false,
            'available_from'     => $pivot->available_from ?? null,
            'available_until'    => $pivot->available_until ?? null,
            'assigned_at'        => $assignedAt,
            'created_at'         => $pivot->created_at ?? null,
            'updated_at'         => $pivot->updated_at ?? null,
            // now from pivot
            'attempt_allowed'    => $attemptAllowed,
        ] : null,
    ];

    return response()->json($response);   
}
public function quizzUpdate(Request $request, $idOrUuid)
{
    $uid = $this->authUserIdFromToken($request);
    if (!$uid) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $batch = $this->findBatch($idOrUuid);
    if (!$batch) return response()->json(['success' => false, 'message' => 'Batch not found'], 404);

    $v = Validator::make($request->all(), [
        // identify pivot either by batch_quiz_id OR quiz_id
        'batch_quiz_id'      => 'nullable|integer|exists:batch_quizzes,id',
        'quiz_id'            => 'nullable|integer|exists:quizz,id',
        'display_order'      => 'sometimes|nullable|integer|min:0',
        'publish_to_students'=> 'sometimes|boolean',
        'available_from'     => 'nullable|date',
        'available_until'    => 'nullable|date|after_or_equal:available_from',
        // update pivot attempt_allowed
        'attempt_allowed'    => 'sometimes|nullable|integer|min:0',
    ]);
    if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

    // locate pivot
    $pivot = null;
    if ($request->filled('batch_quiz_id')) {
        $pivot = DB::table('batch_quizzes')->where('id', (int)$request->batch_quiz_id)->first();
        if (!$pivot) return response()->json(['success'=>false,'message'=>'Assignment not found'],404);
        if ((int)$pivot->batch_id !== (int)$batch->id) {
            return response()->json(['success'=>false,'message'=>'Assignment does not belong to this batch'],403);
        }
    } elseif ($request->filled('quiz_id')) {
        $pivot = DB::table('batch_quizzes')
            ->where('batch_id', $batch->id)
            ->where('quiz_id', (int)$request->quiz_id)
            ->first();
        if (!$pivot) return response()->json(['success'=>false,'message'=>'Assignment not found for this batch'],404);
    } else {
        return response()->json(['success'=>false,'message'=>'batch_quiz_id or quiz_id required'],422);
    }

    $now = now();
    DB::transaction(function() use ($request, $pivot, $now) {
        $update = ['updated_at' => $now];

        if ($request->has('display_order')) {
            $update['display_order'] = $request->input('display_order') === null ? null : (int)$request->input('display_order');
        }

        if ($request->has('publish_to_students')) {
            $update['publish_to_students'] = (int) $request->input('publish_to_students');
        }

        if ($request->has('available_from')) {
            $update['available_from'] = $request->input('available_from') ?: null;
        }

        if ($request->has('available_until')) {
            $update['available_until'] = $request->input('available_until') ?: null;
        }

        // update pivot attempt_allowed when provided
        if ($request->has('attempt_allowed')) {
            $update['attempt_allowed'] = $request->input('attempt_allowed') === null ? null : (int)$request->input('attempt_allowed');
        }

        if (count($update) > 1) {
            DB::table('batch_quizzes')->where('id', $pivot->id)->update($update);
        }
    });

    // fetch fresh pivot to return authoritative values
    $pivot = DB::table('batch_quizzes')->where('id', $pivot->id)->first();
    $assignedAt = $pivot->assigned_at ?? $pivot->created_at ?? null;

    $response = [
        'success' => true,
        'data'    => $pivot ? [
            'id'                 => (int)$pivot->id,
            'uuid'               => $pivot->uuid,
            'batch_id'           => (int)$pivot->batch_id,
            'quiz_id'            => (int)$pivot->quiz_id,
            'display_order'      => isset($pivot->display_order) ? (int)$pivot->display_order : null,
            'assign_status'      => isset($pivot->assign_status) ? (bool)$pivot->assign_status : false,
            'publish_to_students'=> isset($pivot->publish_to_students) ? (bool)$pivot->publish_to_students : false,
            'available_from'     => $pivot->available_from ?? null,
            'available_until'    => $pivot->available_until ?? null,
            'assigned_at'        => $assignedAt,
            'created_at'         => $pivot->created_at ?? null,
            'updated_at'         => $pivot->updated_at ?? null,
            // pivot-sourced attempts
            'attempt_allowed'    => isset($pivot->attempt_allowed) ? (is_null($pivot->attempt_allowed) ? null : (int)$pivot->attempt_allowed) : null,
        ] : null,
    ];

    return response()->json($response);
}


}