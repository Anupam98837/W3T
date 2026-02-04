<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BatchCourseModuleController extends Controller
{
    /**
     * Roles allowed to manage batch modules
     */
    private array $ALLOWED_ROLES = ['superadmin','admin','instructor'];
    
    private function resolveBatchIdFromRequest(Request $r): int
    {
        // 1) If batch_id provided, trust it (already validated as integer)
        if ($r->filled('batch_id')) {
            return (int) $r->batch_id;
        }

        // 2) Otherwise resolve from batch_uuid
        $uuid = (string) $r->input('batch_uuid', '');
        $uuid = trim($uuid);

        if (!$uuid || !Str::isUuid($uuid)) {
            abort(response()->json([
                'success' => false,
                'message' => 'batch_uuid is required when batch_id is missing',
            ], 422));
        }

        $batch = DB::table('batches')
            ->whereNull('deleted_at')
            ->where('uuid', $uuid)
            ->first();

        if (!$batch) {
            abort(response()->json([
                'success' => false,
                'message' => 'Batch not found for batch_uuid',
                'batch_uuid' => $uuid,
            ], 404));
        }

        return (int) $batch->id;
    }
    
    private function resolveCourseIdFromRequest(Request $r, bool $required = false): ?int
    {
        // 1) If course_id provided, use it
        if ($r->filled('course_id')) {
            return (int) $r->course_id;
        }

        // 2) If course_uuid provided, resolve to id
        $uuid = trim((string) $r->input('course_uuid', ''));
        if ($uuid !== '') {
            if (!Str::isUuid($uuid)) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Invalid course_uuid',
                    'course_uuid' => $uuid,
                ], 422));
            }

            $course = DB::table('courses')
                ->whereNull('deleted_at')
                ->where('uuid', $uuid)
                ->first();

            if (!$course) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Course not found for course_uuid',
                    'course_uuid' => $uuid,
                ], 404));
            }

            return (int) $course->id;
        }

        // 3) Auto-detect from course_module_ids (useful for ASSIGN)
        $cmIds = $r->input('course_module_ids', []);
        if (is_array($cmIds) && count($cmIds) > 0) {
            $cmIds = array_values(array_unique(array_map('intval', $cmIds)));

            $courseIds = DB::table('course_modules')
                ->whereNull('deleted_at')
                ->whereIn('id', $cmIds)
                ->pluck('course_id')
                ->unique()
                ->values()
                ->all();

            if (count($courseIds) === 1) {
                return (int) $courseIds[0];
            }

            if (count($courseIds) > 1) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Provided course_module_ids belong to multiple courses. Please pass course_id or course_uuid.',
                    'course_ids_found' => $courseIds,
                ], 422));
            }
        }

        // 4) Required but still not found
        if ($required) {
            abort(response()->json([
                'success' => false,
                'message' => 'course_id or course_uuid is required (or provide course_module_ids to auto-detect)',
            ], 422));
        }

        return null;
    }
private function bcmOrderColumn(): string
{
    static $col = null;
    if ($col) return $col;

    if (Schema::hasColumn('batch_course_module', 'order_no')) return $col = 'order_no';
    if (Schema::hasColumn('batch_course_module', 'display_order')) return $col = 'display_order';

    // fallback (won't error if you keep using the returned name consistently)
    return $col = 'order_no';
}

private function cmOrderColumn(): string
{
    static $col = null;
    if ($col) return $col;

    if (Schema::hasColumn('course_modules', 'order_no')) return $col = 'order_no';
    if (Schema::hasColumn('course_modules', 'display_order')) return $col = 'display_order';

    return $col = 'order_no';
}

    private function actor(Request $r): array
    {
        $role = strtolower((string) ($r->attributes->get('auth_role') ?? $r->user()?->role ?? ''));
        $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? $r->user()?->id ?? 0);
        return [$role, $uid];
    }

    private function assertManageAccess(Request $r): array
    {
        [$role, $uid] = $this->actor($r);
        if (!$role || !in_array($role, $this->ALLOWED_ROLES, true)) {
            abort(response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403));
        }
        return [$role, $uid];
    }

    /**
 * GET /api/batch-course-modules?batch_id=... OR batch_uuid=...
 */
public function index(Request $r)
{
    $this->assertManageAccess($r);

    $r->validate([
        'batch_id'     => ['nullable','integer','required_without:batch_uuid'],
        'batch_uuid'   => ['nullable','string','required_without:batch_id'],

        'course_id'    => ['nullable','integer'],
        'course_uuid'  => ['nullable','string'],

        'status'       => ['nullable', Rule::in(['draft','published','archived'])],
        'is_completed' => ['nullable', Rule::in([0,1,'0','1',true,false])],
        'q'            => ['nullable','string','max:200'],
    ]);

    $batchId  = $this->resolveBatchIdFromRequest($r);
    $courseId = $this->resolveCourseIdFromRequest($r, false);

    // ✅ Detect actual order columns (supports both order_no / display_order)
    $bcmOrder = $this->bcmOrderColumn(); // batch_course_module column
    $cmOrder  = $this->cmOrderColumn();  // course_modules column

    $q = DB::table('batch_course_module as bcm')
        ->leftJoin('course_modules as cm', function ($j) {
            $j->on('cm.id', '=', 'bcm.course_module_id')
              ->whereNull('cm.deleted_at');
        })
        ->whereNull('bcm.deleted_at')
        ->where('bcm.batch_id', $batchId);

    if ($courseId) {
        $q->where('bcm.course_id', $courseId);
    }

    if ($r->filled('status')) {
        $q->where('bcm.status', $r->status);
    }

    if ($r->filled('is_completed')) {
        $q->where('bcm.is_completed', (int) ((string)$r->is_completed === 'true'
            ? 1
            : (string)$r->is_completed
        ));
    }

    if ($r->filled('q')) {
        $q->where('bcm.title', 'like', '%' . trim($r->q) . '%');
    }

    // ✅ Always return a stable "display_order" key for UI
    $q->select('bcm.*', DB::raw("COALESCE(bcm.$bcmOrder, cm.$cmOrder, 0) as display_order"));

    // ✅ Numeric sort (prevents 1,10,2 issue)
    $items = $q->orderByRaw("COALESCE(bcm.$bcmOrder, cm.$cmOrder, 0) + 0 ASC")
        ->orderBy('bcm.id', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'count'   => $items->count(),
        'data'    => $items
    ]);
}


    /**
     * GET /api/batch-course-modules/{idOrUuid}
     */
    public function show(Request $r, string $idOrUuid)
    {
        $this->assertManageAccess($r);

        $row = $this->resolveBatchCourseModule($idOrUuid);

        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return response()->json(['success' => true, 'data' => $row]);
    }

    /**
     * ✅ FIXED: POST /api/batch-course-modules/assign
     * Now handles BOTH assign (assigned:true) and unassign (assigned:false)
     */
    public function assign(Request $r)
    {
        $this->assertManageAccess($r);
        [, $uid] = $this->actor($r);

        $reqId = (string) Str::uuid();
        $ip    = $r->ip();

        Log::info('BatchCourseModule.assign:start', [
            'req_id' => $reqId,
            'ip' => $ip,
            'actor_id' => $uid,
            'payload_keys' => array_keys($r->all() ?? []),
        ]);

        $payload = $r->validate([
            'batch_id'   => ['nullable','integer','required_without_all:batch_uuid,batch_key'],
            'batch_uuid' => ['nullable','string','required_without_all:batch_id,batch_key'],
            'batch_key'  => ['nullable','string','required_without_all:batch_id,batch_uuid'],

            'course_id'   => ['nullable','integer'],
            'course_uuid' => ['nullable','string'],

            // ✅ accept either array OR single OR uuid
            'course_module_ids'   => ['nullable','array','min:1','required_without_all:course_module_id,course_module_uuid'],
            'course_module_ids.*' => ['integer'],

            'course_module_id'    => ['nullable','integer','required_without_all:course_module_ids,course_module_uuid'],
            'course_module_uuid'  => ['nullable','string','required_without_all:course_module_ids,course_module_id'],

            'overwrite_existing'  => ['nullable', Rule::in([0,1,'0','1',true,false])],
            
            // ✅ NEW: accept 'assigned' to determine assign vs unassign
            'assigned'            => ['nullable', Rule::in([0,1,'0','1',true,false])],
            'is_assigned'         => ['nullable', Rule::in([0,1,'0','1',true,false])],
        ]);

        // -------- Normalize modules into $ids --------
        $ids = [];

        if (!empty($payload['course_module_ids'])) {
            $ids = $payload['course_module_ids'];
        } elseif (!empty($payload['course_module_id'])) {
            $ids = [(int)$payload['course_module_id']];
        } elseif (!empty($payload['course_module_uuid'])) {
            $mid = DB::table('course_modules')
                ->whereNull('deleted_at')
                ->where('uuid', $payload['course_module_uuid'])
                ->value('id');

            if (!$mid) {
                return response()->json([
                    'success' => false,
                    'message' => 'course_module_uuid not found',
                ], 422);
            }
            $ids = [(int)$mid];
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));

        $batchId  = $this->resolveBatchIdFromRequest($r);
        $courseId = $this->resolveCourseIdFromRequest($r, true);

        // ✅ NEW: Determine if we're assigning or unassigning
        $wantAssigned = true; // default to assign
        if (array_key_exists('assigned', $payload)) {
            $wantAssigned = (bool) ((string)$payload['assigned'] === 'true' ? 1 : (int)$payload['assigned']);
        } elseif (array_key_exists('is_assigned', $payload)) {
            $wantAssigned = (bool) ((string)$payload['is_assigned'] === 'true' ? 1 : (int)$payload['is_assigned']);
        }

        $overwrite = (int) ((string)($payload['overwrite_existing'] ?? 0) === 'true'
            ? 1
            : ($payload['overwrite_existing'] ?? 0));

        Log::info('BatchCourseModule.assign:resolved', [
            'req_id' => $reqId,
            'batch_id' => $batchId,
            'course_id' => $courseId,
            'want_assigned' => $wantAssigned,
            'overwrite' => $overwrite,
            'module_ids_count' => count($ids),
            'module_ids' => $ids,
        ]);

        // ✅ NEW: If unassigning, just soft-delete and return
        if (!$wantAssigned) {
            return $this->unassignModules($reqId, $batchId, $ids);
        }

        // -------- ASSIGN LOGIC (existing code continues) --------
        $modules = DB::table('course_modules')
            ->whereNull('deleted_at')
            ->where('course_id', $courseId)
            ->whereIn('id', $ids)
            ->get();

        Log::info('BatchCourseModule.assign:modules_fetched', [
            'req_id' => $reqId,
            'fetched_count' => $modules->count(),
            'expected_count' => count($ids),
        ]);

        if ($modules->count() !== count($ids)) {
            $found   = $modules->pluck('id')->all();
            $missing = array_values(array_diff($ids, $found));

            Log::warning('BatchCourseModule.assign:missing_modules', [
                'req_id' => $reqId,
                'batch_id' => $batchId,
                'course_id' => $courseId,
                'missing_course_module_ids' => $missing,
                'found_course_module_ids' => $found,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Some course_module_ids not found for this course_id',
                'missing_course_module_ids' => $missing,
            ], 422);
        }

        $now = now();

        DB::beginTransaction();
        try {
            Log::info('BatchCourseModule.assign:tx_begin', [
                'req_id' => $reqId,
                'batch_id' => $batchId,
                'course_id' => $courseId,
            ]);

            $inserted = [];
            $skipped  = [];
            $overwritten = [];

            foreach ($modules as $m) {
                $mid = (int) $m->id;

                if ($overwrite) {
                    $affected = DB::table('batch_course_module')
                        ->whereNull('deleted_at')
                        ->where('batch_id', $batchId)
                        ->where('course_module_id', $mid)
                        ->update(['deleted_at' => $now, 'updated_at' => $now]);

                    if ($affected > 0) {
                        $overwritten[] = $mid;
                        Log::info('BatchCourseModule.assign:overwrite_soft_deleted', [
                            'req_id' => $reqId,
                            'batch_id' => $batchId,
                            'course_module_id' => $mid,
                            'affected_rows' => $affected,
                        ]);
                    }
                }

                $exists = DB::table('batch_course_module')
                    ->whereNull('deleted_at')
                    ->where('batch_id', $batchId)
                    ->where('course_module_id', $mid)
                    ->exists();

                if ($exists) {
                    $skipped[] = $mid;
                    Log::info('BatchCourseModule.assign:skip_exists', [
                        'req_id' => $reqId,
                        'batch_id' => $batchId,
                        'course_module_id' => $mid,
                    ]);
                    continue;
                }

                DB::table('batch_course_module')->insert([
                    'uuid'              => (string) Str::uuid(),
                    'batch_id'          => $batchId,
                    'course_module_id'  => $mid,
                    'course_id'         => (int)$m->course_id,

                    'is_completed'      => 0,

                    'title'             => (string) $m->title,
                    'short_description' => $m->short_description,
                    'long_description'  => $m->long_description,
                    'order_no'          => (int) ($m->order_no ?? 0),
                    'status'            => (string) ($m->status ?? 'draft'),
                    'metadata'          => $m->metadata,

                    'created_by'        => $uid ?: null,
                    'created_at_ip'     => $ip,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                $inserted[] = $mid;

                Log::info('BatchCourseModule.assign:inserted', [
                    'req_id' => $reqId,
                    'batch_id' => $batchId,
                    'course_module_id' => $mid,
                    'title' => (string) $m->title,
                    'order_no' => (int) ($m->order_no ?? 0),
                    'status' => (string) ($m->status ?? 'draft'),
                ]);
            }

            DB::commit();

            Log::info('BatchCourseModule.assign:committed', [
                'req_id' => $reqId,
                'batch_id' => $batchId,
                'course_id' => $courseId,
                'inserted_count' => count($inserted),
                'skipped_count' => count($skipped),
                'overwritten_count' => count($overwritten),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modules assigned to batch',
                'batch_id' => $batchId,
                'course_id' => $courseId,
                'inserted_course_module_ids' => $inserted,
                'skipped_course_module_ids'  => $skipped,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BatchCourseModule.assign:failed', [
                'req_id' => $reqId,
                'batch_id' => $batchId ?? null,
                'course_id' => $courseId ?? null,
                'overwrite' => $overwrite ?? null,
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to assign modules'], 500);
        }
    }

    /**
     * ✅ NEW: Helper method to unassign (soft-delete) modules
     */
    private function unassignModules(string $reqId, int $batchId, array $moduleIds): \Illuminate\Http\JsonResponse
    {
        $now = now();
        
        DB::beginTransaction();
        try {
            $affected = DB::table('batch_course_module')
                ->whereNull('deleted_at')
                ->where('batch_id', $batchId)
                ->whereIn('course_module_id', $moduleIds)
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::commit();

            Log::info('BatchCourseModule.unassign:success', [
                'req_id' => $reqId,
                'batch_id' => $batchId,
                'module_ids' => $moduleIds,
                'affected_rows' => $affected,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modules unassigned from batch',
                'batch_id' => $batchId,
                'unassigned_count' => $affected,
                'module_ids' => $moduleIds,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BatchCourseModule.unassign:failed', [
                'req_id' => $reqId,
                'batch_id' => $batchId,
                'module_ids' => $moduleIds,
                'err' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to unassign modules'], 500);
        }
    }
/**
 * PUT /api/batch-course-modules/{idOrUuid}
 */
public function update(Request $r, string $idOrUuid)
{
    $this->assertManageAccess($r);

    $data = $r->validate([
        'title'             => ['nullable','string','max:255'],
        'short_description' => ['nullable','string'],
        'long_description'  => ['nullable','string'],

        // ✅ accept all possible order keys coming from frontend
        'display_order'     => ['nullable','integer','min:1'],
        'order_no'          => ['nullable','integer','min:1'],
        'order'             => ['nullable','integer','min:1'],
        'position'          => ['nullable','integer','min:1'],

        'status'            => ['nullable', Rule::in(['draft','published','archived'])],
        'metadata'          => ['nullable'],
        'is_completed'      => ['nullable', Rule::in([0,1,'0','1',true,false,'true','false'])],
    ]);

    // metadata normalization
    if (array_key_exists('metadata', $data)) {
        if (is_array($data['metadata'])) $data['metadata'] = json_encode($data['metadata']);
        elseif ($data['metadata'] === null) $data['metadata'] = null;
        else $data['metadata'] = (string) $data['metadata'];
    }

    // ✅ safer boolean normalization
    if (array_key_exists('is_completed', $data)) {
        $data['is_completed'] = (int) filter_var($data['is_completed'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?: 0;
    }

    // ✅ normalize incoming order value (support multiple names)
    $incomingOrder =
        $r->input('display_order') ??
        $r->input('order_no') ??
        $r->input('order') ??
        $r->input('position');

    // ✅ map to real DB column (display_order OR order_no) depending on your schema
    if ($incomingOrder !== null && $incomingOrder !== '') {
        $incomingOrder = (int) $incomingOrder;

        if (\Illuminate\Support\Facades\Schema::hasColumn('batch_course_module', 'display_order')) {
            $data['display_order'] = $incomingOrder;
            unset($data['order_no'], $data['order'], $data['position']);
        } else {
            // fallback for your current schema
            $data['order_no'] = $incomingOrder;
            unset($data['display_order'], $data['order'], $data['position']);
        }
    } else {
        // avoid trying to update unknown columns
        unset($data['display_order'], $data['order'], $data['position']);
    }

    $row = $this->resolveBatchCourseModule($idOrUuid);
    if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

    $data['updated_at'] = now();

    DB::table('batch_course_module')->where('id', (int) $row->id)->update($data);

    $updated = DB::table('batch_course_module')->where('id', (int) $row->id)->first();

    return response()->json(['success' => true, 'message' => 'Batch module updated', 'data' => $updated]);
}


    /**
     * PATCH /api/batch-course-modules/{idOrUuid}/toggle-completed
     */
    public function toggleCompleted(Request $r, string $idOrUuid)
    {
        $this->assertManageAccess($r);

        $row = $this->resolveBatchCourseModule($idOrUuid);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        // Toggle the current state
        $newState = $row->is_completed ? 0 : 1;

        DB::table('batch_course_module')->where('id', (int)$row->id)->update([
            'is_completed' => $newState,
            'updated_at'   => now(),
        ]);

        $updated = DB::table('batch_course_module')->where('id', (int)$row->id)->first();

        return response()->json(['success' => true, 'message' => 'Completion updated', 'data' => $updated]);
    }

    /**
     * DELETE /api/batch-course-modules/{idOrUuid}
     */
    public function destroy(Request $r, string $idOrUuid)
    {
        $this->assertManageAccess($r);

        $row = $this->resolveBatchCourseModule($idOrUuid);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        DB::table('batch_course_module')->where('id', (int)$row->id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Deleted']);
    }

    // =========================================================
    // Settings APIs (same controller)
    // =========================================================

    /**
     * GET /api/batch-course-modules/{idOrUuid}/settings
     */
   /**
 * GET /api/batch-course-modules/{batch_uuid}/settings?course_id=.. OR course_uuid=..
 * ✅ Aligns with migration: one row per (batch_id, course_id)
 */
public function getSettings(Request $r, string $batchUuid)
{
    $this->assertManageAccess($r);

    if (!Str::isUuid($batchUuid)) {
        return response()->json(['success' => false, 'message' => 'Invalid batch uuid'], 422);
    }

    $r->validate([
        'course_id'   => ['nullable','integer','required_without:course_uuid'],
        'course_uuid' => ['nullable','string','required_without:course_id'],
    ]);

    $batch = DB::table('batches')
        ->whereNull('deleted_at')
        ->where('uuid', $batchUuid)
        ->first();

    if (!$batch) {
        return response()->json(['success' => false, 'message' => 'Batch not found'], 404);
    }

    $courseId = $this->resolveCourseIdFromRequest($r, true);

    $row = DB::table('batch_course_module_settings')
        ->where('batch_id', (int)$batch->id)
        ->where('course_id', (int)$courseId)
        ->first();

    // normalize response using a NEW helper (defined below)
    return response()->json([
        'success'    => true,
        'batch_uuid' => $batchUuid,
        'batch_id'   => (int)$batch->id,
        'course_id'  => (int)$courseId,
        'data'       => $row ? $this->normalizeBatchCourseSettingsRow($row) : null,
    ]);
}
private function normalizeBatchCourseSettingsRow($row): array
{
    $settings = [];
    try { $settings = $row->settings_json ? json_decode($row->settings_json, true) : []; }
    catch (\Throwable $e) { $settings = []; }

    $defaults = [
        'previous_module_completed' => 0,
        'assignment_submitted'      => 0,
        'exam_submitted'            => 0,
        'coding_test_submitted'     => 0,
    ];

    $settings = is_array($settings) ? $settings : [];
    $settings = array_merge($defaults, array_intersect_key($settings, $defaults));
    $settings = array_map(fn($v) => (int)$v, $settings);

    return [
        'id'        => (int)$row->id,
        'batch_id'  => (int)$row->batch_id,
        'course_id' => (int)$row->course_id,
        'settings'  => $settings,
        'created_by'=> $row->created_by ? (int)$row->created_by : null,
        'updated_by'=> $row->updated_by ? (int)$row->updated_by : null,
        'created_at'=> (string)$row->created_at,
        'updated_at'=> (string)$row->updated_at,
    ];
}

    /**
     * POST /api/batch-course-modules/{idOrUuid}/settings
     */
   /**
 * POST /api/batch-course-modules/{batch_uuid}/settings
 * Body: { settings_json: {...}, course_id/course_uuid }
 * ✅ Saves ONE row for (batch_id, course_id) per migration
 */
public function upsertSettings(Request $r, string $batchUuid)
{
    $this->assertManageAccess($r);
    [, $uid] = $this->actor($r);

    if (!Str::isUuid($batchUuid)) {
        return response()->json(['success' => false, 'message' => 'Invalid batch uuid'], 422);
    }

    $payload = $r->validate([
        'course_id'     => ['nullable','integer','required_without:course_uuid'],
        'course_uuid'   => ['nullable','string','required_without:course_id'],
        'settings_json' => ['required'],
    ]);

    $batch = DB::table('batches')
        ->whereNull('deleted_at')
        ->where('uuid', $batchUuid)
        ->first();

    if (!$batch) {
        return response()->json(['success' => false, 'message' => 'Batch not found'], 404);
    }

    $courseId = $this->resolveCourseIdFromRequest($r, true);
    $settings = $this->normalizeIncomingSettings($payload['settings_json']);

    DB::beginTransaction();
    try {
        $now = now();

        // lock row if exists
        $existing = DB::table('batch_course_module_settings')
            ->where('batch_id', (int)$batch->id)
            ->where('course_id', (int)$courseId)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            DB::table('batch_course_module_settings')
                ->where('id', (int)$existing->id)
                ->update([
                    'settings_json' => json_encode($settings),
                    'updated_by'    => $uid ?: null,
                    'updated_at'    => $now,
                ]);

            $saved = DB::table('batch_course_module_settings')->where('id', (int)$existing->id)->first();
        } else {
            $id = DB::table('batch_course_module_settings')->insertGetId([
                'batch_id'      => (int)$batch->id,
                'course_id'     => (int)$courseId,
                'settings_json' => json_encode($settings),
                'created_by'    => $uid ?: null,
                'updated_by'    => $uid ?: null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            $saved = DB::table('batch_course_module_settings')->where('id', (int)$id)->first();
        }

        DB::commit();

        return response()->json([
            'success'    => true,
            'message'    => 'Settings saved (applies to all batch modules under this batch + course)',
            'batch_uuid' => $batchUuid,
            'batch_id'   => (int)$batch->id,
            'course_id'  => (int)$courseId,
            'data'       => $this->normalizeBatchCourseSettingsRow($saved),
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('BatchCourseModule settings upsert failed', ['err' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Failed to save settings'], 500);
    }
}

private function resolveBatchRow(Request $r): object
{
    // If batch_id provided
    if ($r->filled('batch_id')) {
        $batch = DB::table('batches')->whereNull('deleted_at')->where('id', (int)$r->batch_id)->first();
        if ($batch) return $batch;

        abort(response()->json(['success'=>false,'message'=>'Batch not found for batch_id'], 404));
    }

    // Else batch_uuid required
    $uuid = trim((string)$r->input('batch_uuid',''));
    if (!$uuid || !Str::isUuid($uuid)) {
        abort(response()->json(['success'=>false,'message'=>'batch_uuid is required (valid UUID)'], 422));
    }

    $batch = DB::table('batches')->whereNull('deleted_at')->where('uuid', $uuid)->first();
    if (!$batch) {
        abort(response()->json(['success'=>false,'message'=>'Batch not found for batch_uuid','batch_uuid'=>$uuid], 404));
    }
    return $batch;
}

/**
 * Batch-wide settings row (applies to ALL modules under batch)
 * Sentinel: batch_course_module_id = 0
 * If course_id exists, we prefer course-specific settings for that batch.
 */
private function findBatchWideSettingsRow(int $batchId, ?int $courseId = null)
{
    $q = DB::table('batch_course_module_settings')->where('batch_id', $batchId);

    // Prefer course-specific override if courseId given
    if ($courseId) {
        $row = (clone $q)->where('course_id', $courseId)->where('batch_course_module_id', 0)->first();
        if ($row) return $row;
    }

    // Fallback to batch-only
    return $q->where('batch_course_module_id', 0)->first();
}

    /**
 * GET /api/batch-course-modules/unlock-status?batch_uuid=..&course_id=..(optional)
 * ✅ Settings come from batch_course_module_settings (batch_id + course_id)
 */
public function unlockStatus(Request $r)
{
    $this->assertManageAccess($r);

    $r->validate([
        'batch_uuid'  => ['required','string'],
        'course_id'   => ['nullable','integer'],
        'course_uuid' => ['nullable','string'],
    ]);

    $batchId  = $this->resolveBatchIdFromRequest($r);
    $courseId = $this->resolveCourseIdFromRequest($r, false);

    $modsQ = DB::table('batch_course_module')
        ->whereNull('deleted_at')
        ->where('batch_id', $batchId);

    if ($courseId) $modsQ->where('course_id', $courseId);

    $mods = $modsQ->orderBy('order_no')->orderBy('id')->get();

    if ($mods->isEmpty()) {
        return response()->json(['success' => true, 'count' => 0, 'data' => []]);
    }

    // ✅ Fetch settings rows for all involved course_ids (batch + course)
    $courseIdsInMods = $mods->pluck('course_id')->unique()->values()->all();

    $settingsRows = DB::table('batch_course_module_settings')
        ->where('batch_id', $batchId)
        ->whereIn('course_id', $courseIdsInMods)
        ->get()
        ->keyBy('course_id'); // ✅ keyed by course_id

    $defaultRules = [
        'previous_module_completed' => 0,
        'assignment_submitted'      => 0,
        'exam_submitted'            => 0,
        'coding_test_submitted'     => 0,
    ];

    $out = [];
    $prevCompleted = true;

    foreach ($mods as $m) {
        $srow = $settingsRows->get((int)$m->course_id);

        $rules = $defaultRules;
        if ($srow) {
            $rules = $this->normalizeBatchCourseSettingsRow($srow)['settings'];
        }

        $needPrev = (int)$rules['previous_module_completed'] === 1;
        $passPrev = !$needPrev || $prevCompleted;

        $meta = [];
        try {
            if ($m->metadata) $meta = is_array($m->metadata) ? $m->metadata : (json_decode($m->metadata, true) ?: []);
        } catch (\Throwable $e) { $meta = []; }

        $needAssign = (int)$rules['assignment_submitted'] === 1;
        $needExam   = (int)$rules['exam_submitted'] === 1;
        $needCode   = (int)$rules['coding_test_submitted'] === 1;

        $passAssign = !$needAssign || (int)($meta['assignment_submitted'] ?? 0) === 1;
        $passExam   = !$needExam   || (int)($meta['exam_submitted'] ?? 0) === 1;
        $passCode   = !$needCode   || (int)($meta['coding_test_submitted'] ?? 0) === 1;

        $isUnlocked = $passPrev && $passAssign && $passExam && $passCode;

        $out[] = [
            'id'               => (int)$m->id,
            'uuid'             => (string)$m->uuid,
            'batch_id'         => (int)$m->batch_id,
            'course_id'        => (int)$m->course_id,
            'course_module_id' => (int)$m->course_module_id,
            'title'            => (string)$m->title,
            'order_no'         => (int)$m->order_no,
            'status'           => (string)$m->status,
            'is_completed'     => (int)$m->is_completed,
            'rules'            => $rules,
            'is_unlocked'      => (int)$isUnlocked,
        ];

        $prevCompleted = ((int)$m->is_completed) === 1;
    }

    return response()->json(['success' => true, 'count' => count($out), 'data' => $out]);
}

    // =========================================================
    // Helpers
    // =========================================================

    private function resolveBatchCourseModule(string $idOrUuid)
    {
        $q = DB::table('batch_course_module')->whereNull('deleted_at');
        if (ctype_digit($idOrUuid)) $q->where('id', (int)$idOrUuid);
        else $q->where('uuid', $idOrUuid);
        return $q->first();
    }

    private function normalizeIncomingSettings($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        } elseif (is_object($raw)) {
            $raw = json_decode(json_encode($raw), true);
        }

        $raw = is_array($raw) ? $raw : [];

        $map = [
            'previous_module_completed' => ['previous_module_completed', 'Previous module completed', 'previousModuleCompleted'],
            'assignment_submitted'      => ['assignment_submitted', 'Assignment submited', 'assignmentSubmitted'],
            'exam_submitted'            => ['exam_submitted', 'Exam submited', 'examSubmitted'],
            'coding_test_submitted'     => ['coding_test_submitted', 'Coding test submited', 'codingTestSubmitted'],
        ];

        $out = [];
        foreach ($map as $k => $aliases) {
            $val = 0;
            foreach ($aliases as $a) {
                if (array_key_exists($a, $raw)) { $val = $raw[$a]; break; }
            }
            $out[$k] = (int) (((string)$val === 'true') ? 1 : (int)$val) ? 1 : 0;
        }

        return $out;
    }

    private function normalizeSettingsRow($row): array
    {
        $settings = [];
        try { $settings = $row->settings_json ? json_decode($row->settings_json, true) : []; }
        catch (\Throwable $e) { $settings = []; }

        $defaults = [
            'previous_module_completed' => 0,
            'assignment_submitted'      => 0,
            'exam_submitted'            => 0,
            'coding_test_submitted'     => 0,
        ];

        $settings = is_array($settings) ? $settings : [];
        $settings = array_merge($defaults, array_intersect_key($settings, $defaults));
        $settings = array_map(fn($v) => (int)$v, $settings);

        return [
            'id'                     => (int)$row->id,
            'batch_id'               => (int)$row->batch_id,
            'course_id'              => (int)$row->course_id,
            'batch_course_module_id' => (int)$row->batch_course_module_id,
            'settings'               => $settings,
            'created_by'             => $row->created_by ? (int)$row->created_by : null,
            'updated_by'             => $row->updated_by ? (int)$row->updated_by : null,
            'created_at'             => (string)$row->created_at,
            'updated_at'             => (string)$row->updated_at,
        ];
    }
}