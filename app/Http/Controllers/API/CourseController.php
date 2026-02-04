<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;



class CourseController extends Controller
{
    /* =========================
     *  Auth/Role helpers (same style)
     * ========================= */
    private function actor(Request $request): array
{
    $id = $request->attributes->get('auth_tokenable_id');
    
    return [
        'role' => $request->attributes->get('auth_role'),
        'type' => $request->attributes->get('auth_tokenable_type'),
        'id'   => $id !== null ? (int) $id : null,
    ];
}

    private function requireRole(Request $r, array $allowed)
    {
        $a = $this->actor($r);
        if (!$a['role'] || !in_array($a['role'], $allowed, true)) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    private function logWithActor(string $msg, Request $r, array $extra = []): void
    {
        $a = $this->actor($r);
        Log::info($msg, array_merge([
            'actor_role' => $a['role'],
            'actor_type' => $a['type'],
            'actor_id'   => $a['id'],
        ], $extra));
    }

    /* =========================
     *  Activity Log (same columns)
     * ========================= */
    private function logActivity(
        Request $request,
        string $activity,         // store | update | destroy | upload | reorder | suggest
        string $note,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $a = $this->actor($request);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $a['id'] ?: 0,
                'performed_by_role' => $a['role'] ?: null,
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,
                'module'            => 'Courses',
                'table_name'        => $tableName,
                'record_id'         => $recordId,
                'changed_fields'    => $changedFields ? json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[Courses] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /* =========================
     *  Notifications (DB-only)
     * ========================= */
    private function persistNotification(array $payload): void
    {
        $title     = (string)($payload['title']    ?? 'Notification');
        $message   = (string)($payload['message']  ?? '');
        $receivers = array_values(array_map(function($x){
            return [
                'id'   => isset($x['id']) ? (int)$x['id'] : null,
                'role' => (string)($x['role'] ?? 'unknown'),
                'read' => (int)($x['read'] ?? 0),
            ];
        }, $payload['receivers'] ?? []));

        $metadata = $payload['metadata'] ?? [];
        $type     = (string)($payload['type'] ?? 'general');
        $linkUrl  = $payload['link_url'] ?? null;
        $priority = in_array(($payload['priority'] ?? 'normal'), ['low','normal','high','urgent'], true)
                    ? $payload['priority'] : 'normal';
        $status   = in_array(($payload['status'] ?? 'active'), ['active','archived','deleted'], true)
                    ? $payload['status'] : 'active';

        DB::table('notifications')->insert([
            'title'      => $title,
            'message'    => $message,
            'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
            'metadata'   => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'type'       => $type,
            'link_url'   => $linkUrl,
            'priority'   => $priority,
            'status'     => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** 
     * Replaced: query admins from the common `users` table.
     * Picks active users with role in ['admin','super_admin'].
     */
    private function adminReceivers(array $excludeIds = []): array
    {
        $exclude = array_flip(array_map('intval', $excludeIds));

        $rows = DB::table('users')
            ->select('id', 'role', 'status')
            ->whereNull('deleted_at')
            ->whereIn('role', ['admin','super_admin'])
            ->where('status', '=', 'active')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $id = (int)$r->id;
            if (isset($exclude[$id])) continue;

            $role = in_array($r->role, ['admin','super_admin'], true) ? $r->role : 'admin';
            $out[] = ['id' => $id, 'role' => $role, 'read' => 0];
        }
        return $out;
    }

    /* =========================
     *  Other helpers
     * ========================= */
    private function computeFinalPrice(?float $price, ?float $discAmt, ?float $discPct): float
    {
        $p = max(0.0, (float)($price ?? 0));
        $offA = $discAmt !== null ? max(0.0, (float)$discAmt) : 0.0;
        $offP = $discPct !== null ? max(0.0, round($p * ((float)$discPct)/100.0, 2)) : 0.0;
        return max(0.0, round($p - max($offA, $offP), 2));
    }

    private function makeUniqueSlug(string $base): string
    {
        $slug = $base !== '' ? Str::slug($base) : '';
        if ($slug === '') $slug = 'course';
        if (!DB::table('courses')->where('slug', $slug)->exists()) return $slug;

        $i = 2;
        while (true) {
            $candidate = Str::limit($slug, 140 - (strlen((string)$i) + 1), '').'-'.$i;
            if (!DB::table('courses')->where('slug', $candidate)->exists()) {
                return $candidate;
            }
            $i++;
        }
    }


    /** Save targets under /public */
private const MEDIA_FOLDERS = [
    'image' => 'assets/media/images/course',
    'video' => 'assets/media/videos/course',
    'audio' => 'assets/media/audio/course',
    'pdf'   => 'assets/media/docs/course',
    'other' => 'assets/media/other/course',
];

/** If true, every file goes into images/course */
private const FORCE_IMAGES_FOLDER = false;

private function mediaBasePublicPath(): string
{
    return public_path(); // /.../public
}

private function mediaSubdirFor(string $featuredType): string
{
    if (self::FORCE_IMAGES_FOLDER) return self::MEDIA_FOLDERS['image'];
    return self::MEDIA_FOLDERS[$featuredType] ?? self::MEDIA_FOLDERS['other'];
}

private function toPublicUrl(string $relativePath): string
{
    $base = rtrim((string) config('app.url'), '/');
    $rel  = ltrim(str_replace('\\','/',$relativePath), '/');
    return $base . '/' . $rel;
}

private function detectFeaturedType(?string $mime, ?string $pathOrUrl): string
{
    $mime = strtolower((string)$mime);
    $ext  = strtolower(pathinfo((string)$pathOrUrl, PATHINFO_EXTENSION));
    if (str_starts_with($mime, 'image/') || in_array($ext, ['jpg','jpeg','png','gif','webp','avif'])) return 'image';
    if (str_starts_with($mime, 'video/') || in_array($ext, ['mp4','mov','m4v','webm','ogg']))        return 'video';
    if (str_starts_with($mime, 'audio/') || in_array($ext, ['mp3','wav','aac','m4a','ogg']))         return 'audio';
    if ($ext === 'pdf' || $mime === 'application/pdf')                                               return 'pdf';
    return 'other';
}

private function findCourseOr404(string $key)
{
    $q = DB::table('courses')->whereNull('deleted_at');
    if (ctype_digit($key)) $q->where('id', (int)$key); else $q->where('uuid', $key);
    return $q->first();
}

private function nextMediaOrderNo(int $courseId): int
{
    $m = DB::table('course_featured_media')
        ->where('course_id', $courseId)->whereNull('deleted_at')
        ->max('order_no');
    return ((int)$m) + 1;
}


    /* =========================
     *  CREATE (POST /api/courses)
     * ========================= */
   /* =========================
 *  CREATE (POST /api/courses)
 * ========================= */
public function store(Request $request)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;
    $this->logWithActor('[Course Store] begin', $request);

    $data = $request->validate([
        'title'               => ['required','string','max:255'],
        'slug'                => ['nullable','string','max:140','unique:courses,slug'],

        // 🔹 NEW: category is required and must exist in course_categories
        'category_id'         => ['nullable','integer','exists:course_categories,id'],

        'short_description'   => ['nullable','string'],
        'full_description'    => ['nullable','string'],
        'status'              => ['required', Rule::in(['draft','published','archived'])],
        'course_type'         => ['nullable', Rule::in(['free','paid'])],
        'price_amount'        => ['nullable','numeric','min:0'],
        'price_currency'      => ['nullable','string','size:3'],
        'discount_amount'     => ['nullable','numeric','min:0'],
        'discount_percent'    => ['nullable','numeric','min:0','max:100'],
        'discount_expires_at' => ['nullable','date'],
        'is_featured'         => ['nullable','boolean'],
        'featured_rank'       => ['nullable','integer','min:0'],
        'order_no'            => ['nullable','integer','min:0'],
        'level'               => ['nullable','string','max:20'],
        'language'            => ['nullable','string','max:10'],
        'publish_at'          => ['nullable','date'],
        'unpublish_at'        => ['nullable','date','after_or_equal:publish_at'],
        'metadata'            => ['nullable','array'],
    ]);

    $courseType = $data['course_type'] ?? 'paid';
    $status     = $data['status']      ?? 'draft';

    $price = (float)($data['price_amount'] ?? 0);
    if ($courseType === 'paid' && $price <= 0) {
        return response()->json([
            'error'  => 'Validation failed',
            'fields' => ['price_amount' => ['price_amount must be > 0 for paid courses']],
        ], 422);
    }

    $currency   = strtoupper($data['price_currency'] ?? 'INR');
    $discAmt    = array_key_exists('discount_amount',  $data) ? (float)$data['discount_amount']  : null;
    $discPct    = array_key_exists('discount_percent', $data) ? (float)$data['discount_percent'] : null;
    $finalPrice = $this->computeFinalPrice($price, $discAmt, $discPct);

    $slug = $data['slug'] ?? $this->makeUniqueSlug($data['title']);

    $a   = $this->actor($request);
    $now = now();
    $uuid = (string) Str::uuid();

    $insert = [
        'uuid'                => $uuid,
        'title'               => $data['title'],
        'slug'                => $slug,

        // 🔹 NEW: persist category
        'category_id'         => (int)$data['category_id'],

        'short_description'   => $data['short_description'] ?? null,
        'full_description'    => $data['full_description']  ?? null,
        'status'              => $status,
        'course_type'         => $courseType, // free | paid
        'price_amount'        => $price,
        'price_currency'      => $currency,
        'discount_amount'     => $discAmt,
        'discount_percent'    => $discPct,
        'discount_expires_at' => $data['discount_expires_at'] ?? null,
        'is_featured'         => !empty($data['is_featured']) ? 1 : 0,
        'featured_rank'       => (int)($data['featured_rank'] ?? 0),
        'order_no'            => (int)($data['order_no'] ?? 0),
        'level'               => $data['level']    ?? null,
        'language'            => $data['language'] ?? null,
        'publish_at'          => $data['publish_at']   ?? null,
        'unpublish_at'        => $data['unpublish_at'] ?? null,
        'created_by'          => $a['id'] ?: null,
        'created_at'          => $now,
        'created_at_ip'       => $request->ip(),
        'updated_at'          => $now,
        'deleted_at'          => null,
        'metadata'            => isset($data['metadata'])
                                  ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE)
                                  : json_encode(new \stdClass()),
    ];

    $id = DB::table('courses')->insertGetId($insert);

    $fresh = DB::table('courses')->where('id', $id)->first();
    if ($fresh) {
        $fresh->final_price = $this->computeFinalPrice(
            (float)$fresh->price_amount,
            $fresh->discount_amount !== null ? (float)$fresh->discount_amount : null,
            $fresh->discount_percent !== null ? (float)$fresh->discount_percent : null
        );
    }

    $this->logActivity(
        $request,
        'store',
        'Created course "'.$insert['title'].'"',
        'courses',
        $id,
        array_keys($insert),
        null,
        $fresh ? (array)$fresh : null
    );

    $link = rtrim((string)config('app.url'), '/').'/admin/courses/'.$id;
    $this->persistNotification([
        'title'     => 'Course created',
        'message'   => '“'.$insert['title'].'” has been created.',
        'receivers' => $this->adminReceivers(), // now from users table
        'metadata'  => [
            'action'     => 'created',
            'course'     => [
                'id'    => $id,
                'uuid'  => $uuid,
                'title' => $insert['title'],
                'slug'  => $slug,
                'status'=> $status,
                'type'  => $courseType,
            ],
            'created_by' => $a,
        ],
        'type'      => 'course',
        'link_url'  => $link,
        'priority'  => 'normal',
        'status'    => 'active',
    ]);

    $this->logWithActor('[Course Store] success', $request, ['course_id' => $id, 'uuid' => $uuid]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Course created successfully',
        'data'    => $fresh,
    ], 201);
}
public function index(Request $r)
{
    $page        = max(1, (int)$r->query('page', 1));
    $perPage     = max(1, min(100, (int)$r->query('per_page', 20)));
    $qText       = trim((string)$r->query('q', ''));
    $status      = $r->query('status');          // draft|published|archived
    $type        = $r->query('course_type');     // paid|free
    $sort        = (string)$r->query('sort', '-created_at'); // or title,status,...
    $onlyDeleted = (string)$r->query('only_deleted', '') === '1';

    // Accept ?category=... (expects UUID from frontend)
    $categoryToken = trim((string)$r->query('category', ''));

    // Resolve course_categories.uuid -> numeric id (course_categories.id)
    $categoryFilterId = null;
    if ($categoryToken !== '') {
        try {
            if (\Schema::hasTable('course_categories')) {
                $token = $categoryToken;

                $catQuery = DB::table('course_categories')->select('id', 'uuid');

                $cat = $catQuery->where('uuid', $token)->first();

                if (!$cat && ctype_digit($token)) {
                    $cat = DB::table('course_categories')
                        ->select('id', 'uuid')
                        ->where('id', (int)$token)
                        ->first();
                }

                if (!$cat) {
                    $maybeHex = preg_replace('/[^a-fA-F0-9]/', '', $token);
                    if (strlen($maybeHex) === 32) {
                        $cat = DB::table('course_categories')
                            ->select('id', 'uuid')
                            ->whereRaw(
                                "LOWER(REPLACE(`uuid`, '-', '')) = ?",
                                [mb_strtolower($maybeHex)]
                            )
                            ->first();
                    }
                }

                if ($cat) {
                    $categoryFilterId = (int)$cat->id;
                    \Log::info('[Courses.index] matched course_categories', [
                        'token' => $categoryToken,
                        'matched_id' => $categoryFilterId,
                        'matched_uuid' => $cat->uuid ?? null,
                    ]);
                } else {
                    \Log::info('[Courses.index] category token provided but not matched in course_categories', [
                        'token' => $categoryToken,
                    ]);
                }
            } else {
                \Log::warning('[Courses.index] course_categories table not found; skipping category filter');
            }
        } catch (\Exception $ex) {
            \Log::error('[Courses.index] error resolving course_categories token', [
                'token' => $categoryToken,
                'error' => $ex->getMessage(),
            ]);
            $categoryFilterId = null;
        }
    }

    // 🔹 Subquery: pick ONE featured image per course
    $mediaSub = DB::table('course_featured_media as cfm2')
        ->select(
            'cfm2.course_id',
            DB::raw('MIN(cfm2.featured_url) as featured_url')
        )
        ->groupBy('cfm2.course_id');

    // 🔹 Base query on courses + featured image + ✅ category join
    $q = DB::table('courses as c')
        ->leftJoinSub($mediaSub, 'cfm', function ($join) {
            $join->on('cfm.course_id', '=', 'c.id');
        })
        ->leftJoin('course_categories as cc', function ($join) {
            $join->on('cc.id', '=', 'c.category_id')
                 ->whereNull('cc.deleted_at');
        })
        ->select(
            'c.*',

            // Featured image
            DB::raw('cfm.featured_url as thumbnail_url'),

            // ✅ Category data for frontend badge
            'cc.id as category_id',
            'cc.uuid as category_uuid',
            'cc.title as category_title'
        );

    // Soft delete filter
    if ($onlyDeleted) {
        $q->whereNotNull('c.deleted_at');
    } else {
        $q->whereNull('c.deleted_at');
    }

    // Search
    if ($qText !== '') {
        $q->where(function ($w) use ($qText) {
            $w->where('c.title', 'like', "%{$qText}%")
              ->orWhere('c.slug', 'like', "%{$qText}%");
        });
    }

    // Status filter
    if ($status && !$onlyDeleted) {
        $q->where('c.status', $status);
    }

    // Paid / free filter
    if ($type) {
        $q->where('c.course_type', $type);
    }

    // Category filter
    if (!empty($categoryFilterId)) {
        $q->where('c.category_id', $categoryFilterId);
    }

    // Sorting
    $dir = 'asc';
    $col = $sort;
    if (str_starts_with($sort, '-')) {
        $dir = 'desc';
        $col = ltrim($sort, '-');
    }

    if (!in_array($col, ['created_at', 'title', 'status', 'course_type', 'order_no', 'deleted_at'], true)) {
        $col = 'created_at';
        $dir = 'desc';
    }

    $col = 'c.' . $col;

    // Pagination
    $total = (clone $q)->count();
    $rows  = $q->orderBy($col, $dir)
        ->offset(($page - 1) * $perPage)
        ->limit($perPage)
        ->get();

    // Final price UI
    foreach ($rows as $row) {
        $row->final_price_ui = $this->computeFinalPrice(
            (float)$row->price_amount,
            $row->discount_amount !== null ? (float)$row->discount_amount : null,
            $row->discount_percent !== null ? (float)$row->discount_percent : null
        );
    }

    return response()->json([
        'data'       => $rows,
        'pagination' => [
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $total,
        ],
    ]);
}

public function show(Request $r, string $course)
{

    $row = $this->findCourseOr404($course);
    if (!$row) return response()->json(['error'=>'Course not found'], 404);

    $row->final_price_ui = $this->computeFinalPrice(
        (float)$row->price_amount,
        $row->discount_amount !== null ? (float)$row->discount_amount : null,
        $row->discount_percent !== null ? (float)$row->discount_percent : null
    );
    return response()->json(['data'=>$row]);
}
public function update(Request $request, string $course)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

    $row = $this->findCourseOr404($course);
    if (!$row) return response()->json(['error'=>'Course not found'], 404);
    $id = (int)$row->id;

    $data = $request->validate([
        'title'               => ['sometimes','string','max:255'],
        'slug'                => ['sometimes','nullable','string','max:140','unique:courses,slug,'.$id],

        // 🔹 category_id is optional + nullable
        'category_id'         => ['sometimes','nullable','integer','exists:course_categories,id'],

        'short_description'   => ['sometimes','nullable','string'],
        'full_description'    => ['sometimes','nullable','string'],
        'status'              => ['sometimes', Rule::in(['draft','published','archived'])],
        'course_type'         => ['sometimes', Rule::in(['free','paid'])],
        'price_amount'        => ['sometimes','numeric','min:0'],
        'price_currency'      => ['sometimes','string','size:3'],
        'discount_amount'     => ['sometimes','nullable','numeric','min:0'],
        'discount_percent'    => ['sometimes','nullable','numeric','min:0','max:100'],
        'discount_expires_at' => ['sometimes','nullable','date'],
        'is_featured'         => ['sometimes','boolean'],
        'featured_rank'       => ['sometimes','integer','min:0'],
        'order_no'            => ['sometimes','integer','min:0'],
        'level'               => ['sometimes','nullable','string','max:20'],
        'language'            => ['sometimes','nullable','string','max:10'],
        'publish_at'          => ['sometimes','nullable','date'],
        'unpublish_at'        => ['sometimes','nullable','date','after_or_equal:publish_at'],
        'metadata'            => ['sometimes','nullable','array'],
    ]);

    // =========================
    // Validate paid course price
    // =========================

    $newType  = $data['course_type'] ?? $row->course_type;
    $newPrice = array_key_exists('price_amount',$data)
                    ? (float)$data['price_amount']
                    : (float)$row->price_amount;

    if ($newType === 'paid' && $newPrice <= 0) {
        return response()->json([
            'error'=>'Validation failed',
            'fields'=>['price_amount'=>['price_amount must be > 0 for paid courses']]
        ], 422);
    }

    // =========================
    // Build update array safely
    // =========================

    $upd = [];

    foreach ($data as $k => $v) {

        if ($k === 'metadata') {
            // convert to json or empty object
            $v = $v !== null ? json_encode($v, JSON_UNESCAPED_UNICODE) : json_encode(new \stdClass());
        }

        if ($k === 'is_featured') {
            $v = !empty($v) ? 1 : 0;
        }

        // 🔹 category_id allowed to be null (unassign category)
        if ($k === 'category_id') {
            $v = $v !== null ? (int)$v : null;
        }

        $upd[$k] = $v;
    }

    $upd['updated_at'] = now();

    DB::table('courses')->where('id',$id)->update($upd);

    // =========================
    // Fetch fresh and compute final price
    // =========================

    $fresh = DB::table('courses')->where('id',$id)->first();
    if ($fresh) {
        $fresh->final_price_ui = $this->computeFinalPrice(
            (float)$fresh->price_amount,
            $fresh->discount_amount !== null ? (float)$fresh->discount_amount : null,
            $fresh->discount_percent !== null ? (float)$fresh->discount_percent : null
        );
    }

    $this->logActivity(
        $request,
        'update',
        'Updated course "'.($fresh->title ?? $row->title).'"',
        'courses',
        $id,
        array_keys($upd),
        (array)$row,
        $fresh ? (array)$fresh : null
    );

    return response()->json([
        'status'=>'success',
        'message'=>'Course updated',
        'data'=>$fresh
    ]);
}

public function destroy(Request $request, string $course)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

    $row = $this->findCourseOr404($course);
    if (!$row) return response()->json(['error'=>'Course not found'], 404);

    DB::table('courses')->where('id', $row->id)->update([
        'status'     => 'archived',
        'deleted_at' => now(),
        'updated_at' => now(),
    ]);

    $this->logActivity($request,'destroy','Archived/Deleted course "'.$row->title.'"','courses',(int)$row->id,['status','deleted_at'],(array)$row,null);

    return response()->json(['status'=>'success','message'=>'Course deleted']);
}

public function mediaIndex(Request $r, string $course)
{
    if ($resp = $this->requireRole($r, ['admin','superadmin'])) return $resp;

    $row = $this->findCourseOr404($course);
    if (!$row) return response()->json(['error'=>'Course not found'], 404);

    $media = DB::table('course_featured_media')
        ->where('course_id', $row->id)
        ->whereNull('deleted_at')
        ->orderBy('order_no')->orderBy('id')
        ->get();

    return response()->json(['course'=>$row,'media'=>$media]);
}

public function mediaUpload(Request $request, string $course)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

    $row = $this->findCourseOr404($course);
    if (!$row) return response()->json(['error'=>'Course not found'], 404);

    $a   = $this->actor($request);
    $now = now();
    $cid = (int)$row->id;

    $isMultipart = str_starts_with((string)$request->header('Content-Type'), 'multipart/form-data');

    $inserted = [];
    DB::beginTransaction();
    try {
        if ($isMultipart && ($request->hasFile('files') || $request->hasFile('file'))) {
            $files = $request->file('files') ?: [$request->file('file')];

            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $mime = $file->getMimeType();
                $ext  = strtolower($file->getClientOriginalExtension() ?: '');
                $ft   = $this->detectFeaturedType($mime, $file->getClientOriginalName());

                $subdir  = $this->mediaSubdirFor($ft);                             // assets/media/images/course | videos | ...
                $destDir = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . $subdir;
                File::ensureDirectoryExists($destDir, 0755, true);

                $filename = 'course-' . $row->uuid . '-' . Str::uuid()->toString() . ($ext ? ('.'.$ext) : '');
                $file->move($destDir, $filename);

                $relative = $subdir . '/' . $filename;                             // assets/media/images/course/xxx.jpg
                $url      = $this->toPublicUrl($relative);                         // https://app.url/assets/media/images/course/xxx.jpg

                $insId = DB::table('course_featured_media')->insertGetId([
                    'uuid'          => (string) Str::uuid(),
                    'course_id'     => $cid,
                    'featured_type' => $ft,
                    'featured_url'  => $url,                                       // store full link
                    'order_no'      => $this->nextMediaOrderNo($cid),
                    'status'        => 'active',
                    'created_by'    => $a['id'] ?: null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                    'deleted_at'    => null,
                    'metadata'      => json_encode(new \stdClass()),
                ]);

                $inserted[] = DB::table('course_featured_media')->where('id',$insId)->first();
            }
        } else {
            $data = $request->validate([
                'url'           => ['required','url'],
                'featured_type' => ['nullable', Rule::in(['image','video','audio','pdf','other'])],
            ]);
            $ft = $data['featured_type'] ?? $this->detectFeaturedType(null, $data['url']);

            $insId = DB::table('course_featured_media')->insertGetId([
                'uuid'          => (string) Str::uuid(),
                'course_id'     => $cid,
                'featured_type' => $ft,
                'featured_url'  => $data['url'],                                   // remote URL
                'order_no'      => $this->nextMediaOrderNo($cid),
                'status'        => 'active',
                'created_by'    => $a['id'] ?: null,
                'created_at'    => $now,
                'updated_at'    => $now,
                'deleted_at'    => null,
                'metadata'      => json_encode(new \stdClass()),
            ]);
            $inserted[] = DB::table('course_featured_media')->where('id',$insId)->first();
        }

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('[Course Media] upload failed', ['error'=>$e->getMessage()]);
        return response()->json(['error'=>'Upload failed'], 500);
    }

    $this->logActivity($request,'upload','Uploaded featured media','course_featured_media',null,null,null,['count'=>count($inserted),'course_id'=>$cid]);

    return response()->json(['status'=>'success','inserted'=>$inserted], 201);
}

public function mediaReorder(Request $request, string $course)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

    $row = $this->findCourseOr404($course);
    if (!$row) return response()->json(['error'=>'Course not found'], 404);

    $data = $request->validate([
        'ids'    => ['sometimes','array'],
        'ids.*'  => ['integer','min:1'],
        'orders' => ['sometimes','array'],
    ]);

    $cid = (int)$row->id;
    DB::beginTransaction();
    try {
        if (!empty($data['ids'])) {
            $i = 1;
            foreach ($data['ids'] as $id) {
                DB::table('course_featured_media')
                    ->where('course_id',$cid)->where('id',(int)$id)
                    ->update(['order_no'=>$i++, 'updated_at'=>now()]);
            }
        } elseif (!empty($data['orders'])) {
            foreach ($data['orders'] as $id => $ord) {
                DB::table('course_featured_media')
                    ->where('course_id',$cid)->where('id',(int)$id)
                    ->update(['order_no'=>(int)$ord, 'updated_at'=>now()]);
            }
        } else {
            return response()->json(['error'=>'Nothing to reorder'], 422);
        }
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['error'=>'Reorder failed'], 500);
    }

    $this->logActivity($request,'reorder','Reordered featured media','course_featured_media',null,['order_no'],null,null);

    $media = DB::table('course_featured_media')
        ->where('course_id',$cid)->whereNull('deleted_at')
        ->orderBy('order_no')->orderBy('id')->get();

    return response()->json(['status'=>'success','media'=>$media]);
}

public function mediaDestroy(Request $request, string $course, string $media)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

    $row = $this->findCourseOr404($course);
    if (!$row) return response()->json(['error'=>'Course not found'], 404);

    $mq = DB::table('course_featured_media')->where('course_id',$row->id)->whereNull('deleted_at');
    if (ctype_digit($media)) $mq->where('id',(int)$media); else $mq->where('uuid',$media);
    $m = $mq->first();
    if (!$m) return response()->json(['error'=>'Media not found'], 404);

    DB::table('course_featured_media')->where('id',$m->id)->update([
        'status'     => 'deleted',
        'deleted_at' => now(),
        'updated_at' => now(),
    ]);

    $this->logActivity($request,'destroy','Deleted featured media','course_featured_media',(int)$m->id,['status','deleted_at'],(array)$m,null);

    return response()->json(['status'=>'success','message'=>'Media deleted']);
}
//media hard delete
public function mediaHardDestroy(Request $request, string $course, string $media)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

    $row = $this->findCourseOr404($course);
    if (!$row) return response()->json(['error'=>'Course not found'], 404);

    $mq = DB::table('course_featured_media')->where('course_id', $row->id)->whereNull('deleted_at');
    if (ctype_digit($media)) {
        $mq->where('id', (int)$media);
    } else {
        $mq->where('uuid', $media);
    }

    $m = $mq->first();
    if (!$m) return response()->json(['error'=>'Media not found'], 404);

    // Optionally log before deletion so you retain the old data
    $this->logActivity($request, 'destroy', 'Hard deleted featured media (before delete)', 'course_featured_media', (int)$m->id, [], (array)$m, null);

    // Hard delete the row
    DB::table('course_featured_media')->where('id', $m->id)->delete();

    return response()->json(['status'=>'success','message'=>'Media permanently deleted']);
}


public function viewCourse(Request $r, string $key)
{
    // Identify viewer; staff can see everything
    $role    = (string) $r->attributes->get('auth_role');
    $isStaff = in_array($role, ['admin','superadmin','instructor'], true);

    // ----- Fetch course by id/uuid/slug (staff: any status; public: only published) -----
    $q = DB::table('courses')->whereNull('deleted_at');
    if (ctype_digit($key)) {
        $q->where('id', (int)$key);
    } elseif (\Illuminate\Support\Str::isUuid($key)) {
        $q->where('uuid', $key);
    } else {
        $q->where('slug', $key);
    }
    if (!$isStaff) {
        $q->where('status', 'published'); // public-only restriction
    }

    $course = $q->first();
    if (!$course) return response()->json(['error' => 'Course not found'], 404);

    // ----- Pricing breakdown -----
    $price   = (float) ($course->price_amount ?? 0);
    $discAmt = $course->discount_amount !== null ? (float)$course->discount_amount : null;
    $discPct = $course->discount_percent !== null ? (float)$course->discount_percent : null;
    $final   = $this->computeFinalPrice($price, $discAmt, $discPct);
    $effectivePct = $price > 0 ? round((($price - $final) / $price) * 100, 2) : 0.0;

    // ----- Media (cover + gallery; active only) -----
    $mediaAll = DB::table('course_featured_media')
        ->where('course_id', $course->id)
        ->whereNull('deleted_at')
        ->where('status', 'active')
        ->orderBy('order_no')->orderBy('id')
        ->get();

    $cover = $mediaAll->firstWhere('featured_type', 'image') ?? $mediaAll->first();

    // ----- Modules (staff: all; public: only published) -----
    $modQ = DB::table('course_modules')
        ->select('id','uuid','title','short_description','long_description','order_no','status')
        ->where('course_id', $course->id)
        ->whereNull('deleted_at')
        ->orderBy('order_no')->orderBy('id');

    if (!$isStaff) {
        $modQ->where('status', 'published'); // public-only restriction
    }
    $modules = $modQ->get();

    // ----- Optional: pull duration from metadata if present -----
    $durationHours = null;
    if (!empty($course->metadata)) {
        try {
            $meta = is_string($course->metadata) ? json_decode($course->metadata, true) : $course->metadata;
            if (is_array($meta)) {
                if (isset($meta['duration_hours']))     $durationHours = (float)$meta['duration_hours'];
                elseif (isset($meta['duration']))       $durationHours = (float)$meta['duration'];          // common alias
                elseif (isset($meta['duration_minutes'])) $durationHours = round(((int)$meta['duration_minutes'])/60, 2);
            }
        } catch (\Throwable $e) { /* ignore */ }
    }

    // ----- Shape response for the UI -----
    $payload = [
        'course' => [
            'id'                => (int)$course->id,
            'uuid'              => $course->uuid,
            'slug'              => $course->slug,
            'title'             => $course->title,
            'short_description' => $course->short_description,
            'full_description'  => $course->full_description,
            'status'            => $course->status,     // show badge as-is in UI
            'difficulty'        => $course->level,
            'language'          => $course->language,
            'course_type'       => $course->course_type,
            'publish_at'        => $course->publish_at,
            'unpublish_at'      => $course->unpublish_at,
            'created_at'        => $course->created_at,
            'duration_hours'    => $durationHours,      // nullable; use when available
        ],
        'pricing' => [
            'currency'           => $course->price_currency ?? 'INR',
            'original'           => round($price, 2),
            'final'              => $final,
            'discount_amount'    => $discAmt,
            'discount_percent'   => $discPct,
            'effective_percent'  => $effectivePct,
            'is_free'            => ($course->course_type === 'free') || ($price <= 0),
            'has_discount'       => ($final < $price),
            'discount_expires_at'=> $course->discount_expires_at,
        ],
        'media' => [
            'cover'   => $cover ? [
                'id'   => (int)$cover->id,
                'uuid' => $cover->uuid,
                'type' => $cover->featured_type,
                'url'  => $cover->featured_url,
            ] : null,
            'gallery' => $mediaAll->map(fn($m) => [
                'id'   => (int)$m->id,
                'uuid' => $m->uuid,
                'type' => $m->featured_type,
                'url'  => $m->featured_url,
            ])->values(),
        ],
        'modules' => $modules->map(fn($m) => [
            'id'                => (int)$m->id,
            'uuid'              => $m->uuid,
            'title'             => $m->title,
            'short_description' => $m->short_description,
            'long_description'  => $m->long_description,
            'order_no'          => (int)$m->order_no,
            'status'            => $m->status,
        ])->values(),
    ];

    $this->logWithActor('[Course View] payload prepared', $r, [
        'course_id' => (int)$course->id,
        'modules'   => count($payload['modules']),
        'media'     => count($payload['media']['gallery']),
        'public'    => !$isStaff,
        'status'    => $course->status,
    ]);

    return response()->json(['data' => $payload]);
}
public function viewCourseByBatch(Request $r, string $batchKey)
{
    // ---- role from CheckRole (canonical: superadmin/admin/instructor/student/author)
    $role = (string) $r->attributes->get('auth_role');
    $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);

    if (!$role || !in_array($role, ['superadmin','admin','instructor','student'], true)) {
        return response()->json(['error' => 'Unauthorized Access'], 403);
    }

    $isAdminLike  = in_array($role, ['superadmin','admin'], true);
    $isInstructor = $role === 'instructor';
    $isStudent    = $role === 'student';
    $isStaff      = $isAdminLike || $isInstructor;

    // ---- resolve batch by id / uuid / (optional) slug
    $bq = DB::table('batches')->whereNull('deleted_at');
    if (ctype_digit($batchKey)) {
        $bq->where('id', (int)$batchKey);
    } elseif (\Illuminate\Support\Str::isUuid($batchKey)) {
        $bq->where('uuid', $batchKey);
    } elseif (Schema::hasColumn('batches','slug')) {
        $bq->where('slug', $batchKey);
    } else {
        return response()->json(['error' => 'Batch not found'], 404);
    }

    $batch = $bq->first();
    if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

    // ---- detect pivot FK columns safely
    $biUserCol = Schema::hasColumn('batch_instructors','user_id')
        ? 'user_id'
        : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

    $bsUserCol = Schema::hasColumn('batch_students','user_id')
        ? 'user_id'
        : (Schema::hasColumn('batch_students','student_id') ? 'student_id' : null);

    // ---- RBAC: must be assigned if instructor/student
    if ($isInstructor) {
        if (!$biUserCol) {
            return response()->json(['error'=>'Schema issue: batch_instructors needs user_id OR instructor_id'], 500);
        }
        $assigned = DB::table('batch_instructors')
            ->where('batch_id', $batch->id)
            ->whereNull('deleted_at')
            ->where($biUserCol, $uid)
            ->exists();
        if (!$assigned) return response()->json(['error' => 'Forbidden'], 403);
    }

    if ($isStudent) {
        if (!$bsUserCol) {
            return response()->json(['error'=>'Schema issue: batch_students needs user_id OR student_id'], 500);
        }
        $enrolled = DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->whereNull('deleted_at')
            ->where($bsUserCol, $uid)
            ->exists();
        if (!$enrolled) return response()->json(['error' => 'Forbidden'], 403);
    }

    // ---- load course for this batch (students only see published)
    $cq = DB::table('courses')
        ->whereNull('deleted_at')
        ->where('id', $batch->course_id);

    if ($isStudent) $cq->where('status', 'published');

    $course = $cq->first();
    if (!$course) return response()->json(['error' => 'Course not found for this batch'], 404);

    // ---- pricing
    $price   = (float) ($course->price_amount ?? 0);
    $discAmt = $course->discount_amount !== null ? (float)$course->discount_amount : null;
    $discPct = $course->discount_percent !== null ? (float)$course->discount_percent : null;
    $final   = $this->computeFinalPrice($price, $discAmt, $discPct);
    $effectivePct = $price > 0 ? round((($price - $final) / $price) * 100, 2) : 0.0;

    // ---- media (active only)
    $mediaAll = DB::table('course_featured_media')
        ->where('course_id', $course->id)
        ->whereNull('deleted_at')
        ->where('status', 'active')
        ->orderBy('order_no')->orderBy('id')
        ->get();

    $cover = $mediaAll->firstWhere('featured_type', 'image') ?? $mediaAll->first();

    // ---- instructors for sidebar (join only on the column that exists)
    $instructors = collect();
    if ($biUserCol) {
        $instructors = DB::table('batch_instructors as bi')
            ->join('users as u', function($j) use ($biUserCol){
                $j->on('u.id', '=', DB::raw("bi.$biUserCol"));
            })
            ->where('bi.batch_id', $batch->id)
            ->whereNull('bi.deleted_at')
            ->whereNull('u.deleted_at')
            ->select('u.id','u.uuid','u.name','u.email','u.role')
            ->get()
            ->map(fn($u) => [
                'id'    => (int)$u->id,
                'uuid'  => $u->uuid,
                'name'  => $u->name,
                'email' => $u->email,
                'role'  => $u->role,
            ])
            ->values();
    }

    // ---- batch stats
    $studentsCount = DB::table('batch_students')
        ->where('batch_id', $batch->id)
        ->whereNull('deleted_at')
        ->count();

    // ---- duration from course->metadata (optional)
    $durationHours = null;
    if (!empty($course->metadata)) {
        try {
            $meta = is_string($course->metadata) ? json_decode($course->metadata, true) : $course->metadata;
            if (is_array($meta)) {
                if (isset($meta['duration_hours']))        $durationHours = (float)$meta['duration_hours'];
                elseif (isset($meta['duration']))          $durationHours = (float)$meta['duration'];
                elseif (isset($meta['duration_minutes']))  $durationHours = round(((int)$meta['duration_minutes'])/60, 2);
            }
        } catch (\Throwable $e) {}
    }

    // =========================================================
    // ✅ batch_course_module ONLY + join course_modules for LIVE details
    // IMPORTANT: we will rely on batch_course_module_id (already selected)
    // =========================================================
    $batchMods = DB::table('batch_course_module as bcm')
        ->join('course_modules as cm', function ($j) {
            $j->on('cm.id', '=', 'bcm.course_module_id')
              ->whereNull('cm.deleted_at');
        })
        ->whereNull('bcm.deleted_at')
        ->where('bcm.batch_id', (int)$batch->id)
        ->where('bcm.course_id', (int)$course->id)
        ->orderBy('bcm.order_no')->orderBy('bcm.id')
        ->select([
            // batch_course_module keys (✅ use these!)
            'bcm.id as batch_course_module_id',
            'bcm.uuid as batch_course_module_uuid',

            'bcm.batch_id',
            'bcm.course_id',
            'bcm.course_module_id',
            'bcm.is_completed as bcm_is_completed',
            'bcm.order_no as bcm_order_no',
            'bcm.status as bcm_status',
            'bcm.metadata as bcm_metadata',

            // LIVE course_modules details
            'cm.uuid as cm_uuid',
            'cm.title as cm_title',
            'cm.short_description as cm_short_description',
            'cm.long_description as cm_long_description',
            'cm.order_no as cm_order_no',
            'cm.status as cm_status',
        ])
        ->get();

    if ($batchMods->isEmpty()) {
        $payload = [
            'batch' => (array)$batch,
            'course' => [
                'id'                => (int)$course->id,
                'uuid'              => $course->uuid,
                'slug'              => $course->slug,
                'title'             => $course->title,
                'short_description' => $course->short_description,
                'full_description'  => $course->full_description,
                'status'            => $course->status,
                'difficulty'        => $course->level,
                'language'          => $course->language,
                'course_type'       => $course->course_type,
                'publish_at'        => $course->publish_at,
                'unpublish_at'      => $course->unpublish_at,
                'created_at'        => $course->created_at,
                'duration_hours'    => $durationHours,
            ],
            'pricing' => [
                'currency'            => $course->price_currency ?? 'INR',
                'original'            => round($price, 2),
                'final'               => $final,
                'discount_amount'     => $discAmt,
                'discount_percent'    => $discPct,
                'effective_percent'   => $effectivePct,
                'is_free'             => ($course->course_type === 'free') || ($price <= 0),
                'has_discount'        => ($final < $price),
                'discount_expires_at' => $course->discount_expires_at,
            ],
            'media' => [
                'cover'   => $cover ? [
                    'id'   => (int)$cover->id,
                    'uuid' => $cover->uuid,
                    'type' => $cover->featured_type,
                    'url'  => $cover->featured_url,
                ] : null,
                'gallery' => $mediaAll->map(fn($m) => [
                    'id'   => (int)$m->id,
                    'uuid' => $m->uuid,
                    'type' => $m->featured_type,
                    'url'  => $m->featured_url,
                ])->values(),
            ],
            'modules' => [],
            'instructors' => $instructors,
            'stats' => [
                'students_count'      => (int)$studentsCount,
                'you_are_instructor'  => $isInstructor,
                'you_are_student'     => $isStudent,
            ],
            'permissions' => [
                'can_view_unpublished_modules' => $isStaff,
            ],
        ];
        return response()->json(['data' => $payload]);
    }

    // =========================================================
    // ✅ settings row per (batch_id, course_id)
    // =========================================================
    $defaults = [
        'previous_module_completed' => 0,
        'assignment_submitted'      => 0,
        'exam_submitted'            => 0,
        'coding_test_submitted'     => 0,
    ];

    $settingsRow = DB::table('batch_course_module_settings')
        ->where('batch_id', (int)$batch->id)
        ->where('course_id', (int)$course->id)
        ->first();

    $rules = $defaults;
    if ($settingsRow && !empty($settingsRow->settings_json)) {
        try {
            $tmp = json_decode($settingsRow->settings_json, true);
            if (is_array($tmp)) {
                $rules = array_merge($defaults, array_intersect_key($tmp, $defaults));
                $rules = array_map(fn($v) => (int)$v, $rules);
            }
        } catch (\Throwable $e) {}
    }

    // =========================================================
    // ✅ access states ONLY FOR STUDENT
    // key by batch_course_module_id
    // =========================================================
    $accessStates = [];
    if ($isStudent) {
        $accessStates = $this->computeBatchModuleAccessStatesForStudent(
            (int)$batch->id,
            (int)$course->id,
            (int)$uid,
            $rules,
            $batchMods
        );
    }

    // =========================================================
    // ✅ output modules
    // =========================================================
    $modules = $batchMods->map(function($m) use ($isStudent, $isStaff, $rules, $accessStates) {

        $access = 'unlocked';
        if ($isStudent) {
            $access = $accessStates[(int)$m->batch_course_module_id] ?? 'locked';
        } elseif ($isStaff) {
            $access = 'unlocked';
        }

        return [
            'id'   => (int)$m->course_module_id,
            'uuid' => (string)$m->cm_uuid,

            'title'             => (string)$m->cm_title,
            'short_description' => $m->cm_short_description,
            'long_description'  => $m->cm_long_description,
            'order_no'          => (int)($m->cm_order_no ?? 0),
            'status'            => (string)($m->cm_status ?? 'draft'),

            'batch_course_module_id'   => (int)$m->batch_course_module_id,
            'batch_course_module_uuid' => (string)$m->batch_course_module_uuid,
            'is_completed'             => (int)($m->bcm_is_completed ?? 0),
            'access_state'             => (string)$access,

            // optional debug
            'rules' => $rules,
        ];
    })->values();

    $payload = [
        'batch' => (array)$batch,
        'course' => [
            'id'                => (int)$course->id,
            'uuid'              => $course->uuid,
            'slug'              => $course->slug,
            'title'             => $course->title,
            'short_description' => $course->short_description,
            'full_description'  => $course->full_description,
            'status'            => $course->status,
            'difficulty'        => $course->level,
            'language'          => $course->language,
            'course_type'       => $course->course_type,
            'publish_at'        => $course->publish_at,
            'unpublish_at'      => $course->unpublish_at,
            'created_at'        => $course->created_at,
            'duration_hours'    => $durationHours,
        ],
        'pricing' => [
            'currency'            => $course->price_currency ?? 'INR',
            'original'            => round($price, 2),
            'final'               => $final,
            'discount_amount'     => $discAmt,
            'discount_percent'    => $discPct,
            'effective_percent'   => $effectivePct,
            'is_free'             => ($course->course_type === 'free') || ($price <= 0),
            'has_discount'        => ($final < $price),
            'discount_expires_at' => $course->discount_expires_at,
        ],
        'media' => [
            'cover'   => $cover ? [
                'id'   => (int)$cover->id,
                'uuid' => $cover->uuid,
                'type' => $cover->featured_type,
                'url'  => $cover->featured_url,
            ] : null,
            'gallery' => $mediaAll->map(fn($m) => [
                'id'   => (int)$m->id,
                'uuid' => $m->uuid,
                'type' => $m->featured_type,
                'url'  => $m->featured_url,
            ])->values(),
        ],
        'modules' => $modules,
        'instructors' => $instructors,
        'stats' => [
            'students_count'      => (int)$studentsCount,
            'you_are_instructor'  => $isInstructor,
            'you_are_student'     => $isStudent,
        ],
        'permissions' => [
            'can_view_unpublished_modules' => $isStaff,
        ],
    ];

    $this->logWithActor('[Course View By Batch] payload prepared', $r, [
        'batch_id'  => (int)$batch->id,
        'course_id' => (int)$course->id,
        'modules'   => count($payload['modules']),
        'role'      => $role,
    ]);

    return response()->json(['data' => $payload]);
}
private function computeBatchModuleAccessStatesForStudent(
    int $batchId,
    int $courseId,
    int $userId,
    array $rules,
    \Illuminate\Support\Collection $batchModsOrdered
): array {

    $defaults = [
        'previous_module_completed' => 0,
        'assignment_submitted'      => 0,
        'exam_submitted'            => 0,
        'coding_test_submitted'     => 0,
    ];
    $rules = array_merge($defaults, array_intersect_key($rules, $defaults));
    $rules = array_map(fn($v) => (int)$v, $rules);

    // If no rule enabled → all unlocked
    if (array_sum($rules) <= 0) {
        return $batchModsOrdered
            ->mapWithKeys(fn($m) => [(int)$m->batch_course_module_id => 'unlocked'])
            ->all();
    }

    // all module ids in this batch-course
    $cmIds = $batchModsOrdered->pluck('course_module_id')->map(fn($v)=>(int)$v)->values()->all();

    // ✅ actor column for assignment_submissions is student_id (your confirmation)
    $assignmentActorCol = 'student_id';

    // auto-detect actor col for quizz_attempt_batch / coding_results
    $quizAttemptActorCol = Schema::hasColumn('quizz_attempt_batch', 'student_id')
        ? 'student_id'
        : (Schema::hasColumn('quizz_attempt_batch', 'user_id') ? 'user_id' : null);

    $codingActorCol = Schema::hasColumn('coding_results', 'student_id')
        ? 'student_id'
        : (Schema::hasColumn('coding_results', 'user_id') ? 'user_id' : null);

    // 2) Assignment submissions
    $assignmentSubmittedSet = [];
    if ($rules['assignment_submitted'] === 1) {
        $q = DB::table('assignment_submissions');

        if (Schema::hasColumn('assignment_submissions','deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $q->where($assignmentActorCol, $userId);

        // batch filter only if column exists
        if (Schema::hasColumn('assignment_submissions','batch_id')) {
            $q->where('batch_id', $batchId);
        }

        $q->whereIn('course_module_id', $cmIds);

        $assignmentSubmittedSet = $q->pluck('course_module_id')
            ->map(fn($v)=>(int)$v)
            ->flip()
            ->all();
    }

    // 3) ✅ Exam submissions via quizz_attempt_batch (batch_id + course_module_id + actor)
    // We'll store attempts keyed by course_module_id
    $examAttemptSetByCourseModule = [];
    if ($rules['exam_submitted'] === 1 && $quizAttemptActorCol) {

        $q = DB::table('quizz_attempt_batch');

        if (Schema::hasColumn('quizz_attempt_batch','deleted_at')) {
            $q->whereNull('deleted_at');
        }

        // mandatory filters per your requirement
        if (Schema::hasColumn('quizz_attempt_batch','batch_id')) {
            $q->where('batch_id', $batchId);
        } else {
            // if batch_id column doesn't exist, cannot reliably enforce this rule
            // keep set empty so unlock won't happen incorrectly
            $q = null;
        }

        if ($q) {
            $q->where($quizAttemptActorCol, $userId);

            // course_module_id must exist here
            if (Schema::hasColumn('quizz_attempt_batch','course_module_id')) {
                $q->whereIn('course_module_id', $cmIds);

                $examAttemptSetByCourseModule = $q->pluck('course_module_id')
                    ->map(fn($v)=>(int)$v)
                    ->flip()
                    ->all();
            }
        }
    }

    // 4) Coding submissions via question mapping
    $questionByCourseModule = [];
    $codingResultSet = [];
    if ($rules['coding_test_submitted'] === 1) {

        $questionByCourseModule = DB::table('batch_coding_questions')
            ->whereNull('deleted_at')
            ->where('batch_id', $batchId)
            ->whereIn('course_module_id', $cmIds)
            ->pluck('question_id', 'course_module_id')
            ->mapWithKeys(fn($qid, $cmId) => [(int)$cmId => (int)$qid])
            ->all();

        $qIds = array_values(array_unique(array_filter(array_values($questionByCourseModule))));
        if (!empty($qIds) && $codingActorCol) {
            $q = DB::table('coding_results');
            if (Schema::hasColumn('coding_results','deleted_at')) $q->whereNull('deleted_at');
            if (Schema::hasColumn('coding_results','batch_id'))   $q->where('batch_id', $batchId);

            $q->where($codingActorCol, $userId)
              ->whereIn('question_id', $qIds);

            $codingResultSet = $q->pluck('question_id')
                ->map(fn($v)=>(int)$v)
                ->flip()
                ->all();
        }
    }

    // ---------- Sequential unlock (based on IMMEDIATE PREVIOUS bcm row) ----------
    $states = [];
    $mods = $batchModsOrdered->values();

    for ($i = 0; $i < $mods->count(); $i++) {
        $m = $mods[$i];

        // ✅ first module ALWAYS unlocked
        if ($i === 0) {
            $states[(int)$m->batch_course_module_id] = 'unlocked';
            continue;
        }

        $prev = $mods[$i - 1];
        $prevCourseModuleId = (int)$prev->course_module_id;

        $ok = true;

        // 1) previous module completed? -> check previous bcm row's is_completed
        if ($rules['previous_module_completed'] === 1) {
            $ok = $ok && ((int)($prev->bcm_is_completed ?? 0) === 1);
        }

        // 2) assignment submitted? -> check previous module assignment by actor
        if ($ok && $rules['assignment_submitted'] === 1) {
            $ok = isset($assignmentSubmittedSet[$prevCourseModuleId]);
        }

        // 3) ✅ exam submitted? -> check quizz_attempt_batch row for prev module + user + batch
        if ($ok && $rules['exam_submitted'] === 1) {
            $ok = isset($examAttemptSetByCourseModule[$prevCourseModuleId]);
        }

        // 4) coding submitted? -> check previous module coding by actor
        if ($ok && $rules['coding_test_submitted'] === 1) {
            $qid = (int)($questionByCourseModule[$prevCourseModuleId] ?? 0);
            $ok = $qid > 0 && isset($codingResultSet[$qid]);
        }

        $states[(int)$m->batch_course_module_id] = $ok ? 'unlocked' : 'locked';
    }

    return $states;
}

public function listCourseBatchCards(Request $r)
{
    // ---- role from CheckRole (canonical): superadmin, admin, instructor, student
    $role = (string) $r->attributes->get('auth_role');
    $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);

    if (!$role || !in_array($role, ['superadmin','admin','instructor','student'], true)) {
        return response()->json(['error' => 'Unauthorized Access'], 403);
    }

    // --- query params
    $page    = max(1, (int)$r->query('page', 1));
    $perPage = max(1, min(100, (int)$r->query('per_page', 20)));
    $qText   = trim((string)$r->query('q', ''));      // search course title / batch name
    $sort    = (string)$r->query('sort', '-b.created_at'); // -b.starts_at, c.title, etc.

    $isInstructor = $role === 'instructor';
    $isStudent    = $role === 'student';

    // ---- your schema (from migrations)
    $bNameCol   = 'badge_title';  // batches.badge_title
    $bStartCol  = 'starts_at';    // batches.starts_at
    // end column (graceful detection)
    $bEndCol    = \Illuminate\Support\Facades\Schema::hasColumn('batches','ends_at')   ? 'ends_at'
                : (\Illuminate\Support\Facades\Schema::hasColumn('batches','end_date') ? 'end_date'
                : (\Illuminate\Support\Facades\Schema::hasColumn('batches','finish_at')? 'finish_at'
                : null));

    $biUserCol  = 'user_id';      // batch_instructors.user_id
    $bsUserCol  = 'user_id';      // batch_students.user_id

    // --- base: batches + courses
    $q = DB::table('batches as b')
        ->join('courses as c', 'c.id', '=', 'b.course_id')
        ->whereNull('b.deleted_at')
        ->whereNull('c.deleted_at');

    // Students see only published courses
    if ($isStudent) {
        $q->where('c.status', 'published');
    }

    // Role filters
    if ($isInstructor) {
        $q->join('batch_instructors as bi', 'bi.batch_id', '=', 'b.id')
          ->whereNull('bi.deleted_at')
          ->where("bi.$biUserCol", $uid);
    } elseif ($isStudent) {
        $q->join('batch_students as bs', 'bs.batch_id', '=', 'b.id')
          ->whereNull('bs.deleted_at')
          ->where("bs.$bsUserCol", $uid);
    }

    // Search filter
    if ($qText !== '') {
        $q->where(function($w) use ($qText, $bNameCol) {
            $w->where('c.title', 'like', "%$qText%")
              ->orWhere('c.slug', 'like', "%$qText%")
              ->orWhere("b.$bNameCol", 'like', "%$qText%");
        });
    }

    // Sorting
    $dir = 'asc'; $col = $sort;
    if (str_starts_with($sort, '-')) { $dir='desc'; $col=ltrim($sort,'-'); }
    $sortable = ['b.created_at','b.updated_at','c.title','c.created_at',"b.$bStartCol"];
    if ($bEndCol) $sortable[] = "b.$bEndCol";
    if (!in_array($col, $sortable, true)) { $col = "b.$bStartCol"; $dir='desc'; }

    // Count + page
    $total = (clone $q)->count();

    $rows = $q->select(
            'b.id as batch_id',
            'b.uuid as batch_uuid',
            DB::raw("b.$bNameCol as batch_name"),
            DB::raw("b.$bStartCol as batch_start"),
            DB::raw($bEndCol ? "b.$bEndCol as batch_end" : "NULL as batch_end"),
            'b.status as batch_status',
            'c.id as course_id',
            'c.uuid as course_uuid',
            'c.slug as course_slug',
            'c.title as course_title',
            'c.short_description as course_short',
            'c.status as course_status'
        )
        ->orderBy($col, $dir)
        ->offset(($page-1)*$perPage)
        ->limit($perPage)
        ->get();

    // --- Covers in one shot (prefer image; then any)
    $courseIds = $rows->pluck('course_id')->unique()->values();
    $covers = collect();
    if ($courseIds->count() > 0) {
        $covers = DB::table('course_featured_media')
            ->whereIn('course_id', $courseIds)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->orderByRaw("CASE WHEN featured_type='image' THEN 0 ELSE 1 END")
            ->orderBy('order_no')
            ->orderBy('id')
            ->get()
            ->groupBy('course_id')
            ->map(fn($grp) => optional($grp->first())->featured_url);
    }

    // --- Build cards (integer days only)
    $today = Carbon::today();
    $cards = $rows->map(function($r) use ($covers, $today) {
        $start = $r->batch_start ? Carbon::parse($r->batch_start)->startOfDay() : null;
        $end   = $r->batch_end   ? Carbon::parse($r->batch_end)->endOfDay()     : null;

        $durationDays  = null;
        if ($start && $end) {
            // Inclusive duration: Mon→Wed = 3
            $durationDays = (int) ($start->diffInDays($end) + 1);
        }

        $remainingDays = null;
        if ($end) {
            // Negative if in the past; clamp to 0
            $remainingDays = (int) max(0, $today->diffInDays($end, false));
        }

        return [
            'batch' => [
                'id'             => (int)$r->batch_id,
                'uuid'           => $r->batch_uuid,
                'name'           => $r->batch_name,
                'start_date'     => $r->batch_start,
                'end_date'       => $r->batch_end,
                'duration_days'  => $durationDays,   // integer
                'remaining_days' => $remainingDays,  // integer, never negative
                'status'         => $r->batch_status,
            ],
            'course' => [
                'id'                => (int)$r->course_id,
                'uuid'              => $r->course_uuid,
                'slug'              => $r->course_slug,
                'title'             => $r->course_title,
                'short_description' => $r->course_short,
                'status'            => $r->course_status,
                'cover_url'         => $covers->get($r->course_id),
            ],
            // Frontend "View" → /api/courses/by-batch/{batch_uuid}/view
            'view_hint' => [
                'api' => "/api/courses/by-batch/{$r->batch_uuid}/view"
            ],
        ];
    })->values();

    return response()->json([
        'data' => $cards,
        'pagination' => [
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $total,
        ],
        'role' => $role,
    ]);
}
    /**
 * GET /api/courses/deleted
 * List soft-deleted courses (admin/superadmin only)
 */
public function indexDeleted(Request $r)
{
    if ($resp = $this->requireRole($r, ['admin','superadmin'])) return $resp;

    $page     = max(1, (int)$r->query('page', 1));
    $perPage  = max(1, min(100, (int)$r->query('per_page', 20)));
    $qText    = trim((string)$r->query('q', ''));
    $sort     = (string)$r->query('sort', '-deleted_at');

    $q = DB::table('courses')->whereNotNull('deleted_at');

    if ($qText !== '') {
        $q->where(function($w) use ($qText){
            $w->where('title','like',"%$qText%")->orWhere('slug','like',"%$qText%");
        });
    }

    $dir = 'asc'; $col = $sort;
    if (str_starts_with($sort, '-')) { $dir = 'desc'; $col = ltrim($sort, '-'); }
    if (!in_array($col, ['deleted_at','title','status','course_type','created_at'], true)) { $col='deleted_at'; $dir='desc'; }

    $total = (clone $q)->count();
    $rows  = $q->orderBy($col, $dir)->offset(($page-1)*$perPage)->limit($perPage)->get();

    // Add minimal UI fields
    foreach ($rows as $row) {
        $row->final_price_ui = $this->computeFinalPrice(
            (float)$row->price_amount,
            $row->discount_amount !== null ? (float)$row->discount_amount : null,
            $row->discount_percent !== null ? (float)$row->discount_percent : null
        );

        // count media items (soft-deleted or active)
        $row->media_count = (int) DB::table('course_featured_media')
            ->where('course_id', $row->id)
            ->count();
    }

    return response()->json(['data'=>$rows,'pagination'=>['page'=>$page,'per_page'=>$perPage,'total'=>$total]]);
}

/**
 * POST /api/courses/{course}/restore
 * Restore a soft-deleted course (admin/superadmin only)
 */
public function restore(Request $request, string $course)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

    // find including deleted
    $q = DB::table('courses');
    if (ctype_digit($course)) $q->where('id', (int)$course);
    elseif (Str::isUuid($course)) $q->where('uuid', $course);
    else $q->where('slug', $course);

    $row = $q->first();
    if (!$row) return response()->json(['error'=>'Course not found'], 404);
    if ($row->deleted_at === null) return response()->json(['error'=>'Course is not deleted'], 422);

    $id = (int)$row->id;

    DB::beginTransaction();
    try {
        // restore course: clear deleted_at, set status to draft (change if you prefer)
        DB::table('courses')->where('id', $id)->update([
            'deleted_at' => null,
            'status'     => 'draft',
            'updated_at' => now(),
        ]);

        // restore any soft-deleted media for this course
        DB::table('course_featured_media')
            ->where('course_id', $id)
            ->whereNotNull('deleted_at')
            ->update([
                'deleted_at' => null,
                'status'     => 'active',
                'updated_at' => now(),
            ]);

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('[Course Restore] failed', ['error'=>$e->getMessage(), 'course'=>$course]);
        return response()->json(['error'=>'Restore failed'], 500);
    }

    $fresh = DB::table('courses')->where('id', $id)->first();

    $this->logActivity($request, 'store', 'Restored course "'.($fresh->title ?? $row->title).'"', 'courses', $id, null, (array)$row, $fresh ? (array)$fresh : null);

    // notify admins
    $this->persistNotification([
        'title'     => 'Course restored',
        'message'   => '“'.($fresh->title ?? $row->title).'” has been restored.',
        'receivers' => $this->adminReceivers(),
        'metadata'  => ['action'=>'restored','course'=>['id'=>$id,'uuid'=>$row->uuid ?? null,'title'=>$fresh->title ?? null]],
        'type'      => 'course',
        'link_url'  => rtrim((string)config('app.url'), '/').'/admin/courses/'.$id,
        'priority'  => 'normal',
        'status'    => 'active',
    ]);

    $this->logWithActor('[Course Restore] success', $request, ['course_id'=>$id]);

    return response()->json(['status'=>'success','message'=>'Course restored','data'=>$fresh]);
}

/**
 * DELETE /api/courses/{course}/force
 * Permanently delete a course and its media (admin/superadmin only)
 * - removes DB rows and attempts to delete local files that belong to app.url
 */
public function forceDestroy(Request $request, string $course)
{
    if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

    // find including deleted
    $q = DB::table('courses');
    if (ctype_digit($course)) $q->where('id', (int)$course);
    elseif (Str::isUuid($course)) $q->where('uuid', $course);
    else $q->where('slug', $course);

    $row = $q->first();
    if (!$row) return response()->json(['error'=>'Course not found'], 404);

    $id = (int)$row->id;
    $appUrl = rtrim((string) config('app.url'), '/');

    DB::beginTransaction();
    try {
        // fetch media rows for cleanup
        $mediaRows = DB::table('course_featured_media')->where('course_id', $id)->get();

        // Attempt to delete local files referenced by featured_url if they belong to our app URL
        foreach ($mediaRows as $m) {
            if (!empty($m->featured_url) && str_starts_with((string)$m->featured_url, $appUrl)) {
                try {
                    $relative = ltrim(str_replace($appUrl, '', (string)$m->featured_url), '/');
                    $fullpath = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
                    if (File::exists($fullpath)) {
                        File::delete($fullpath);
                    }
                } catch (\Throwable $e) {
                    // log and continue; don't fail the whole delete because of a missing file
                    Log::warning('[Course ForceDelete] file delete failed', ['media_id'=>$m->id,'error'=>$e->getMessage()]);
                }
            }
        }

        // delete media rows (permanent)
        DB::table('course_featured_media')->where('course_id', $id)->delete();
        DB::table('course_modules')->where('course_id', $id)->delete();
        // Optionally delete batches? Usually batches may be kept — comment ou     t if you prefer:
        // DB::table('batches')->where('course_id', $id)->delete();

        // finally delete the course row
        DB::table('courses')->where('id', $id)->delete();

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('[Course ForceDelete] failed', ['error'=>$e->getMessage(),'course_id'=>$id]);
        return response()->json(['error'=>'Force delete failed'], 500);
    }

    $this->logActivity($request, 'destroy', 'Permanently deleted course "'.$row->title.'"', 'courses', $id, null, (array)$row, null);

    $this->logWithActor('[Course ForceDelete] success', $request, ['course_id'=>$id]);

    return response()->json(['status'=>'success','message'=>'Course permanently deleted']);
}
public function myCourses(Request $r)
{
    $a = $this->actor($r);
    if (!$a['id'] || !$a['role']) {
        return response()->json(['error' => 'Authentication required'], 401);
    }

    $role = strtolower((string)$a['role']);
    $uid  = (int)$a['id'];

    $isAdmin      = in_array($role, ['admin', 'super_admin', 'superadmin'], true);
    $isStudent    = ($role === 'student');
    $isInstructor = ($role === 'instructor');

    if (!$isAdmin && !$isStudent && !$isInstructor) {
        return response()->json(['error' => 'Unauthorized Access'], 403);
    }

    // same query params as index (no pagination)
    $qText       = trim((string)$r->query('q', ''));
    $status      = $r->query('status');                  // draft|published|archived (admin only)
    $type        = $r->query('course_type');             // paid|free
    $sort        = (string)$r->query('sort', '-created_at');
    $onlyDeleted = (string)$r->query('only_deleted', '') === '1';

    $categoryToken    = trim((string)$r->query('category', ''));
    $categoryFilterId = null;

    // Resolve course_categories.uuid -> id (same as index)
    if ($categoryToken !== '') {
        try {
            if (\Schema::hasTable('course_categories')) {
                $token = $categoryToken;

                $cat = DB::table('course_categories')->select('id','uuid')
                    ->where('uuid', $token)->first();

                if (!$cat && ctype_digit($token)) {
                    $cat = DB::table('course_categories')->select('id','uuid')
                        ->where('id', (int)$token)->first();
                }

                if (!$cat) {
                    $maybeHex = preg_replace('/[^a-fA-F0-9]/', '', $token);
                    if (strlen($maybeHex) === 32) {
                        $cat = DB::table('course_categories')->select('id','uuid')
                            ->whereRaw("LOWER(REPLACE(`uuid`, '-', '')) = ?", [mb_strtolower($maybeHex)])
                            ->first();
                    }
                }

                if ($cat) $categoryFilterId = (int)$cat->id;
            }
        } catch (\Throwable $ex) {
            \Log::error('[Courses.myCourses] category resolve failed', [
                'token' => $categoryToken,
                'error' => $ex->getMessage(),
            ]);
            $categoryFilterId = null;
        }
    }

    // One featured image per course (same as index)
    $mediaSub = DB::table('course_featured_media as cfm2')
        ->select('cfm2.course_id', DB::raw('MIN(cfm2.featured_url) as featured_url'))
        ->groupBy('cfm2.course_id');

    $q = DB::table('courses as c')
        ->leftJoinSub($mediaSub, 'cfm', function ($join) {
            $join->on('cfm.course_id', '=', 'c.id');
        })
        ->leftJoin('course_categories as cc', function ($join) {
            $join->on('cc.id', '=', 'c.category_id')
                 ->whereNull('cc.deleted_at');
        })
        ->select(
            'c.*',
            DB::raw('cfm.featured_url as thumbnail_url'),
            'cc.id as category_id',
            'cc.uuid as category_uuid',
            'cc.title as category_title'
        );

    // soft delete (same as index)
    if ($onlyDeleted) $q->whereNotNull('c.deleted_at');
    else $q->whereNull('c.deleted_at');

    // search (same as index)
    if ($qText !== '') {
        $q->where(function ($w) use ($qText) {
            $w->where('c.title', 'like', "%{$qText}%")
              ->orWhere('c.slug', 'like', "%{$qText}%");
        });
    }

    // paid/free (same as index)
    if ($type) {
        $q->where('c.course_type', $type);
    }

    // category (same as index)
    if (!empty($categoryFilterId)) {
        $q->where('c.category_id', $categoryFilterId);
    }

    // ✅ role scoping
    if ($isAdmin) {
        // admin: optional status filter
        if ($status && !$onlyDeleted) {
            $q->where('c.status', $status);
        }
    } else {
        // student/instructor: FORCE published (so your frontend status=published is consistent)
        $q->where('c.status', 'published');

        if ($isStudent) {
            $userCol = \Schema::hasColumn('batch_students', 'user_id') ? 'user_id' : 'student_id';

            $q->whereExists(function ($sub) use ($uid, $userCol) {
                $sub->select(DB::raw(1))
                    ->from('batches as b')
                    ->join('batch_students as bs', 'bs.batch_id', '=', 'b.id')
                    ->whereNull('b.deleted_at')
                    ->whereNull('bs.deleted_at')
                    ->where("bs.$userCol", $uid)
                    ->whereColumn('b.course_id', 'c.id');
            });

        } else { // instructor
            $userCol = \Schema::hasColumn('batch_instructors', 'user_id') ? 'user_id' : 'instructor_id';

            $q->whereExists(function ($sub) use ($uid, $userCol) {
                $sub->select(DB::raw(1))
                    ->from('batches as b')
                    ->join('batch_instructors as bi', 'bi.batch_id', '=', 'b.id')
                    ->whereNull('b.deleted_at')
                    ->whereNull('bi.deleted_at')
                    ->where("bi.$userCol", $uid)
                    ->whereColumn('b.course_id', 'c.id');
            });
        }
    }

    // sorting (same as index)
    $dir = 'asc';
    $col = $sort;
    if (str_starts_with($sort, '-')) {
        $dir = 'desc';
        $col = ltrim($sort, '-');
    }

    if (!in_array($col, ['created_at','title','status','course_type','order_no','deleted_at'], true)) {
        $col = 'created_at';
        $dir = 'desc';
    }

    $rows = $q->orderBy('c.'.$col, $dir)->get();

    foreach ($rows as $row) {
        $row->final_price_ui = $this->computeFinalPrice(
            (float)$row->price_amount,
            $row->discount_amount !== null ? (float)$row->discount_amount : null,
            $row->discount_percent !== null ? (float)$row->discount_percent : null
        );
    }

    return response()->json(['data' => $rows]);
}

}
