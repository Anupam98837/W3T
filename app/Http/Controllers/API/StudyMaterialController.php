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

    private function ensureDir(string $path): string
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0775, true);
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

    /* =========================================================
     |  List (filters for dropdown-driven page)
     * ========================================================= */
    public function index(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

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
     |  Create
     |  - saves files under storage/app/batchStudyMaterial/{batch_id}
     |  - stores full streaming URL in attachment JSON
     * ========================================================= */
    public function store(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
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
     |  Update basic fields and (optionally) add more files
     * ========================================================= */
    /**
 * Update basic fields and (optionally) add more files / remove selected files
 */
public function update(Request $r, $id)
{
    // permission check (kept as in original)
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $row = DB::table('study_materials')->where('id', (int)$id)->whereNull('deleted_at')->first();
    if (!$row) return response()->json(['error' => 'Not found'], 404);

    // validation: note remove_attachments is optional array of strings
    $v = Validator::make($r->all(), [
        'title'               => 'sometimes|required|string|max:255',
        'description'         => 'nullable|string',
        'view_policy'         => 'nullable|in:inline_only,downloadable',
        'attachments.*'       => 'nullable|file|max:51200',
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
                    if (!empty($att['path']) && (empty($att['disk']) || $att['disk'] === 'local')) {
                        $candidate = storage_path('app/' . ltrim($att['path'], '/'));
                        if (file_exists($candidate)) {
                            @unlink($candidate);
                        }
                    }
                } catch (\Throwable $ex) {
                    // ignore deletion errors; optionally log: \Log::warning(...)
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
    // Append newly uploaded files (same logic as original)
    //
    if ($r->hasFile('attachments')) {
        $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
        $root  = $this->ensureDir(storage_path('app/batchStudyMaterial/' . (int)$row->batch_id));
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $fid  = Str::lower(Str::random(10));
            $name = $fid . '.' . $ext;
            $file->move($root, $name);

            $absPath = $root . DIRECTORY_SEPARATOR . $name;
            $relPath = 'batchStudyMaterial/' . (int)$row->batch_id . '/' . $name;

            $mime = $file->getClientMimeType() ?: mime_content_type($absPath) ?: 'application/octet-stream';
            $size = @filesize($absPath) ?: 0;
            $sha  = hash_file('sha256', $absPath);
            $url  = $this->appUrl() . "/api/study-materials/stream/{$row->uuid}/{$fid}";

            $stored[] = [
                'id'          => $fid,
                'disk'        => 'local',
                'path'        => $relPath,
                'url'         => $url,
                'mime'        => $mime,
                'ext'         => $ext,
                'size'        => $size,
                'sha256'      => $sha,
                'uploaded_at' => Carbon::now()->toIso8601String(),
            ];
        }
    }

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
            $absPath = storage_path('app/'.($a['path'] ?? ''));
            if (is_file($absPath)) {
                @unlink($absPath);
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
        // Roles: allow admins now; extend later (instructor/student) if needed
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
            // ok — we’ll force inline below
        }

        $absPath = storage_path('app/'.($file['path'] ?? ''));
        if (!is_file($absPath)) {
            return response()->json(['error'=>'File missing on server'], 410);
        }

        $ext  = $file['ext']  ?? 'bin';
        $mime = $file['mime'] ?? 'application/octet-stream';
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
 |  View Study Materials by Batch (with RBAC)
 * ========================================================= */
public function viewStudyMaterialByBatch(Request $r, string $batchKey)
{
    // ---- role from CheckRole (canonical: superadmin/admin/instructor/student)
    $role = (string) $r->attributes->get('auth_role');
    $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
    if (!$role || !in_array($role, ['superadmin','admin','instructor','student'], true)) {
        return response()->json(['error' => 'Unauthorized Access'], 403);
    }
    $isAdminLike = in_array($role, ['superadmin','admin'], true);
    $isInstructor = $role === 'instructor';
    $isStudent    = $role === 'student';

    // ---- resolve batch by id / uuid / (optional) slug
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

    // ---- detect pivot FK columns safely (same as your course function)
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

    // ---- load course for context
    $course = DB::table('courses')
        ->where('id', $batch->course_id)
        ->whereNull('deleted_at')
        ->first();
    if (!$course) {
        return response()->json(['error' => 'Course not found for this batch'], 404);
    }

    // ---- load modules for this course (students only see published)
    $isStaff = $isAdminLike || $isInstructor;
    $modQ = DB::table('course_modules')
        ->where('course_id', $course->id)
        ->whereNull('deleted_at')
        ->orderBy('order_no')->orderBy('id');
    if (!$isStaff) $modQ->where('status', 'published');
    $modules = $modQ->get();

    // ---- load study materials for this batch
    $smQ = DB::table('study_materials as sm')
        ->leftJoin('course_modules as cm', 'cm.id', '=', 'sm.course_module_id')
        ->where('sm.batch_id', $batch->id)
        ->whereNull('sm.deleted_at')
        ->whereNull('cm.deleted_at')
        ->select(
            'sm.id',
            'sm.uuid',
            'sm.title',
            'sm.slug',
            'sm.description',
            'sm.view_policy',
            'sm.attachment',
            'sm.attachment_count',
            'sm.course_module_id',
            'cm.title as module_title',
            'cm.uuid as module_uuid',
            'sm.created_at',
            'sm.updated_at'
        )
        ->orderBy('cm.order_no')
        ->orderBy('sm.created_at', 'desc');

    // Students only see study materials from published modules
    if (!$isStaff) {
        $smQ->where('cm.status', 'published');
    }

    $studyMaterials = $smQ->get();

    // Process attachments and group by module
    $materialsByModule = [];
    foreach ($studyMaterials as $material) {
        $moduleId = $material->course_module_id;
        
        if (!isset($materialsByModule[$moduleId])) {
            $materialsByModule[$moduleId] = [
                'module' => [
                    'id' => (int)$material->course_module_id,
                    'uuid' => $material->module_uuid,
                    'title' => $material->module_title,
                ],
                'materials' => []
            ];
        }

        $attachments = $this->jsonDecode($material->attachment);
        $materialData = [
            'id' => (int)$material->id,
            'uuid' => $material->uuid,
            'title' => $material->title,
            'slug' => $material->slug,
            'description' => $material->description,
            'view_policy' => $material->view_policy,
            'attachments' => $attachments,
            'attachment_count' => (int)$material->attachment_count,
            'created_at' => $material->created_at,
            'updated_at' => $material->updated_at,
        ];

        // Generate stream URLs for frontend
        foreach ($materialData['attachments'] as &$attachment) {
            if (isset($attachment['id'])) {
                $attachment['stream_url'] = $this->appUrl() . "/api/study-materials/stream/{$material->uuid}/{$attachment['id']}";
            }
        }

        $materialsByModule[$moduleId]['materials'][] = $materialData;
    }

    // Convert to indexed array
    $modulesWithMaterials = array_values($materialsByModule);

    // ---- instructors for sidebar (same as your course function)
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

    $materialsCount = DB::table('study_materials')
        ->where('batch_id', $batch->id)
        ->whereNull('deleted_at')
        ->count();

    // ---- payload
    $payload = [
        'batch' => (array)$batch,
        'course' => [
            'id' => (int)$course->id,
            'uuid' => $course->uuid,
            'title' => $course->title,
            'slug' => $course->slug,
        ],
        'modules_with_materials' => $modulesWithMaterials,
        'all_modules' => $modules->map(fn($m) => [
            'id' => (int)$m->id,
            'uuid' => $m->uuid,
            'title' => $m->title,
            'status' => $m->status,
            'order_no' => (int)$m->order_no,
        ])->values(),
        'instructors' => $instructors,
        'stats' => [
            'students_count' => (int)$studentsCount,
            'materials_count' => (int)$materialsCount,
            'modules_count' => count($modules),
            'you_are_instructor' => $isInstructor,
            'you_are_student' => $isStudent,
        ],
        'permissions' => [
            'can_view_unpublished_modules' => $isStaff,
            'can_view_unpublished_materials' => $isStaff,
            'can_upload_materials' => $isAdminLike || $isInstructor,
        ],
    ];

    // Log access (using your logging pattern)
    $this->logWithActor('[Study Materials View By Batch] payload prepared', $r, [
        'batch_id' => (int)$batch->id,
        'course_id' => (int)$course->id,
        'materials_count' => $materialsCount,
        'modules_count' => count($modulesWithMaterials),
        'role' => $role,
    ]);

    return response()->json(['data' => $payload]);
}
/**
 * Create study material for a batch (resolve batch by id|uuid|slug).
 *
 * Roles allowed: admin, superadmin, instructor
 * - instructor must be assigned to the batch
 *
 * Request:
 *  - course_module_id (required)
 *  - title (required)
 *  - description (optional)
 *  - view_policy (nullable inline_only|downloadable)
 *  - attachments[] (optional files)
 */
public function storeByBatch(Request $r, string $batchKey)
{
    // permission (admin/superadmin/instructor)
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
    $actor = $this->actor($r);
    $role = $actor['role'];
    $uid  = $actor['id'];

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

    // If instructor, must be assigned to this batch
    if ($role === 'instructor') {
        // detect pivot user column like other functions
        $biUserCol = \Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','user_id')
            ? 'user_id'
            : (\Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);
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

    // infer course_id from batch (safer for consistency)
    $courseId = (int) ($batch->course_id ?? 0);

    // validation — module optional now; allow module_uuid for convenience
    $v = Validator::make($r->all(), [
        'course_module_id' => 'sometimes|nullable|integer|exists:course_modules,id',
        'module_uuid'      => 'sometimes|nullable|uuid|exists:course_modules,uuid',
        'module'           => 'sometimes|nullable', // allow alternative param name
        'title'            => 'required|string|max:255',
        'description'      => 'nullable|string',
        'view_policy'      => 'nullable|in:inline_only,downloadable',
        'attachments.*'    => 'nullable|file|max:51200',
    ], [
        'attachments.*.max' => 'Each attachment must be <= 50 MB.'
    ]);
    if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

    //
    // Resolve module (optional) — priority:
    // 1) course_module_id (explicit)
    // 2) module_uuid or module (uuid) from client
    // 3) infer from course: single module -> pick it; else prefer published -> pick first published; else null
    //
    $moduleId = null;

    if ($r->filled('course_module_id')) {
        $module = DB::table('course_modules')->where('id', (int)$r->course_module_id)->whereNull('deleted_at')->first();
        if (!$module) {
            return response()->json(['errors' => ['course_module_id' => ['Course module not found']]], 422);
        }
        $moduleId = (int)$module->id;
    } elseif ($r->filled('module_uuid') || $r->filled('module')) {
        $modUuid = $r->input('module_uuid') ?: $r->input('module');
        if ($modUuid && \Illuminate\Support\Str::isUuid($modUuid)) {
            $module = DB::table('course_modules')->where('uuid', $modUuid)->whereNull('deleted_at')->first();
            if (!$module) {
                return response()->json(['errors' => ['module_uuid' => ['Course module (uuid) not found']]], 422);
            }
            $moduleId = (int)$module->id;
        } else {
            // provided module param isn't a UUID — ignore, we'll try to infer
            $moduleId = null;
        }
    } else {
        // Try to infer module from the course associated with the batch
        $modsQuery = DB::table('course_modules')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->orderBy('order_no')->orderBy('id');

        $modules = $modsQuery->get();

        if ($modules->count() === 1) {
            $moduleId = (int)$modules->first()->id;
        } else {
            // prefer a published module if any
            $published = $modules->firstWhere('status', 'published');
            if ($published) {
                $moduleId = (int)$published->id;
            } else {
                // leave null (unassigned)
                $moduleId = null;
            }
        }
    }

    // Sanity: if moduleId found, ensure it belongs to same course (extra safety)
    if ($moduleId !== null) {
        $moduleCheck = DB::table('course_modules')->where('id', $moduleId)->whereNull('deleted_at')->first();
        if (!$moduleCheck || (int)$moduleCheck->course_id !== $courseId) {
            return response()->json(['errors' => ['course_module_id' => ['Course module does not belong to this batch\'s course']]], 422);
        }
    }

    // build identifiers
    $uuid = $this->genUuid();
    $slug = $this->uniqueSlug($r->title);
    $policy = $r->input('view_policy', 'inline_only');

    // collect files (support attachments or attachments[])
    $files = [];
    if ($r->hasFile('attachments')) {
        $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
    }

    $stored = [];
    if (!empty($files)) {
        $root = $this->ensureDir(storage_path('app/batchStudyMaterial/'.(int)$batch->id));
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $fid  = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(10));
            $name = $fid.'.'.$ext;

            // move uploaded file
            $file->move($root, $name);

            $absPath = $root.DIRECTORY_SEPARATOR.$name;
            $relPath = 'batchStudyMaterial/'.(int)$batch->id.'/'.$name;

            $mime = $file->getClientMimeType() ?: mime_content_type($absPath) ?: 'application/octet-stream';
            $size = @filesize($absPath) ?: 0;
            $sha  = hash_file('sha256', $absPath);

            $url  = $this->appUrl()."/api/study-materials/stream/{$uuid}/{$fid}";

            $stored[] = [
                'id'          => $fid,
                'disk'        => 'local',
                'path'        => $relPath,
                'url'         => $url,
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
        'course_id'          => $courseId,
        'course_module_id'   => $moduleId !== null ? $moduleId : null,
        'batch_id'           => (int)$batch->id,
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
            ->whereNull('deleted_at')
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
}
