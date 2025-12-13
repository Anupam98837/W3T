<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BatchCodingQuestionController extends Controller
{
    /* ============================================
     | Helpers
     |============================================ */

    private function actor(Request $r): array
    {
        return [
            'role' => (string)($r->attributes->get('auth_role') ?? ''),
            'id'   => (int)($r->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) return $cache[$key];

        try {
            return $cache[$key] = Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return $cache[$key] = false;
        }
    }

    private function resolveBatchId($idOrUuid): ?int
    {
        if (is_numeric($idOrUuid)) return (int)$idOrUuid;

        $idOrUuid = (string)$idOrUuid;
        if (Str::isUuid($idOrUuid)) {
            $b = DB::table('batches')->where('uuid', $idOrUuid)->first();
            return $b ? (int)$b->id : null;
        }
        return null;
    }

    private function isStaffRole(string $role): bool
    {
        return in_array($role, ['superadmin', 'admin', 'instructor'], true);
    }

    /* ============================================
     | GET: List coding questions for a batch
     | - Staff: ALL questions with assigned flag
     | - Student: ONLY assigned + published + active + available
     |============================================ */
    public function index(Request $r, $batch)
    {
        $batchId = $this->resolveBatchId($batch);
        if (!$batchId) {
            return response()->json(['status' => 'error', 'message' => 'Batch not found'], 404);
        }

        $actor = $this->actor($r);
        $role  = $actor['role'];
        $now   = now();

        $perPage = (int)$r->input('per_page', 25);
        if ($perPage < 1) $perPage = 25;
        if ($perPage > 100) $perPage = 100;

        $page = (int)$r->input('page', 1);
        if ($page < 1) $page = 1;

        // Filters
        $search     = trim((string)$r->input('q', ''));
        $difficulty = trim((string)$r->input('difficulty', '')); // easy/medium/hard (as per your DB)
        $status     = trim((string)$r->input('status', ''));     // active/inactive etc (cq.status)
        $assigned   = $r->has('assigned') ? (int)$r->input('assigned') : null; // 1 or 0 (staff only)

        // Staff: show all questions (even unassigned) using LEFT JOIN
        // Student: show only assigned (turn LEFT JOIN into INNER via whereNotNull)
        $q = DB::table('coding_questions as cq')
            ->leftJoin('batch_coding_questions as bcq', function ($join) use ($batchId) {
                $join->on('bcq.question_id', '=', 'cq.id')
                    ->where('bcq.batch_id', '=', $batchId);

                // soft-delete on pivot if exists
                // (if deleted_at doesn’t exist, the condition is ignored by not adding it)
            })
            ->select([
                'cq.id as question_id',
                'cq.uuid as question_uuid',
                'cq.title',
                'cq.difficulty',
                'cq.status as question_status',
                'cq.total_attempts',

                DB::raw('CASE WHEN bcq.id IS NULL THEN 0 ELSE 1 END as assigned'),

                // assignment fields (nullable when not assigned)
                'bcq.uuid as assignment_uuid',
                'bcq.status as assignment_status',
                'bcq.display_order',
                'bcq.available_from',
                'bcq.available_until',
                'bcq.publish_to_students',
                'bcq.assign_status',
                'bcq.attempt_allowed',
            ]);

        // Add pivot deleted_at filter safely
        if ($this->tableHasColumn('batch_coding_questions', 'deleted_at')) {
            $q->where(function ($w) {
                // either not assigned OR assigned and not soft-deleted
                $w->whereNull('bcq.id')
                  ->orWhereNull('bcq.deleted_at');
            });
        }

        // coding_questions soft delete (safe)
        if ($this->tableHasColumn('coding_questions', 'deleted_at')) {
            $q->whereNull('cq.deleted_at');
        }

        // Apply filters
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('cq.title', 'like', '%' . $search . '%')
                  ->orWhere('cq.uuid', 'like', '%' . $search . '%');
            });
        }
        if ($difficulty !== '') {
            $q->where('cq.difficulty', $difficulty);
        }
        if ($status !== '') {
            $q->where('cq.status', $status);
        }

        // STUDENT RULES: only assigned, active, published, available window
        if ($role === 'student') {
            $q->whereNotNull('bcq.id')
              ->whereNull('bcq.deleted_at')
              ->where('bcq.status', 'active')
              ->where('bcq.publish_to_students', 1);

            // if you want assign_status to gate student visibility, keep this:
            $q->where('bcq.assign_status', 1);

            $q->where(function ($w) use ($now) {
                $w->whereNull('bcq.available_from')
                  ->orWhere('bcq.available_from', '<=', $now);
            });
            $q->where(function ($w) use ($now) {
                $w->whereNull('bcq.available_until')
                  ->orWhere('bcq.available_until', '>=', $now);
            });
        } else {
            // STAFF OPTIONAL FILTER: assigned=1 or assigned=0
            if ($assigned === 1) $q->whereNotNull('bcq.id')->whereNull('bcq.deleted_at');
            if ($assigned === 0) $q->whereNull('bcq.id');
        }

        // Ordering:
        // - Staff: assigned first, then display_order, then title
        // - Student: display_order, then title
        if ($role === 'student') {
            $q->orderByRaw('COALESCE(bcq.display_order, 999999) asc')
              ->orderBy('cq.title', 'asc');
        } else {
            $q->orderByRaw('CASE WHEN bcq.id IS NULL THEN 1 ELSE 0 END asc')
              ->orderByRaw('COALESCE(bcq.display_order, 999999) asc')
              ->orderBy('cq.title', 'asc');
        }

        // Paginate (manual page set to support query param consistently)
        $paginator = $q->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data'   => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ], 200);
    }

    /* ============================================
     | POST: Assign questions to batch (bulk/single)
     | Body:
     | - question_uuids: array (bulk) OR question_uuid: string (single)
     | - attempt_allowed (optional)
     | - publish_to_students (optional)
     | - assign_status (optional)
     | - available_from/until (optional)
     |============================================ */
    public function assign(Request $r, $batch)
    {
        $batchId = $this->resolveBatchId($batch);
        if (!$batchId) {
            return response()->json(['status' => 'error', 'message' => 'Batch not found'], 404);
        }

        $actor = $this->actor($r);
        if (!$this->isStaffRole($actor['role'])) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        // Support both: question_uuid or question_uuids[]
        $payload = $r->all();
        if (!empty($payload['question_uuid']) && empty($payload['question_uuids'])) {
            $payload['question_uuids'] = [$payload['question_uuid']];
        }

        $v = Validator::make($payload, [
            'question_uuids'        => 'required|array|min:1',
            'question_uuids.*'      => 'required|string',
            'attempt_allowed'       => 'nullable|integer|min:1|max:50',
            'publish_to_students'   => 'nullable|boolean',
            'assign_status'         => 'nullable|boolean',
            'available_from'        => 'nullable|date',
            'available_until'       => 'nullable|date|after_or_equal:available_from',
        ]);

        if ($v->fails()) {
            return response()->json(['status' => 'error', 'message' => $v->errors()->first()], 422);
        }

        $p   = $v->validated();
        $now = now();

        // Fetch questions by uuid => id + total_attempts
        $questions = DB::table('coding_questions')
            ->whereIn('uuid', $p['question_uuids'])
            ->select('id', 'uuid', 'total_attempts')
            ->get();

        if ($questions->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No valid questions found'], 404);
        }

        $qids = $questions->pluck('id')->map(fn($x) => (int)$x)->all();

        // Existing assignments for these questions (including soft-deleted)
        $existingRows = DB::table('batch_coding_questions')
            ->where('batch_id', $batchId)
            ->whereIn('question_id', $qids)
            ->select('id', 'question_id', 'display_order', 'deleted_at')
            ->get()
            ->keyBy('question_id');

        // Next display order for new inserts
        $maxOrder = (int) DB::table('batch_coding_questions')
            ->where('batch_id', $batchId)
            ->when($this->tableHasColumn('batch_coding_questions', 'deleted_at'), fn($qq) => $qq->whereNull('deleted_at'))
            ->max('display_order');

        $nextOrder = $maxOrder > 0 ? ($maxOrder + 1) : 1;

        $inserted = 0;
        $updated  = 0;

        DB::beginTransaction();
        try {
            foreach ($questions as $q) {
                $qid = (int)$q->id;

                // attempt_allowed default 1, and clamp to question total_attempts if present
                $attemptAllowed = isset($p['attempt_allowed']) ? (int)$p['attempt_allowed'] : 1;
                if (!is_null($q->total_attempts)) {
                    $maxAttempts = (int)$q->total_attempts;
                    if ($maxAttempts > 0 && $attemptAllowed > $maxAttempts) {
                        $attemptAllowed = $maxAttempts;
                    }
                }

                $data = [
                    'status'              => 'active',
                    'attempt_allowed'     => $attemptAllowed,
                    'publish_to_students' => isset($p['publish_to_students']) ? (int)((bool)$p['publish_to_students']) : 0,
                    'assign_status'       => isset($p['assign_status']) ? (int)((bool)$p['assign_status']) : 0,
                    'available_from'      => $p['available_from'] ?? null,
                    'available_until'     => $p['available_until'] ?? null,
                    'updated_at'          => $now,
                    'updated_at_ip'       => $r->ip(),
                ];

                $existing = $existingRows->get($qid);

                if ($existing) {
                    // If restoring a soft-deleted assignment, ensure deleted_at cleared
                    if ($this->tableHasColumn('batch_coding_questions', 'deleted_at')) {
                        $data['deleted_at'] = null;
                    }

                    // Keep existing display_order unless it’s missing
                    if (empty($existing->display_order)) {
                        $data['display_order'] = $nextOrder++;
                    }

                    DB::table('batch_coding_questions')
                        ->where('id', (int)$existing->id)
                        ->update($data);

                    $updated++;
                } else {
                    $insert = array_merge($data, [
                        'uuid'          => (string) Str::uuid(),
                        'batch_id'      => $batchId,
                        'question_id'   => $qid,
                        'display_order' => $nextOrder++,
                        'created_by'    => $actor['id'],
                        'created_at'    => $now,
                        'created_at_ip' => $r->ip(),
                    ]);

                    DB::table('batch_coding_questions')->insert($insert);
                    $inserted++;
                }
            }

            DB::commit();

            return response()->json([
                'status'   => 'success',
                'message'  => 'Assigned successfully.',
                'inserted' => $inserted,
                'updated'  => $updated,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BatchCodingQuestionController@assign failed', [
                'err' => $e->getMessage(),
                'batch_id' => $batchId,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Assignment failed'], 500);
        }
    }

    /* ============================================
     | DELETE: Unassign (soft delete)
     | URL: /batches/{batch}/coding-questions/{questionUuid}
     |============================================ */
    public function unassign(Request $r, $batch, $questionUuid)
    {
        $batchId = $this->resolveBatchId($batch);
        if (!$batchId) {
            return response()->json(['status' => 'error', 'message' => 'Batch not found'], 404);
        }

        $actor = $this->actor($r);
        if (!$this->isStaffRole($actor['role'])) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        if (!Str::isUuid((string)$questionUuid)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid question uuid'], 422);
        }

        $q = DB::table('coding_questions')->where('uuid', (string)$questionUuid)->select('id')->first();
        if (!$q) {
            return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
        }

        $row = DB::table('batch_coding_questions')
            ->where('batch_id', $batchId)
            ->where('question_id', (int)$q->id)
            ->first();

        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Assignment not found'], 404);
        }

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $r->ip(),
        ];

        if ($this->tableHasColumn('batch_coding_questions', 'deleted_at')) {
            $update['deleted_at'] = now();
        } else {
            // fallback if no deleted_at exists (hard delete)
            DB::table('batch_coding_questions')
                ->where('id', (int)$row->id)
                ->delete();

            return response()->json(['status' => 'success', 'message' => 'Unassigned.'], 200);
        }

        DB::table('batch_coding_questions')
            ->where('id', (int)$row->id)
            ->update($update);

        return response()->json(['status' => 'success', 'message' => 'Unassigned.'], 200);
    }
}
