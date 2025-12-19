<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

class AssignmentController extends Controller
{
    // Media folder for uploaded attachments
    private const MEDIA_SUBDIR = 'assets/media/assignments';

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

    private function logActivity(
        Request $request,
        string $activity,
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
                'module'            => 'Assignments',
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
            Log::error('[Assignments] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

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

    /* Helpers */
    private function mediaBasePublicPath(): string
    {
        return public_path(); // /.../public
    }

    private function toPublicUrl(string $relativePath): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $rel  = ltrim(str_replace('\\','/',$relativePath), '/');
        return $base . '/' . $rel;
    }

    private function findAssignmentOr404(string $key)
    {
        $q = DB::table('assignments')->whereNull('deleted_at');
        if (ctype_digit($key)) $q->where('id', (int)$key);
        elseif (Str::isUuid($key)) $q->where('uuid', $key);
        else $q->where('slug', $key);
        return $q->first();
    }

    private function nextAttachmentOrderNo(int $assignmentId): int
    {
        $row = DB::table('assignments')->where('id', $assignmentId)->first();
        if (!$row || empty($row->attachments_json)) return 1;
        try {
            $arr = is_string($row->attachments_json) ? json_decode($row->attachments_json, true) : (array)$row->attachments_json;
            return count($arr) + 1;
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /* INDEX */
    public function index(Request $r, $course = null)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','instructor'])) return $resp;

        $page     = max(1, (int)$r->query('page', 1));
        $perPage  = max(1, min(100, (int)$r->query('per_page', 20)));
        $qText    = trim((string)$r->query('q', ''));
        $status   = $r->query('status');
        $courseId = $course ?? $r->query('course_id');
        $moduleId = $r->query('course_module_id');
        $batchId  = $r->query('batch_id');

        $q = DB::table('assignments')->whereNull('deleted_at');
        if ($courseId) $q->where('course_id', (int)$courseId);
        if ($moduleId) $q->where('course_module_id', (int)$moduleId);
        if ($batchId)  $q->where('batch_id', (int)$batchId);

        if ($qText !== '') {
            $q->where(function($w) use ($qText){
                $w->where('title','like',"%$qText%")->orWhere('slug','like',"%$qText%");
            });
        }
        if ($status) $q->where('status', $status);

        $total = (clone $q)->count();
        $rows  = $q->orderBy('created_at', 'desc')->offset(($page-1)*$perPage)->limit($perPage)->get();

        foreach ($rows as $row) {
            $row->attachments = [];
            if (!empty($row->attachments_json)) {
                try {
                    $row->attachments = is_string($row->attachments_json) ? json_decode($row->attachments_json, true) : $row->attachments_json;
                } catch (\Throwable $e) {
                    $row->attachments = [];
                }
            }
        }

        return response()->json(['data'=>$rows,'pagination'=>['page'=>$page,'per_page'=>$perPage,'total'=>$total]]);
    }

    /* SHOW */
    public function show(string $assignment)
{
    $row = $this->findAssignmentOr404($assignment);
    if (!$row) return response()->json(['error'=>'Assignment not found'], 404);

    // Apply the same decoding logic as in update
    $row->attachments = [];
    if (!empty($row->attachments_json)) {
        try { 
            $row->attachments = json_decode($row->attachments_json, true); 
        } catch (\Throwable $e) { 
            $row->attachments = []; 
        }
    }

    // DECODE allowed_submission_types for SHOW method too
    $rawAllowedTypes = $row->allowed_submission_types;
    if ($rawAllowedTypes === null || $rawAllowedTypes === '') {
        $row->allowed_submission_types = [];
    } elseif (is_string($rawAllowedTypes)) {
        try {
            $decoded = json_decode($rawAllowedTypes, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $row->allowed_submission_types = $decoded;
            } else {
                $row->allowed_submission_types = array_filter(array_map('trim', explode(',', $rawAllowedTypes)));
            }
        } catch (\Throwable $e) {
            $row->allowed_submission_types = [];
        }
    } elseif (is_array($rawAllowedTypes)) {
        $row->allowed_submission_types = $rawAllowedTypes;
    } else {
        $row->allowed_submission_types = [];
    }

    return response()->json(['data' => $row]);
}
   /* STORE */
public function store(Request $r, $course = null)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) {
        return $res;
    }

    $actor = $this->actor($r);

    // If route passes course in URL, force that
    if ($course !== null) {
        $r->merge(['course_id' => (int)$course]);
    }

    /* =========================
       Validation
       ========================= */
    $v = Validator::make($r->all(), [
        'course_id'         => 'required|integer|exists:courses,id',
        'course_module_id'  => 'required|integer|exists:course_modules,id',
        'batch_id'          => 'required|integer|exists:batches,id',
        'title'             => 'required|string|max:255',

        // description instead of instruction
        'description'       => 'nullable|string',
        'view_policy'       => 'nullable|in:inline_only,downloadable',

        // attachments
        'attachments.*'     => 'nullable|file|max:51200', // 50MB each

        // assignment options
        'status'            => 'nullable|in:draft,published,closed',
        'submission_type'   => 'nullable|in:file,link,text,code,mixed',

        // ✅ IMPORTANT
        'allowed_submission_types'   => 'nullable|array',
        'allowed_submission_types.*' => 'string',

        'attempts_allowed'  => 'nullable|integer|min:0',
        'total_marks'       => 'nullable|numeric|min:0',
        'pass_marks'        => 'nullable|numeric|min:0',
        'release_at'        => 'nullable|date',
        'due_at'            => 'nullable|date|after_or_equal:release_at',
        'end_at'            => 'nullable|date|after_or_equal:due_at',
        'allow_late'        => 'nullable|boolean',
        'late_penalty_percent' => 'nullable|numeric|min:0|max:100',
    ], [
        'attachments.*.max' => 'Each attachment must be <= 50 MB.',
    ]);

    if ($v->fails()) {
        return response()->json(['errors' => $v->errors()], 422);
    }

    $data   = $v->validated();
    $uuid   = (string) Str::uuid();
    $slug   = Str::slug($data['title']) ?: $uuid;
    $policy = $r->input('view_policy', 'inline_only');

    /* =========================
       ✅ Normalize allowed_submission_types
       ========================= */
    $allowedTypes = $r->input('allowed_submission_types');

    $allowedTypes = (is_array($allowedTypes) && count($allowedTypes))
        ? json_encode(array_values($allowedTypes), JSON_UNESCAPED_UNICODE)
        : null;

    /* =========================
       Attachments
       ========================= */
    $stored = $this->appendFilesAndLibraryUrls(
        $r,
        (int)$data['batch_id'],
        []
    );

    $now = now();

    /* =========================
       Insert payload
       ========================= */
    $insert = [
        'uuid'                  => $uuid,
        'course_id'             => (int)$data['course_id'],
        'course_module_id'      => (int)$data['course_module_id'],
        'batch_id'              => (int)$data['batch_id'],
        'title'                 => $data['title'],
        'slug'                  => $slug,
        'instruction'           => $data['description'] ?? null,
        'status'                => $data['status'] ?? 'draft',
        'submission_type'       => $data['submission_type'] ?? 'file',

        // ✅ FIXED (no more null override)
        'allowed_submission_types' => $allowedTypes,

        'attempts_allowed'      => (int)($data['attempts_allowed'] ?? 1),
        'total_marks'           => isset($data['total_marks']) ? (float)$data['total_marks'] : 100.00,
        'pass_marks'            => isset($data['pass_marks']) ? (float)$data['pass_marks'] : null,
        'release_at'            => $data['release_at'] ?? null,
        'due_at'                => $data['due_at'] ?? null,
        'end_at'                => $data['end_at'] ?? null,
        'allow_late'            => !empty($data['allow_late']) ? 1 : 0,
        'late_penalty_percent'  => isset($data['late_penalty_percent']) ? (float)$data['late_penalty_percent'] : null,
        'attachments_json'      => $stored
            ? json_encode($stored, JSON_UNESCAPED_UNICODE)
            : json_encode([], JSON_UNESCAPED_UNICODE),
        'created_by'            => $actor['id'] ?: 0,
        'created_at'            => $now,
        'created_at_ip'         => $r->ip(),
        'updated_at'            => $now,
        'deleted_at'            => null,
        'metadata'              => json_encode(
            ['view_policy' => $policy],
            JSON_UNESCAPED_UNICODE
        ),
    ];

    $id = DB::table('assignments')->insertGetId($insert);

    return response()->json([
        'status'  => 'success',
        'message' => 'Assignment created',
        'data'    => array_merge($insert, [
            'id'          => $id,
            'attachments' => $stored,
        ]),
    ], 201);
}

   /* UPDATE */
/**
 * Update Assignment basic fields and (optionally) add/remove attachments and library URLs.
 */
public function update(Request $r, $id)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) return $res;

    $row = DB::table('assignments')
        ->where('id', (int)$id)
        ->whereNull('deleted_at')
        ->first();

    if (!$row) return response()->json(['error' => 'Assignment not found'], 404);

    $v = Validator::make($r->all(), [
        'title'               => 'sometimes|required|string|max:255',
        'description'         => 'nullable|string',
        'view_policy'         => 'nullable|in:inline_only,downloadable',
        // assignment fields you might update
        'status'              => 'sometimes|in:draft,published,closed',
        'submission_type'     => 'sometimes|in:file,link,text,code,mixed',
        'attempts_allowed'    => 'sometimes|integer|min:0',
        'total_marks'         => 'sometimes|numeric|min:0',
        'pass_marks'          => 'sometimes|numeric|min:0',
        'release_at'          => 'sometimes|nullable|date',
        'due_at'              => 'sometimes|nullable|date|after_or_equal:release_at',
        'end_at'              => 'sometimes|nullable|date|after_or_equal:due_at',
        'allow_late'          => 'sometimes|boolean',
        'late_penalty_percent'=> 'sometimes|nullable|numeric|min:0|max:100',

        'attachments.*'       => 'nullable|file|max:51200',
        'library_urls.*'      => 'nullable|url',
        'remove_attachments'  => 'sometimes|array',
        'remove_attachments.*'=> 'string',
    ], [
        'attachments.*.max' => 'Each attachment must be <= 50 MB.',
    ]);

    if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

    $data   = $v->validated();
    $update = [];

    if (array_key_exists('title', $data)) {
        $update['title'] = $data['title'];
        if ($r->boolean('regenerate_slug', false)) {
            $update['slug'] = Str::slug($data['title']) ?: $row->uuid;
        }
    }

    if (array_key_exists('description', $data)) {
        $update['instruction'] = $data['description'];
    }

    if (array_key_exists('view_policy', $data)) {
        // merge/overwrite metadata->view_policy
        $meta = $this->jsonDecode($row->metadata);
        if (!is_array($meta)) $meta = [];
        $meta['view_policy'] = $data['view_policy'];
        $update['metadata'] = json_encode($meta, JSON_UNESCAPED_UNICODE);
    }

    // assignment fields
    foreach ([
        'status',
        'submission_type',
        'attempts_allowed',
        'total_marks',
        'pass_marks',
        'release_at',
        'due_at',
        'end_at',
        'late_penalty_percent',
    ] as $fld) {
        if (array_key_exists($fld, $data)) {
            $update[$fld] = $data[$fld];
        }
    }

    if (array_key_exists('allow_late', $data)) {
        $update['allow_late'] = !empty($data['allow_late']) ? 1 : 0;
    }

    // decode existing attachments_json
    $stored = $this->jsonDecode($row->attachments_json);
    if (!is_array($stored)) $stored = [];

    //
    // 1) Remove attachments (remove_attachments[])
    //
    $toRemove = $r->input('remove_attachments', []);
    if (!is_array($toRemove)) $toRemove = [$toRemove];

    if (!empty($toRemove)) {
        $remSet = array_flip(array_map('strval', $toRemove));
        $kept   = [];

        foreach ($stored as $att) {
            $attId = '';

            if (isset($att['id']))               $attId = (string)$att['id'];
            elseif (isset($att['attachment_id']))$attId = (string)$att['attachment_id'];
            elseif (isset($att['file_id']))      $attId = (string)$att['file_id'];
            elseif (isset($att['storage_key']))  $attId = (string)$att['storage_key'];
            elseif (isset($att['key']))          $attId = (string)$att['key'];
            else                                 $attId = (string)($att['path'] ?? ($att['url'] ?? ''));

            if ($attId !== '' && isset($remSet[$attId])) {
                // try to delete physical file (best-effort)
                try {
                    if (!empty($att['path'])) {
                        $p = $att['path'];

                        if (strpos($p, 'assets/media') === 0 || strpos($p, 'batchStudyMaterial') === 0) {
                            $candidate = $this->mediaBasePublicPath()
                                . DIRECTORY_SEPARATOR
                                . str_replace('/', DIRECTORY_SEPARATOR, ltrim($p, '/'));
                        } else {
                            $candidate = storage_path('app/' . ltrim($p, '/'));
                        }

                        if (File::exists($candidate) && is_file($candidate)) {
                            File::delete($candidate);
                        }
                    }
                } catch (\Throwable $ex) {
                    Log::warning('Failed to delete assignment attachment: ' . $ex->getMessage(), ['att' => $att]);
                }

                continue; // don't keep
            }

            $kept[] = $att;
        }

        $stored = $kept;
    }

    //
    // 2) Append new files + library_urls
    //
    $batchId = (int)$row->batch_id;
    $stored  = $this->appendFilesAndLibraryUrls($r, $batchId, $stored);

    $update['attachments_json'] = $stored ? json_encode($stored, JSON_UNESCAPED_UNICODE) : json_encode([], JSON_UNESCAPED_UNICODE);
    $update['updated_at']       = now();

    DB::table('assignments')->where('id', (int)$id)->update($update);

    return response()->json([
        'status'      => 'success',
        'message'     => 'Assignment updated',
        'id'          => (int)$id,
        'attachments' => $stored,
    ]);
}

    /* DESTROY */
    public function destroy(Request $request, string $assignment)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin','instructor'])) return $resp;

        $row = $this->findAssignmentOr404($assignment);
        if (!$row) return response()->json(['error'=>'Assignment not found'], 404);

        DB::table('assignments')->where('id', $row->id)->update([
            'status'     => 'closed',
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logActivity($request,'destroy','Archived/Deleted assignment "'.$row->title.'"','assignments',(int)$row->id,['status','deleted_at'],(array)$row,null);

        return response()->json(['status'=>'success','message'=>'Assignment deleted']);
    }
    /**
 * List all soft-deleted assignments (system-wide) — admin only
 */
public function indexDeleted(Request $r)
{
        Log::info('This is a test log message.');

    $page     = max(1, (int)$r->query('page', 1));
    $perPage  = max(1, min(100, (int)$r->query('per_page', 20)));
    $qText    = trim((string)$r->query('q', ''));
    $courseId = $r->query('course_id');
    $moduleId = $r->query('course_module_id');
    $batchId  = $r->query('batch_id');

    $q = DB::table('assignments')->whereNotNull('deleted_at');
    if ($courseId) $q->where('course_id', (int)$courseId);
    if ($moduleId) $q->where('course_module_id', (int)$moduleId);
    if ($batchId)  $q->where('batch_id', (int)$batchId);

    if ($qText !== '') {
        $q->where(function($w) use ($qText){
            $w->where('title','like',"%$qText%")->orWhere('slug','like',"%$qText%");
        });
    }

    $total = (clone $q)->count();
    $rows  = $q->orderBy('deleted_at', 'desc')->offset(($page-1)*$perPage)->limit($perPage)->get();

    // decode attachments for convenience
    foreach ($rows as $row) {
        $row->attachments = [];
        if (!empty($row->attachments_json)) {
            try { $row->attachments = is_string($row->attachments_json) ? json_decode($row->attachments_json, true) : $row->attachments_json; } catch (\Throwable $e) { $row->attachments = []; }
        }
    }

    return response()->json(['data'=>$rows,'pagination'=>['page'=>$page,'per_page'=>$perPage,'total'=>$total]]);
}
/**
 * Permanently delete an assignment and attempt to delete its files (admin only)
 */
public function forceDelete(Request $r, string $assignment)
{
    if ($res = $this->requireRole($r, ['admin','super_admin'])) return $res;

    // resolve assignment by id|uuid|slug (include deleted ones)
    $q = DB::table('assignments');
    if (ctype_digit($assignment)) $q->where('id', (int)$assignment);
    elseif (Str::isUuid($assignment)) $q->where('uuid', $assignment);
    else $q->where('slug', $assignment);

    $row = $q->first();
    if (!$row) return response()->json(['error'=>'Assignment not found'], 404);

    // Attempt to delete files referenced in attachments_json (best-effort)
    $attachments = [];
    if (!empty($row->attachments_json)) {
        try { $attachments = is_string($row->attachments_json) ? json_decode($row->attachments_json, true) : (array)$row->attachments_json; } catch (\Throwable $e) { $attachments = []; }
    }

    foreach ($attachments as $a) {
        // try common fields for filename/url
        $filePathCandidates = [];

        if (!empty($a['filename'])) {
            $filePathCandidates[] = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . self::MEDIA_SUBDIR . DIRECTORY_SEPARATOR . ltrim($a['filename'], '/\\');
        }
        if (!empty($a['url'])) {
            // try to derive relative path from url if it points to our domain
            try {
                $u = parse_url($a['url']);
                if (!empty($u['path'])) {
                    $maybe = public_path(ltrim($u['path'], '/'));
                    $filePathCandidates[] = $maybe;
                }
            } catch (\Throwable $_) {}
        }

        foreach ($filePathCandidates as $candidate) {
            if ($candidate && is_file($candidate)) {
                try { @unlink($candidate); } catch (\Throwable $_) {}
            }
        }
    }

    // Delete DB row
    DB::table('assignments')->where('id', (int)$row->id)->delete();

    $this->logActivity($r, 'force_delete', 'Permanently deleted assignment "'.($row->title ?? $row->id).'"', 'assignments', (int)$row->id, null, null, null);
    $this->logWithActor('[Assignment ForceDelete] removed', $r, ['assignment_id' => (int)$row->id]);

    return response()->json(['status'=>'success','message'=>'Assignment permanently deleted']);
}
/**
 * Restore soft-deleted assignment (admin only)
 */
public function restore(Request $r, string $assignment)
{
    if ($res = $this->requireRole($r, ['admin','super_admin'])) return $res;

    $q = DB::table('assignments')->whereNotNull('deleted_at');
    if (ctype_digit($assignment)) $q->where('id', (int)$assignment);
    elseif (Str::isUuid($assignment)) $q->where('uuid', $assignment);
    else $q->where('slug', $assignment);

    $row = $q->first();
    if (!$row) return response()->json(['error'=>'Assignment not found or not deleted'], 404);

    DB::table('assignments')->where('id', (int)$row->id)->update(['deleted_at' => null, 'updated_at' => now()]);

    $this->logActivity($r, 'restore', 'Restored assignment "'.($row->title ?? $row->id).'"', 'assignments', (int)$row->id, ['deleted_at'], (array)$row, null);

    return response()->json(['status'=>'success','message'=>'Assignment restored']);
}
/**
 * View assignments for a batch (RBAC aware)
 * Route example: GET /api/batches/{batchKey}/assignments
 */
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
 * Create assignment for a batch (resolve batch by id|uuid|slug).
 * Delegates to store() after injecting course_id, batch_id, course_module_id.
 */
public function storeByBatch(Request $r, string $batchKey)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) return $res;

    $actor = $this->actor($r);
    $role  = $actor['role'];
    $uid   = $actor['id'];

    /* =========================
       Resolve batch
       ========================= */
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
    if (!$batch) {
        return response()->json(['error' => 'Batch not found'], 404);
    }

    /* =========================
       Instructor access check
       ========================= */
    if ($role === 'instructor') {
        $biUserCol = \Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','user_id')
            ? 'user_id'
            : (\Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','instructor_id')
                ? 'instructor_id'
                : null);

        if (!$biUserCol) {
            return response()->json([
                'error' => 'Schema issue: batch_instructors needs user_id OR instructor_id'
            ], 500);
        }

        $assigned = DB::table('batch_instructors')
            ->where('batch_id', $batch->id)
            ->whereNull('deleted_at')
            ->where($biUserCol, $uid)
            ->exists();

        if (!$assigned) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
    }

    /* =========================
       Infer course_id
       ========================= */
    $courseId = (int) ($batch->course_id ?? 0);

    /* =========================
       Resolve course_module_id
       ========================= */
    $moduleId = null;

    if ($r->filled('course_module_id')) {
        $module = DB::table('course_modules')
            ->where('id', (int)$r->input('course_module_id'))
            ->whereNull('deleted_at')
            ->first();

        if (!$module || (int)$module->course_id !== $courseId) {
            return response()->json([
                'errors' => [
                    'course_module_id' => [
                        'Course module not found or does not belong to this batch\'s course'
                    ]
                ]
            ], 422);
        }

        $moduleId = (int)$module->id;
    } else {
        $modules = DB::table('course_modules')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->orderBy('order_no')
            ->orderBy('id')
            ->get();

        if ($modules->count() === 1) {
            $moduleId = (int)$modules->first()->id;
        } else {
            $published = $modules->firstWhere('status', 'published');
            if ($published) {
                $moduleId = (int)$published->id;
            } elseif ($modules->count() > 0) {
                $moduleId = (int)$modules->first()->id;
            } else {
                return response()->json([
                    'errors' => [
                        'course_module_id' => [
                            'Unable to infer course_module_id from batch — please provide course_module_id'
                        ]
                    ]
                ], 422);
            }
        }
    }

    /* =========================
       🔴 PRESERVE allowed_submission_types
       ========================= */
    $allowedTypes = $r->input('allowed_submission_types');

    if (is_array($allowedTypes)) {
        $allowedTypes = array_values(
            array_filter($allowedTypes, fn ($v) => is_string($v) && $v !== '')
        );
    } else {
        $allowedTypes = null;
    }

    /* =========================
       Merge everything for store()
       ========================= */
    $r->merge([
        'batch_id'                  => (int)$batch->id,
        'course_id'                 => $courseId,
        'course_module_id'          => $moduleId,
        'allowed_submission_types'  => $allowedTypes,
    ]);

    return $this->store($r, $courseId);
}


/**
 * Bin (deleted items) by batch — admin/instructor (instructor must be assigned)
 */
public function binByBatch(Request $r, string $batchKey)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) return $res;
    $actor = $this->actor($r);
    $uid   = $actor['id'];
    $role  = $actor['role'];

    // resolve batch
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

    // Instructor permission: must be assigned
    if ($role === 'instructor') {
        $biUserCol = \Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','user_id')
            ? 'user_id'
            : (\Illuminate\Support\Facades\Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

        if (!$biUserCol) {
            return response()->json(['error' => 'Schema issue: batch_instructors needs user_id or instructor_id'], 500);
        }

        $assigned = DB::table('batch_instructors')->where('batch_id', $batch->id)->whereNull('deleted_at')->where($biUserCol, $uid)->exists();
        if (!$assigned) return response()->json(['error' => 'Forbidden'], 403);
    }

    // Fetch ONLY soft-deleted assignments for this batch
    $items = DB::table('assignments')
        ->where('batch_id', $batch->id)
        ->whereNotNull('deleted_at')
        ->orderBy('deleted_at', 'desc')
        ->get()
        ->map(function($row){
            if (is_string($row->attachments_json)) {
                try { $row->attachments_json = json_decode($row->attachments_json, true) ?: []; } catch (\Throwable $e) { $row->attachments_json = []; }
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
 * Append uploaded files AND library_urls[] (remote URL attachments) to existing stored attachments
 * for ASSIGNMENTS.
 *
 * @param \Illuminate\Http\Request $r
 * @param int $batchId
 * @param array $existing
 * @return array
 */
protected function appendFilesAndLibraryUrls(Request $r, int $batchId, array $existing = []): array
{
    $stored = $existing ?: [];
    if (!is_array($stored)) $stored = [];

    // dedupe sets
    $seenUrl = [];
    $seenSha = [];

    foreach ($stored as $att) {
        if (!empty($att['url']))    $seenUrl[(string)$att['url']]   = true;
        if (!empty($att['sha256'])) $seenSha[(string)$att['sha256']] = true;
    }

    // 1) Uploaded files: attachments[] / attachments
    $files = [];
    if ($r->hasFile('attachments')) {
        $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
    }

    if (!empty($files)) {
        // store under: public/assets/media/assignments/batchStudyMaterial/{batchId}/...
        $folder   = "batchStudyMaterial/" . (int)$batchId;
        $destBase = $this->mediaBasePublicPath()
            . DIRECTORY_SEPARATOR
            . self::MEDIA_SUBDIR
            . DIRECTORY_SEPARATOR
            . $folder;

        if (!File::exists($destBase)) {
            File::makeDirectory($destBase, 0755, true, true);
        }

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $fid  = Str::lower(Str::random(10));
            $name = $fid . '.' . $ext;

            try {
                $file->move($destBase, $name);
            } catch (\Throwable $e) {
                Log::error(
                    'Failed to move uploaded assignment file (append helper): ' . $e->getMessage(),
                    ['dest' => $destBase, 'file' => $name]
                );
                continue;
            }

            $absPath = $destBase . DIRECTORY_SEPARATOR . $name;
            $relPath = self::MEDIA_SUBDIR . '/' . $folder . '/' . $name;

            $mime = $file->getClientMimeType()
                ?: (function_exists('mime_content_type') ? mime_content_type($absPath) : 'application/octet-stream');

            $size = @filesize($absPath) ?: 0;
            $sha  = @hash_file('sha256', $absPath) ?: null;
            $url  = $this->toPublicUrl($relPath);

            if ($sha && isset($seenSha[$sha])) continue;
            if ($url && isset($seenUrl[$url])) continue;

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

    // 2) library_urls[]
    $libUrls = $r->input('library_urls', []);
    if ($libUrls && !is_array($libUrls)) $libUrls = [$libUrls];

    foreach ($libUrls as $u) {
        if (!$u || !is_string($u)) continue;
        $u = trim($u);
        if ($u === '') continue;

        if (isset($seenUrl[$u])) continue;

        $id  = 'lib-' . substr(hash('sha256', $u), 0, 12);
        $ext = pathinfo(parse_url($u, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION);
        $ext = strtolower((string)$ext);
        $mime = '';

        if ($ext) {
            $map = [
                'pdf'  => 'application/pdf',
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'mp4'  => 'video/mp4',
                'webm' => 'video/webm',
                'gif'  => 'image/gif',
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

        $seenUrl[$u] = true;
    }

    // Final dedupe by url/sha/path
    $uniq = [];
    $out  = [];

    foreach ($stored as $att) {
        if (!empty($att['url'])) {
            $key = 'u:' . (string)$att['url'];
        } elseif (!empty($att['sha256'])) {
            $key = 's:' . (string)$att['sha256'];
        } elseif (!empty($att['path'])) {
            $key = 'p:' . (string)$att['path'];
        } else {
            $key = 'i:' . (string)($att['id'] ?? Str::random(6));
        }

        if (isset($uniq[$key])) continue;
        $uniq[$key] = true;
        $out[]      = $att;
    }

    return $out;
}

}
