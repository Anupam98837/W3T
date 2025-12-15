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

    private function tableExists(string $table): bool
    {
        try { return Schema::hasTable($table); } catch (\Throwable) { return false; }
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) return (bool)$cache[$key];

        try { return $cache[$key] = Schema::hasColumn($table, $column); }
        catch (\Throwable) { return $cache[$key] = false; }
    }

    private function resolveFirstExistingTable(array $candidates): ?string
    {
        foreach ($candidates as $t) {
            if ($this->tableExists($t)) return $t;
        }
        return null;
    }

    /**
     * Resolve batch id from numeric id OR uuid-ish string.
     * If batches table is missing, falls back to numeric casting.
     */
    private function resolveBatchId($batch): int
    {
        if (is_numeric($batch)) return (int)$batch;

        $batchesTable = $this->resolveFirstExistingTable(['batches', 'course_batches', 'batch']);
        if (!$batchesTable) {
            // No batches table detected; cannot resolve uuid safely.
            return 0;
        }

        $uuidCol = $this->tableHasColumn($batchesTable, 'uuid') ? 'uuid' :
                  ($this->tableHasColumn($batchesTable, 'batch_uuid') ? 'batch_uuid' : null);

        if (!$uuidCol) return 0;

        $row = DB::table($batchesTable)->where($uuidCol, (string)$batch)->first();
        return $row && isset($row->id) ? (int)$row->id : 0;
    }

    /**
     * Detect mapping + question + attempts tables (best-effort).
     */
    private function detectTables(): array
    {
        $mapTable = $this->resolveFirstExistingTable([
            'batch_coding_questions',
            'batch_coding_question',
            'batch_coding_question_map',
            'batch_coding_questions_map',
        ]);

        $questionTable = $this->resolveFirstExistingTable([
            'coding_questions',
            'coding_question_bank',
            'coding_question',
        ]);

        $attemptTable = $this->resolveFirstExistingTable([
            'coding_test_attempts',
            'coding_attempts',
            'coding_question_attempts',
            'judge_attempts',
            'coding_submissions',
            'code_submissions',
        ]);

        return [$mapTable, $questionTable, $attemptTable];
    }

    /**
     * Detect common columns for FK joins.
     */
    private function detectQuestionFkCols(string $mapTable, string $questionTable): array
    {
        // map -> question FK column
        $mapQCol = $this->tableHasColumn($mapTable, 'coding_question_uuid') ? 'coding_question_uuid' :
                  ($this->tableHasColumn($mapTable, 'question_uuid') ? 'question_uuid' :
                  ($this->tableHasColumn($mapTable, 'coding_question_id') ? 'coding_question_id' :
                  ($this->tableHasColumn($mapTable, 'question_id') ? 'question_id' : null)));

        // question PK (prefer uuid)
        $qPkCol = $this->tableHasColumn($questionTable, 'uuid') ? 'uuid' :
                 ($this->tableHasColumn($questionTable, 'question_uuid') ? 'question_uuid' :
                 ($this->tableHasColumn($questionTable, 'id') ? 'id' : null));

        return [$mapQCol, $qPkCol];
    }

    private function detectBatchFkCol(string $mapTable): ?string
    {
        return $this->tableHasColumn($mapTable, 'batch_id') ? 'batch_id' :
              ($this->tableHasColumn($mapTable, 'course_batch_id') ? 'course_batch_id' :
              ($this->tableHasColumn($mapTable, 'batch_uuid') ? 'batch_uuid' : null));
    }

    private function detectAttemptCols(string $attemptTable): array
    {
        $userCol = $this->tableHasColumn($attemptTable, 'user_id') ? 'user_id' :
                  ($this->tableHasColumn($attemptTable, 'student_id') ? 'student_id' :
                  ($this->tableHasColumn($attemptTable, 'attempt_by') ? 'attempt_by' :
                  ($this->tableHasColumn($attemptTable, 'tokenable_id') ? 'tokenable_id' : null)));

        $qCol = $this->tableHasColumn($attemptTable, 'question_uuid') ? 'question_uuid' :
               ($this->tableHasColumn($attemptTable, 'coding_question_uuid') ? 'coding_question_uuid' :
               ($this->tableHasColumn($attemptTable, 'coding_question_id') ? 'coding_question_id' :
               ($this->tableHasColumn($attemptTable, 'question_id') ? 'question_id' : null)));

        $batchCol = $this->tableHasColumn($attemptTable, 'batch_id') ? 'batch_id' :
                   ($this->tableHasColumn($attemptTable, 'course_batch_id') ? 'course_batch_id' :
                   ($this->tableHasColumn($attemptTable, 'batch_uuid') ? 'batch_uuid' : null));

        $uuidCol = $this->tableHasColumn($attemptTable, 'uuid') ? 'uuid' :
                  ($this->tableHasColumn($attemptTable, 'attempt_uuid') ? 'attempt_uuid' : null);

        $statusCol = $this->tableHasColumn($attemptTable, 'status') ? 'status' :
                    ($this->tableHasColumn($attemptTable, 'attempt_status') ? 'attempt_status' : null);

        $startedCol = $this->tableHasColumn($attemptTable, 'started_at') ? 'started_at' :
                     ($this->tableHasColumn($attemptTable, 'start_at') ? 'start_at' : null);

        $submittedCol = $this->tableHasColumn($attemptTable, 'submitted_at') ? 'submitted_at' :
                       ($this->tableHasColumn($attemptTable, 'end_at') ? 'end_at' : null);

        $createdCol = $this->tableHasColumn($attemptTable, 'created_at') ? 'created_at' : null;

        return [$userCol, $qCol, $batchCol, $uuidCol, $statusCol, $startedCol, $submittedCol, $createdCol];
    }

    private function detectAttemptAllowedCol(string $mapTable): ?string
    {
        return $this->tableHasColumn($mapTable, 'attempt_allowed') ? 'attempt_allowed' :
              ($this->tableHasColumn($mapTable, 'attempts_allowed') ? 'attempts_allowed' :
              ($this->tableHasColumn($mapTable, 'attempt_limit') ? 'attempt_limit' : null));
    }

    private function detectWindowCols(string $mapTable): array
    {
        $startCol = $this->tableHasColumn($mapTable, 'start_at') ? 'start_at' :
                   ($this->tableHasColumn($mapTable, 'available_from') ? 'available_from' : null);

        $endCol = $this->tableHasColumn($mapTable, 'end_at') ? 'end_at' :
                 ($this->tableHasColumn($mapTable, 'available_to') ? 'available_to' : null);

        return [$startCol, $endCol];
    }

    private function nowSql(): string
    {
        // safe string for SQL comparisons
        return now()->toDateTimeString();
    }

    /* ============================================
     | API
     |============================================ */

    /**
     * GET /api/batches/{batch}/coding-questions
     * - Students: returns assigned questions + attempts info + can_start
     * - Staff: returns assigned questions (or all if ?mode=all) with assigned flag
     */
    public function index(Request $r, $batch)
    {
        [$mapTable, $questionTable, $attemptTable] = $this->detectTables();

        if (!$mapTable || !$questionTable) {
            return response()->json([
                'ok' => false,
                'error' => 'Required tables not found. Need batch_coding_questions & coding_questions (or equivalents).'
            ], 500);
        }

        $actor = $this->actor($r);
        $role  = strtolower($actor['role'] ?? '');
        $uid   = (int)($actor['id'] ?? 0);

        $batchId = $this->resolveBatchId($batch);
        if ($batchId <= 0 && is_numeric($batch)) {
            $batchId = (int)$batch;
        }
        if ($batchId <= 0) {
            return response()->json(['ok' => false, 'error' => 'Batch not found/invalid.'], 404);
        }

        [$mapQCol, $qPkCol] = $this->detectQuestionFkCols($mapTable, $questionTable);
        $mapBatchCol        = $this->detectBatchFkCol($mapTable);
        $attemptAllowedCol  = $this->detectAttemptAllowedCol($mapTable);
        [$startCol, $endCol]= $this->detectWindowCols($mapTable);

        if (!$mapQCol || !$qPkCol || !$mapBatchCol) {
            return response()->json([
                'ok' => false,
                'error' => 'Cannot detect required FK columns on mapping/questions table.'
            ], 500);
        }

        $isStudent = ($role === 'student');

        // Base mapping query (assigned rows)
        $assignedQ = DB::table($mapTable . ' as bcq')
            ->join($questionTable . ' as cq', "cq.$qPkCol", '=', "bcq.$mapQCol")
            ->where("bcq.$mapBatchCol", '=', $batchId);

        // Safe soft-delete filters (only if columns exist)
        if ($this->tableHasColumn($mapTable, 'deleted_at')) $assignedQ->whereNull('bcq.deleted_at');
        if ($this->tableHasColumn($questionTable, 'deleted_at')) $assignedQ->whereNull('cq.deleted_at');

        // Active filter if exists
        if ($this->tableHasColumn($mapTable, 'is_active')) $assignedQ->where('bcq.is_active', 1);

        // Select fields (conditional)
        $select = [
            "cq.$qPkCol as question_key",
        ];

        // question fields (best effort)
        foreach (['uuid','title','name','difficulty','level','tags','topic','slug'] as $col) {
            if ($this->tableHasColumn($questionTable, $col)) $select[] = "cq.$col";
        }

        // mapping fields
        foreach (['id','uuid','assigned_by','created_at','updated_at'] as $col) {
            if ($this->tableHasColumn($mapTable, $col)) $select[] = "bcq.$col as map_$col";
        }

        if ($attemptAllowedCol) $select[] = "bcq.$attemptAllowedCol as attempt_allowed";
        if ($startCol) $select[] = "bcq.$startCol as available_from";
        if ($endCol)   $select[] = "bcq.$endCol as available_to";

        // time fields from question (do in PHP later to avoid missing-column SQL errors)
        foreach (['total_time_sec','time_limit_sec','duration_sec','time_sec','total_time','time_limit'] as $col) {
            if ($this->tableHasColumn($questionTable, $col)) $select[] = "cq.$col";
        }

        $assignedQ->select($select);

        // For staff, optionally return ALL questions with assigned flag
        $mode = strtolower((string)$r->query('mode', 'assigned')); // assigned|all
        if (!$isStudent && $mode === 'all') {
            $allQ = DB::table($questionTable . ' as cq');

            if ($this->tableHasColumn($questionTable, 'deleted_at')) $allQ->whereNull('cq.deleted_at');

            // left join mapping for this batch
            $allQ->leftJoin($mapTable . ' as bcq', function ($j) use ($qPkCol, $mapQCol, $mapBatchCol, $batchId, $mapTable) {
                $j->on("bcq.$mapQCol", '=', "cq.$qPkCol")
                  ->where("bcq.$mapBatchCol", '=', $batchId);

                if ($this->tableHasColumn($mapTable, 'deleted_at')) {
                    $j->whereNull('bcq.deleted_at');
                }
            });

            // Select (merge)
            $selectAll = ["cq.$qPkCol as question_key"];
            foreach (['uuid','title','name','difficulty','level','tags','topic','slug'] as $col) {
                if ($this->tableHasColumn($questionTable, $col)) $selectAll[] = "cq.$col";
            }
            foreach (['id','uuid'] as $col) {
                if ($this->tableHasColumn($mapTable, $col)) $selectAll[] = "bcq.$col as map_$col";
            }
            if ($attemptAllowedCol) $selectAll[] = "bcq.$attemptAllowedCol as attempt_allowed";
            if ($startCol) $selectAll[] = "bcq.$startCol as available_from";
            if ($endCol)   $selectAll[] = "bcq.$endCol as available_to";

            foreach (['total_time_sec','time_limit_sec','duration_sec','time_sec','total_time','time_limit'] as $col) {
                if ($this->tableHasColumn($questionTable, $col)) $selectAll[] = "cq.$col";
            }

            // Assigned flag
            $assignedIdCol = $this->tableHasColumn($mapTable, 'id') ? 'id' : ($this->tableHasColumn($mapTable, 'uuid') ? 'uuid' : null);
            if ($assignedIdCol) {
                $selectAll[] = DB::raw("CASE WHEN bcq.$assignedIdCol IS NULL THEN 0 ELSE 1 END as is_assigned");
            } else {
                $selectAll[] = DB::raw("0 as is_assigned");
            }

            $rows = $allQ->select($selectAll)->orderBy('is_assigned', 'desc')->orderBy('question_key', 'desc')->get();

            $data = $rows->map(function ($row) {
                $timeSec = null;
                foreach (['total_time_sec','time_limit_sec','duration_sec','time_sec','total_time','time_limit'] as $k) {
                    if (isset($row->$k) && $row->$k !== null && $row->$k !== '') { $timeSec = (int)$row->$k; break; }
                }
                return [
                    'question_key'    => $row->question_key,
                    'uuid'            => $row->uuid ?? null,
                    'title'           => $row->title ?? ($row->name ?? null),
                    'difficulty'      => $row->difficulty ?? ($row->level ?? null),
                    'tags'            => $row->tags ?? null,
                    'time_sec'        => $timeSec,
                    'is_assigned'     => (int)($row->is_assigned ?? 0) === 1,
                    'attempt_allowed' => isset($row->attempt_allowed) ? (int)$row->attempt_allowed : null,
                    'available_from'  => $row->available_from ?? null,
                    'available_to'    => $row->available_to ?? null,
                    'map_id'          => $row->map_id ?? null,
                    'map_uuid'        => $row->map_uuid ?? null,
                ];
            });

            return response()->json(['ok' => true, 'batch_id' => $batchId, 'mode' => 'all', 'items' => $data]);
        }

        // Student: attach attempts aggregation (attempt_used / last_attempt_at / remaining / can_start)
        if ($isStudent && $attemptTable) {
            [$userCol, $qCol, $attBatchCol, $attUuidCol, $statusCol, $startedCol, $submittedCol, $createdCol] = $this->detectAttemptCols($attemptTable);

            if ($userCol && $qCol) {
                $attAgg = DB::table($attemptTable)
                    ->selectRaw("$qCol as qkey, COUNT(*) as attempt_used");

                // last attempt date (best)
                if ($createdCol) {
                    $attAgg->selectRaw("MAX($createdCol) as last_attempt_at");
                } elseif ($submittedCol) {
                    $attAgg->selectRaw("MAX($submittedCol) as last_attempt_at");
                } elseif ($startedCol) {
                    $attAgg->selectRaw("MAX($startedCol) as last_attempt_at");
                } else {
                    $attAgg->selectRaw("NULL as last_attempt_at");
                }

                $attAgg->where($userCol, '=', $uid);

                if ($attBatchCol) {
                    $attAgg->where($attBatchCol, '=', $batchId);
                }

                $attAgg->groupBy($qCol);

                $assignedQ->leftJoinSub($attAgg, 'att', function ($j) use ($mapQCol) {
                    $j->on('att.qkey', '=', "bcq.$mapQCol");
                });

                $assignedQ->addSelect([
                    DB::raw('COALESCE(att.attempt_used, 0) as attempt_used'),
                    DB::raw('att.last_attempt_at as last_attempt_at'),
                ]);
            }
        }

        // Student availability flag (only if window cols exist)
        if ($isStudent && ($startCol || $endCol)) {
            $now = $this->nowSql();
            if ($startCol && $endCol) {
                $assignedQ->addSelect([
                    DB::raw("CASE
                        WHEN bcq.$startCol IS NOT NULL AND bcq.$startCol > '$now' THEN 'upcoming'
                        WHEN bcq.$endCol   IS NOT NULL AND bcq.$endCol   < '$now' THEN 'closed'
                        ELSE 'open' END as availability")
                ]);
            } elseif ($startCol) {
                $assignedQ->addSelect([
                    DB::raw("CASE WHEN bcq.$startCol IS NOT NULL AND bcq.$startCol > '$now' THEN 'upcoming' ELSE 'open' END as availability")
                ]);
            } else { // only endCol
                $assignedQ->addSelect([
                    DB::raw("CASE WHEN bcq.$endCol IS NOT NULL AND bcq.$endCol < '$now' THEN 'closed' ELSE 'open' END as availability")
                ]);
            }
        } else {
            $assignedQ->addSelect([DB::raw("'open' as availability")]);
        }

        $rows = $assignedQ->orderBy('map_created_at', 'desc')->get();

        $items = $rows->map(function ($row) use ($attemptAllowedCol) {
            // compute time_sec safely
            $timeSec = null;
            foreach (['total_time_sec','time_limit_sec','duration_sec','time_sec','total_time','time_limit'] as $k) {
                if (isset($row->$k) && $row->$k !== null && $row->$k !== '') { $timeSec = (int)$row->$k; break; }
            }

            $attemptAllowed = null;
            if (isset($row->attempt_allowed)) $attemptAllowed = (int)$row->attempt_allowed;
            if ($attemptAllowed === null || $attemptAllowed <= 0) $attemptAllowed = 1;

            $attemptUsed = isset($row->attempt_used) ? (int)$row->attempt_used : 0;
            $attemptRemaining = max(0, $attemptAllowed - $attemptUsed);

            $availability = (string)($row->availability ?? 'open');
            $canStart = ($availability === 'open') && ($attemptRemaining > 0);

            return [
                'question_key'       => $row->question_key,
                'uuid'               => $row->uuid ?? null,
                'title'              => $row->title ?? ($row->name ?? null),
                'difficulty'         => $row->difficulty ?? ($row->level ?? null),
                'tags'               => $row->tags ?? null,
                'time_sec'           => $timeSec,

                'attempt_allowed'    => $attemptAllowed,
                'attempt_used'       => $attemptUsed,
                'attempt_remaining'  => $attemptRemaining,
                'last_attempt_at'    => $row->last_attempt_at ?? null,

                'availability'       => $availability, // open|upcoming|closed
                'available_from'     => $row->available_from ?? null,
                'available_to'       => $row->available_to ?? null,
                'can_start'          => $canStart,

                'map_id'             => $row->map_id ?? null,
                'map_uuid'           => $row->map_uuid ?? null,
            ];
        });

        return response()->json([
            'ok'       => true,
            'batch_id' => $batchId,
            'mode'     => $isStudent ? 'student' : 'assigned',
            'items'    => $items,
        ]);
    }

    /**
     * POST /api/batches/{batch}/coding-questions/assign
     * Body: { question_uuid, attempt_allowed?, start_at?, end_at? }
     */
    public function assign(Request $r, $batch)
    {
        [$mapTable, $questionTable] = $this->detectTables();
        if (!$mapTable || !$questionTable) {
            return response()->json(['ok' => false, 'error' => 'Required tables not found.'], 500);
        }

        $actor = $this->actor($r);
        $role  = strtolower($actor['role'] ?? '');
        $uid   = (int)($actor['id'] ?? 0);

        if (!in_array($role, ['superadmin', 'admin', 'instructor'], true)) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $batchId = $this->resolveBatchId($batch);
        if ($batchId <= 0 && is_numeric($batch)) $batchId = (int)$batch;
        if ($batchId <= 0) return response()->json(['ok' => false, 'error' => 'Batch not found/invalid.'], 404);

        $v = Validator::make($r->all(), [
            'question_uuid'   => 'required|string',
            'attempt_allowed' => 'nullable|integer|min:1|max:50',
            'start_at'        => 'nullable|date',
            'end_at'          => 'nullable|date|after_or_equal:start_at',
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'errors' => $v->errors()], 422);
        }

        [$mapQCol, $qPkCol] = $this->detectQuestionFkCols($mapTable, $questionTable);
        $mapBatchCol        = $this->detectBatchFkCol($mapTable);
        $attemptAllowedCol  = $this->detectAttemptAllowedCol($mapTable);
        [$startCol, $endCol]= $this->detectWindowCols($mapTable);

        if (!$mapQCol || !$qPkCol || !$mapBatchCol) {
            return response()->json(['ok' => false, 'error' => 'Cannot detect required FK columns.'], 500);
        }

        // Validate question exists (best-effort)
        $questionKey = (string)$r->input('question_uuid');
        $qExists = DB::table($questionTable)->where($qPkCol, $questionKey);
        if ($this->tableHasColumn($questionTable, 'deleted_at')) $qExists->whereNull('deleted_at');
        if (!$qExists->exists()) {
            return response()->json(['ok' => false, 'error' => 'Coding question not found.'], 404);
        }

        $payload = [];

        // Ensure mapping has uuid if column exists
        if ($this->tableHasColumn($mapTable, 'uuid')) {
            $payload['uuid'] = (string)Str::uuid();
        }

        // Required keys
        $payload[$mapBatchCol] = $batchId;
        $payload[$mapQCol]     = $questionKey;

        // Attempts
        if ($attemptAllowedCol) {
            $payload[$attemptAllowedCol] = (int)($r->input('attempt_allowed') ?: 1);
        }

        // Window
        if ($startCol && $r->filled('start_at')) $payload[$startCol] = $r->input('start_at');
        if ($endCol   && $r->filled('end_at'))   $payload[$endCol]   = $r->input('end_at');

        // Active
        if ($this->tableHasColumn($mapTable, 'is_active')) $payload['is_active'] = 1;

        // Audit
        if ($this->tableHasColumn($mapTable, 'assigned_by')) $payload['assigned_by'] = $uid;
        if ($this->tableHasColumn($mapTable, 'created_by'))  $payload['created_by']  = $uid;
        if ($this->tableHasColumn($mapTable, 'updated_by'))  $payload['updated_by']  = $uid;
        if ($this->tableHasColumn($mapTable, 'assigned_at')) $payload['assigned_at'] = now();
        if ($this->tableHasColumn($mapTable, 'created_at'))  $payload['created_at']  = now();
        if ($this->tableHasColumn($mapTable, 'updated_at'))  $payload['updated_at']  = now();

        try {
            DB::beginTransaction();

            // If row exists (batch + question), update instead of inserting duplicates.
            $existing = DB::table($mapTable)
                ->where($mapBatchCol, $batchId)
                ->where($mapQCol, $questionKey);

            if ($this->tableHasColumn($mapTable, 'deleted_at')) {
                // revive soft-deleted rows if present
                $payload['deleted_at'] = null;
            }

            if ($existing->exists()) {
                // Don’t overwrite uuid if already there
                if ($this->tableHasColumn($mapTable, 'uuid')) unset($payload['uuid']);
                if (!$this->tableHasColumn($mapTable, 'created_at')) unset($payload['created_at']);

                $existing->update($payload);
            } else {
                DB::table($mapTable)->insert($payload);
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Assigned successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BatchCodingQuestionController@assign failed', [
                'error' => $e->getMessage(),
                'batch' => $batch,
                'batchId' => $batchId,
                'question' => $questionKey,
            ]);
            return response()->json(['ok' => false, 'error' => 'Assign failed.'], 500);
        }
    }

    /**
     * DELETE /api/batches/{batch}/coding-questions/{questionUuid}
     */
    public function unassign(Request $r, $batch, string $questionUuid)
    {
        [$mapTable, $questionTable] = $this->detectTables();
        if (!$mapTable) {
            return response()->json(['ok' => false, 'error' => 'Mapping table not found.'], 500);
        }

        $actor = $this->actor($r);
        $role  = strtolower($actor['role'] ?? '');
        $uid   = (int)($actor['id'] ?? 0);

        if (!in_array($role, ['superadmin', 'admin', 'instructor'], true)) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $batchId = $this->resolveBatchId($batch);
        if ($batchId <= 0 && is_numeric($batch)) $batchId = (int)$batch;
        if ($batchId <= 0) return response()->json(['ok' => false, 'error' => 'Batch not found/invalid.'], 404);

        // Detect FK cols
        $questionTable = $questionTable ?: $this->resolveFirstExistingTable(['coding_questions','coding_question_bank','coding_question']);
        if (!$questionTable) {
            return response()->json(['ok' => false, 'error' => 'Question table not found.'], 500);
        }

        [$mapQCol] = $this->detectQuestionFkCols($mapTable, $questionTable);
        $mapBatchCol = $this->detectBatchFkCol($mapTable);
        if (!$mapQCol || !$mapBatchCol) {
            return response()->json(['ok' => false, 'error' => 'Cannot detect FK columns.'], 500);
        }

        try {
            $q = DB::table($mapTable)
                ->where($mapBatchCol, $batchId)
                ->where($mapQCol, $questionUuid);

            if ($this->tableHasColumn($mapTable, 'deleted_at')) {
                $update = ['deleted_at' => now()];
                if ($this->tableHasColumn($mapTable, 'updated_at')) $update['updated_at'] = now();
                if ($this->tableHasColumn($mapTable, 'updated_by')) $update['updated_by'] = $uid;
                $q->update($update);
            } else {
                $q->delete();
            }

            return response()->json(['ok' => true, 'message' => 'Unassigned successfully']);
        } catch (\Throwable $e) {
            Log::error('BatchCodingQuestionController@unassign failed', [
                'error' => $e->getMessage(),
                'batch' => $batch,
                'batchId' => $batchId,
                'question' => $questionUuid,
            ]);
            return response()->json(['ok' => false, 'error' => 'Unassign failed.'], 500);
        }
    }

    /**
     * GET /api/batches/{batch}/coding-questions/{questionUuid}/my-attempts
     * Student-only: returns attempt list for result dropdown.
     */
    public function myAttempts(Request $r, $batch, string $questionUuid)
    {
        [$mapTable, $questionTable, $attemptTable] = $this->detectTables();
        if (!$attemptTable) {
            return response()->json(['ok' => true, 'attempts' => []]); // no attempts table detected
        }

        $actor = $this->actor($r);
        $role  = strtolower($actor['role'] ?? '');
        $uid   = (int)($actor['id'] ?? 0);

        if ($role !== 'student') {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $batchId = $this->resolveBatchId($batch);
        if ($batchId <= 0 && is_numeric($batch)) $batchId = (int)$batch;
        if ($batchId <= 0) return response()->json(['ok' => false, 'error' => 'Batch not found/invalid.'], 404);

        // Ensure question is assigned to this batch (best effort)
        if ($mapTable && $questionTable) {
            [$mapQCol] = $this->detectQuestionFkCols($mapTable, $questionTable);
            $mapBatchCol = $this->detectBatchFkCol($mapTable);
            if ($mapQCol && $mapBatchCol) {
                $assigned = DB::table($mapTable)
                    ->where($mapBatchCol, $batchId)
                    ->where($mapQCol, $questionUuid);
                if ($this->tableHasColumn($mapTable, 'deleted_at')) $assigned->whereNull('deleted_at');
                if (!$assigned->exists()) {
                    return response()->json(['ok' => false, 'error' => 'Not assigned to this batch.'], 404);
                }
            }
        }

        [$userCol, $qCol, $attBatchCol, $attUuidCol, $statusCol, $startedCol, $submittedCol, $createdCol] = $this->detectAttemptCols($attemptTable);

        if (!$userCol || !$qCol) {
            return response()->json(['ok' => true, 'attempts' => []]);
        }

        $q = DB::table($attemptTable)->where($userCol, $uid)->where($qCol, $questionUuid);
        if ($attBatchCol) $q->where($attBatchCol, $batchId);

        // Soft delete safe
        if ($this->tableHasColumn($attemptTable, 'deleted_at')) $q->whereNull('deleted_at');

        // Select columns
        $sel = [];
        if ($attUuidCol) $sel[] = $attUuidCol . ' as attempt_key';
        if ($this->tableHasColumn($attemptTable, 'id')) $sel[] = 'id';
        if ($statusCol) $sel[] = "$statusCol as status";
        if ($startedCol) $sel[] = "$startedCol as started_at";
        if ($submittedCol) $sel[] = "$submittedCol as submitted_at";
        if ($createdCol) $sel[] = "$createdCol as created_at";

        // attempt number if exists
        if ($this->tableHasColumn($attemptTable, 'attempt_no')) $sel[] = 'attempt_no';

        if (empty($sel)) $sel = ['*'];

        $rows = $q->select($sel)->orderBy($createdCol ?: ($submittedCol ?: ($startedCol ?: 'id')), 'desc')->get();

        $attempts = [];
        $i = 0;
        foreach ($rows as $row) {
            $i++;
            $key = $row->attempt_key ?? ($row->uuid ?? ($row->id ?? null));
            if (!$key) continue;

            $attemptNo = isset($row->attempt_no) ? (int)$row->attempt_no : $i;

            $attempts[] = [
                'attempt_key'  => (string)$key,
                'attempt_no'   => $attemptNo,
                'status'       => $row->status ?? null,
                'started_at'   => $row->started_at ?? null,
                'submitted_at' => $row->submitted_at ?? null,
                'created_at'   => $row->created_at ?? null,
                // Your UI can open this page (you can implement route/view)
                'view_url'     => url('/coding-tests/results/' . $key),
            ];
        }

        return response()->json(['ok' => true, 'attempts' => $attempts]);
    }
}
