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


class CourseController extends Controller
{
    /* =========================
     *  Auth/Role helpers (same style)
     * ========================= */
    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
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
    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;
        $this->logWithActor('[Course Store] begin', $request);

        $data = $request->validate([
            'title'               => ['required','string','max:255'],
            'slug'                => ['nullable','string','max:140','unique:courses,slug'],
            'short_description'   => ['nullable','string'],
            'full_description'    => ['nullable','string'],
            'status'              => ['nullable', Rule::in(['draft','published','archived'])],
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
    if ($resp = $this->requireRole($r, ['admin','superadmin'])) return $resp;

    $page     = max(1, (int)$r->query('page', 1));
    $perPage  = max(1, min(100, (int)$r->query('per_page', 20)));
    $qText    = trim((string)$r->query('q', ''));
    $status   = $r->query('status');       // draft|published|archived
    $type     = $r->query('course_type');  // paid|free
    $sort     = (string)$r->query('sort', '-created_at'); // or title,status,...

    $q = DB::table('courses')->whereNull('deleted_at');
    if ($qText !== '') {
        $q->where(function($w) use ($qText){
            $w->where('title','like',"%$qText%")->orWhere('slug','like',"%$qText%");
        });
    }
    if ($status) $q->where('status', $status);
    if ($type)   $q->where('course_type', $type);

    $dir = 'asc'; $col = $sort;
    if (str_starts_with($sort, '-')) { $dir = 'desc'; $col = ltrim($sort, '-'); }
    if (!in_array($col, ['created_at','title','status','course_type','order_no'], true)) { $col='created_at'; $dir='desc'; }

    $total = (clone $q)->count();
    $rows  = $q->orderBy($col, $dir)->offset(($page-1)*$perPage)->limit($perPage)->get();

    foreach ($rows as $row) {
        $row->final_price_ui = $this->computeFinalPrice(
            (float)$row->price_amount,
            $row->discount_amount !== null ? (float)$row->discount_amount : null,
            $row->discount_percent !== null ? (float)$row->discount_percent : null
        );
    }

    return response()->json(['data'=>$rows,'pagination'=>['page'=>$page,'per_page'=>$perPage,'total'=>$total]]);
}

public function show(Request $r, string $course)
{
    if ($resp = $this->requireRole($r, ['admin','superadmin'])) return $resp;

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

    $newType  = $data['course_type'] ?? $row->course_type;
    $newPrice = array_key_exists('price_amount',$data) ? (float)$data['price_amount'] : (float)$row->price_amount;
    if ($newType === 'paid' && $newPrice <= 0) {
        return response()->json(['error'=>'Validation failed','fields'=>['price_amount'=>['price_amount must be > 0 for paid courses']]], 422);
    }

    $upd = [];
    foreach ($data as $k => $v) {
        if ($k === 'metadata') $v = $v !== null ? json_encode($v, JSON_UNESCAPED_UNICODE) : json_encode(new \stdClass());
        if ($k === 'is_featured') $v = !empty($v) ? 1 : 0;
        $upd[$k] = $v;
    }
    $upd['updated_at'] = now();

    DB::table('courses')->where('id',$id)->update($upd);

    $fresh = DB::table('courses')->where('id',$id)->first();
    if ($fresh) {
        $fresh->final_price_ui = $this->computeFinalPrice(
            (float)$fresh->price_amount,
            $fresh->discount_amount !== null ? (float)$fresh->discount_amount : null,
            $fresh->discount_percent !== null ? (float)$fresh->discount_percent : null
        );
    }

    $this->logActivity($request,'update','Updated course "'.($fresh->title ?? $row->title).'"','courses',$id,array_keys($upd),(array)$row,$fresh ? (array)$fresh : null);

    return response()->json(['status'=>'success','message'=>'Course updated','data'=>$fresh]);
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





}
