<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BatchMessageController extends Controller
{
    /* =========================================================
     | Helpers
     * ========================================================= */

    /**
     * Convenience helper to read actor data attached by CheckRole.
     */
    private function actor(Request $r): array
    {
        return [
            'role' => $r->attributes->get('auth_role'),
            'type' => $r->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    /**
     * Log with actor context (role + id).
     */
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
     *
     * Returns array with keys:
     *   - batch        (object)
     *   - role         (string)
     *   - uid          (int)
     *   - isAdminLike  (bool)
     *   - isInstructor (bool)
     *   - isStudent    (bool)
     *
     * Or ['error' => JsonResponse] on failure.
     */
    private function resolveBatchContext(Request $r, string $batchKey): array
    {
        $role = (string) $r->attributes->get('auth_role');
        $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);

        // Only these roles may access batch chat
        if (!$role || !in_array($role, ['superadmin','admin','instructor','student'], true)) {
            return ['error' => response()->json(['error' => 'Unauthorized Access'], 403)];
        }

        $isAdminLike  = in_array($role, ['superadmin','admin'], true);
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
        $biUserCol = Schema::hasColumn('batch_instructors','user_id')
            ? 'user_id'
            : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

        $bsUserCol = Schema::hasColumn('batch_students','user_id')
            ? 'user_id'
            : (Schema::hasColumn('batch_students','student_id') ? 'student_id' : null);

        // Instructor must be assigned to batch
        if ($isInstructor) {
            if (!$biUserCol) {
                return ['error' => response()->json(['error' => 'Schema issue: batch_instructors needs user_id OR instructor_id'], 500)];
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
                return ['error' => response()->json(['error' => 'Schema issue: batch_students needs user_id OR student_id'], 500)];
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

    /**
     * Decode json column safely into array|null.
     */
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

    /**
     * Transform raw DB row into API-friendly message shape.
     */
    private function transformMessageRow($row): array
    {
        if (!$row) {
            return [];
        }

        $meta = $this->decodeJson($row->metadata ?? null);
        $createdAt = $row->created_at ?? null;

        try {
            $human = $createdAt ? Carbon::parse($createdAt)->diffForHumans() : null;
        } catch (\Throwable $e) {
            $human = null;
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
            'is_pinned'         => (bool) $row->is_pinned,
            'is_edited'         => (bool) $row->is_edited,
            'metadata'          => $meta,
            'created_at'        => $createdAt,
            'created_at_human'  => $human,
        ];
    }

    /* =========================================================
     | GET /api/batches/{batchKey}/messages
     | List chat messages for a batch (paginated)
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

        $page    = max(1, (int) $r->query('page', 1));
        $perPage = (int) $r->query('per_page', 30);
        if ($perPage < 1)   $perPage = 30;
        if ($perPage > 100) $perPage = 100;

        $q = DB::table('batch_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.batch_id', $batch->id)
            ->whereNull('m.deleted_at');

        // Optional: load only messages after a given ID (for polling)
        if ($r->filled('after_id')) {
            $afterId = (int) $r->query('after_id');
            $q->where('m.id', '>', $afterId);
        }

        $total = (clone $q)->count();

        $rows = $q->select(
                'm.*',
                'u.name as sender_name',
                'u.uuid as sender_uuid',
                'u.role as sender_user_role'
            )
            ->orderBy('m.created_at', 'asc')
            ->orderBy('m.id', 'asc')
            ->forPage($page, $perPage)
            ->get();

        $messages = $rows->map(function ($row) {
            return $this->transformMessageRow($row);
        })->values();

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
            'messages'   => $messages,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 1,
            ],
        ];

        $this->logWithActor('[BatchChat] messages index', $r, [
            'batch_id' => (int) $batch->id,
            'page'     => $page,
            'per_page' => $perPage,
            'count'    => count($messages),
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
            'message'      => 'nullable|string',
            'message_html' => 'nullable|string',
            'message_text' => 'nullable|string',
        ]);

        $v->after(function ($validator) use ($r) {
            $msg   = trim((string) $r->input('message', ''));
            $html  = trim((string) $r->input('message_html', ''));
            $plain = trim((string) $r->input('message_text', ''));

            if ($msg === '' && $html === '' && $plain === '') {
                $validator->errors()->add('message', 'Message content is required.');
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
            // Escape + simple nl2br for now; front-end can send richer HTML later
            $msgHtml = nl2br(e($message));
        }
        if ($msgText === null) {
            $msgText = $message !== '' ? $message : strip_tags((string) $msgHtml);
        }

        $now = Carbon::now();

        $id = DB::table('batch_messages')->insertGetId([
            'uuid'            => (string) Str::uuid(),
            'batch_id'        => $batch->id,
            'sender_type'     => $r->attributes->get('auth_tokenable_type'),
            'sender_id'       => $uid,
            'sender_role'     => $role,
            'message_html'    => $msgHtml,
            'message_text'    => $msgText,
            'has_attachments' => false,
            'is_pinned'       => false,
            'is_edited'       => false,
            'metadata'        => json_encode(['source' => 'batch_chat', 'via' => 'web'], JSON_UNESCAPED_UNICODE),
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $row = DB::table('batch_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.id', $id)
            ->first();

        $messagePayload = $this->transformMessageRow($row);

        $this->logWithActor('[BatchChat] message created', $r, [
            'batch_id'   => (int) $batch->id,
            'message_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'message' => $messagePayload,
            ],
        ], 201);
    }
}
