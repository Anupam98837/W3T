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
        return response()->json(['ok' => false, 'error' => 'Required tables not found.'], 500);
    }

    $actor = $this->actor($r);
    $role  = strtolower($actor['role'] ?? '');
    $uid   = (int)($actor['id'] ?? 0);

    $batchId = $this->resolveBatchId($batch);
    if ($batchId <= 0 && is_numeric($batch)) $batchId = (int)$batch;
    if ($batchId <= 0) {
        return response()->json(['ok' => false, 'error' => 'Batch not found'], 404);
    }

    [$mapQCol, $qPkCol] = $this->detectQuestionFkCols($mapTable, $questionTable);
    $mapBatchCol        = $this->detectBatchFkCol($mapTable);
    $attemptAllowedCol  = $this->detectAttemptAllowedCol($mapTable);
    [$startCol, $endCol]= $this->detectWindowCols($mapTable);

    if (!$mapQCol || !$mapBatchCol) {
        return response()->json(['ok' => false, 'error' => 'FK detection failed'], 500);
    }

    $isStudent = ($role === 'student');

    // Determine which question column to join on (map side might store _id or uuid)
    $useQuestionIdJoin = preg_match('/_id$/', $mapQCol);

    $questionJoinCol = $useQuestionIdJoin ? 'id' : ($qPkCol ?? 'uuid');

    /* ================= ASSIGNED QUERY ================= */
    $assignedQ = DB::table($mapTable.' as bcq')
        ->join($questionTable.' as cq', "cq.$questionJoinCol", '=', "bcq.$mapQCol")
        ->where("bcq.$mapBatchCol", $batchId);

    if ($this->tableHasColumn($mapTable,'deleted_at')) $assignedQ->whereNull('bcq.deleted_at');
    if ($this->tableHasColumn($questionTable,'deleted_at')) $assignedQ->whereNull('cq.deleted_at');

    $selectAssigned = [
        'cq.uuid as question_key',
        'cq.uuid',
    ];

    foreach (['title','slug','difficulty','status','sort_order','description'] as $col) {
        if ($this->tableHasColumn($questionTable, $col)) {
            $selectAssigned[] = "cq.$col";
        }
    }

    $selectAssigned[] = DB::raw('1 as is_assigned');

    if ($attemptAllowedCol) $selectAssigned[] = "bcq.$attemptAllowedCol as attempt_allowed";
    if ($startCol) $selectAssigned[] = "bcq.$startCol as available_from";
    if ($endCol)   $selectAssigned[] = "bcq.$endCol as available_to";

    $selectAssigned[] = 'bcq.id as map_id';
    $selectAssigned[] = 'bcq.uuid as map_uuid';

    $assignedQ->select($selectAssigned);

    /* ================= ALL QUESTIONS (ADMIN) ================= */
    if (!$isStudent && strtolower($r->query('mode')) === 'all') {

        $allQ = DB::table($questionTable.' as cq')
            ->leftJoin($mapTable.' as bcq', function ($j) use ($mapQCol, $mapBatchCol, $batchId, $questionJoinCol, $mapTable) {
                $j->on("bcq.$mapQCol", '=', "cq.$questionJoinCol")
                  ->where("bcq.$mapBatchCol", $batchId);

                if (Schema::hasColumn($mapTable,'deleted_at')) {
                    $j->whereNull('bcq.deleted_at');
                }
            });

        $selectAll = [
            'cq.uuid as question_key',
            'cq.uuid',
        ];

        foreach (['title','slug','difficulty','status','sort_order','description'] as $col) {
            if ($this->tableHasColumn($questionTable, $col)) {
                $selectAll[] = "cq.$col";
            }
        }

        $selectAll[] = DB::raw("
            CASE
                WHEN bcq.assign_status = 1 THEN 1
                ELSE 0
            END as is_assigned
        ");

        if ($attemptAllowedCol) $selectAll[] = "bcq.$attemptAllowedCol as attempt_allowed";
        if ($startCol) $selectAll[] = "bcq.$startCol as available_from";
        if ($endCol)   $selectAll[] = "bcq.$endCol as available_to";

        $selectAll[] = 'bcq.id as map_id';
        $selectAll[] = 'bcq.uuid as map_uuid';

        $rows = $allQ->select($selectAll)
                     ->orderBy('is_assigned','desc')
                     ->orderBy('question_key')
                     ->get();

        return response()->json([
            'ok' => true,
            'batch_id' => $batchId,
            'mode' => 'all',
            'items' => $rows
        ]);
    }

    /* ================= ASSIGNED ONLY ================= */
    return response()->json([
        'ok' => true,
        'batch_id' => $batchId,
        'mode' => $isStudent ? 'student' : 'assigned',
        'items' => $assignedQ->get()
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
    if ($batchId <= 0) {
        return response()->json(['ok' => false, 'error' => 'Batch not found/invalid.'], 404);
    }

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

    // ===== Resolve UUID → numeric ID (and keep both forms) =====
    $questionUuid = (string)$r->input('question_uuid');

    $questionId = DB::table($questionTable)
        ->where($qPkCol, $questionUuid)
        ->when($this->tableHasColumn($questionTable, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
        ->value('id');

    if (!$questionId) {
        return response()->json(['ok' => false, 'error' => 'Coding question not found.'], 404);
    }

    // determine whether map stores id or uuid
    $mapStoresId = preg_match('/_id$/', $mapQCol);

    $payload = [];

    // Ensure mapping has uuid if column exists
    if ($this->tableHasColumn($mapTable, 'uuid')) {
        $payload['uuid'] = (string) Str::uuid();
    }

    // Required keys (STORE the correct form based on map column)
    $payload[$mapBatchCol] = $batchId;
    if ($mapStoresId) {
        $payload[$mapQCol] = (int) $questionId;
    } else {
        $payload[$mapQCol] = $questionUuid;
    }

    // Attempts
    if ($attemptAllowedCol) {
        $payload[$attemptAllowedCol] = (int) ($r->input('attempt_allowed') ?: 1);
    }

    // Window
    if ($startCol && $r->filled('start_at')) $payload[$startCol] = $r->input('start_at');
    if ($endCol   && $r->filled('end_at'))   $payload[$endCol]   = $r->input('end_at');

    // ===================== FIX: ASSIGN FLAGS =====================
    if ($this->tableHasColumn($mapTable, 'assign_status')) {
        $payload['assign_status'] = 1;
    }

    if ($this->tableHasColumn($mapTable, 'publish_to_students')) {
        $payload['publish_to_students'] = 1;
    }

    if ($this->tableHasColumn($mapTable, 'status')) {
        $payload['status'] = 'active';
    }
    // =============================================================

    // Active
    if ($this->tableHasColumn($mapTable, 'is_active')) {
        $payload['is_active'] = 1;
    }

    // Audit
    if ($this->tableHasColumn($mapTable, 'assigned_by')) $payload['assigned_by'] = $uid;
    if ($this->tableHasColumn($mapTable, 'created_by'))  $payload['created_by']  = $uid;
    if ($this->tableHasColumn($mapTable, 'updated_by'))  $payload['updated_by']  = $uid;
    if ($this->tableHasColumn($mapTable, 'assigned_at')) $payload['assigned_at'] = now();
    if ($this->tableHasColumn($mapTable, 'created_at'))  $payload['created_at']  = now();
    if ($this->tableHasColumn($mapTable, 'updated_at'))  $payload['updated_at']  = now();

    try {
        DB::beginTransaction();

        // Build existing selector using same key type as payload
        $existing = DB::table($mapTable)
            ->where($mapBatchCol, $batchId)
            ->where($mapQCol, $payload[$mapQCol]);

        if ($this->tableHasColumn($mapTable, 'deleted_at')) {
            $payload['deleted_at'] = null;
        }

        if ($existing->exists()) {
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
            'error'    => $e->getMessage(),
            'batch'    => $batch,
            'batchId'  => $batchId,
            'question' => $questionUuid,
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
    if ($batchId <= 0) {
        return response()->json(['ok' => false, 'error' => 'Batch not found/invalid.'], 404);
    }

    // ✅ Resolve UUID → numeric question_id
    $questionId = DB::table($questionTable)
        ->where('uuid', $questionUuid)
        ->when($this->tableHasColumn($questionTable, 'deleted_at'),
            fn ($q) => $q->whereNull('deleted_at'))
        ->value('id');

    if (!$questionId) {
        return response()->json(['ok' => false, 'error' => 'Question not found.'], 404);
    }

    $mapBatchCol = $this->detectBatchFkCol($mapTable);
    $mapQCol     = $this->detectQuestionFkCols($mapTable, $questionTable)[0];

    if (!$mapBatchCol || !$mapQCol) {
        return response()->json(['ok' => false, 'error' => 'FK detection failed.'], 500);
    }

    // choose correct comparison value based on map col type
    $mapStoresId = preg_match('/_id$/', $mapQCol);
    $mapCompareValue = $mapStoresId ? $questionId : $questionUuid;

    try {
        $q = DB::table($mapTable)
            ->where($mapBatchCol, $batchId)
            ->where($mapQCol, $mapCompareValue);

        // ✅ Soft unassign (preferred)
        $update = ['assign_status' => 0];

        if ($this->tableHasColumn($mapTable, 'updated_at')) {
            $update['updated_at'] = now();
        }
        if ($this->tableHasColumn($mapTable, 'updated_by')) {
            $update['updated_by'] = $uid;
        }

        $q->update($update);

        return response()->json([
            'ok' => true,
            'message' => 'Unassigned successfully'
        ]);
    } catch (\Throwable $e) {
        Log::error('BatchCodingQuestionController@unassign failed', [
            'error'    => $e->getMessage(),
            'batchId'  => $batchId,
            'question' => $questionUuid,
        ]);

        return response()->json(['ok' => false, 'error' => 'Unassign failed.'], 500);
    }
}

 public function myAttempts(Request $r, $batch, string $questionUuid)
{
    [$mapTable, $questionTable, $attemptTable] = $this->detectTables();
    if (!$attemptTable) {
        return response()->json(['ok' => true, 'attempts' => []]);
    }

    $actor = $this->actor($r);
    $role  = strtolower($actor['role'] ?? '');
    $uid   = (int)($actor['id'] ?? 0);

    if ($role !== 'student') {
        return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
    }

    $batchId = $this->resolveBatchId($batch);
    if ($batchId <= 0 && is_numeric($batch)) $batchId = (int)$batch;
    if ($batchId <= 0) {
        return response()->json(['ok' => false, 'error' => 'Batch not found'], 404);
    }

    [$userCol, $qCol, $attBatchCol, $attUuidCol, $statusCol, $startedCol, $submittedCol, $createdCol]
        = $this->detectAttemptCols($attemptTable);

    if (!$userCol || !$qCol) {
        return response()->json(['ok' => true, 'attempts' => []]);
    }

    // Resolve question id if attempts table stores numeric FK
    $attemptQuestionValue = $questionUuid;
    if (preg_match('/_id$/', $qCol)) {
        $qid = DB::table($questionTable)->where('uuid', $questionUuid)->value('id');
        if (!$qid) {
            return response()->json(['ok' => true, 'attempts' => []]);
        }
        $attemptQuestionValue = $qid;
    }

    $rows = DB::table($attemptTable . ' as a')
        ->leftJoin('coding_results as r', 'r.attempt_id', '=', 'a.id')
        ->where('a.' . $userCol, $uid)
        ->where('a.' . $qCol, $attemptQuestionValue)
        ->when($attBatchCol, fn($q) => $q->where('a.' . $attBatchCol, $batchId))
        ->when(
            $this->tableHasColumn($attemptTable, 'deleted_at'),
            fn($q) => $q->whereNull('a.deleted_at')
        )
        // 🔥 FIX: order oldest → newest so numbering is correct
        ->orderBy($createdCol ?: 'a.id', 'asc')
        ->get([
            'a.id',
            'a.' . $attUuidCol . ' as attempt_uuid',
            'r.uuid as result_uuid',
            $statusCol ? 'a.' . $statusCol . ' as status' : DB::raw('NULL as status'),
            $submittedCol ? 'a.' . $submittedCol . ' as submitted_at' : DB::raw('NULL as submitted_at'),
            $createdCol ? 'a.' . $createdCol . ' as created_at' : DB::raw('NULL as created_at'),
        ]);

    $attempts = [];
    $i = 0;

    foreach ($rows as $row) {
        $i++;
        $attempts[] = [
            'attempt_uuid' => (string)$row->attempt_uuid,
            'attempt_no'   => $i, // ✅ now matches actual attempt
            'status'       => $row->status,
            'submitted_at' => $row->submitted_at,
            'result_uuid'  => $row->result_uuid,
            'view_url'     => $row->result_uuid
                ? url('/coding/results/' . $row->result_uuid . '/view')
                : null,
        ];
    }

    return response()->json([
        'ok'       => true,
        'attempts' => $attempts
    ]);
}

}
