<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class CourseModuleController extends Controller
{
    /** Whitelisted sortable fields */
    private const SORTABLE = ['created_at','title','order_no','status'];

    /** Allowed statuses */
    private const STATUSES = ['draft','published','archived'];

    public function __construct() {}
private function batchesQuery()
{
    $q = DB::table('batches');

    // soft delete safe (only apply if column exists)
    if (Schema::hasColumn('batches', 'deleted_at')) {
        $q->whereNull('deleted_at');
    }

    return $q;
}

/**
 * Resolve batch id from: batch_id | batch_uuid | batch_key
 * Throws 422 if a batch identifier is provided but not found.
 */
private function resolveBatchIdFromRequest(Request $r): int
{
    if ($r->filled('batch_id')) {
        return (int) $r->input('batch_id');
    }

    if ($r->filled('batch_uuid')) {
        $id = $this->batchesQuery()
            ->where('uuid', (string) $r->input('batch_uuid'))
            ->value('id');

        if (!$id) {
            throw ValidationException::withMessages([
                'batch_uuid' => ['Batch not found for the given batch_uuid.'],
            ]);
        }
        return (int) $id;
    }

    if ($r->filled('batch_key')) {
        $key = (string) $r->input('batch_key');

        // Try common possible column names for "batch_key"
        $possibleCols = ['batch_key', 'key', 'code', 'slug', 'batch_code'];
        $col = null;
        foreach ($possibleCols as $c) {
            if (Schema::hasColumn('batches', $c)) { $col = $c; break; }
        }

        if (!$col) {
            throw ValidationException::withMessages([
                'batch_key' => ['No batch key column found in batches table (expected batch_key/key/code/slug/batch_code).'],
            ]);
        }

        $id = $this->batchesQuery()
            ->where($col, $key)
            ->value('id');

        if (!$id) {
            throw ValidationException::withMessages([
                'batch_key' => ["Batch not found for the given batch_key (matched on column: {$col})."],
            ]);
        }
        return (int) $id;
    }

    throw ValidationException::withMessages([
        'batch' => ['Provide batch_id or batch_uuid or batch_key.'],
    ]);
}

    /* =========================================================
     |                       LIST (active)
     |  GET /api/course-modules
     |  ?course_id&status&q&sort=-created_at&page=1&per_page=20
     |  NOTE: by default this now EXCLUDES archived items unless
     |        status=archived is explicitly requested.
     |=========================================================*/
    public function index(Request $r)
{
    // if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

    $q        = trim((string) $r->query('q', ''));
    $status   = $r->query('status'); // if null => exclude archived
    $courseId = $r->query('course_id');

    $page    = max(1, (int) $r->query('page', 1));
    $perPage = min(100, max(1, (int) $r->query('per_page', 20)));

    // ✅ Default: oldest -> newest
    $sort = (string) $r->query('sort', 'created_at');

    $dir = \Illuminate\Support\Str::startsWith($sort, '-') ? 'desc' : 'asc';
    $col = ltrim($sort, '-');
    if (!in_array($col, self::SORTABLE, true)) $col = 'created_at';

    // ✅ OPTIONAL: if batch is provided, include assignment info in response
    $batchId = null;
    if ($r->filled('batch_id') || $r->filled('batch_uuid') || $r->filled('batch_key')) {
        // works with query params too (Laravel input() merges query/body)
        $batchId = $this->resolveBatchIdFromRequest($r);
    }

    $builder = DB::table('course_modules as cm')
        ->whereNull('cm.deleted_at');

    if ($courseId !== null && $courseId !== '') {
        $builder->where('cm.course_id', (int) $courseId);
    }

    if ($status && in_array($status, self::STATUSES, true)) {
        $builder->where('cm.status', $status);
    } else {
        // Default: hide archived from normal list
        $builder->where('cm.status', '!=', 'archived');
    }

    if ($q !== '') {
        $builder->where(function ($qb) use ($q) {
            $qb->where('cm.title', 'like', '%' . $q . '%')
               ->orWhere('cm.short_description', 'like', '%' . $q . '%')
               ->orWhere('cm.long_description', 'like', '%' . $q . '%');
        });
    }

    /**
     * ✅ If batchId is provided, add:
     * - assigned (0/1)
     * - batch_course_module_id / uuid
     *
     * Using a subquery (MAX(id) per course_module_id) to avoid duplicate rows
     * if your bcm table ever has multiple active rows for same module+batch.
     */
    if ($batchId) {
        $bcmPick = DB::table('batch_course_module')
            ->selectRaw('MAX(id) as id, course_module_id')
            ->whereNull('deleted_at')
            ->where('batch_id', $batchId)
            ->groupBy('course_module_id');

        $builder
            ->leftJoinSub($bcmPick, 'bcmx', function ($j) {
                $j->on('bcmx.course_module_id', '=', 'cm.id');
            })
            ->leftJoin('batch_course_module as bcm', 'bcm.id', '=', 'bcmx.id')
            ->select('cm.*')
            ->addSelect([
                DB::raw('IF(bcm.id IS NULL, 0, 1) as assigned'),
                DB::raw('bcm.id as batch_course_module_id'),
                DB::raw('bcm.uuid as batch_course_module_uuid'),
            ]);
    } else {
        $builder->select('cm.*');
    }

    // ✅ total count (safe even with joins)
    $total = (clone $builder)->distinct('cm.id')->count('cm.id');

    // ✅ Tie-breaker aligned with direction (oldest->newest => id asc)
    $rows = $builder
        ->orderBy('cm.' . $col, $dir)
        ->orderBy('cm.id', $dir === 'asc' ? 'asc' : 'desc')
        ->forPage($page, $perPage)
        ->get();

    return response()->json([
        'data'     => $rows,
        'page'     => $page,
        'per_page' => $perPage,
        'total'    => $total,
    ]);
}


    /* =========================================================
     |                    BIN (soft-deleted only)
     |  GET /api/course-modules/bin
     |  Same query params as index (course_id, q, sort, page…)
     |=========================================================*/
    public function bin(Request $r)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $q         = trim((string)$r->query('q', ''));
        $courseId  = $r->query('course_id');
        $page      = max(1, (int)$r->query('page', 1));
        $perPage   = min(100, max(1, (int)$r->query('per_page', 20)));
        $sort      = (string)$r->query('sort', '-created_at');

        $dir = Str::startsWith($sort, '-') ? 'desc' : 'asc';
        $col = ltrim($sort, '-');
        if (!in_array($col, self::SORTABLE, true)) $col = 'created_at';

        $builder = DB::table('course_modules')->whereNotNull('deleted_at');

        if ($courseId !== null && $courseId !== '') {
            $builder->where('course_id', (int)$courseId);
        }

        if ($q !== '') {
            $builder->where(function ($qb) use ($q) {
                $qb->where('title', 'like', '%' . $q . '%')
                   ->orWhere('short_description', 'like', '%' . $q . '%')
                   ->orWhere('long_description', 'like', '%' . $q . '%');
            });
        }

        $total = (clone $builder)->count();
        $rows  = $builder->orderBy($col, $dir)->orderBy('id', 'desc')
            ->forPage($page, $perPage)->get();

        return response()->json([
            'data'     => $rows,
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $total,
        ]);
    }

    /* =========================================================
     |                       SHOW
     |  GET /api/course-modules/{id|uuid}
     |=========================================================*/
    public function show(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $row = $this->findModule($idOrUuid, false);
        if (!$row) {
            return response()->json(['message' => 'Course module not found'], 404);
        }
        return response()->json(['data' => $row]);
    }

    /* =========================================================
     |                       CREATE  (UNCHANGED)
     |  POST /api/course-modules
     |=========================================================*/
    public function store(Request $r)
    {
        // unchanged from your current implementation
        if ($resp = $this->requireRole($r, ['admin','superadmin'])) return $resp;

        $rules = [
            'course_id'         => [
                'required','integer','min:1',
                Rule::exists('courses','id')->whereNull('deleted_at'),
            ],
            'title'             => ['required','string','max:255'],
            'short_description' => ['nullable','string'],
            'long_description'  => ['nullable','string'],
            'order_no'          => ['nullable','integer','min:0'],
            'status'            => ['nullable', Rule::in(self::STATUSES)],
            'metadata'          => ['nullable'],
        ];

        $data = $r->all();
        $validated = validator($data, $rules)->validate();

        $uuid      = (string) Str::uuid();
        $now       = now();
        $actor     = $this->actor($r);
        $metadata  = $this->normalizeJson($validated['metadata'] ?? null);
        $orderNo   = (int)($validated['order_no'] ?? 0);
        $status    = (string)($validated['status'] ?? 'draft');

        try {
            DB::beginTransaction();

            $id = DB::table('course_modules')->insertGetId([
                'uuid'              => $uuid,
                'course_id'         => (int)$validated['course_id'],
                'title'             => (string)$validated['title'],
                'short_description' => $validated['short_description'] ?? null,
                'long_description'  => $validated['long_description'] ?? null,
                'order_no'          => $orderNo,
                'status'            => $status,
                'metadata'          => $metadata,
                'created_by'        => $actor['id'],
                'created_at_ip'     => $r->ip(),
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $row = DB::table('course_modules')->where('id', $id)->first();
            $this->logActivity($actor, 'store', 'course_modules', $id, null, $row);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.store failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to create course module'], 500);
        }

        return response()->json(['data' => $row], 201);
    }

    /* =========================================================
     |                       UPDATE
     |  PUT/PATCH /api/course-modules/{id|uuid}
     |=========================================================*/
    public function update(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $row = $this->findModule($idOrUuid, false);
        if (!$row) return response()->json(['message' => 'Course module not found'], 404);

        $rules = [
            'course_id'         => [
                'sometimes','integer','min:1',
                Rule::exists('courses','id')->whereNull('deleted_at'),
            ],
            'title'             => ['sometimes','string','max:255'],
            'short_description' => ['sometimes','nullable','string'],
            'long_description'  => ['sometimes','nullable','string'],
            'order_no'          => ['sometimes','integer','min:0'],
            'status'            => ['sometimes', Rule::in(self::STATUSES)],
            'metadata'          => ['sometimes','nullable'],
        ];

        $payload = validator($r->all(), $rules)->validate();

        $set = [];
        foreach (['course_id','title','short_description','long_description','order_no','status'] as $k) {
            if (array_key_exists($k, $payload)) $set[$k] = $payload[$k];
        }
        if (array_key_exists('metadata', $payload)) {
            $set['metadata'] = $this->normalizeJson($payload['metadata']);
        }
        if (empty($set)) {
            return response()->json(['data' => $row]);
        }

        $actor = $this->actor($r);
        $set['updated_at'] = now();

        try {
            DB::beginTransaction();

            DB::table('course_modules')->where('id', $row->id)->update($set);
            $newRow = DB::table('course_modules')->where('id', $row->id)->first();

            $this->logActivity($actor, 'update', 'course_modules', $row->id, $row, $newRow);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.update failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to update course module'], 500);
        }

        return response()->json(['data' => $newRow]);
    }

    /* =========================================================
     |                    ARCHIVE / UNARCHIVE
     |  POST /api/course-modules/{id|uuid}/archive
     |  POST /api/course-modules/{id|uuid}/unarchive
     |  (status flip only; NOT a delete)
     |=========================================================*/
    public function archive(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $row = $this->findModule($idOrUuid, false);
        if (!$row) return response()->json(['message' => 'Course module not found'], 404);

        if ($row->status === 'archived') {
            return response()->json(['message' => 'Already archived']);
        }

        $actor = $this->actor($r);
        try {
            DB::beginTransaction();
            DB::table('course_modules')->where('id', $row->id)->update([
                'status'     => 'archived',
                'updated_at' => now(),
            ]);
            $newRow = DB::table('course_modules')->where('id', $row->id)->first();
            $this->logActivity($actor, 'archive', 'course_modules', $row->id, $row, $newRow);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.archive failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to archive course module'], 500);
        }

        return response()->json(['data' => $newRow]);
    }

    public function unarchive(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $row = $this->findModule($idOrUuid, false);
        if (!$row) return response()->json(['message' => 'Course module not found'], 404);

        if ($row->status !== 'archived') {
            return response()->json(['message' => 'Module is not archived']);
        }

        $actor = $this->actor($r);
        try {
            DB::beginTransaction();
            // You can choose to restore to 'draft' (safer) or keep previous. We'll use 'draft'.
            DB::table('course_modules')->where('id', $row->id)->update([
                'status'     => 'draft',
                'updated_at' => now(),
            ]);
            $newRow = DB::table('course_modules')->where('id', $row->id)->first();
            $this->logActivity($actor, 'unarchive', 'course_modules', $row->id, $row, $newRow);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.unarchive failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to unarchive course module'], 500);
        }

        return response()->json(['data' => $newRow]);
    }

    /* =========================================================
     |                       DELETE (Soft)
     |  DELETE /api/course-modules/{id|uuid}
     |  NOTE: this is now a REAL "delete to bin":
     |        sets deleted_at but DOES NOT change status.
     |=========================================================*/
    public function destroy(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $row = $this->findModule($idOrUuid, false);
        if (!$row) return response()->json(['message' => 'Course module not found'], 404);

        $actor = $this->actor($r);

        try {
            DB::beginTransaction();

            DB::table('course_modules')
                ->where('id', $row->id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            $deleted = DB::table('course_modules')->where('id', $row->id)->first();

            $this->logActivity($actor, 'soft_delete', 'course_modules', $row->id, $row, $deleted);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.destroy failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to delete course module'], 500);
        }

        return response()->json(['message' => 'Course module moved to bin']);
    }

    /* =========================================================
     |                       RESTORE (from bin)
     |  POST /api/course-modules/{id|uuid}/restore
     |=========================================================*/
    public function restore(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $row = $this->findModule($idOrUuid, true); // include deleted
        if (!$row || $row->deleted_at === null) {
            return response()->json(['message' => 'Course module not found in bin'], 404);
        }

        $actor = $this->actor($r);
        try {
            DB::beginTransaction();
            DB::table('course_modules')->where('id', $row->id)->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);
            $newRow = DB::table('course_modules')->where('id', $row->id)->first();
            $this->logActivity($actor, 'restore', 'course_modules', $row->id, $row, $newRow);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.restore failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to restore course module'], 500);
        }

        return response()->json(['data' => $newRow]);
    }

    /* =========================================================
     |                       FORCE DELETE (permanent)
     |  DELETE /api/course-modules/{id|uuid}/force
     |=========================================================*/
    public function forceDestroy(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $row = $this->findModule($idOrUuid, true); // include deleted
        if (!$row) return response()->json(['message' => 'Course module not found'], 404);

        $actor = $this->actor($r);
        try {
            DB::beginTransaction();
            DB::table('course_modules')->where('id', $row->id)->delete();
            $this->logActivity($actor, 'force_delete', 'course_modules', $row->id, $row, null);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.forceDestroy failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to permanently delete module'], 500);
        }

        return response()->json(['message' => 'Course module permanently deleted']);
    }

    /* =========================================================
     |                       REORDER (unchanged)
     |  POST /api/course-modules/reorder
     |=========================================================*/
    public function reorder(Request $r)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin','superadmin'])) return $resp;

        $payload = validator($r->all(), [
            'course_id' => [
                'required','integer','min:1',
                Rule::exists('courses','id')->whereNull('deleted_at'),
            ],
            'ids'       => ['sometimes','array','min:1'],
            'ids.*'     => ['integer','min:1'],
            'orders'    => ['sometimes','array','min:1'],
        ])->validate();

        $courseId = (int)$payload['course_id'];

        if (!isset($payload['ids']) && !isset($payload['orders'])) {
            return response()->json(['message' => 'Provide ids[] or orders{}'], 422);
        }

        try {
            DB::beginTransaction();

            if (isset($payload['ids'])) {
                $pos = 1;
                foreach ($payload['ids'] as $id) {
                    DB::table('course_modules')
                        ->where('course_id', $courseId)
                        ->where('id', (int)$id)
                        ->whereNull('deleted_at')
                        ->update(['order_no' => $pos++, 'updated_at' => now()]);
                }
            } else {
                foreach ($payload['orders'] as $id => $position) {
                    DB::table('course_modules')
                        ->where('course_id', $courseId)
                        ->where('id', (int)$id)
                        ->whereNull('deleted_at')
                        ->update(['order_no' => (int)$position, 'updated_at' => now()]);
                }
            }

            $rows = DB::table('course_modules')
                ->where('course_id', $courseId)
                ->whereNull('deleted_at')
                ->orderBy('order_no')->orderBy('id')
                ->get();

            DB::commit();

            return response()->json(['data' => $rows]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.reorder failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to reorder modules'], 500);
        }
    }

    /* =========================================================
     |                       Helpers
     |=========================================================*/

private function findModule(string $idOrUuid, bool $withTrashed = false)
{
    $q = DB::table('course_modules');
    if (!$withTrashed) {
        $q->whereNull('deleted_at');
    }

    if (ctype_digit($idOrUuid)) {
        return $q->where('id', (int)$idOrUuid)->first();
    }
    return $q->where('uuid', $idOrUuid)->first();
}


    private function normalizeJson($val)
    {
        if ($val === null || $val === '') return null;
        if (is_array($val) || is_object($val)) return json_encode($val, JSON_UNESCAPED_UNICODE);
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        }
        return null;
    }

    private function actor(Request $r): array
    {
        $role  = (string) ($r->attributes->get('auth_role') ?? '');
        $id    = (int)    ($r->attributes->get('auth_tokenable_id') ?? 0);
        return ['id' => $id ?: null, 'role' => $role];
    }

    /** Accepts both 'super_admin' and 'superadmin' etc. */
    private function requireRole(Request $r, array $roles)
    {
        $role = (string) ($r->attributes->get('auth_role') ?? '');
        if (!$role) return response()->json(['message' => 'Forbidden'], 403);

        $canon = fn($s) => strtolower(preg_replace('/[^a-z]/','', $s));
        $have  = $canon($role);
        $allow = array_map($canon, $roles);

        if (!in_array($have, $allow, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return null;
    }

    private function logActivity(array $actor, string $action, string $table, $recordId, $old, $new): void
    {
        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $actor['id'],
                'performed_by_role' => $actor['role'],
                'action'            => $action,
                'table_name'        => $table,
                'record_id'         => (string)$recordId,
                'old_values'        => $old ? json_encode($old) : null,
                'new_values'        => $new ? json_encode($new) : null,
                'metadata'          => null,
                'created_at'        => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('activity_log insert failed (non-fatal)', ['e' => $e]);
        }
    }
    /**
 * GET /api/course-modules/my
 * Returns published course modules for the authenticated user
 * (via enrolled batches → courses)
 */
public function myModules(Request $r)
{
    // 🔐 Get logged-in user
    $actor = $this->actor($r);
    if (!$actor['id']) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        // ---- Step 1: Find course IDs via batch enrollment
        $courseIds = DB::table('batch_students as bs')
            ->join('batches as b', 'b.id', '=', 'bs.batch_id')
            ->where('bs.user_id', $actor['id'])
            ->whereNull('bs.deleted_at')
            ->whereNull('b.deleted_at')
            ->pluck('b.course_id')
            ->unique()
            ->values();

        if ($courseIds->isEmpty()) {
            return response()->json([
                'user_id' => $actor['id'],
                'data'    => [],
            ]);
        }

        // ---- Step 2: Fetch published modules for those courses
        $modules = DB::table('course_modules')
            ->whereIn('course_id', $courseIds)
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->orderBy('course_id')
            ->orderBy('order_no')
            ->get();

        return response()->json([
            'user_id' => $actor['id'],
            'total'   => $modules->count(),
            'data'    => $modules,
        ]);

    } catch (Throwable $e) {
        Log::error('course_modules.myModules failed', [
            'user_id' => $actor['id'],
            'error'   => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Failed to fetch course modules'
        ], 500);
    }
}

}
