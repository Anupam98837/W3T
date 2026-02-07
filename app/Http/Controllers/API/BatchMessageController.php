<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BatchMessageController extends Controller
{
    /* =========================================================
     | Helpers
     * ========================================================= */

    /** Convenience helper to read actor data attached by CheckRole. */
    private function actor(Request $r): array
    {
        return [
            'role' => $r->attributes->get('auth_role'),
            'type' => $r->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    /** Log with actor context (role + id). */
    private function logWithActor(string $message, Request $r, array $extra = []): void
    {
        $a = $this->actor($r);
        Log::info($message, array_merge([
            'actor_role' => $a['role'],
            'actor_id'   => $a['id'],
        ], $extra));
    }

    /**
     * Resolve a batch by key (id | uuid | optional slug) and enforce
     * that the current actor is allowed to access it.
     */
    private function resolveBatchContext(Request $r, string $batchKey): array
    {
        $role = (string) $r->attributes->get('auth_role');
        $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);

        // Only these roles may access batch chat
        if (!$role || !in_array($role, ['superadmin', 'admin', 'instructor', 'student'], true)) {
            return ['error' => response()->json(['error' => 'Unauthorized Access'], 403)];
        }

        $isAdminLike  = in_array($role, ['superadmin', 'admin'], true);
        $isInstructor = $role === 'instructor';
        $isStudent    = $role === 'student';

        // Resolve batch
        $bq = DB::table('batches')->whereNull('deleted_at');
        if (ctype_digit($batchKey)) {
            $bq->where('id', (int) $batchKey);
        } elseif (Str::isUuid($batchKey)) {
            $bq->where('uuid', $batchKey);
        } elseif (Schema::hasColumn('batches', 'slug')) {
            $bq->where('slug', $batchKey);
        } else {
            return ['error' => response()->json(['error' => 'Batch not found'], 404)];
        }

        $batch = $bq->first();
        if (!$batch) {
            return ['error' => response()->json(['error' => 'Batch not found'], 404)];
        }

        // detect pivot FK columns safely
        $biUserCol = Schema::hasColumn('batch_instructors', 'user_id')
            ? 'user_id'
            : (Schema::hasColumn('batch_instructors', 'instructor_id') ? 'instructor_id' : null);

        $bsUserCol = Schema::hasColumn('batch_students', 'user_id')
            ? 'user_id'
            : (Schema::hasColumn('batch_students', 'student_id') ? 'student_id' : null);

        // Instructor must be assigned to batch
        if ($isInstructor) {
            if (!$biUserCol) {
                return ['error' => response()->json([
                    'error' => 'Schema issue: batch_instructors needs user_id OR instructor_id'
                ], 500)];
            }

            $assigned = DB::table('batch_instructors')
                ->where('batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->where($biUserCol, $uid)
                ->exists();

            if (!$assigned) {
                return ['error' => response()->json(['error' => 'Forbidden'], 403)];
            }
        }

        // Student must be enrolled in batch
        if ($isStudent) {
            if (!$bsUserCol) {
                return ['error' => response()->json([
                    'error' => 'Schema issue: batch_students needs user_id OR student_id'
                ], 500)];
            }

            $enrolled = DB::table('batch_students')
                ->where('batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->where($bsUserCol, $uid)
                ->exists();

            if (!$enrolled) {
                return ['error' => response()->json(['error' => 'Forbidden'], 403)];
            }
        }

        return [
            'batch'        => $batch,
            'role'         => $role,
            'uid'          => $uid,
            'isAdminLike'  => $isAdminLike,
            'isInstructor' => $isInstructor,
            'isStudent'    => $isStudent,
        ];
    }

    /** Decode json column safely into array|null. */
    private function decodeJson($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }
        try {
            $arr = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return is_array($arr) ? $arr : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Transform raw DB row into base message shape (without seen flags). */
    private function transformMessageRow($row): array
    {
        if (!$row) {
            return [];
        }

        $meta = $this->decodeJson($row->metadata ?? null);

        $attachments = [];
        if (is_array($meta) && isset($meta['attachments']) && is_array($meta['attachments'])) {
            $attachments = $meta['attachments'];
        }

        $createdAt = $row->created_at ?? null;
        $human     = null;
        $timeOnly  = null;

        try {
            if ($createdAt) {
                $dt       = Carbon::parse($createdAt);
                $human    = $dt->diffForHumans();
                $timeOnly = $dt->format('H:i');
            }
        } catch (\Throwable $e) {
            // ignore parse errors
        }

        return [
            'id'                => (int) $row->id,
            'uuid'              => $row->uuid,
            'batch_id'          => (int) $row->batch_id,
            'sender_id'         => $row->sender_id ? (int) $row->sender_id : null,
            'sender_role'       => $row->sender_role,
            'sender_type'       => $row->sender_type,
            'sender_name'       => $row->sender_name ?? null,
            'sender_uuid'       => $row->sender_uuid ?? null,
            'sender_user_role'  => $row->sender_user_role ?? null,
            'message_html'      => $row->message_html,
            'message_text'      => $row->message_text,
            'has_attachments'   => (bool) $row->has_attachments,
            'attachments'       => $attachments,
            'is_pinned'         => (bool) $row->is_pinned,
            'is_edited'         => (bool) $row->is_edited,
            'metadata'          => $meta,
            'created_at'        => $createdAt,
            'created_at_human'  => $human,
            'created_at_time'   => $timeOnly,
        ];
    }

    /* =========================================================
     | Activity Log (added)
     * ========================================================= */
    private function logActivity(
        Request $request,
        string $activity, // store
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
                'performed_by'       => $a['id'] ?: 0,
                'performed_by_role'  => $a['role'] ?: null,
                'ip'                 => $request->ip(),
                'user_agent'         => (string) $request->userAgent(),
                'activity'           => $activity,
                'module'             => 'BatchChat',
                'table_name'         => $tableName,
                'record_id'          => $recordId,
                'changed_fields'     => $changedFields ? json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE) : null,
                'old_values'         => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'         => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'           => $note,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[BatchChat] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /* =========================================================
     | GET /api/batches/{batchKey}/messages
     | List chat messages for a batch
     * ========================================================= */
    public function index(Request $r, string $batchKey)
    {
        $ctx = $this->resolveBatchContext($r, $batchKey);
        if (isset($ctx['error'])) {
            return $ctx['error'];
        }

        /** @var object $batch */
        $batch = $ctx['batch'];
        $role  = $ctx['role'];
        $uid   = $ctx['uid'];

        $limit = (int) $r->query('limit', 50);
        if ($limit < 1)   $limit = 50;
        if ($limit > 100) $limit = 100;

        $beforeId = $r->filled('before_id') ? (int) $r->query('before_id') : null;
        $afterId  = $r->filled('after_id')  ? (int) $r->query('after_id')  : null;

        $q = DB::table('batch_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.batch_id', $batch->id)
            ->whereNull('m.deleted_at');

        $isInitial = !$beforeId && !$afterId;

        // Initial load: only last 3 days of messages
        if ($isInitial) {
            $since = Carbon::now()->subDays(3);
            $q->where('m.created_at', '>=', $since);
        }

        if ($beforeId) {
            // Load older messages
            $q->where('m.id', '<', $beforeId);
        }

        if ($afterId) {
            // Poll newer messages
            $q->where('m.id', '>', $afterId);
        }

        $q->select(
            'm.*',
            'u.name as sender_name',
            'u.uuid as sender_uuid',
            'u.role as sender_user_role'
        );

        $hasMoreBefore = false;

        if ($afterId) {
            // Poll newer: just fetch ascending
            $rows = $q->orderBy('m.id', 'asc')
                      ->limit($limit)
                      ->get();
        } else {
            // Initial + load older: get newest first, then reverse
            $rowsDesc = $q->orderBy('m.id', 'desc')
                          ->limit($limit)
                          ->get();
            $rows = $rowsDesc->reverse()->values();

            // Should we show "Load more"?
            if ($rows->count() > 0) {
                $oldestId = $rows->first()->id;
                $hasMoreBefore = DB::table('batch_messages')
                    ->where('batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->where('id', '<', $oldestId)
                    ->exists();
            } elseif ($isInitial) {
                // No messages in last 3 days – but older ones may exist
                $hasMoreBefore = DB::table('batch_messages')
                    ->where('batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->exists();
            }
        }

        // ===== Seen / unseen info (batch_message_reads) ==================
        $messageIds = $rows->pluck('id')->map(fn($v) => (int)$v)->all();

        $seenByMeMap   = [];
        $seenCountMap  = [];

        if (!empty($messageIds)) {
            $seenRows = DB::table('batch_message_reads')
                ->whereIn('message_id', $messageIds)
                ->whereNotNull('read_at')
                ->get();

            foreach ($seenRows as $sr) {
                $mid = (int) $sr->message_id;

                // total seen count
                if (!isset($seenCountMap[$mid])) {
                    $seenCountMap[$mid] = 0;
                }
                $seenCountMap[$mid]++;

                // seen-by-me timestamp
                if ((int) $sr->user_id === $uid) {
                    $seenByMeMap[$mid] = $sr->read_at;
                }
            }
        }

        // ===== Build final message payloads ==============================
        $messages = $rows->map(function ($row) use ($uid, $seenByMeMap, $seenCountMap) {
            $msg = $this->transformMessageRow($row);
            $mid = $msg['id'];

            $msg['is_mine']       = ($msg['sender_id'] === $uid);
            $msg['is_seen_by_me'] = array_key_exists($mid, $seenByMeMap);
            $msg['seen_by_me_at'] = $seenByMeMap[$mid] ?? null;
            $msg['seen_by_total'] = (int) ($seenCountMap[$mid] ?? 0);

            return $msg;
        })->values();

        $first    = $messages->first();
        $last     = $messages->last();
        $oldestId = $first ? ($first['id'] ?? null) : null;
        $newestId = $last ? ($last['id'] ?? null) : null;

        $payload = [
            'batch' => [
                'id'    => (int) $batch->id,
                'uuid'  => $batch->uuid ?? null,
                'title' => $batch->badge_title ?? $batch->batch_code ?? $batch->title ?? null,
            ],
            'actor' => [
                'id'   => $uid,
                'role' => $role,
            ],
            'messages' => $messages,
            'meta' => [
                'limit'           => $limit,
                'is_initial'      => $isInitial,
                'before_id'       => $beforeId,
                'after_id'        => $afterId,
                'has_more_before' => $hasMoreBefore,
                'oldest_id'       => $oldestId,
                'newest_id'       => $newestId,
            ],
        ];

        $this->logWithActor('[BatchChat] messages index', $r, [
            'batch_id'        => (int) $batch->id,
            'limit'           => $limit,
            'is_initial'      => $isInitial,
            'before_id'       => $beforeId,
            'after_id'        => $afterId,
            'count'           => count($messages),
            'has_more_before' => $hasMoreBefore,
        ]);

        return response()->json(['data' => $payload]);
    }

    /* =========================================================
     | POST /api/batches/{batchKey}/messages
     | Create a new chat message
     * ========================================================= */
    public function store(Request $r, string $batchKey)
    {
        $ctx = $this->resolveBatchContext($r, $batchKey);
        if (isset($ctx['error'])) {
            return $ctx['error'];
        }

        /** @var object $batch */
        $batch = $ctx['batch'];
        $role  = $ctx['role'];
        $uid   = $ctx['uid'];

        $v = Validator::make($r->all(), [
            'message'        => 'nullable|string',
            'message_html'   => 'nullable|string',
            'message_text'   => 'nullable|string',
            'attachments.*'  => 'file|max:20480', // 20MB each – tune as needed
        ]);

        $v->after(function ($validator) use ($r) {
            $msg      = trim((string) $r->input('message', ''));
            $html     = trim((string) $r->input('message_html', ''));
            $plain    = trim((string) $r->input('message_text', ''));
            $hasFiles = $r->hasFile('attachments');

            if ($msg === '' && $html === '' && $plain === '' && !$hasFiles) {
                $validator->errors()
                    ->add('message', 'Message text or at least one attachment is required.');
            }
        });

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $message = trim((string) $r->input('message', ''));
        $msgHtml = $r->input('message_html');
        $msgText = $r->input('message_text');

        if ($msgHtml === null && $message !== '') {
            $msgHtml = nl2br(e($message));
        }
        if ($msgText === null) {
            $msgText = $message !== '' ? $message : strip_tags((string) $msgHtml);
        }

        // ===== Handle attachments (stored under public/chatFiles) ========
        $attachmentsMeta = [];
        if ($r->hasFile('attachments')) {
            $files = $r->file('attachments');
            if (!is_array($files)) {
                $files = [$files];
            }

            $uploadDir = public_path('chatFiles');
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            foreach ($files as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                $original = $file->getClientOriginalName();
                $ext      = strtolower((string) $file->getClientOriginalExtension());
                $basename = pathinfo($original, PATHINFO_FILENAME);
                $safeBase = Str::slug($basename, '_');
                if ($safeBase === '') {
                    $safeBase = 'file';
                }

                $filename = $safeBase . '-' . date('YmdHis') . '-' . Str::random(6);
                if ($ext !== '') {
                    $filename .= '.' . $ext;
                }

                $size = $file->getSize();
                $mime = $file->getClientMimeType();

                $file->move($uploadDir, $filename);

                $relative = 'chatFiles/' . $filename;
                $url      = url($relative);

                $attachmentsMeta[] = [
                    'name' => $original,
                    'url'  => $url,      // full URL with app url
                    'path' => $relative, // relative path under public/
                    'ext'  => $ext,
                    'size' => $size,
                    'mime' => $mime,
                ];
            }
        }

        $now = Carbon::now();

        $meta = [
            'source'      => 'batch_chat',
            'via'         => 'web',
            'attachments' => $attachmentsMeta,
        ];

        $uuid = (string) Str::uuid();

        $id = DB::table('batch_messages')->insertGetId([
            'uuid'            => $uuid,
            'batch_id'        => $batch->id,
            'sender_type'     => $r->attributes->get('auth_tokenable_type'),
            'sender_id'       => $uid,
            'sender_role'     => $role,
            'message_html'    => $msgHtml,
            'message_text'    => $msgText,
            'has_attachments' => !empty($attachmentsMeta),
            'is_pinned'       => false,
            'is_edited'       => false,
            'metadata'        => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        // ✅ ADDED ACTIVITY LOG (only change requested)
        $fresh = DB::table('batch_messages')->where('id', $id)->first();
        $this->logActivity(
            $r,
            'store',
            'Created batch chat message',
            'batch_messages',
            $id,
            ['batch_id','sender_id','sender_role','has_attachments'],
            null,
            $fresh ? (array)$fresh : null
        );

        // Sender has obviously "seen" their own message → upsert read row
        DB::table('batch_message_reads')->updateOrInsert(
            [
                'message_id' => $id,
                'user_id'    => $uid,
            ],
            [
                'user_role' => $role,
                'read_at'   => $now,
                'read_from' => 'web_send',
                'meta'      => null,
                'created_at'=> $now,
                'updated_at'=> $now,
            ]
        );

        $row = DB::table('batch_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.id', $id)
            ->select(
                'm.*',
                'u.name as sender_name',
                'u.uuid as sender_uuid',
                'u.role as sender_user_role'
            )
            ->first();

        $msg = $this->transformMessageRow($row);
        $msg['is_mine']       = true;
        $msg['is_seen_by_me'] = true;
        $msg['seen_by_me_at'] = $now->toDateTimeString();
        $msg['seen_by_total'] = 1; // at least the sender

        $this->logWithActor('[BatchChat] message created', $r, [
            'batch_id'   => (int) $batch->id,
            'message_id' => $id,
            'has_files'  => !empty($attachmentsMeta),
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'message' => $msg,
            ],
        ], 201);
    }

    /* =========================================================
     | POST /api/batches/{batchKey}/messages/read
     | Mark messages as "seen" by current user.
     * ========================================================= */
    public function markRead(Request $r, string $batchKey)
    {
        $ctx = $this->resolveBatchContext($r, $batchKey);
        if (isset($ctx['error'])) {
            return $ctx['error'];
        }

        /** @var object $batch */
        $batch = $ctx['batch'];
        $role  = $ctx['role'];
        $uid   = $ctx['uid'];

        $v = Validator::make($r->all(), [
            'up_to_id'      => 'nullable|integer|min:1',
            'message_ids'   => 'nullable|array',
            'message_ids.*' => 'integer|min:1',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $messageIds = [];

        if (is_array($r->input('message_ids'))) {
            $messageIds = array_map('intval', $r->input('message_ids', []));
        } elseif ($r->filled('up_to_id')) {
            $upTo = (int) $r->input('up_to_id');
            $messageIds = DB::table('batch_messages')
                ->where('batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->where('id', '<=', $upTo)
                ->pluck('id')
                ->map(fn($v) => (int)$v)
                ->all();
        }

        $messageIds = array_values(array_unique(array_filter($messageIds, fn($v) => $v > 0)));

        if (empty($messageIds)) {
            return response()->json([
                'success'       => true,
                'updated_count' => 0,
            ]);
        }

        $now = Carbon::now();
        $from = $r->input('read_from', 'web');

        foreach ($messageIds as $mid) {
            DB::table('batch_message_reads')->updateOrInsert(
                [
                    'message_id' => $mid,
                    'user_id'    => $uid,
                ],
                [
                    'user_role' => $role,
                    'read_at'   => $now,
                    'read_from' => $from,
                    'meta'      => null,
                    'created_at'=> $now,
                    'updated_at'=> $now,
                ]
            );
        }

        $this->logWithActor('[BatchChat] messages marked read', $r, [
            'batch_id' => (int) $batch->id,
            'count'    => count($messageIds),
        ]);

        return response()->json([
            'success'       => true,
            'updated_count' => count($messageIds),
            'last_read_id'  => max($messageIds),
        ]);
    }
}
