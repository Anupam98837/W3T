<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StudyMaterialController extends Controller
{
    // Public-facing media subdir under public/
    private const MEDIA_SUBDIR = 'assets/media/study_materials';

    /* =========================================================
     |  Auth / Actor helpers  (same pattern you use elsewhere)
     * ========================================================= */
    private function actor(Request $r): array
    {
        return [
            'role' => $r->attributes->get('auth_role'),
            'type' => $r->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
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

    private function appUrl(): string
    {
        return rtrim(config('app.url'), '/');
    }

    private function mediaBasePublicPath(): string
    {
        // same as public_path()
        return public_path();
    }

    private function toPublicUrl(string $relativePath): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $rel  = ltrim(str_replace('\\','/',$relativePath), '/');
        return $base . '/' . $rel;
    }

    private function ensureDir(string $path): string
    {
        if (! File::exists($path)) {
            File::ensureDirectoryExists($path, 0755, true);
        }
        return $path;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 120, ''));
        if ($base === '') $base = 'study-material';
        $slug = $base;
        $i = 1;
        while (DB::table('study_materials')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }
        return $slug;
    }

    private function genUuid(): string
    {
        return (string) Str::uuid();
    }

    private function jsonDecode($value): array
    {
        if (!$value) return [];
        try { $a = json_decode($value, true, 512, JSON_THROW_ON_ERROR); }
        catch (\Throwable $e) { return []; }
        return is_array($a) ? $a : [];
    }

    /**
     * Log with actor context (similar to your other controllers)
     */
    private function logWithActor(string $message, Request $r, array $context = [])
    {
        $actor = $this->actor($r);
        Log::info($message, array_merge([
            'actor_id' => $actor['id'],
            'actor_role' => $actor['role'],
        ], $context));
    }
     /* =========================================================
     |  Create
     |  - saves files under storage/app/batchStudyMaterial/{batch_id}
     |  - stores full streaming URL in attachment JSON
     * ========================================================= */
    public function store(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;
        $actor = $this->actor($r);

        $v = Validator::make($r->all(), [
            'course_id'         => 'required|integer|exists:courses,id',
            'course_module_id'  => 'required|integer|exists:course_modules,id',
            'batch_id'          => 'required|integer|exists:batches,id',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'view_policy'       => 'nullable|in:inline_only,downloadable',
            // accept multiple inputs: attachments[] or attachments
            'attachments.*'     => 'nullable|file|max:51200', // 50MB each
        ], [
            'attachments.*.max' => 'Each attachment must be <= 50 MB.'
        ]);

        if ($v->fails()) {
            return response()->json(['errors'=>$v->errors()], 422);
        }

        $uuid = $this->genUuid();
        $slug = $this->uniqueSlug($r->title);
        $policy = $r->input('view_policy', 'inline_only');

        // Collect files (support both "attachments" and "attachments[]")
        $files = [];
        if ($r->hasFile('attachments')) {
            $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
        }

        $stored = [];
        if (!empty($files)) {
            $root = $this->ensureDir(storage_path('app/batchStudyMaterial/'.(int)$r->batch_id));
            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $fid  = Str::lower(Str::random(10));
                $name = $fid.'.'.$ext;

                // move uploaded file to our private folder
                $file->move($root, $name);

                $absPath = $root.DIRECTORY_SEPARATOR.$name;
                $relPath = 'batchStudyMaterial/'.(int)$r->batch_id.'/'.$name;

                // mime detection (fallback)
                $mime = $file->getClientMimeType() ?: mime_content_type($absPath) ?: 'application/octet-stream';
                $size = @filesize($absPath) ?: 0;
                $sha  = hash_file('sha256', $absPath);

                // full streaming URL that frontend will use in <iframe>/<img>/<video>
                $url  = $this->appUrl()."/api/study-materials/stream/{$uuid}/{$fid}";

                $stored[] = [
                    'id'          => $fid,
                    'disk'        => 'local',
                    'path'        => $relPath,                // relative to storage/app
                    'url'         => $url,                    // full link (stream)
                    'mime'        => $mime,
                    'ext'         => $ext,
                    'size'        => $size,
                    'sha256'      => $sha,
                    'uploaded_at' => Carbon::now()->toIso8601String(),
                ];
            }
        }

        $now = Carbon::now();
        $id = DB::table('study_materials')->insertGetId([
            'uuid'               => $uuid,
            'course_id'          => (int)$r->course_id,
            'course_module_id'   => (int)$r->course_module_id,
            'batch_id'           => (int)$r->batch_id,
            'title'              => $r->title,
            'slug'               => $slug,
            'description'        => $r->input('description'),
            'attachment'         => $stored ? json_encode($stored) : null,
            'attachment_count'   => count($stored),
            'view_policy'        => $policy,
            'created_by'         => $actor['id'] ?: 0,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        return response()->json([
            'message' => 'Study material created',
            'id'      => $id,
            'uuid'    => $uuid,
            'slug'    => $slug,
            'attachments' => $stored,
        ], 201);
    }
    /* =========================================================
     |  List (filters for dropdown-driven page)
     * ========================================================= */
    public function index(Request $r)
    {

        $q  = DB::table('study_materials')->whereNull('deleted_at');
        
        if ($r->filled('course_id'))        $q->where('course_id', (int)$r->course_id);
        if ($r->filled('course_module_id')) $q->where('course_module_id', (int)$r->course_module_id);
        if ($r->filled('batch_id'))         $q->where('batch_id', (int)$r->batch_id);
        if ($r->filled('search')) {
            $s = '%'.trim($r->search).'%';
            $q->where(function($w) use ($s){
                $w->where('title', 'like', $s)->orWhere('description', 'like', $s);
            });
        }
        $per = max(1, min(100, (int)($r->per_page ?? 20)));
        $page = max(1, (int)($r->page ?? 1));

        $total = (clone $q)->count();
        $rows = $q->orderByDesc('created_at')
                  ->offset(($page-1)*$per)
                  ->limit($per)
                  ->get();

        return response()->json([
            'data' => $rows,
            'meta' => ['page'=>$page,'per_page'=>$per,'total'=>$total]
        ]);
    }

    /* =========================================================
     |  View assignments for a batch (RBAC aware) — unchanged
     * ========================================================= */
    public function viewAssignmentByBatch(Request $r, string $batchKey)
    {
        // allow super_admin/admin/instructor/student (with checks)
        $role = (string) $r->attributes->get('auth_role');
        $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        if (!$role || !in_array($role, ['super_admin','admin','instructor','student'], true)) {
            return response()->json(['error'=>'Unauthorized Access'], 403);
        }

        $isAdminLike = in_array($role, ['super_admin','admin'], true);
        $isInstructor = $role === 'instructor';
        $isStudent = $role === 'student';

        // resolve batch (id | uuid | slug)
        $bq = DB::table('batches')->whereNull('deleted_at');
        if (ctype_digit($batchKey)) {
            $bq->where('id', (int)$batchKey);
        } elseif (\Illuminate\Support\Str::isUuid($batchKey)) {
            $bq->where('uuid', $batchKey);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('batches','slug')) {
            $bq->where('slug', $batchKey);
        } else {
            return response()->json(['error' => 'Batch not found'], 404);
        }
        $batch = $bq->first();
        if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

        // determine pivot columns
        $biUserCol = \Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','user_id')
            ? 'user_id'
            : (\Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

        $bsUserCol = \Illuminate\Support\Facades\Schema::hasColumn('batch_students','user_id')
            ? 'user_id'
            : (\Illuminate\Support\Facades\Schema::hasColumn('batch_students','student_id') ? 'student_id' : null);

        // RBAC checks for instructor/student
        if ($isInstructor) {
            if (!$biUserCol) {
                return response()->json(['error'=>'Schema issue: batch_instructors needs user_id OR instructor_id'], 500);
            }
            $assigned = DB::table('batch_instructors')->where('batch_id', $batch->id)->whereNull('deleted_at')->where($biUserCol, $uid)->exists();
            if (!$assigned) return response()->json(['error'=>'Forbidden'], 403);
        }
        if ($isStudent) {
            if (!$bsUserCol) {
                return response()->json(['error'=>'Schema issue: batch_students needs user_id OR student_id'], 500);
            }
            $enrolled = DB::table('batch_students')->where('batch_id', $batch->id)->whereNull('deleted_at')->where($bsUserCol, $uid)->exists();
            if (!$enrolled) return response()->json(['error'=>'Forbidden'], 403);
        }

        // load course
        $course = DB::table('courses')->where('id', $batch->course_id)->whereNull('deleted_at')->first();
        if (!$course) return response()->json(['error'=>'Course not found for this batch'], 404);

        // load modules for the course (students see only published)
        $isStaff = $isAdminLike || $isInstructor;
        $modQ = DB::table('course_modules')->where('course_id', $course->id)->whereNull('deleted_at')->orderBy('order_no')->orderBy('id');
        if (!$isStaff) $modQ->where('status', 'published');
        $modules = $modQ->get();

        // load assignments for batch (join modules for context and creator info)
        $aq = DB::table('assignments as a')
            ->leftJoin('course_modules as cm', 'cm.id', '=', 'a.course_module_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'a.created_by') // Join with users table for creator info
            ->where('a.batch_id', $batch->id)
            ->whereNull('a.deleted_at')
            ->whereNull('cm.deleted_at')
            ->select(
                'a.id','a.uuid','a.title','a.slug','a.instruction','a.status','a.attachments_json',
                'a.course_module_id','cm.title as module_title','cm.uuid as module_uuid','cm.status as module_status',
                'a.created_at','a.updated_at',
                'creator.name as created_by_name' // Get creator's name
            )
            ->orderBy('cm.order_no')
            ->orderBy('a.created_at', 'desc');

        if (!$isStaff) {
            $aq->where('cm.status', 'published');
        }

        $assignments = $aq->get();

        // group by module
        $byModule = [];
        foreach ($assignments as $as) {
            $mid = $as->course_module_id ?: 0;
            if (!isset($byModule[$mid])) {
                $byModule[$mid] = [
                    'module' => [
                        'id' => (int)$as->course_module_id,
                        'uuid' => $as->module_uuid,
                        'title' => $as->module_title,
                        'status' => $as->module_status ?? null,
                    ],
                    'assignments' => []
                ];
            }

            $attachments = [];
            if (!empty($as->attachments_json)) {
                try { $attachments = is_string($as->attachments_json) ? json_decode($as->attachments_json, true) : $as->attachments_json; } catch (\Throwable $e) { $attachments = []; }
            }

            $byModule[$mid]['assignments'][] = [
                'id' => (int)$as->id,
                'uuid' => $as->uuid,
                'title' => $as->title,
                'slug' => $as->slug,
                'instruction' => $as->instruction,
                'status' => $as->status,
                'attachments' => $attachments,
                'created_at' => $as->created_at,
                'updated_at' => $as->updated_at,
                'created_by_name' => $as->created_by_name, // Include creator's name
            ];
        }

        // instructors list (if pivot exists)
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
                    'id' => (int)$u->id,
                    'uuid' => $u->uuid,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                ])->values();
        }

        $studentsCount = DB::table('batch_students')->where('batch_id', $batch->id)->whereNull('deleted_at')->count();
        $assignmentsCount = DB::table('assignments')->where('batch_id', $batch->id)->whereNull('deleted_at')->count();

        $payload = [
            'batch' => (array)$batch,
            'course' => [
                'id' => (int)$course->id,
                'uuid' => $course->uuid,
                'title' => $course->title,
                'slug' => $course->slug,
            ],
            'modules_with_assignments' => array_values($byModule),
            'all_modules' => $modules->map(fn($m) => [
                'id' => (int)$m->id, 'uuid' => $m->uuid, 'title' => $m->title, 'status' => $m->status, 'order_no' => (int)$m->order_no
            ])->values(),
            'instructors' => $instructors,
            'stats' => [
                'students_count' => (int)$studentsCount,
                'assignments_count' => (int)$assignmentsCount,
                'modules_count' => count($modules),
                'you_are_instructor' => $isInstructor,
                'you_are_student' => $isStudent,
            ],
            'permissions' => [
                'can_create_assignment' => $isAdminLike || $isInstructor,
                'can_view_unpublished_modules' => $isAdminLike || $isInstructor,
            ],
        ];

        $this->logWithActor('[Assignments] view by batch', $r, ['batch_id' => $batch->id, 'assignments_count' => $assignmentsCount]);

        return response()->json(['data' => $payload]);
    }

       /**
     * Update basic fields and (optionally) add more files / remove selected files
     */
    public function update(Request $r, $id)
{
    // permission check (kept as in original)
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $row = DB::table('study_materials')->where('id', (int)$id)->whereNull('deleted_at')->first();
    if (!$row) return response()->json(['error' => 'Not found'], 404);

    // validation: note remove_attachments is optional array of strings; allow library_urls
    $v = Validator::make($r->all(), [
        'title'               => 'sometimes|required|string|max:255',
        'description'         => 'nullable|string',
        'view_policy'         => 'nullable|in:inline_only,downloadable',
        'attachments.*'       => 'nullable|file|max:51200',
        'library_urls.*'      => 'nullable|url',
        'remove_attachments'  => 'sometimes|array',
        'remove_attachments.*'=> 'string',
    ]);

    if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

    $update = [];

    if ($r->filled('title')) {
        $update['title'] = $r->title;
        // If title changes, keep slug stable unless asked
        if ($r->boolean('regenerate_slug', false)) {
            $update['slug'] = $this->uniqueSlug($r->title);
        }
    }

    if ($r->exists('description')) $update['description'] = $r->input('description');
    if ($r->filled('view_policy')) $update['view_policy'] = $r->input('view_policy');

    // decode existing attachments (may be null)
    $stored = $this->jsonDecode($row->attachment);
    if (!is_array($stored)) $stored = [];

    //
    // Handle removals first (if any)
    //
    $toRemove = $r->input('remove_attachments', []);
    if (!is_array($toRemove)) $toRemove = [$toRemove];

    if (!empty($toRemove)) {
        // normalize lookup set
        $remSet = array_flip(array_map('strval', $toRemove));

        $kept = [];
        foreach ($stored as $att) {
            // determine identifier used by frontend (your code uses 'id' => $fid)
            $attId = '';
            if (isset($att['id'])) $attId = (string)$att['id'];
            elseif (isset($att['attachment_id'])) $attId = (string)$att['attachment_id'];
            elseif (isset($att['file_id'])) $attId = (string)$att['file_id'];
            elseif (isset($att['storage_key'])) $attId = (string)$att['storage_key'];
            elseif (isset($att['key'])) $attId = (string)$att['key'];
            else $attId = (string)($att['path'] ?? ($att['url'] ?? ''));

            if ($attId !== '' && isset($remSet[$attId])) {
                // attempt to remove local file if appropriate
                try {
                    // In the new flow attachments are stored under public/ assets media or local storage path.
                    if (!empty($att['path'])) {
                        // if path looks like relative public assets (assets/media/...) delete from public
                        $p = $att['path'];
                        if (strpos($p, 'assets/media') === 0 || strpos($p, 'batchStudyMaterial') === 0) {
                            $candidate = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($p, '/'));
                        } else {
                            // fallback: previous behavior used storage_path('app/...')
                            $candidate = storage_path('app/' . ltrim($p, '/'));
                        }
                        if (File::exists($candidate) && is_file($candidate)) {
                            File::delete($candidate);
                        }
                    }
                } catch (\Throwable $ex) {
                    // ignore deletion errors; optionally log
                    Log::warning('Failed to delete attachment during update remove_attachments: '.$ex->getMessage(), ['att'=>$att]);
                }
                // skip adding to $kept -> effectively removed
                continue;
            }

            // keep this attachment
            $kept[] = $att;
        }

        // replace stored with kept
        $stored = $kept;
    }

    //
    // Append newly uploaded files and library URLs using helper
    //
    $batchId = (int)$row->batch_id;
    $stored = $this->appendFilesAndLibraryUrls($r, $batchId, $stored);

    // persist updated attachments and counts
    $update['attachment'] = $stored ? json_encode($stored) : null;
    $update['attachment_count'] = count($stored);
    $update['updated_at'] = Carbon::now();

    DB::table('study_materials')->where('id', (int)$id)->update($update);

    return response()->json([
        'message' => 'Study material updated',
        'id'      => (int)$id,
        'attachments' => $stored,
    ]);
}

    /* =========================================================
     |  Soft delete
     * ========================================================= */
    public function destroy(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        $row = DB::table('study_materials')->where('id', (int)$id)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        DB::table('study_materials')->where('id', (int)$id)->update([
            'deleted_at' => Carbon::now()
        ]);

        return response()->json(['message'=>'Study material moved to bin']);
    }

    // 3) Hard delete — admin only — optionally delete files
    public function forceDelete(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('study_materials')->where('id', (int)$id)->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        // delete attached files from disk (best-effort)
        $attachments = $this->jsonDecode($row->attachment);
        foreach ($attachments as $a) {
            try {
                if (!empty($a['path'])) {
                    // path may be relative to public/ (assets/media...) or storage/app/
                    $p = $a['path'];
                    if (strpos($p, 'assets/media') === 0 || strpos($p, 'batchStudyMaterial') === 0 || strpos($p, self::MEDIA_SUBDIR) !== false) {
                        $absPath = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($p, '/'));
                    } else {
                        $absPath = storage_path('app/' . ltrim($p, '/'));
                    }
                    if (is_file($absPath)) {
                        @unlink($absPath);
                    }
                }
            } catch (\Throwable $ex) {
                Log::warning('forceDelete: failed to remove attachment file', ['error'=>$ex->getMessage(),'attachment'=>$a]);
            }
        }

        DB::table('study_materials')->where('id', (int)$id)->delete();

        return response()->json(['message'=>'Study material permanently deleted']);
    }

    // 1) List soft-deleted (the "bin") — admin only
    public function indexDeleted(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $q = DB::table('study_materials')->whereNotNull('deleted_at');

        if ($r->filled('course_id'))        $q->where('course_id', (int)$r->course_id);
        if ($r->filled('course_module_id')) $q->where('course_module_id', (int)$r->course_module_id);
        if ($r->filled('batch_id'))         $q->where('batch_id', (int)$r->batch_id);
        if ($r->filled('search')) {
            $s = '%'.trim($r->search).'%';
            $q->where(function($w) use ($s){
                $w->where('title', 'like', $s)->orWhere('description', 'like', $s);
            });
        }

        $per = max(1, min(100, (int)($r->per_page ?? 20)));
        $page = max(1, (int)($r->page ?? 1));
        $total = (clone $q)->count();
        $rows = $q->orderByDesc('deleted_at')
                  ->offset(($page-1)*$per)
                  ->limit($per)
                  ->get();

        return response()->json(['data'=>$rows,'meta'=>['page'=>$page,'per_page'=>$per,'total'=>$total]]);
    }

    // 2) Restore from bin — admin only
    public function restore(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('study_materials')->where('id', (int)$id)->whereNotNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        DB::table('study_materials')->where('id', (int)$id)->update(['deleted_at' => null, 'updated_at' => Carbon::now()]);

        return response()->json(['message'=>'Study material restored']);
    }

    /* =========================================================
     |  Show one (by uuid) – useful for a “View” page manifest
     * ========================================================= */
    public function showByUuid(Request $r, string $uuid)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        $row = DB::table('study_materials')->where('uuid', $uuid)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        $row->attachment = $this->jsonDecode($row->attachment);
        return response()->json($row);
    }

    /* =========================================================
     |  Stream inline (view-only)
     |  - returns file with Content-Disposition:inline
     |  - no symlink, no CLI needed, works on shared hosting
     * ========================================================= */
    public function streamInline(Request $r, string $uuid, string $fileId)
    {
        // Roles: allow admins/instructors/students
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor','student'])) return $res;

        $row = DB::table('study_materials')->where('uuid', $uuid)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        $attachments = $this->jsonDecode($row->attachment);
        $file = null;
        foreach ($attachments as $a) {
            if (($a['id'] ?? null) === $fileId) { $file = $a; break; }
        }
        if (!$file) return response()->json(['error'=>'File not found'], 404);

        // Enforce policy
        if (($row->view_policy ?? 'inline_only') === 'inline_only') {
            // ok — we'll force inline below
        }

        // Determine absolute path (prefer public assets path)
        $absPath = null;
        if (!empty($file['path'])) {
            $p = $file['path'];
            if (strpos($p, 'assets/media') === 0 || strpos($p, self::MEDIA_SUBDIR) !== false || strpos($p, 'batchStudyMaterial') === 0) {
                $absPath = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($p, '/'));
            } else {
                $absPath = storage_path('app/' . ltrim($p, '/'));
            }
        }

        if (!$absPath || !is_file($absPath)) {
            return response()->json(['error'=>'File missing on server'], 410);
        }

        $ext  = $file['ext']  ?? pathinfo($absPath, PATHINFO_EXTENSION) ?? 'bin';
        $mime = $file['mime'] ?? mime_content_type($absPath) ?: 'application/octet-stream';
        $name = "view.{$ext}"; // browser-visible filename, but disposition is inline

        // Important: inline view, disable download prompt as much as possible
        $headers = [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'inline; filename="'.$name.'"',
            'X-Content-Type-Options'    => 'nosniff',
            'Cache-Control'             => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'                    => 'no-cache',
            'Expires'                   => '0',
        ];

        // Use Laravel's binary file response with inline disposition
        return response()->download($absPath, $name, $headers, 'inline');
    }
/* =========================================================
 | View Study Materials by Batch (with RBAC + Module Filter)
 * ========================================================= */
public function viewStudyMaterialByBatch(Request $r, string $batchKey)
{
    /* --------------------------
     | Role & Actor
     * -------------------------- */
    $role = (string) $r->attributes->get('auth_role');
    $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);

    if (!in_array($role, ['superadmin','admin','instructor','student'], true)) {
        return response()->json(['error' => 'Unauthorized Access'], 403);
    }

    $isAdminLike = in_array($role, ['superadmin','admin'], true);
    $isInstructor = $role === 'instructor';
    $isStudent    = $role === 'student';
    $isStaff      = $isAdminLike || $isInstructor;

    /* --------------------------
     | Resolve Batch
     * -------------------------- */
    $bq = DB::table('batches')->whereNull('deleted_at');

    if (ctype_digit($batchKey)) {
        $bq->where('id', (int)$batchKey);
    } elseif (Str::isUuid($batchKey)) {
        $bq->where('uuid', $batchKey);
    } elseif (Schema::hasColumn('batches','slug')) {
        $bq->where('slug', $batchKey);
    } else {
        return response()->json(['error' => 'Batch not found'], 404);
    }

    $batch = $bq->first();
    if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

    /* --------------------------
     | RBAC checks
     * -------------------------- */
    $biUserCol = Schema::hasColumn('batch_instructors','user_id')
        ? 'user_id'
        : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

    $bsUserCol = Schema::hasColumn('batch_students','user_id')
        ? 'user_id'
        : (Schema::hasColumn('batch_students','student_id') ? 'student_id' : null);

    if ($isInstructor) {
        if (!$biUserCol) return response()->json(['error'=>'Schema error'],500);
        if (!DB::table('batch_instructors')
            ->where('batch_id',$batch->id)
            ->where($biUserCol,$uid)
            ->whereNull('deleted_at')
            ->exists()) {
            return response()->json(['error'=>'Forbidden'],403);
        }
    }

    if ($isStudent) {
        if (!$bsUserCol) return response()->json(['error'=>'Schema error'],500);
        if (!DB::table('batch_students')
            ->where('batch_id',$batch->id)
            ->where($bsUserCol,$uid)
            ->whereNull('deleted_at')
            ->exists()) {
            return response()->json(['error'=>'Forbidden'],403);
        }
    }

    /* --------------------------
     | Course
     * -------------------------- */
    $course = DB::table('courses')
        ->where('id', $batch->course_id)
        ->whereNull('deleted_at')
        ->first();

    if (!$course) {
        return response()->json(['error'=>'Course not found'],404);
    }

    /* =========================================================
     | 🔥 Resolve Module Filter (UUID or ID)
     * ========================================================= */
    $filterModuleId = null;

    $moduleUuid = $r->query('module_uuid') ?? $r->query('module');
    $moduleId   = $r->query('course_module_id');

    if ($moduleUuid && Str::isUuid($moduleUuid)) {
        $filterModuleId = DB::table('course_modules')
            ->where('uuid', $moduleUuid)
            ->where('course_id', $course->id)
            ->whereNull('deleted_at')
            ->value('id');
    }
    elseif ($moduleId && ctype_digit((string)$moduleId)) {
        $filterModuleId = DB::table('course_modules')
            ->where('id', (int)$moduleId)
            ->where('course_id', $course->id)
            ->whereNull('deleted_at')
            ->value('id');
    }

    // ❌ Module param provided but invalid
    if (($moduleUuid || $moduleId) && !$filterModuleId) {
        return response()->json([
            'error' => 'Module not found for this course'
        ], 404);
    }

    /* --------------------------
     | Modules (Sidebar)
     * -------------------------- */
    $modQ = DB::table('course_modules')
        ->where('course_id', $course->id)
        ->whereNull('deleted_at');

    if (!$isStaff) $modQ->where('status', 'published');
    if ($filterModuleId) $modQ->where('id', $filterModuleId);

    $modules = $modQ
        ->orderBy('order_no')
        ->orderBy('id')
        ->get();

    /* --------------------------
     | Study Materials
     * -------------------------- */
    $rows = DB::table('study_materials as sm')
        ->join('course_modules as cm', function ($j) {
            $j->on('cm.id','=','sm.course_module_id')
              ->whereNull('cm.deleted_at');
        })
        ->leftJoin('users as u','u.id','=','sm.created_by')
        ->where('sm.batch_id', $batch->id)
        ->where('cm.course_id', $course->id)
        ->whereNull('sm.deleted_at')
        ->when(!$isStaff, fn($q)=>$q->where('cm.status','published'))
        ->when($filterModuleId, fn($q)=>$q->where('sm.course_module_id',$filterModuleId))
        ->orderBy('cm.order_no')
        ->orderBy('sm.created_at','desc')
        ->select(
            'sm.*',
            'cm.uuid as module_uuid',
            'cm.title as module_title',
            'u.name as created_by_name'
        )
        ->get();

    /* --------------------------
     | Group by Module
     * -------------------------- */
    $grouped = [];

    foreach ($rows as $row) {
        $mid = (int)$row->course_module_id;

        if (!isset($grouped[$mid])) {
            $grouped[$mid] = [
                'module' => [
                    'id'    => $mid,
                    'uuid'  => $row->module_uuid,
                    'title' => $row->module_title,
                ],
                'materials' => []
            ];
        }

        $attachments = $this->jsonDecode($row->attachment);

        foreach ($attachments as &$a) {
            if (isset($a['id'])) {
                $a['stream_url'] =
                    $this->appUrl()."/api/study-materials/stream/{$row->uuid}/{$a['id']}";
            }
        }

        $grouped[$mid]['materials'][] = [
            'id' => (int)$row->id,
            'uuid' => $row->uuid,
            'title' => $row->title,
            'description' => $row->description,
            'attachments' => $attachments,
            'attachment_count' => (int)$row->attachment_count,
            'created_by_name' => $row->created_by_name,
            'created_at' => $row->created_at,
        ];
    }

    /* --------------------------
     | Response
     * -------------------------- */
    return response()->json([
        'data' => [
            'batch' => (array)$batch,
            'course' => [
                'id' => (int)$course->id,
                'uuid' => $course->uuid,
                'title' => $course->title,
            ],
            'modules_with_materials' => array_values($grouped),
            'all_modules' => $modules->map(fn($m)=>[
                'id' => (int)$m->id,
                'uuid' => $m->uuid,
                'title' => $m->title,
                'status' => $m->status,
            ])->values(),
            'permissions' => [
                'can_upload_materials' => $isAdminLike || $isInstructor,
            ]
        ]
    ]);
}

    /**
     * Create study material for a batch (resolve batch by id|uuid|slug).
     *
     * Roles allowed: admin, superadmin, instructor
     */
    public function storeByBatch(Request $r, string $batchKey)
{
    // ---- permission (admin/superadmin/instructor)
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
    $actor = $this->actor($r);
    $role  = $actor['role'];
    $uid   = $actor['id'];

    /* --------------------------------
     | Resolve batch (id | uuid | slug)
     * -------------------------------- */
    $bq = DB::table('batches')->whereNull('deleted_at');

    if (ctype_digit($batchKey)) {
        $bq->where('id', (int)$batchKey);
    } elseif (Str::isUuid($batchKey)) {
        $bq->where('uuid', $batchKey);
    } elseif (Schema::hasColumn('batches','slug')) {
        $bq->where('slug', $batchKey);
    } else {
        return response()->json(['error'=>'Batch not found'],404);
    }

    $batch = $bq->first();
    if (!$batch) return response()->json(['error'=>'Batch not found'],404);

    /* --------------------------------
     | Instructor must be assigned
     * -------------------------------- */
    if ($role === 'instructor') {
        $biUserCol = Schema::hasColumn('batch_instructors','user_id')
            ? 'user_id'
            : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

        if (!$biUserCol) {
            return response()->json(['error'=>'Schema issue: batch_instructors missing FK'],500);
        }

        $assigned = DB::table('batch_instructors')
            ->where('batch_id',$batch->id)
            ->where($biUserCol,$uid)
            ->whereNull('deleted_at')
            ->exists();

        if (!$assigned) return response()->json(['error'=>'Forbidden'],403);
    }

    /* --------------------------------
     | Infer course
     * -------------------------------- */
    $courseId = (int) $batch->course_id;
    if (!$courseId) {
        return response()->json(['error'=>'Batch has no course'],422);
    }

    /* --------------------------------
     | Validation
     * -------------------------------- */
    $v = Validator::make($r->all(), [
        'course_module_id' => 'nullable|integer',
        'module_uuid'      => 'nullable|uuid',
        'module'           => 'nullable',
        'title'            => 'required|string|max:255',
        'description'      => 'nullable|string',
        'view_policy'      => 'nullable|in:inline_only,downloadable',
        'attachments.*'    => 'nullable|file|max:51200',
        'library_urls.*'   => 'nullable|url',
    ], [
        'attachments.*.max' => 'Each attachment must be <= 50 MB.',
    ]);

    if ($v->fails()) {
        return response()->json(['errors'=>$v->errors()],422);
    }

    /* =========================================================
     | 🔥 Unified module resolution (single source of truth)
     * ========================================================= */
    $moduleKey =
        $r->input('course_module_id')
        ?? $r->input('module_uuid')
        ?? $r->input('module')
        ?? $r->query('module'); // optional future support

    $moduleId = null;

    if ($moduleKey !== null && $moduleKey !== '') {

        if (ctype_digit((string)$moduleKey)) {
            $moduleId = (int)$moduleKey;

        } elseif (Str::isUuid((string)$moduleKey)) {
            $moduleId = DB::table('course_modules')
                ->where('uuid',$moduleKey)
                ->whereNull('deleted_at')
                ->value('id');
        }

       
        if ($moduleKey === null) {
    return response()->json([
        'errors' => [
            'module' => ['Module is required when creating study material']
        ]
    ], 422);
}


        // 🔐 ensure module belongs to batch’s course
        $valid = DB::table('course_modules')
            ->where('id',$moduleId)
            ->where('course_id',$courseId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$valid) {
            return response()->json([
                'errors'=>['module'=>['Module does not belong to this batch course']]
            ],422);
        }
    }

    /* --------------------------------
     | Identifiers
     * -------------------------------- */
    $uuid   = $this->genUuid();
    $slug   = $this->uniqueSlug($r->title);
    $policy = $r->input('view_policy','inline_only');

    /* --------------------------------
     | Attachments (files + library)
     * -------------------------------- */
    $stored = $this->appendFilesAndLibraryUrls($r, (int)$batch->id, []);

    /* --------------------------------
     | Insert
     * -------------------------------- */
    $now = Carbon::now();

    $id = DB::table('study_materials')->insertGetId([
        'uuid'             => $uuid,
        'course_id'        => $courseId,
        'course_module_id' => $moduleId,
        'batch_id'         => (int)$batch->id,
        'title'            => $r->title,
        'slug'             => $slug,
        'description'      => $r->input('description'),
        'attachment'       => $stored ? json_encode($stored) : null,
        'attachment_count' => count($stored),
        'view_policy'      => $policy,
        'created_by'       => $actor['id'] ?: 0,
        'created_at'       => $now,
        'updated_at'       => $now,
    ]);

    return response()->json([
        'message'     => 'Study material created',
        'id'          => $id,
        'uuid'        => $uuid,
        'slug'        => $slug,
        'module_id'   => $moduleId,
        'attachments' => $stored,
    ],201);
}


    /* =========================================================
     |  BIN (deleted items) by Batch
     * ========================================================= */
    public function binByBatch(Request $r, string $batchKey)
    {
        // require admin or superadmin or instructor
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
        $actor = $this->actor($r);
        $uid   = $actor['id'];
        $role  = $actor['role'];

        // resolve batch (id | uuid | slug)
        $bq = DB::table('batches')->whereNull('deleted_at');
        if (ctype_digit($batchKey)) {
            $bq->where('id', (int)$batchKey);
        } elseif (\Illuminate\Support\Str::isUuid($batchKey)) {
            $bq->where('uuid', $batchKey);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('batches', 'slug')) {
            $bq->where('slug', $batchKey);
        } else {
            return response()->json(['error' => 'Batch not found'], 404);
        }
        $batch = $bq->first();
        if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

        // Instructor permission: must be assigned to the batch
        if ($role === 'instructor') {
            $biUserCol = \Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','user_id')
                ? 'user_id'
                : (\Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

            if (!$biUserCol) {
                return response()->json(['error' => 'Schema issue: batch_instructors needs user_id or instructor_id'], 500);
            }

            $assigned = DB::table('batch_instructors')
                ->where('batch_id', $batch->id)
                ->whereNull('batch_instructors.deleted_at')
                ->where($biUserCol, $uid)
                ->exists();

            if (!$assigned) return response()->json(['error' => 'Forbidden'], 403);
        }

        // Fetch ONLY soft-deleted materials for this batch
        $items = DB::table('study_materials')
            ->where('batch_id', $batch->id)
            ->whereNotNull('deleted_at')
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(function($row){
                // decode attachments safely
                if (is_string($row->attachment)) {
                    try { $row->attachment = json_decode($row->attachment, true) ?: []; }
                    catch (\Throwable $e) { $row->attachment = []; }
                }
                return $row;
            });

        return response()->json([
            'message' => 'Deleted items (bin) for batch',
            'batch_uuid' => $batch->uuid,
            'data' => $items,
        ], 200);
    }
    /**
 * Append uploaded files AND library_urls[] (remote URL attachments) to existing stored attachments.
 *
 * @param Request $r
 * @param int $batchId
 * @param array $stored  Existing decoded attachments array (will be appended to)
 * @return array        Merged stored attachments array
 */
protected function appendFilesAndLibraryUrls(Request $r, int $batchId, array $existing = []): array
{
    // normalize existing attachments to array
    $stored = $existing ?: [];
    if (!is_array($stored)) $stored = [];

    // Build dedupe keys set (by url or sha256)
    $seenUrl = [];
    $seenSha = [];

    foreach ($stored as $att) {
        if (!empty($att['url'])) $seenUrl[(string)$att['url']] = true;
        if (!empty($att['sha256'])) $seenSha[(string)$att['sha256']] = true;
    }

    // 1) Handle uploaded files (attachments[] / attachments)
    $files = [];
    if ($r->hasFile('attachments')) {
        $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
    }

    if (!empty($files)) {
        // store under public/assets/media/study_materials/batchStudyMaterial/{batchId}/...
        $folder = "batchStudyMaterial/".(int)$batchId;
        $destBase = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . self::MEDIA_SUBDIR . DIRECTORY_SEPARATOR . $folder;
        $this->ensureDir($destBase);

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            // move & compute metadata
            $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $fid  = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(10));
            $name = $fid.'.'.$ext;

            try {
                $file->move($destBase, $name);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to move uploaded study material file (append helper): '.$e->getMessage(), ['dest'=>$destBase,'file'=>$name]);
                continue;
            }

            $absPath = $destBase.DIRECTORY_SEPARATOR.$name;
            $relPath = self::MEDIA_SUBDIR . '/' . $folder . '/' . $name;
            $mime = $file->getClientMimeType() ?: (@function_exists('mime_content_type') ? mime_content_type($absPath) : 'application/octet-stream');
            $size = @filesize($absPath) ?: 0;
            $sha  = @hash_file('sha256', $absPath) ?: null;
            $url  = $this->toPublicUrl($relPath);

            // dedupe by sha if present
            if ($sha && isset($seenSha[$sha])) {
                // already present, skip
                continue;
            }
            if ($url && isset($seenUrl[$url])) {
                // exact same URL already present, skip
                continue;
            }

            // register seen keys
            if ($sha) $seenSha[$sha] = true;
            if ($url) $seenUrl[$url] = true;

            $stored[] = [
                'id'          => $fid,
                'disk'        => 'public',
                'path'        => $relPath,
                'url'         => $url,
                'mime'        => $mime,
                'ext'         => $ext,
                'size'        => $size,
                'sha256'      => $sha,
                'uploaded_at' => \Carbon\Carbon::now()->toIso8601String(),
            ];
        }
    }

    // 2) Handle library_urls[] (URL-based attachments)
    $libUrls = $r->input('library_urls', []);
    if ($libUrls && !is_array($libUrls)) $libUrls = [$libUrls];

    foreach ($libUrls as $u) {
        if (!$u || !is_string($u)) continue;
        $u = trim($u);
        if ($u === '') continue;

        // Normalize URL (avoid duplicates by exact URL)
        if (isset($seenUrl[$u])) continue;

        // Create id from hash of URL so it's stable and unique-ish
        $id = 'lib-' . substr(hash('sha256', $u), 0, 12);

        // attempt to detect extension/mime (best-effort)
        $ext = pathinfo(parse_url($u, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION);
        $ext = strtolower((string)$ext);
        $mime = '';
        if ($ext) {
            // best-effort mime map for common types (optional)
            $map = [
                'pdf'=>'application/pdf',
                'png'=>'image/png',
                'jpg'=>'image/jpeg','jpeg'=>'image/jpeg',
                'mp4'=>'video/mp4','webm'=>'video/webm',
                'gif'=>'image/gif'
            ];
            if (isset($map[$ext])) $mime = $map[$ext];
        }

        $stored[] = [
            'id'          => $id,
            'disk'        => 'external',
            'path'        => $u,
            'url'         => $u,
            'mime'        => $mime,
            'ext'         => $ext,
            'size'        => null,
            'sha256'      => null,
            'uploaded_at' => \Carbon\Carbon::now()->toIso8601String(),
        ];

        // mark seen
        $seenUrl[$u] = true;
    }

    // Final: ensure uniqueness in $stored by url then by sha (stable)
    $uniq = [];
    $out = [];
    foreach ($stored as $att) {
        $key = '';
        if (!empty($att['url'])) $key = 'u:'.(string)$att['url'];
        elseif (!empty($att['sha256'])) $key = 's:'.(string)$att['sha256'];
        elseif (!empty($att['path'])) $key = 'p:'.(string)$att['path'];
        else $key = 'i:'.(string)($att['id'] ?? \Illuminate\Support\Str::random(6));

        if (isset($uniq[$key])) continue;
        $uniq[$key] = true;
        $out[] = $att;
    }

    return $out;
}

}
