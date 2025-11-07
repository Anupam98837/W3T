<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    public function show(Request $r, string $assignment)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','instructor'])) return $resp;

        $row = $this->findAssignmentOr404($assignment);
        if (!$row) return response()->json(['error'=>'Assignment not found'], 404);

        $row->attachments = [];
        if (!empty($row->attachments_json)) {
            try { $row->attachments = is_string($row->attachments_json) ? json_decode($row->attachments_json, true) : $row->attachments_json; } catch (\Throwable $e) { $row->attachments = []; }
        }
        $row->allowed_submission_types = [];
        if (!empty($row->allowed_submission_types)) {
            try { $row->allowed_submission_types = is_string($row->allowed_submission_types) ? json_decode($row->allowed_submission_types, true) : $row->allowed_submission_types; } catch (\Throwable $e) { $row->allowed_submission_types = []; }
        }

        return response()->json(['data'=>$row]);
    }

    /* STORE */
    public function store(Request $request, $course = null)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin','instructor'])) return $resp;
        $this->logWithActor('[Assignment Store] begin', $request);

        $rules = [
            'course_id'         => ['sometimes','required','numeric'],
            'course_module_id'  => ['required','numeric'],
            'batch_id'          => ['required','numeric'],
            'title'             => ['required','string','max:255'],
            'slug'              => ['nullable','string','max:140','unique:assignments,slug'],
            'instruction'       => ['nullable','string'],
            'status'            => ['nullable', Rule::in(['draft','published','closed'])],
            'submission_type'   => ['nullable', Rule::in(['file','link','text','code','mixed'])],
            'allowed_submission_types' => ['nullable','array'],
            'allowed_submission_types.*' => ['string'],
            'attempts_allowed'  => ['nullable','integer','min:0'],
            'total_marks'       => ['nullable','numeric','min:0'],
            'pass_marks'        => ['nullable','numeric','min:0'],
            'release_at'        => ['nullable','date'],
            'due_at'            => ['nullable','date','after_or_equal:release_at'],
            'end_at'            => ['nullable','date','after_or_equal:due_at'],
            'allow_late'        => ['nullable','boolean'],
            'late_penalty_percent' => ['nullable','numeric','min:0','max:100'],
            'attachments_json'  => ['nullable','array'],
            'attachments_json.*'=> ['array'],
            'metadata'          => ['nullable','array'],
        ];

        if ($course !== null) {
            $request->merge(['course_id' => (int)$course]);
        }

        $data = $request->validate($rules);

        $submissionType = $data['submission_type'] ?? 'file';
        $allowedTypes   = $data['allowed_submission_types'] ?? null;

        $a = $this->actor($request);
        $now = now();
        $uuid = (string) Str::uuid();

        $insert = [
            'uuid'                  => $uuid,
            'course_id'             => (int)($data['course_id'] ?? 0),
            'course_module_id'      => (int)$data['course_module_id'],
            'batch_id'              => (int)$data['batch_id'],
            'title'                 => $data['title'],
            'slug'                  => $data['slug'] ?? Str::slug($data['title'] ?? 'assignment'),
            'instruction'           => $data['instruction'] ?? null,
            'status'                => $data['status'] ?? 'draft',
            'submission_type'       => $submissionType,
            'allowed_submission_types' => isset($data['allowed_submission_types'])
                                            ? json_encode(array_values($data['allowed_submission_types']), JSON_UNESCAPED_UNICODE)
                                            : null,
            'attempts_allowed'      => (int)($data['attempts_allowed'] ?? 1),
            'total_marks'           => isset($data['total_marks']) ? (float)$data['total_marks'] : 100.00,
            'pass_marks'            => isset($data['pass_marks']) ? (float)$data['pass_marks'] : null,
            'release_at'            => $data['release_at'] ?? null,
            'due_at'                => $data['due_at'] ?? null,
            'end_at'                => $data['end_at'] ?? null,
            'allow_late'            => !empty($data['allow_late']) ? 1 : 0,
            'late_penalty_percent'  => isset($data['late_penalty_percent']) ? (float)$data['late_penalty_percent'] : null,
            'attachments_json'      => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_by'            => $a['id'] ?: null,
            'created_at'            => $now,
            'created_at_ip'         => $request->ip(),
            'updated_at'            => $now,
            'deleted_at'            => null,
            'metadata'              => isset($data['metadata']) ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE) : json_encode(new \stdClass()),
        ];

        $insertedAttachments = [];
        DB::beginTransaction();
        try {
            $id = DB::table('assignments')->insertGetId($insert);

            // Normalize files: allow 'attachments' or 'attachments[]'
            $files = $request->file('attachments') ?: $request->file('attachments[]') ?: [];
            if (!is_array($files) && $files !== null) $files = [$files];
            if (!empty($files)) {
                $destBase = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . self::MEDIA_SUBDIR;
                File::ensureDirectoryExists($destBase, 0755, true);

                // start order from existing count + 1
                $orderNo = $this->nextAttachmentOrderNo($id);

                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) continue;

                    // capture metadata BEFORE moving file to avoid stat errors
                    $originalName = $file->getClientOriginalName();
                    $origSize = $file->getSize();
                    $mime = $file->getClientMimeType() ?: $file->getMimeType();
                    $ext  = strtolower($file->getClientOriginalExtension() ?: '');

                    $fname = 'assignment-' . $uuid . '-' . (string) Str::uuid() . ($ext ? ('.'.$ext) : '');
                    // move
                    $file->move($destBase, $fname);

                    $relative = self::MEDIA_SUBDIR . '/' . $fname;
                    $url = $this->toPublicUrl($relative);

                    $meta = [
                        'id'            => (string) Str::uuid(),
                        'original_name' => $originalName,
                        'filename'      => $fname,
                        'url'           => $url,
                        'size'          => $origSize,
                        'mime'          => $mime,
                        'order_no'      => $orderNo++,
                        'uploaded_by'   => $a['id'] ?: null,
                        'created_at'    => $now->toDateTimeString(),
                    ];

                    $insertedAttachments[] = $meta;
                }

                if (!empty($insertedAttachments)) {
                    $merged = array_merge([], $insertedAttachments);
                    DB::table('assignments')->where('id', $id)->update(['attachments_json' => json_encode($merged, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Assignments] create failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Creation failed'], 500);
        }

        $fresh = DB::table('assignments')->where('id', $id)->first();
        $fresh->attachments = [];
        if (!empty($fresh->attachments_json)) {
            try { $fresh->attachments = json_decode($fresh->attachments_json, true); } catch (\Throwable $e) { $fresh->attachments = []; }
        }

        $this->logActivity($request, 'store', 'Created assignment "'.$insert['title'].'"', 'assignments', $id, array_keys($insert), null, $fresh ? (array)$fresh : null);

        $link = rtrim((string)config('app.url'), '/').'/admin/assignments/'.$id;
        $this->persistNotification([
            'title'     => 'Assignment created',
            'message'   => '“'.$insert['title'].'” has been created.',
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action' => 'created',
                'assignment' => [
                    'id' => $id,
                    'uuid' => $uuid,
                    'title' => $insert['title'],
                ],
                'created_by' => $a,
            ],
            'type'     => 'assignment',
            'link_url' => $link,
            'priority' => 'normal',
            'status'   => 'active',
        ]);

        $this->logWithActor('[Assignment Store] success', $request, ['assignment_id' => $id, 'uuid' => $uuid]);

        return response()->json(['status'=>'success','message'=>'Assignment created','data'=>$fresh], 201);
    }

    /* UPDATE */
    public function update(Request $request, string $assignment)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin','instructor'])) return $resp;

        $row = $this->findAssignmentOr404($assignment);
        if (!$row) return response()->json(['error'=>'Assignment not found'], 404);

        $id = (int)$row->id;

        $rules = [
            'course_id'         => ['sometimes','numeric'],
            'course_module_id'  => ['sometimes','numeric'],
            'batch_id'          => ['sometimes','numeric'],
            'title'             => ['sometimes','string','max:255'],
            'slug'              => ['sometimes','nullable','string','max:140','unique:assignments,slug,'.$id],
            'instruction'       => ['sometimes','nullable','string'],
            'status'            => ['sometimes', Rule::in(['draft','published','closed'])],
            'submission_type'   => ['sometimes', Rule::in(['file','link','text','code','mixed'])],
            'allowed_submission_types' => ['sometimes','nullable','array'],
            'allowed_submission_types.*' => ['string'],
            'attempts_allowed'  => ['sometimes','integer','min:0'],
            'total_marks'       => ['sometimes','numeric','min:0'],
            'pass_marks'        => ['sometimes','numeric','min:0'],
            'release_at'        => ['sometimes','nullable','date'],
            'due_at'            => ['sometimes','nullable','date','after_or_equal:release_at'],
            'end_at'            => ['sometimes','nullable','date','after_or_equal:due_at'],
            'allow_late'        => ['sometimes','boolean'],
            'late_penalty_percent' => ['sometimes','nullable','numeric','min:0','max:100'],
            'attachments_json'  => ['sometimes','nullable','array'],
            'metadata'          => ['sometimes','nullable','array'],
        ];

        $data = $request->validate($rules);

        $upd = [];
        foreach ($data as $k => $v) {
            if ($k === 'allowed_submission_types') {
                $upd[$k] = $v !== null ? json_encode(array_values($v), JSON_UNESCAPED_UNICODE) : null;
            } elseif ($k === 'attachments_json') {
                $upd[$k] = $v !== null ? json_encode($v, JSON_UNESCAPED_UNICODE) : json_encode([], JSON_UNESCAPED_UNICODE);
            } elseif ($k === 'metadata') {
                $upd[$k] = $v !== null ? json_encode($v, JSON_UNESCAPED_UNICODE) : json_encode(new \stdClass());
            } elseif ($k === 'allow_late') {
                $upd[$k] = !empty($v) ? 1 : 0;
            } else {
                $upd[$k] = $v;
            }
        }
        $upd['updated_at'] = now();

        DB::beginTransaction();
        try {
            DB::table('assignments')->where('id', $id)->update($upd);

            $insertedAttachments = [];
            $files = $request->file('attachments') ?: $request->file('attachments[]') ?: [];
            if (!is_array($files) && $files !== null) $files = [$files];

            if (!empty($files)) {
                $destBase = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . self::MEDIA_SUBDIR;
                File::ensureDirectoryExists($destBase, 0755, true);

                $orderNo = $this->nextAttachmentOrderNo($id);

                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) continue;

                    $originalName = $file->getClientOriginalName();
                    $origSize = $file->getSize();
                    $mime = $file->getClientMimeType() ?: $file->getMimeType();
                    $ext  = strtolower($file->getClientOriginalExtension() ?: '');

                    $fname = 'assignment-' . ($row->uuid ?: (string) Str::uuid()) . '-' . (string) Str::uuid() . ($ext ? ('.'.$ext) : '');
                    $file->move($destBase, $fname);

                    $relative = self::MEDIA_SUBDIR . '/' . $fname;
                    $url = $this->toPublicUrl($relative);

                    $meta = [
                        'id' => (string) Str::uuid(),
                        'original_name' => $originalName,
                        'filename' => $fname,
                        'url' => $url,
                        'size' => $origSize,
                        'mime' => $mime,
                        'order_no' => $orderNo++,
                        'uploaded_by' => $this->actor($request)['id'] ?: null,
                        'created_at' => now()->toDateTimeString(),
                    ];
                    $insertedAttachments[] = $meta;
                }

                if (!empty($insertedAttachments)) {
                    $existing = [];
                    if (!empty($row->attachments_json)) {
                        try { $existing = json_decode($row->attachments_json, true) ?: []; } catch (\Throwable $e) { $existing = []; }
                    }
                    $merged = array_merge($existing, $insertedAttachments);
                    DB::table('assignments')->where('id', $id)->update(['attachments_json' => json_encode($merged, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Assignments] update failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error'=>'Update failed'], 500);
        }

        $fresh = DB::table('assignments')->where('id', $id)->first();
        if ($fresh) {
            $fresh->attachments = [];
            if (!empty($fresh->attachments_json)) {
                try { $fresh->attachments = json_decode($fresh->attachments_json, true); } catch (\Throwable $e) { $fresh->attachments = []; }
            }
            $fresh->allowed_submission_types = [];
            if (!empty($fresh->allowed_submission_types)) {
                try { $fresh->allowed_submission_types = json_decode($fresh->allowed_submission_types, true); } catch (\Throwable $e) { $fresh->allowed_submission_types = []; }
            }
        }

        $this->logActivity($request, 'update', 'Updated assignment "'.($fresh->title ?? $row->title).'"', 'assignments', $id, array_keys($upd), (array)$row, $fresh ? (array)$fresh : null);

        return response()->json(['status'=>'success','message'=>'Assignment updated','data'=>$fresh]);
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
}
