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
 *
 * ✅ Filter by module (course-scoped):
 *   ?course_module_id=ID   OR ?module_id=ID
 *   ?course_module_uuid=UUID OR ?module_uuid=UUID
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

    $useQuestionIdJoin = preg_match('/_id$/', $mapQCol);
    $questionJoinCol   = $useQuestionIdJoin ? 'id' : ($qPkCol ?? 'uuid');

    /* =========================================================
     * ✅ Proper module filter resolve (course-scoped via batch->course_id)
     * ========================================================= */

    // detect module columns on mapping table (bcq)
    $mapModuleIdCol   = null;
    $mapModuleUuidCol = null;

    if ($this->tableHasColumn($mapTable, 'course_module_id')) $mapModuleIdCol = 'course_module_id';
    elseif ($this->tableHasColumn($mapTable, 'module_id'))    $mapModuleIdCol = 'module_id';

    if ($this->tableHasColumn($mapTable, 'course_module_uuid')) $mapModuleUuidCol = 'course_module_uuid';
    elseif ($this->tableHasColumn($mapTable, 'module_uuid'))    $mapModuleUuidCol = 'module_uuid';

    // raw params
    $moduleIdRaw   = $r->query('course_module_id', $r->query('module_id'));
    $moduleUuidRaw = $r->query('course_module_uuid', $r->query('module_uuid'));

    $wantModuleId   = (is_string($moduleIdRaw) || is_numeric($moduleIdRaw)) && (string)$moduleIdRaw !== '' && ctype_digit((string)$moduleIdRaw);
    $wantModuleUuid = is_string($moduleUuidRaw) && $moduleUuidRaw !== '' && \Illuminate\Support\Str::isUuid($moduleUuidRaw);

    $moduleId   = $wantModuleId ? (int)$moduleIdRaw : null;
    $moduleUuid = $wantModuleUuid ? (string)$moduleUuidRaw : null;

    $moduleFilterRequested = ($moduleId !== null) || ($moduleUuid !== null);

    // ✅ FIX 1: Only validate schema if filter is actually requested
    if ($moduleFilterRequested && !$mapModuleIdCol && !$mapModuleUuidCol) {
        return response()->json([
            'ok' => false,
            'error' => "Schema issue: {$mapTable} needs course_module_id/module_id or *_uuid to filter by module"
        ], 500);
    }

    // ✅ resolve batch->course_id (for course-scoped module validation)
    $batchRow = DB::table('batches')
        ->when($this->tableHasColumn('batches','deleted_at'), fn($q)=>$q->whereNull('deleted_at'))
        ->where('id', $batchId)
        ->select('id','course_id')
        ->first();

    // ✅ FIX 2: Handle missing batch gracefully
    if (!$batchRow) {
        return response()->json(['ok' => false, 'error' => 'Batch not found'], 404);
    }

    $courseId = (int)($batchRow->course_id ?? 0);
    
    // ✅ FIX 3: Only validate course if module filter is requested
    if ($moduleFilterRequested && $courseId <= 0) {
        return response()->json(['ok'=>false,'error'=>'Batch has no associated course'], 404);
    }

    // ✅ normalize requested module to BOTH id+uuid (course-scoped)
    // so we can filter regardless of whether mapping stores id or uuid.
    if ($moduleFilterRequested) {
        $mq = DB::table('course_modules')
            ->where('course_id', $courseId)
            ->when($this->tableHasColumn('course_modules','deleted_at'), fn($q)=>$q->whereNull('deleted_at'));

        if ($moduleId !== null) {
            $mq->where('id', $moduleId);
        } elseif ($moduleUuid !== null) {
            // ✅ FIX 4: Guard UUID filter if column doesn't exist
            if (!$this->tableHasColumn('course_modules','uuid')) {
                return response()->json(['ok'=>false,'error'=>'Schema issue: course_modules.uuid missing'], 500);
            }
            $mq->where('uuid', $moduleUuid);
        }

        $mrow = $mq->select(
            'id',
            $this->tableHasColumn('course_modules','uuid') ? 'uuid' : DB::raw('NULL as uuid')
        )->first();

        if (!$mrow) {
            return response()->json(['ok'=>false,'error'=>'Course module not found for this batch/course'], 404);
        }

        // ✅ FIX 5: Update both id and uuid from resolved module
        $moduleId   = (int)$mrow->id;
        $moduleUuid = $mrow->uuid ?? null; // allow null if schema doesn't support
    }

    /* ================= ASSIGNED QUERY ================= */
    $assignedQ = DB::table($mapTable.' as bcq')
        ->join($questionTable.' as cq', "cq.$questionJoinCol", '=', "bcq.$mapQCol")
        ->where("bcq.$mapBatchCol", $batchId);

    if ($this->tableHasColumn($mapTable,'deleted_at')) $assignedQ->whereNull('bcq.deleted_at');
    if ($this->tableHasColumn($questionTable,'deleted_at')) $assignedQ->whereNull('cq.deleted_at');

    // ✅ FIX 6: Apply module filter (prefer id; fallback to uuid)
    if ($moduleFilterRequested) {
        if ($mapModuleIdCol && $moduleId !== null) {
            $assignedQ->where("bcq.$mapModuleIdCol", $moduleId);
        } elseif ($mapModuleUuidCol && $moduleUuid !== null) {
            $assignedQ->where("bcq.$mapModuleUuidCol", $moduleUuid);
        }
        // ✅ If both columns exist, prefer id filter (more reliable)
    }

    $selectAssigned = [
        'cq.uuid as question_key',
        'cq.uuid',
        'cq.total_attempts',
        DB::raw('1 as is_assigned'),
        DB::raw('1 as assigned'), // ✅ FIX 7: Add both keys for frontend compatibility
    ];

    foreach (['title','slug','difficulty','status','sort_order','description'] as $col) {
        if ($this->tableHasColumn($questionTable, $col)) {
            $selectAssigned[] = "cq.$col";
        }
    }

    if ($attemptAllowedCol) $selectAssigned[] = "bcq.$attemptAllowedCol as attempt_allowed";
    if ($startCol) $selectAssigned[] = "bcq.$startCol as available_from";
    if ($endCol)   $selectAssigned[] = "bcq.$endCol as available_to";

    // ✅ FIX 8: Always include module columns (even if null) for consistent frontend handling
    if ($mapModuleIdCol) {
        $selectAssigned[] = "bcq.$mapModuleIdCol as course_module_id";
    } else {
        $selectAssigned[] = DB::raw('NULL as course_module_id');
    }

    if ($mapModuleUuidCol) {
        $selectAssigned[] = "bcq.$mapModuleUuidCol as course_module_uuid";
    } else {
        $selectAssigned[] = DB::raw('NULL as course_module_uuid');
    }

    $selectAssigned[] = 'bcq.id as map_id';
    $selectAssigned[] = 'bcq.uuid as map_uuid';

    $assignedQ->select($selectAssigned);

    /* ================= ALL QUESTIONS (ADMIN) ================= */
    if (!$isStudent && strtolower($r->query('mode')) === 'all') {

        $allQ = DB::table($questionTable.' as cq')
            ->leftJoin($mapTable.' as bcq', function ($j) use (
                $mapQCol, $mapBatchCol, $batchId, $questionJoinCol, $mapTable,
                $moduleId, $moduleUuid, $mapModuleIdCol, $mapModuleUuidCol, $moduleFilterRequested
            ) {
                $j->on("bcq.$mapQCol", '=', "cq.$questionJoinCol")
                  ->where("bcq.$mapBatchCol", $batchId);

                if (Schema::hasColumn($mapTable,'deleted_at')) {
                    $j->whereNull('bcq.deleted_at');
                }

                // ✅ FIX 9: Module-aware join (prefer id; fallback uuid)
                if ($moduleFilterRequested) {
                    if ($mapModuleIdCol && $moduleId !== null) {
                        $j->where("bcq.$mapModuleIdCol", $moduleId);
                    } elseif ($mapModuleUuidCol && $moduleUuid !== null) {
                        $j->where("bcq.$mapModuleUuidCol", $moduleUuid);
                    }
                }
            });

        if ($this->tableHasColumn($questionTable,'deleted_at')) $allQ->whereNull('cq.deleted_at');

        $selectAll = [
            'cq.uuid as question_key',
            'cq.uuid',
            'cq.total_attempts',
        ];

        foreach (['title','slug','difficulty','status','sort_order','description'] as $col) {
            if ($this->tableHasColumn($questionTable, $col)) {
                $selectAll[] = "cq.$col";
            }
        }

        // ✅ FIX 10: Robust assignment flag + both keys
        $selectAll[] = DB::raw("CASE WHEN bcq.id IS NULL THEN 0 ELSE 1 END as is_assigned");
        $selectAll[] = DB::raw("CASE WHEN bcq.id IS NULL THEN 0 ELSE 1 END as assigned");

        if ($attemptAllowedCol) $selectAll[] = "bcq.$attemptAllowedCol as attempt_allowed";
        if ($startCol) $selectAll[] = "bcq.$startCol as available_from";
        if ($endCol)   $selectAll[] = "bcq.$endCol as available_to";

        // ✅ FIX 11: Always include module columns (even if null)
        if ($mapModuleIdCol) {
            $selectAll[] = "bcq.$mapModuleIdCol as course_module_id";
        } else {
            $selectAll[] = DB::raw('NULL as course_module_id');
        }

        if ($mapModuleUuidCol) {
            $selectAll[] = "bcq.$mapModuleUuidCol as course_module_uuid";
        } else {
            $selectAll[] = DB::raw('NULL as course_module_uuid');
        }

        $selectAll[] = 'bcq.id as map_id';
        $selectAll[] = 'bcq.uuid as map_uuid';

        $rows = $allQ->select($selectAll)
                     ->orderBy('is_assigned','desc')
                     ->orderBy('question_key')
                     ->get();

        return response()->json([
            'ok' => true,
            'batch_id' => $batchId,
            'course_id' => $courseId, // ✅ FIX 12: Include for frontend reference
            'mode' => 'all',
            'module' => [
                'course_module_id' => $moduleId,
                'course_module_uuid' => $moduleUuid,
                'filter_active' => $moduleFilterRequested, // ✅ FIX 13: Explicit flag
            ],
            'items' => $rows,
            'data' => $rows, // ✅ FIX 14: Alias for frontend compatibility
        ]);
    }

    /* ================= ASSIGNED ONLY ================= */
    $assignedRows = $assignedQ->get();

    return response()->json([
        'ok' => true,
        'batch_id' => $batchId,
        'course_id' => $courseId, // ✅ FIX 15: Include course_id
        'mode' => $isStudent ? 'student' : 'assigned',
        'module' => [
            'course_module_id' => $moduleId,
            'course_module_uuid' => $moduleUuid,
            'filter_active' => $moduleFilterRequested, // ✅ FIX 16: Explicit flag
        ],
        'items' => $assignedRows,
        'data' => $assignedRows, // ✅ FIX 17: Alias for frontend compatibility
        'questions' => $assignedRows, // ✅ FIX 18: Another common alias
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

    // ===========================
    // ✅ Detect module columns on map table
    // ===========================
    $mapModuleIdCol = null;
    $mapModuleUuidCol = null;

    if ($this->tableHasColumn($mapTable, 'course_module_id')) $mapModuleIdCol = 'course_module_id';
    elseif ($this->tableHasColumn($mapTable, 'module_id'))    $mapModuleIdCol = 'module_id';

    if ($this->tableHasColumn($mapTable, 'course_module_uuid')) $mapModuleUuidCol = 'course_module_uuid';
    elseif ($this->tableHasColumn($mapTable, 'module_uuid'))    $mapModuleUuidCol = 'module_uuid';

    // ===========================
    // ✅ Validation (module is REQUIRED if map has module column to avoid default 1)
    // ===========================
    $v = Validator::make($r->all(), [
        'question_uuid'       => 'required|string',

        // module context keys (frontend should send from URL)
        'module_uuid'         => 'nullable|string',
        'course_module_uuid'  => 'nullable|string',
        'module_id'           => 'nullable|integer|min:1',
        'course_module_id'    => 'nullable|integer|min:1',

        'attempt_allowed'     => 'nullable|integer|min:1|max:50',
        'start_at'            => 'nullable|date',
        'end_at'              => 'nullable|date|after_or_equal:start_at',
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

    // ===========================
    // ✅ Resolve module_uuid -> module_id (NO DEFAULT 1)
    // ===========================
    $moduleId = null;
    $moduleUuid = null;

    // accept multiple param names
    $moduleIdRaw = $r->input('course_module_id', $r->input('module_id'));
    if ($moduleIdRaw !== null && $moduleIdRaw !== '' && is_numeric($moduleIdRaw) && (int)$moduleIdRaw > 0) {
        $moduleId = (int)$moduleIdRaw;
    }

    $moduleUuidRaw = $r->input('course_module_uuid', $r->input('module_uuid'));
    if ($moduleUuidRaw && Str::isUuid($moduleUuidRaw)) {
        $moduleUuid = (string)$moduleUuidRaw;
    }

    // If map table expects module info, enforce it
    if (($mapModuleIdCol || $mapModuleUuidCol) && !$moduleId && !$moduleUuid) {
        return response()->json([
            'ok' => false,
            'error' => 'Module context required. Send module_uuid (preferred) or module_id.'
        ], 422);
    }

    // If uuid provided and we need/store module_id, resolve uuid -> id
    if (!$moduleId && $moduleUuid && $mapModuleIdCol) {
        $moduleId = DB::table('course_modules')
            ->when($this->tableHasColumn('course_modules', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->where('uuid', $moduleUuid)
            ->value('id');

        if (!$moduleId) {
            return response()->json(['ok' => false, 'error' => 'Course module not found.'], 404);
        }
    }

    // ===========================
    // ✅ Resolve UUID → numeric ID (question)
    // ===========================
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

    // Required keys
    $payload[$mapBatchCol] = $batchId;

    if ($mapStoresId) {
        $payload[$mapQCol] = (int) $questionId;
    } else {
        $payload[$mapQCol] = $questionUuid;
    }

    // ✅ STORE module into map table (id + uuid if available)
    if ($mapModuleIdCol) {
        // if moduleId still missing but uuid exists, above resolver would have fetched it
        $payload[$mapModuleIdCol] = $moduleId; // may be null only if map doesn't require module
    }
    if ($mapModuleUuidCol) {
        // keep uuid too (optional but useful for debugging)
        $payload[$mapModuleUuidCol] = $moduleUuid ?: null;
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

        // ✅ IMPORTANT: include module in selector so one module doesn’t overwrite another
        if ($mapModuleIdCol && array_key_exists($mapModuleIdCol, $payload)) {
            $existing->where($mapModuleIdCol, $payload[$mapModuleIdCol]);
        } elseif ($mapModuleUuidCol && array_key_exists($mapModuleUuidCol, $payload)) {
            $existing->where($mapModuleUuidCol, $payload[$mapModuleUuidCol]);
        }

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
            'moduleId' => $moduleId,
            'moduleUuid' => $moduleUuid,
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
