<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class BlogController extends Controller
{
    /* =========================================================
     |  Upload folders (PUBLIC like CourseController)
     |========================================================= */
    private const MEDIA_FOLDERS = [
        'image' => 'assets/media/images/blog',
        'other' => 'assets/media/other/blog',
    ];

    /* =========================================================
     |  Helpers
     |========================================================= */

    private function actor(Request $request): array
    {
        $id = $request->attributes->get('auth_tokenable_id');

        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => $id !== null ? (int)$id : null,
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

    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null) return null;
        // minimal protection
        return preg_replace('#<script.*?>.*?</script>#is', '', $html);
    }

    private function normSlug(?string $s): string
    {
        $s = trim((string)$s);
        return $s === '' ? '' : Str::slug($s, '-');
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'blog';
        $try  = $slug;
        $i    = 2;

        while (true) {
            $q = DB::table('blogs')->where('slug', $try);
            if ($ignoreId) $q->where('id', '!=', $ignoreId);
            if (!$q->exists()) return $try;

            $try = $slug . '-' . $i;
            $i++;

            if ($i > 200) {
                $try = $slug . '-' . Str::lower(Str::random(4));
                $q = DB::table('blogs')->where('slug', $try);
                if ($ignoreId) $q->where('id', '!=', $ignoreId);
                if (!$q->exists()) return $try;
            }
        }
    }

    private function buildShortcodeFromTitle(string $title): string
    {
        $base = strtoupper(substr(Str::slug($title, ''), 0, 6));
        $suffix = strtoupper(Str::random(3));
        return $base . '_' . $suffix; // e.g. MYBLOG_X7K
    }

    private function resolveBlog($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('blogs');
        if (!$includeDeleted) $q->whereNull('deleted_at');

        if (ctype_digit((string)$identifier)) {
            $q->where('id', (int)$identifier);
        } elseif (Str::isUuid((string)$identifier)) {
            $q->where('uuid', (string)$identifier);
        } else {
            $q->where('slug', $this->normSlug((string)$identifier));
        }

        return $q->first();
    }

    private function public_path_base(): string
    {
        return public_path();
    }

    private function toPublicUrl(string $relativePath): string
    {
        $base = rtrim((string)config('app.url'), '/');
        $rel  = ltrim(str_replace('\\', '/', $relativePath), '/');
        return $base . '/' . $rel;
    }

    private function detectType(?string $mime, ?string $name): string
    {
        $mime = strtolower((string)$mime);
        $ext  = strtolower(pathinfo((string)$name, PATHINFO_EXTENSION));
        if (str_starts_with($mime, 'image/') || in_array($ext, ['jpg','jpeg','png','gif','webp','avif'], true)) return 'image';
        return 'other';
    }

    /**
     * If the current featured_image_url is local (under app.url), delete that file.
     */
    private function tryDeleteLocalFeatured(?string $featuredImageUrl): void
    {
        try {
            $featuredImageUrl = (string)$featuredImageUrl;
            if ($featuredImageUrl === '') return;

            $appUrl = rtrim((string)config('app.url'), '/');
            if ($appUrl === '') return;

            if (!str_starts_with($featuredImageUrl, $appUrl)) return;

            $relative = ltrim(str_replace($appUrl, '', $featuredImageUrl), '/');
            $full = $this->public_path_base() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

            if (File::exists($full)) {
                File::delete($full);
            }
        } catch (\Throwable $e) {
            Log::warning('[Blog] local featured delete failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle featured_image upload (multipart file) and return public URL.
     * $blogUuid is used to make filenames stable.
     */
    private function saveFeaturedImage(Request $request, string $blogUuid): string
    {
        $file = $request->file('featured_image');
        if (!$file || !$file->isValid()) {
            throw new \RuntimeException('Invalid file');
        }

        $mime = $file->getMimeType();
        $ft   = $this->detectType($mime, $file->getClientOriginalName());
        $sub  = self::MEDIA_FOLDERS[$ft] ?? self::MEDIA_FOLDERS['other'];

        $destDir = $this->public_path_base() . DIRECTORY_SEPARATOR . $sub;
        File::ensureDirectoryExists($destDir, 0755, true);

        $ext  = strtolower($file->getClientOriginalExtension() ?: '');
        $name = 'blog-' . $blogUuid . '-' . Str::uuid()->toString() . ($ext ? ('.' . $ext) : '');

        $file->move($destDir, $name);

        $relative = $sub . '/' . $name;
        return $this->toPublicUrl($relative);
    }

    /* =========================================================
     |  Status Rules
     |========================================================= */
    private function allowedStatuses(): array
    {
        // as you requested
        return ['draft', 'pending_approval', 'approved', 'active', 'inactive'];
    }

    private function normalizePublish($val): int
    {
        // accept: yes/no, 1/0, true/false
        if (is_bool($val)) return $val ? 1 : 0;
        $s = strtolower(trim((string)$val));
        if (in_array($s, ['1','true','yes','y','on'], true)) return 1;
        return 0;
    }
/* =========================================================
 |  LIST
 |  GET /api/blogs
 |========================================================= */
public function index(Request $r)
{
    $page    = max(1, (int)$r->query('page', 1));
    $per     = min(100, max(5, (int)$r->query('per_page', 20)));
    $qText   = trim((string)$r->query('q', ''));
    $status  = $r->query('status', null);
    $pub     = $r->query('is_published', null);
    $sort    = (string)$r->query('sort', 'created_at');
    $dir     = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

    $allowedSort = ['created_at','updated_at','title','slug','blog_date','approved_at'];
    if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

    $actor = $this->actor($r);
    $role  = strtolower((string)($actor['role'] ?? ''));
    $uid   = (int)($actor['id'] ?? 0);

    // ✅ if not authenticated, block
    if ($uid <= 0) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    }

    $isAdmin = in_array($role, ['admin', 'superadmin', 'super_admin'], true);

    $base = DB::table('blogs')->whereNull('deleted_at');

    // ✅ non-admin users see only their own created blogs
    if (!$isAdmin) {
        $base->where('created_by_user_id', $uid);
    }

    if ($qText !== '') {
        $base->where(function($w) use ($qText){
            $w->where('title', 'like', "%{$qText}%")
              ->orWhere('slug', 'like', "%{$qText}%")
              ->orWhere('shortcode', 'like', "%{$qText}%")
              ->orWhere('short_description', 'like', "%{$qText}%");
        });
    }

    if ($status !== null && $status !== '') {
        $base->where('status', (string)$status);
    }

    if ($pub !== null && $pub !== '') {
        $base->where('is_published', $this->normalizePublish($pub));
    }

    $total = (clone $base)->count();
    $rows  = $base->orderBy($sort, $dir)->orderBy('id', 'asc')->forPage($page, $per)->get();

    return response()->json([
        'success' => true,
        'data' => $rows,
        'pagination' => [
            'page' => $page,
            'per_page' => $per,
            'total' => $total,
        ],
    ]);
}


    /* =========================================================
     |  TRASH
     |  GET /api/blogs-trash
     |========================================================= */
    public function indexTrash(Request $r)
    {
        if ($resp = $this->requireRole($r, ['admin','superadmin'])) return $resp;

        $page  = max(1, (int)$r->query('page', 1));
        $per   = min(100, max(5, (int)$r->query('per_page', 20)));
        $qText = trim((string)$r->query('q', ''));

        $base = DB::table('blogs')->whereNotNull('deleted_at');

        if ($qText !== '') {
            $base->where(function($w) use ($qText){
                $w->where('title','like',"%{$qText}%")
                  ->orWhere('slug','like',"%{$qText}%")
                  ->orWhere('shortcode','like',"%{$qText}%");
            });
        }

        $total = (clone $base)->count();
        $rows  = $base->orderBy('deleted_at','desc')->orderBy('id','asc')->forPage($page, $per)->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'page' => $page,
                'per_page' => $per,
                'total' => $total,
            ],
        ]);
    }

    /* =========================================================
     |  SHOW
     |  GET /api/blogs/{identifier}
     |========================================================= */
    public function show(Request $r, $identifier)
    {
        $row = $this->resolveBlog($identifier, false);
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        return response()->json(['success'=>true,'data'=>$row]);
    }

        /* =========================================================
     |  PUBLIC VIEW API
     |  GET /api/blogs/view/{identifier}
     |
     |  Default: ONLY published + approved/active + not deleted
     |  Test mode: show any status/published (still not deleted)
     |  Query: ?mode=test   OR   ?test=1
     |========================================================= */
     public function publicView(Request $request, string $identifier)
     {
         $key  = trim((string)$identifier);
 
         // ✅ test mode flags
         $mode = strtolower(trim((string)$request->query('mode', '')));
         $test = (string)$request->query('test', '');
         $isTestMode = ($mode === 'test') || in_array(strtolower($test), ['1','true','yes','y','on'], true);
 
         $q = DB::table('blogs')
             ->whereNull('deleted_at')
             ->where(function($w) use ($key){
                 // allow resolve by slug or shortcode
                 $w->where('slug', $key)
                   ->orWhere('shortcode', $key);
 
                 // Optional: allow uuid/id too (safe + helpful)
                 if (ctype_digit($key)) {
                     $w->orWhere('id', (int)$key);
                 } elseif (Str::isUuid($key)) {
                     $w->orWhere('uuid', $key);
                 }
             });
 
         // ✅ default rule: only published + approved/active
         if (!$isTestMode) {
             $q->where('is_published', 1)
               ->whereIn('status', ['approved','active']);
         }
 
         $row = $q->first([
             'id','uuid',
             'title','slug','shortcode',
             'short_description',
             'featured_image_url',
             'content_html',
             'blog_date',
             'status','is_published',
             'created_at','updated_at'
         ]);
 
         if (!$row) {
             return response()->json(['error' => 'Not found'], 404);
         }
 
         return response()->json([
             'success' => true,
             'mode'    => $isTestMode ? 'test' : 'public',
             'data'    => $row,
         ]);
     }
 

    /* =========================================================
     |  PUBLIC RESOLVE (slug/shortcode)
     |  GET /api/blogs/public/{identifier}
     |  - only published + active/approved
     |========================================================= */
    public function publicApi(string $identifier)
    {
        $key = trim((string)$identifier);

        $row = DB::table('blogs')
            ->whereNull('deleted_at')
            ->where(function($q) use ($key){
                $q->where('slug', $key)->orWhere('shortcode', $key);
            })
            ->where('is_published', 1)
            ->whereIn('status', ['approved','active']) // public visibility rule
            ->first([
                'title','slug','shortcode','short_description','featured_image_url','content_html','blog_date','status','is_published'
            ]);

        if (!$row) abort(404);

        return response()->json($row);
    }
/* =========================================================
     |  ACTIVITY LOG (DB FACADE) - schema safe
     |  table: activity_logs
     |========================================================= */

    private function rowToArray($row): ?array
    {
        if (!$row) return null;
        if (is_array($row)) return $row;
        return json_decode(json_encode($row), true);
    }

    private function safeJson($val): ?string
    {
        try {
            if ($val === null) return null;
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function logActivity(
        Request $request,
        string $action,
        string $message,
        array $meta = [],
        ?string $tableName = null,
        ?int $rowId = null,
        $oldRow = null,
        $newRow = null
    ): void {
        try {
            if (!Schema::hasTable('activity_logs')) return;

            $actor = $this->actor($request); // expects ['id'=>..,'role'=>..] OR at least id
            $actorId = (int)($actor['id'] ?? 0);
            $actorRole = (string)($actor['role'] ?? '');

            $now = now();
            $ip  = $request->ip();
            $ua  = substr((string)$request->userAgent(), 0, 255);

            $oldArr = $this->rowToArray($oldRow);
            $newArr = $this->rowToArray($newRow);

            // lightweight diff (best-effort)
            $changes = [];
            if (is_array($oldArr) && is_array($newArr)) {
                $keys = array_unique(array_merge(array_keys($oldArr), array_keys($newArr)));
                foreach ($keys as $k) {
                    $ov = $oldArr[$k] ?? null;
                    $nv = $newArr[$k] ?? null;
                    if ($ov !== $nv) $changes[$k] = ['old' => $ov, 'new' => $nv];
                }
            }

            $ins = [];

            if (Schema::hasColumn('activity_logs', 'uuid')) $ins['uuid'] = (string)Str::uuid();

            // module/action/message
            if (Schema::hasColumn('activity_logs', 'module')) $ins['module'] = 'blogs';
            if (Schema::hasColumn('activity_logs', 'action')) $ins['action'] = $action;
            if (Schema::hasColumn('activity_logs', 'message')) $ins['message'] = $message;

            // actor id
            foreach (['actor_id','user_id','created_by','created_by_user_id'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $actorId ?: null; break; }
            }
            // actor role
            foreach (['actor_role','role'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $actorRole ?: null; break; }
            }

            // request info
            foreach (['endpoint','path','url'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = (string)$request->path(); break; }
            }
            foreach (['method','http_method'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = (string)$request->method(); break; }
            }

            // table/ref
            foreach (['table_name','table','ref_table'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $tableName; break; }
            }
            foreach (['row_id','ref_id','subject_id'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $rowId; break; }
            }

            // ip & ua
            foreach (['ip','ip_address'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $ip; break; }
            }
            foreach (['user_agent','ua'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $ua; break; }
            }

            // json blobs
            foreach (['meta_json','meta','metadata'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $this->safeJson($meta); break; }
            }
            foreach (['old_json','old_data','old_payload'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $this->safeJson($oldArr); break; }
            }
            foreach (['new_json','new_data','new_payload'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $this->safeJson($newArr); break; }
            }
            foreach (['changes_json','changes'] as $col) {
                if (Schema::hasColumn('activity_logs', $col)) { $ins[$col] = $this->safeJson($changes); break; }
            }

            if (Schema::hasColumn('activity_logs', 'created_at') && !isset($ins['created_at'])) $ins['created_at'] = $now;
            if (Schema::hasColumn('activity_logs', 'updated_at') && !isset($ins['updated_at'])) $ins['updated_at'] = $now;

            if (!empty($ins)) DB::table('activity_logs')->insert($ins);
        } catch (\Throwable $e) {
            Log::warning('Activity log failed', ['err' => $e->getMessage()]);
        }
    }

    /* =========================================================
     |  CREATE  - POST /api/blogs
     |========================================================= */
    public function store(Request $request)
    {
        // if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $data = $request->validate([
            'title'             => ['required','string','max:255'],
            'slug'              => ['sometimes','nullable','string','max:160'],
            'shortcode'         => ['sometimes','nullable','string','max:20'],

            'short_description' => ['sometimes','nullable','string','max:500'],
            'content_html'      => ['sometimes','nullable','string'],

            'featured_image'    => ['sometimes','nullable','file','max:5120'],
            'featured_image_url'=> ['sometimes','nullable','string','max:500'],

            'blog_date'         => ['sometimes','nullable','date'],
            'status'            => ['sometimes', Rule::in($this->allowedStatuses())],
            'is_published'      => ['sometimes'],
        ]);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $uuid = (string) Str::uuid();

        $slugBase = $this->normSlug($data['slug'] ?? $data['title']);
        $slug     = $this->uniqueSlug($slugBase);

        $shortcode = (isset($data['shortcode']) && trim((string)$data['shortcode']) !== '')
            ? trim((string)$data['shortcode'])
            : $this->buildShortcodeFromTitle($data['title']);

        $status = $data['status'] ?? 'draft';
        $isPub  = array_key_exists('is_published', $data) ? $this->normalizePublish($data['is_published']) : 0;

        if ($isPub === 1 && in_array($status, ['draft'], true)) {
            $status = 'pending_approval';
        }

        $featuredUrl = null;
        if ($request->hasFile('featured_image')) {
            try {
                $featuredUrl = $this->saveFeaturedImage($request, $uuid);
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Featured image upload failed', 'details' => $e->getMessage()], 422);
            }
        } elseif (!empty($data['featured_image_url'])) {
            $featuredUrl = $data['featured_image_url'];
        }

        $id = DB::table('blogs')->insertGetId([
            'uuid'              => $uuid,
            'title'             => $data['title'],
            'slug'              => $slug,
            'shortcode'         => $shortcode,

            'short_description' => $data['short_description'] ?? null,
            'featured_image_url'=> $featuredUrl,
            'content_html'      => $this->sanitizeHtml($data['content_html'] ?? null),

            'blog_date'         => !empty($data['blog_date']) ? Carbon::parse($data['blog_date']) : null,

            'status'            => $status,
            'is_published'      => $isPub,

            'approved_by_user_id' => null,
            'approved_at'         => null,

            'created_by_user_id'  => $actor['id'] ?: null,
            'updated_by_user_id'  => $actor['id'] ?: null,
            'created_at_ip'       => $request->ip(),
            'created_at'          => $now,
            'updated_at'          => $now,
            'deleted_at'          => null,
        ]);

        $row = DB::table('blogs')->where('id', $id)->first();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_create', 'Blog created', [
            'blog_id' => (int)$id,
            'blog_uuid' => (string)$uuid,
            'slug' => (string)$slug,
            'status' => (string)$status,
            'is_published' => (int)$isPub,
        ], 'blogs', (int)$id, null, $row);

        return response()->json(['success'=>true,'data'=>$row], 201);
    }

    /* =========================================================
     |  UPDATE  - PUT/PATCH /api/blogs/{identifier}
     |========================================================= */
    public function update(Request $request, $identifier)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $blog = $this->resolveBlog($identifier, false);
        if (!$blog) return response()->json(['error'=>'Not found'], 404);

        $old = $blog;

        $data = $request->validate([
            'title'             => ['sometimes','string','max:255'],
            'slug'              => ['sometimes','nullable','string','max:160'],
            'shortcode'         => ['sometimes','nullable','string','max:20'],

            'short_description' => ['sometimes','nullable','string','max:500'],
            'content_html'      => ['sometimes','nullable','string'],

            'featured_image'    => ['sometimes','nullable','file','max:5120'],
            'featured_image_url'=> ['sometimes','nullable','string','max:500'],

            'blog_date'         => ['sometimes','nullable','date'],
            'status'            => ['sometimes', Rule::in($this->allowedStatuses())],
            'is_published'      => ['sometimes'],

            'regenerate_slug'   => ['sometimes','boolean'],
        ]);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $slug = $blog->slug;
        if (array_key_exists('slug', $data)) {
            $norm = $this->normSlug($data['slug']);
            if ($norm === '' || !empty($data['regenerate_slug'])) {
                $base = $this->normSlug($data['title'] ?? $blog->title ?? 'blog');
                $slug = $this->uniqueSlug($base, (int)$blog->id);
            } else {
                $slug = $this->uniqueSlug($norm, (int)$blog->id);
            }
        } elseif (!empty($data['regenerate_slug']) || (isset($data['title']) && $data['title'] !== $blog->title)) {
            $base = $this->normSlug($data['title'] ?? $blog->title ?? 'blog');
            $slug = $this->uniqueSlug($base, (int)$blog->id);
        }

        if (array_key_exists('shortcode', $data) && trim((string)$data['shortcode']) !== '') {
            $shortcode = trim((string)$data['shortcode']);
        } elseif (isset($data['title']) && $data['title'] !== $blog->title) {
            $shortcode = $this->buildShortcodeFromTitle($data['title']);
        } else {
            $shortcode = $blog->shortcode;
        }

        $isPub = $blog->is_published;
        if (array_key_exists('is_published', $data)) {
            $isPub = $this->normalizePublish($data['is_published']);
        }

        $status = $blog->status;
        if (array_key_exists('status', $data)) {
            $status = (string)$data['status'];
        }

        if ((int)$isPub === 1 && in_array($status, ['draft'], true)) {
            $status = 'pending_approval';
        }

        $featuredUrl = $blog->featured_image_url;
        if ($request->hasFile('featured_image')) {
            try {
                $this->tryDeleteLocalFeatured($blog->featured_image_url);
                $featuredUrl = $this->saveFeaturedImage($request, (string)$blog->uuid);
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Featured image upload failed', 'details' => $e->getMessage()], 422);
            }
        } elseif (array_key_exists('featured_image_url', $data)) {
            $featuredUrl = $data['featured_image_url'] ?: null;
        }

        $update = [
            'title'             => $data['title'] ?? $blog->title,
            'slug'              => $slug,
            'shortcode'         => $shortcode,
            'short_description' => array_key_exists('short_description',$data) ? ($data['short_description'] ?: null) : $blog->short_description,
            'featured_image_url'=> $featuredUrl,

            'content_html'      => array_key_exists('content_html',$data)
                ? $this->sanitizeHtml($data['content_html'])
                : $blog->content_html,

            'blog_date'         => array_key_exists('blog_date',$data)
                ? ($data['blog_date'] ? Carbon::parse($data['blog_date']) : null)
                : $blog->blog_date,

            'status'            => $status,
            'is_published'      => (int)$isPub,

            'updated_at'        => $now,
            'updated_by_user_id'=> $actor['id'] ?: null,
        ];

        DB::table('blogs')->where('id', (int)$blog->id)->update($update);

        $fresh = DB::table('blogs')->where('id', (int)$blog->id)->first();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_update', 'Blog updated', [
            'blog_id' => (int)$blog->id,
            'blog_uuid' => (string)($blog->uuid ?? ''),
        ], 'blogs', (int)$blog->id, $old, $fresh);

        return response()->json(['success'=>true,'data'=>$fresh]);
    }

    /* =========================================================
     |  SOFT DELETE  - DELETE /api/blogs/{identifier}
     |========================================================= */
    public function destroy(Request $request, $identifier)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $blog = $this->resolveBlog($identifier, false);
        if (!$blog) return response()->json(['error'=>'Not found'], 404);

        $old = $blog;
        $actor = $this->actor($request);

        DB::table('blogs')->where('id', (int)$blog->id)->update([
            'deleted_at'         => Carbon::now(),
            'updated_at'         => Carbon::now(),
            'updated_by_user_id' => $actor['id'] ?: null,
        ]);

        $fresh = DB::table('blogs')->where('id', (int)$blog->id)->first();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_soft_delete', 'Blog moved to bin', [
            'blog_id' => (int)$blog->id,
            'blog_uuid' => (string)($blog->uuid ?? ''),
        ], 'blogs', (int)$blog->id, $old, $fresh);

        return response()->json(['success'=>true,'message'=>'Moved to bin']);
    }

    /* =========================================================
     |  RESTORE  - POST /api/blogs/{identifier}/restore
     |========================================================= */
    public function restore(Request $request, $identifier)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $blog = $this->resolveBlog($identifier, true);
        if (!$blog || $blog->deleted_at === null) {
            return response()->json(['error'=>'Not found in bin'], 404);
        }

        $old = $blog;
        $actor = $this->actor($request);

        DB::table('blogs')->where('id', (int)$blog->id)->update([
            'deleted_at'         => null,
            'updated_at'         => Carbon::now(),
            'updated_by_user_id' => $actor['id'] ?: null,
        ]);

        $fresh = DB::table('blogs')->where('id', (int)$blog->id)->first();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_restore', 'Blog restored from bin', [
            'blog_id' => (int)$blog->id,
            'blog_uuid' => (string)($blog->uuid ?? ''),
        ], 'blogs', (int)$blog->id, $old, $fresh);

        return response()->json(['success'=>true,'message'=>'Restored']);
    }

    /* =========================================================
     |  HARD DELETE  - DELETE /api/blogs/{identifier}/force
     |========================================================= */
    public function forceDelete(Request $request, $identifier)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $blog = $this->resolveBlog($identifier, true);
        if (!$blog) return response()->json(['error'=>'Not found'], 404);

        $old = $blog;

        $this->tryDeleteLocalFeatured($blog->featured_image_url);

        DB::table('blogs')->where('id', (int)$blog->id)->delete();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_force_delete', 'Blog deleted permanently', [
            'blog_id' => (int)$blog->id,
            'blog_uuid' => (string)($blog->uuid ?? ''),
            'featured_image_url' => (string)($blog->featured_image_url ?? ''),
        ], 'blogs', (int)$blog->id, $old, null);

        return response()->json(['success'=>true,'message'=>'Deleted permanently']);
    }

    /* =========================================================
     |  TOGGLE ACTIVE/INACTIVE  - POST /api/blogs/{identifier}/toggle-status
     |========================================================= */
    public function toggleStatus(Request $request, $identifier)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $blog = $this->resolveBlog($identifier, false);
        if (!$blog) return response()->json(['error'=>'Not found'], 404);

        $old = $blog;
        $actor = $this->actor($request);

        $newStatus = ($blog->status === 'active') ? 'inactive' : 'active';

        DB::table('blogs')->where('id', (int)$blog->id)->update([
            'status'             => $newStatus,
            'updated_at'         => Carbon::now(),
            'updated_by_user_id' => $actor['id'] ?: null,
        ]);

        $fresh = DB::table('blogs')->where('id', (int)$blog->id)->first();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_toggle_status', 'Blog status toggled', [
            'blog_id' => (int)$blog->id,
            'from' => (string)($old->status ?? ''),
            'to' => (string)$newStatus,
        ], 'blogs', (int)$blog->id, $old, $fresh);

        return response()->json(['success'=>true,'message'=>'Status updated','status'=>$newStatus]);
    }

    /* =========================================================
     |  APPROVE  - POST /api/blogs/{identifier}/approve
     |========================================================= */
    public function approve(Request $request, $identifier)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $blog = $this->resolveBlog($identifier, false);
        if (!$blog) return response()->json(['error'=>'Not found'], 404);

        $old = $blog;
        $actor = $this->actor($request);

        DB::table('blogs')->where('id', (int)$blog->id)->update([
            'status'               => 'approved',
            'approved_by_user_id'  => $actor['id'] ?: null,
            'approved_at'          => Carbon::now(),
            'updated_at'           => Carbon::now(),
            'updated_by_user_id'   => $actor['id'] ?: null,
        ]);

        $fresh = DB::table('blogs')->where('id', (int)$blog->id)->first();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_approve', 'Blog approved', [
            'blog_id' => (int)$blog->id,
            'blog_uuid' => (string)($blog->uuid ?? ''),
        ], 'blogs', (int)$blog->id, $old, $fresh);

        return response()->json(['success'=>true,'message'=>'Blog approved','data'=>$fresh]);
    }

    /* =========================================================
     |  SET PUBLISH  - POST /api/blogs/{identifier}/publish
     |========================================================= */
    public function publish(Request $request, $identifier)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $blog = $this->resolveBlog($identifier, false);
        if (!$blog) return response()->json(['error'=>'Not found'], 404);

        $old = $blog;

        $data = $request->validate([
            'is_published' => ['required'],
        ]);

        $actor = $this->actor($request);
        $isPub = $this->normalizePublish($data['is_published']);

        $status = $blog->status;
        if ($isPub === 1 && !in_array($status, ['approved','active'], true)) {
            $status = 'pending_approval';
        }

        DB::table('blogs')->where('id', (int)$blog->id)->update([
            'is_published'       => $isPub,
            'status'             => $status,
            'updated_at'         => Carbon::now(),
            'updated_by_user_id' => $actor['id'] ?: null,
        ]);

        $fresh = DB::table('blogs')->where('id', (int)$blog->id)->first();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_publish', 'Blog publish flag updated', [
            'blog_id' => (int)$blog->id,
            'from_is_published' => (int)($old->is_published ?? 0),
            'to_is_published' => (int)$isPub,
            'final_status' => (string)$status,
        ], 'blogs', (int)$blog->id, $old, $fresh);

        return response()->json(['success'=>true,'message'=>'Publish flag updated','data'=>$fresh]);
    }

    /* =========================================================
     |  FEATURED IMAGE UPLOAD  - POST /api/blogs/{identifier}/featured-image
     |========================================================= */
    public function featuredUpload(Request $request, $identifier)
    {
        if ($resp = $this->requireRole($request, ['admin','superadmin'])) return $resp;

        $blog = $this->resolveBlog($identifier, false);
        if (!$blog) return response()->json(['error'=>'Not found'], 404);

        $old = $blog;

        if (!$request->hasFile('featured_image')) {
            return response()->json(['error'=>'featured_image file is required'], 422);
        }

        try {
            $this->tryDeleteLocalFeatured($blog->featured_image_url);
            $url = $this->saveFeaturedImage($request, (string)$blog->uuid);
        } catch (\Throwable $e) {
            return response()->json(['error'=>'Featured image upload failed', 'details'=>$e->getMessage()], 422);
        }

        $actor = $this->actor($request);

        DB::table('blogs')->where('id', (int)$blog->id)->update([
            'featured_image_url'  => $url,
            'updated_at'          => Carbon::now(),
            'updated_by_user_id'  => $actor['id'] ?: null,
        ]);

        $fresh = DB::table('blogs')->where('id', (int)$blog->id)->first();

        // ✅ ACTIVITY LOG
        $this->logActivity($request, 'blog_featured_upload', 'Featured image uploaded', [
            'blog_id' => (int)$blog->id,
            'from' => (string)($old->featured_image_url ?? ''),
            'to' => (string)$url,
        ], 'blogs', (int)$blog->id, $old, $fresh);

        return response()->json([
            'success' => true,
            'message' => 'Featured image uploaded',
            'data'    => $fresh,
        ], 201);
    }
}