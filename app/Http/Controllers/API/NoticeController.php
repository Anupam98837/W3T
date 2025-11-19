<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NoticeController extends Controller
{
    /* -------------------------
     |  Helpers
     * ------------------------- */

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
        if ($base === '') $base = 'notice';
        $slug = $base;
        $i = 1;
        while (DB::table('notices')->where('slug', $slug)->exists()) {
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

    private function logWithActor(string $message, Request $r, array $context = [])
    {
        $actor = $this->actor($r);
        Log::info($message, array_merge([
            'actor_id' => $actor['id'],
            'actor_role' => $actor['role'],
        ], $context));
    }

    /* =========================================================
     |  Index (list) — filters for dropdown-driven page
     * ========================================================= */
    public function index(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        $q = DB::table('notices')->whereNull('deleted_at');

        if ($r->filled('course_id'))        $q->where('course_id', (int)$r->course_id);
        if ($r->filled('course_module_id')) $q->where('course_module_id', (int)$r->course_module_id);
        if ($r->filled('batch_id'))         $q->where('batch_id', (int)$r->batch_id);
        if ($r->filled('visibility_scope')) $q->where('visibility_scope', $r->visibility_scope);
        if ($r->filled('status'))           $q->where('status', $r->status);
        if ($r->filled('priority'))         $q->where('priority', $r->priority);

        if ($r->filled('search')) {
            $s = '%'.trim($r->search).'%';
            $q->where(function($w) use ($s){
                $w->where('title', 'like', $s)->orWhere('message_html', 'like', $s);
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
     * ========================================================= */
    public function store(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
        $actor = $this->actor($r);

        $v = Validator::make($r->all(), [
            'course_id'         => 'required|integer|exists:courses,id',
            'course_module_id'  => 'nullable|integer|exists:course_modules,id',
            'batch_id'          => 'required|integer|exists:batches,id',
            'visibility_scope'  => 'nullable|in:course,batch,module',
            'title'             => 'required|string|max:255',
            'message_html'      => 'nullable|string',
            'priority'          => 'nullable|in:low,normal,high',
            'status'            => 'nullable|in:draft,published,archived',
            'attachments.*'     => 'nullable|file|max:51200',
            'created_at_ip'     => 'nullable|ip',
        ], [
            'attachments.*.max' => 'Each attachment must be <= 50 MB.'
        ]);

        if ($v->fails()) {
            return response()->json(['errors'=>$v->errors()], 422);
        }

        $uuid = $this->genUuid();
        $slug = $this->uniqueSlug($r->title);
        $visibility = $r->input('visibility_scope', 'batch');

        // Collect files
        $files = [];
        if ($r->hasFile('attachments')) {
            $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
        }

        $stored = [];
        if (!empty($files)) {
            $root = $this->ensureDir(storage_path('app/notices/'.(int)$r->batch_id));
            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $fid  = Str::lower(Str::random(10));
                $name = $fid.'.'.$ext;

                $file->move($root, $name);

                $absPath = $root.DIRECTORY_SEPARATOR.$name;
                $relPath = 'notices/'.(int)$r->batch_id.'/'.$name;

                $mime = $file->getClientMimeType() ?: (is_file($absPath) ? mime_content_type($absPath) : 'application/octet-stream');
                $size = @filesize($absPath) ?: 0;
                $sha  = is_file($absPath) ? hash_file('sha256', $absPath) : null;

                $url  = $this->appUrl()."/api/notices/stream/{$uuid}/{$fid}";

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
        $id = DB::table('notices')->insertGetId([
            'uuid'              => $uuid,
            'course_id'         => (int)$r->course_id,
            'course_module_id'  => $r->input('course_module_id') ? (int)$r->course_module_id : null,
            'batch_id'          => (int)$r->batch_id,
            'visibility_scope'  => $visibility,
            'title'             => $r->title,
            'slug'              => $slug,
            'message_html'      => $r->input('message_html'),
            'attachments'       => $stored ? json_encode($stored) : null,
            'priority'          => $r->input('priority', 'normal'),
            'status'            => $r->input('status', 'draft'),
            'created_by'        => $actor['id'] ?: 0,
            'created_at_ip'     => $r->input('created_at_ip'),
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        return response()->json([
            'message' => 'Notice created',
            'id'      => $id,
            'uuid'    => $uuid,
            'slug'    => $slug,
            'attachments' => $stored,
        ], 201);
    }

    /* =========================================================
     |  Update (fields + add/remove attachments)
     * ========================================================= */
    public function update(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        $row = DB::table('notices')->where('id', (int)$id)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $v = Validator::make($r->all(), [
            'visibility_scope'     => 'nullable|in:course,batch,module',
            'title'                => 'sometimes|required|string|max:255',
            'message_html'         => 'nullable|string',
            'priority'             => 'nullable|in:low,normal,high',
            'status'               => 'nullable|in:draft,published,archived',
            'attachments.*'        => 'nullable|file|max:51200',
            'remove_attachments'   => 'sometimes|array',
            'remove_attachments.*' => 'string',
            'regenerate_slug'      => 'sometimes|boolean',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $update = [];

        if ($r->filled('title')) {
            $update['title'] = $r->title;
            if ($r->boolean('regenerate_slug', false)) {
                $update['slug'] = $this->uniqueSlug($r->title);
            }
        }

        if ($r->exists('message_html')) $update['message_html'] = $r->input('message_html');
        if ($r->filled('visibility_scope')) $update['visibility_scope'] = $r->input('visibility_scope');
        if ($r->filled('priority')) $update['priority'] = $r->input('priority');
        if ($r->filled('status')) $update['status'] = $r->input('status');

        $stored = $this->jsonDecode($row->attachments);
        if (!is_array($stored)) $stored = [];

        // Handle removals
        $toRemove = $r->input('remove_attachments', []);
        if (!is_array($toRemove)) $toRemove = [$toRemove];

        if (!empty($toRemove)) {
            $remSet = array_flip(array_map('strval', $toRemove));
            $kept = [];
            foreach ($stored as $att) {
                $attId = isset($att['id']) ? (string)$att['id'] : (string)($att['path'] ?? ($att['url'] ?? ''));
                if ($attId !== '' && isset($remSet[$attId])) {
                    // attempt to remove file (best-effort)
                    try {
                        if (!empty($att['path']) && (empty($att['disk']) || $att['disk'] === 'local')) {
                            $candidate = storage_path('app/' . ltrim($att['path'], '/'));
                            if (file_exists($candidate)) {
                                @unlink($candidate);
                            }
                        }
                    } catch (\Throwable $ex) {
                        Log::warning('Failed to unlink notice attachment', ['path' => $att['path'] ?? null, 'error' => $ex->getMessage()]);
                    }
                    continue; // removed
                }
                $kept[] = $att;
            }
            $stored = $kept;
        }

        // Append new attachments
        if ($r->hasFile('attachments')) {
            $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
            $root  = $this->ensureDir(storage_path('app/notices/' . (int)$row->batch_id));
            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $fid  = Str::lower(Str::random(10));
                $name = $fid . '.' . $ext;
                $file->move($root, $name);

                $absPath = $root . DIRECTORY_SEPARATOR . $name;
                $relPath = 'notices/' . (int)$row->batch_id . '/' . $name;

                $mime = $file->getClientMimeType() ?: (is_file($absPath) ? mime_content_type($absPath) : 'application/octet-stream');
                $size = @filesize($absPath) ?: 0;
                $sha  = is_file($absPath) ? hash_file('sha256', $absPath) : null;
                $url  = $this->appUrl() . "/api/notices/stream/{$row->uuid}/{$fid}";

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

        $update['attachments'] = $stored ? json_encode($stored) : null;
        $update['updated_at'] = Carbon::now();

        DB::table('notices')->where('id', (int)$id)->update($update);

        return response()->json([
            'message' => 'Notice updated',
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

        $row = DB::table('notices')->where('id', (int)$id)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        DB::table('notices')->where('id', (int)$id)->update([
            'deleted_at' => Carbon::now()
        ]);

        return response()->json(['message'=>'Notice moved to bin']);
    }

    /* =========================================================
     |  Force delete (hard)
     * ========================================================= */
    public function forceDelete(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('notices')->where('id', (int)$id)->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        // delete attached files (best-effort)
        $attachments = $this->jsonDecode($row->attachments);
        foreach ($attachments as $a) {
            $absPath = storage_path('app/'.($a['path'] ?? ''));
            if (is_file($absPath)) {
                @unlink($absPath);
            }
        }

        DB::table('notices')->where('id', (int)$id)->delete();

        return response()->json(['message'=>'Notice permanently deleted']);
    }

    /* =========================================================
     |  Bin listing (soft-deleted) — admin only
     * ========================================================= */
    public function indexDeleted(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $q = DB::table('notices')->whereNotNull('deleted_at');

        if ($r->filled('course_id'))        $q->where('course_id', (int)$r->course_id);
        if ($r->filled('course_module_id')) $q->where('course_module_id', (int)$r->course_module_id);
        if ($r->filled('batch_id'))         $q->where('batch_id', (int)$r->batch_id);
        if ($r->filled('search')) {
            $s = '%'.trim($r->search).'%';
            $q->where(function($w) use ($s){
                $w->where('title', 'like', $s)->orWhere('message_html', 'like', $s);
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

    /* =========================================================
     |  Restore from bin — admin only
     * ========================================================= */
    public function restore(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('notices')->where('id', (int)$id)->whereNotNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        DB::table('notices')->where('id', (int)$id)->update(['deleted_at' => null, 'updated_at' => Carbon::now()]);

        return response()->json(['message'=>'Notice restored']);
    }

    /* =========================================================
     |  Show one by uuid
     * ========================================================= */
    public function showByUuid(Request $r, string $uuid)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        $row = DB::table('notices')->where('uuid', $uuid)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        $row->attachments = $this->jsonDecode($row->attachments);
        return response()->json($row);
    }

    /* =========================================================
     |  Stream attachment (inline view)
     * ========================================================= */
    public function streamInline(Request $r, string $uuid, string $fileId)
    {
        // Roles allowed to view attachments (adjust as needed)
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor','student'])) return $res;

        $row = DB::table('notices')->where('uuid', $uuid)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $attachments = $this->jsonDecode($row->attachments);
        $file = null;
        foreach ($attachments as $a) {
            if (($a['id'] ?? null) === $fileId) { $file = $a; break; }
        }
        if (!$file) return response()->json(['error' => 'File not found'], 404);

        $absPath = storage_path('app/'.($file['path'] ?? ''));
        if (!is_file($absPath)) {
            return response()->json(['error'=>'File missing on server'], 410);
        }

        $mime = $file['mime'] ?? mime_content_type($absPath) ?? 'application/octet-stream';
        $ext  = $file['ext'] ?? pathinfo($absPath, PATHINFO_EXTENSION);
        $name = "view.{$ext}";

        $headers = [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'inline; filename="'.$name.'"',
            'X-Content-Type-Options'    => 'nosniff',
            'Cache-Control'             => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'                    => 'no-cache',
            'Expires'                   => '0',
        ];

        return response()->file($absPath, $headers);
    }

    /* =========================================================
     |  View Notices by Batch (RBAC-aware) — similar to study materials view
     * ========================================================= */
    public function viewByBatch(Request $r, string $batchKey)
    {
        // ---- roles
        $role = (string) $r->attributes->get('auth_role');
        $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        if (!$role || !in_array($role, ['superadmin','admin','instructor','student'], true)) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }
        $isAdminLike = in_array($role, ['superadmin','admin'], true);
        $isInstructor = $role === 'instructor';
        $isStudent    = $role === 'student';

        // resolve batch
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

        // pivot detection
        $biUserCol = Schema::hasColumn('batch_instructors','user_id')
            ? 'user_id'
            : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

        $bsUserCol = Schema::hasColumn('batch_students','user_id')
            ? 'user_id'
            : (Schema::hasColumn('batch_students','student_id') ? 'student_id' : null);

        // RBAC: instructor assigned?
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

        // load course
        $course = DB::table('courses')
            ->where('id', $batch->course_id)
            ->whereNull('deleted_at')
            ->first();
        if (!$course) {
            return response()->json(['error' => 'Course not found for this batch'], 404);
        }

        // load modules for this course (students see published only)
        $isStaff = $isAdminLike || $isInstructor;
        $modQ = DB::table('course_modules')
            ->where('course_id', $course->id)
            ->whereNull('deleted_at')
            ->orderBy('order_no')->orderBy('id');
        if (!$isStaff) $modQ->where('status', 'published');
        $modules = $modQ->get();

        // load notices for this batch
        $nQ = DB::table('notices as n')
            ->leftJoin('course_modules as cm', 'cm.id', '=', 'n.course_module_id')
            ->where('n.batch_id', $batch->id)
            ->whereNull('n.deleted_at')
            ->whereNull('cm.deleted_at')
            ->select(
                'n.id',
                'n.uuid',
                'n.title',
                'n.slug',
                'n.message_html',
                'n.visibility_scope',
                'n.attachments',
                'n.priority',
                'n.status',
                'n.course_module_id',
                'cm.title as module_title',
                'cm.uuid as module_uuid',
                'n.created_at',
                'n.updated_at'
            )
            ->orderBy('cm.order_no')
            ->orderBy('n.created_at', 'desc');

        if (!$isStaff) {
            $nQ->where('cm.status', 'published');
        }

        $notices = $nQ->get();

        // group by module
        $byModule = [];
        foreach ($notices as $notice) {
            $moduleId = $notice->course_module_id;
            if (!isset($byModule[$moduleId])) {
                $byModule[$moduleId] = [
                    'module' => [
                        'id' => $notice->course_module_id ? (int)$notice->course_module_id : null,
                        'uuid' => $notice->module_uuid,
                        'title' => $notice->module_title,
                    ],
                    'notices' => []
                ];
            }

            $attachments = $this->jsonDecode($notice->attachments);
            $nData = [
                'id' => (int)$notice->id,
                'uuid' => $notice->uuid,
                'title' => $notice->title,
                'slug' => $notice->slug,
                'message_html' => $notice->message_html,
                'visibility_scope' => $notice->visibility_scope,
                'priority' => $notice->priority,
                'status' => $notice->status,
                'attachments' => $attachments,
                'created_at' => $notice->created_at,
                'updated_at' => $notice->updated_at,
            ];

            foreach ($nData['attachments'] as &$attachment) {
                if (isset($attachment['id'])) {
                    $attachment['stream_url'] = $this->appUrl() . "/api/notices/stream/{$notice->uuid}/{$attachment['id']}";
                }
            }

            $byModule[$moduleId]['notices'][] = $nData;
        }

        $modulesWithNotices = array_values($byModule);

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
                    'id'    => (int)$u->id,
                    'uuid'  => $u->uuid,
                    'name'  => $u->name,
                    'email' => $u->email,
                    'role'  => $u->role,
                ])
                ->values();
        }

        $studentsCount = DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->whereNull('deleted_at')
            ->count();

        $noticesCount = DB::table('notices')
            ->where('batch_id', $batch->id)
            ->whereNull('deleted_at')
            ->count();

        $payload = [
            'batch' => (array)$batch,
            'course' => [
                'id' => (int)$course->id,
                'uuid' => $course->uuid,
                'title' => $course->title,
                'slug' => $course->slug,
            ],
            'modules_with_notices' => $modulesWithNotices,
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
                'notices_count' => (int)$noticesCount,
                'modules_count' => count($modules),
                'you_are_instructor' => $isInstructor,
                'you_are_student' => $isStudent,
            ],
            'permissions' => [
                'can_view_unpublished_modules' => $isAdminLike || $isInstructor,
                'can_view_unpublished_notices' => $isAdminLike || $isInstructor,
                'can_create_notices' => $isAdminLike || $isInstructor,
            ],
        ];

        $this->logWithActor('[Notices View By Batch] payload prepared', $r, [
            'batch_id' => (int)$batch->id,
            'course_id' => (int)$course->id,
            'notices_count' => $noticesCount,
            'modules_count' => count($modulesWithNotices),
            'role' => $role,
        ]);

        return response()->json(['data' => $payload]);
    }

    /* =========================================================
     |  Create notice by batch (resolve batch by id|uuid|slug)
     * ========================================================= */
    public function storeByBatch(Request $r, string $batchKey)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
        $actor = $this->actor($r);
        $role = $actor['role'];
        $uid  = $actor['id'];

        // resolve batch
        $bq = DB::table('batches')->whereNull('deleted_at');
        if (ctype_digit($batchKey)) {
            $bq->where('id', (int)$batchKey);
        } elseif (Str::isUuid($batchKey)) {
            $bq->where('uuid', $batchKey);
        } elseif (Schema::hasColumn('batches', 'slug')) {
            $bq->where('slug', $batchKey);
        } else {
            return response()->json(['error' => 'Batch not found'], 404);
        }
        $batch = $bq->first();
        if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

        if ($role === 'instructor') {
            $biUserCol = Schema::hasColumn('batch_instructors','user_id')
                ? 'user_id'
                : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);
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

        $courseId = (int) ($batch->course_id ?? 0);

        $v = Validator::make($r->all(), [
            'course_module_id' => 'sometimes|nullable|integer|exists:course_modules,id',
            'module_uuid'      => 'sometimes|nullable|uuid|exists:course_modules,uuid',
            'title'            => 'required|string|max:255',
            'message_html'     => 'nullable|string',
            'visibility_scope' => 'nullable|in:course,batch,module',
            'priority'         => 'nullable|in:low,normal,high',
            'status'           => 'nullable|in:draft,published,archived',
            'attachments.*'    => 'nullable|file|max:51200',
        ], [
            'attachments.*.max' => 'Each attachment must be <= 50 MB.'
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        // resolve module similar to study-material logic
        $moduleId = null;
        if ($r->filled('course_module_id')) {
            $module = DB::table('course_modules')->where('id', (int)$r->course_module_id)->whereNull('deleted_at')->first();
            if (!$module) {
                return response()->json(['errors' => ['course_module_id' => ['Course module not found']]], 422);
            }
            $moduleId = (int)$module->id;
        } elseif ($r->filled('module_uuid')) {
            $modUuid = $r->input('module_uuid');
            if ($modUuid && Str::isUuid($modUuid)) {
                $module = DB::table('course_modules')->where('uuid', $modUuid)->whereNull('deleted_at')->first();
                if (!$module) {
                    return response()->json(['errors' => ['module_uuid' => ['Course module (uuid) not found']]], 422);
                }
                $moduleId = (int)$module->id;
            }
        } else {
            $modsQuery = DB::table('course_modules')
                ->where('course_id', $courseId)
                ->whereNull('deleted_at')
                ->orderBy('order_no')->orderBy('id');
            $modules = $modsQuery->get();
            if ($modules->count() === 1) {
                $moduleId = (int)$modules->first()->id;
            } else {
                $published = $modules->firstWhere('status', 'published');
                if ($published) $moduleId = (int)$published->id;
            }
        }

        if ($moduleId !== null) {
            $moduleCheck = DB::table('course_modules')->where('id', $moduleId)->whereNull('deleted_at')->first();
            if (!$moduleCheck || (int)$moduleCheck->course_id !== $courseId) {
                return response()->json(['errors' => ['course_module_id' => ['Course module does not belong to this batch\'s course']]], 422);
            }
        }

        $uuid = $this->genUuid();
        $slug = $this->uniqueSlug($r->title);
        $visibility = $r->input('visibility_scope', 'batch');

        $files = [];
        if ($r->hasFile('attachments')) {
            $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
        }

        $stored = [];
        if (!empty($files)) {
            $root = $this->ensureDir(storage_path('app/notices/'.(int)$batch->id));
            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $fid  = Str::lower(Str::random(10));
                $name = $fid.'.'.$ext;

                $file->move($root, $name);

                $absPath = $root.DIRECTORY_SEPARATOR.$name;
                $relPath = 'notices/'.(int)$batch->id.'/'.$name;

                $mime = $file->getClientMimeType() ?: (is_file($absPath) ? mime_content_type($absPath) : 'application/octet-stream');
                $size = @filesize($absPath) ?: 0;
                $sha  = is_file($absPath) ? hash_file('sha256', $absPath) : null;

                $url  = $this->appUrl()."/api/notices/stream/{$uuid}/{$fid}";

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
        $id = DB::table('notices')->insertGetId([
            'uuid'              => $uuid,
            'course_id'         => $courseId,
            'course_module_id'  => $moduleId !== null ? $moduleId : null,
            'batch_id'          => (int)$batch->id,
            'visibility_scope'  => $visibility,
            'title'             => $r->title,
            'slug'              => $slug,
            'message_html'      => $r->input('message_html'),
            'attachments'       => $stored ? json_encode($stored) : null,
            'priority'          => $r->input('priority', 'normal'),
            'status'            => $r->input('status', 'draft'),
            'created_by'        => $actor['id'] ?: 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        return response()->json([
            'message' => 'Notice created',
            'id'      => $id,
            'uuid'    => $uuid,
            'slug'    => $slug,
            'attachments' => $stored,
        ], 201);
    }

    /* =========================================================
     |  Bin by Batch (soft-deleted notices for a given batch)
     * ========================================================= */
    public function binByBatch(Request $r, string $batchKey)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
        $actor = $this->actor($r);
        $uid   = $actor['id'];
        $role  = $actor['role'];

        $bq = DB::table('batches')->whereNull('deleted_at');
        if (ctype_digit($batchKey)) {
            $bq->where('id', (int)$batchKey);
        } elseif (Str::isUuid($batchKey)) {
            $bq->where('uuid', $batchKey);
        } elseif (Schema::hasColumn('batches', 'slug')) {
            $bq->where('slug', $batchKey);
        } else {
            return response()->json(['error' => 'Batch not found'], 404);
        }
        $batch = $bq->first();
        if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

        if ($role === 'instructor') {
            $biUserCol = Schema::hasColumn('batch_instructors','user_id')
                ? 'user_id'
                : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

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

        $items = DB::table('notices')
            ->where('batch_id', $batch->id)
            ->whereNotNull('deleted_at')
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(function($row){
                if (is_string($row->attachments)) {
                    try { $row->attachments = json_decode($row->attachments, true) ?: []; }
                    catch (\Throwable $e) { $row->attachments = []; }
                }
                return $row;
            });

        return response()->json([
            'message' => 'Deleted items (bin) for batch',
            'batch_uuid' => $batch->uuid,
            'data' => $items,
        ], 200);
    }
}
