<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BatchInstructorController extends Controller
{
    /* =========================================================
     |                       Helpers
     |=========================================================*/

    /** Auth helpers via CheckRole middleware-attached attributes */
    protected function authUserId(Request $request): ?int
    {
        return $request->attributes->get('auth_tokenable_id');
    }

    protected function now(): string
    {
        return Carbon::now()->toDateTimeString();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return response()->json(['message' => $message, 'data' => $data], $code);
    }

    protected function fail(array $errors, int $code = 422)
    {
        return response()->json(['errors' => $errors], $code);
    }

    /** Basic pagination parsing consistent with the rest of the app */
    protected function paging(Request $req): array
    {
        $perPage = (int) ($req->query('per_page', 20));
        $perPage = max(1, min(100, $perPage));
        $page    = (int) ($req->query('page', 1));
        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;

        // simple "-created_at" or "title" style
        $sortRaw = (string) $req->query('sort', '-created_at');
        $dir = 'asc';
        $col = $sortRaw;
        if (str_starts_with($sortRaw, '-')) {
            $dir = 'desc';
            $col = substr($sortRaw, 1);
        }
        $allowed = ['created_at','updated_at','assigned_at','unassigned_at','id'];
        if (!in_array($col, $allowed, true)) $col = 'created_at';

        return compact('perPage','page','offset','col','dir');
    }

    /** Activity log (optional—aligns with patterns used elsewhere) */
    protected function logActivity(Request $req, string $event, array $payload = []): void
    {
        try {
            DB::table('user_data_activity_log')->insert([
                'user_id'     => $this->authUserId($req),
                'event'       => $event,
                'payload'     => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'ip_address'  => $req->ip(),
                'created_at'  => $this->now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ActivityLog failed: '.$e->getMessage());
        }
    }

    /** Validate role & status against allowed sets */
    protected function validRoleInBatch(?string $role): bool
    {
        return in_array($role, ['instructor','tutor','TA','mentor'], true);
    }

    protected function validAssignStatus(?string $st): bool
    {
        return in_array($st, ['active','standby','replaced','removed'], true);
    }

    /* =========================================================
     |                      Endpoints
     |=========================================================*/

    /**
     * GET /api/batch-instructors
     * Filters: batch_id?, user_id?, assign_status?, role_in_batch?, q?
     * Pagination: per_page, page, sort (-created_at by default)
     */
    public function index(Request $request)
    {
        $pg = $this->paging($request);

        $q = DB::table('batch_instructors as bi')
            ->join('batches as b', 'b.id', '=', 'bi.batch_id')
            ->join('users as u', 'u.id', '=', 'bi.user_id')
            ->whereNull('bi.deleted_at')
            ->select([
                'bi.id','bi.uuid','bi.batch_id','bi.user_id','bi.role_in_batch','bi.assign_status',
                'bi.assigned_at','bi.unassigned_at','bi.created_by','bi.created_at','bi.updated_at',
                'b.title as batch_title','b.batch_code',
                'u.name as instructor_name','u.email as instructor_email','u.role as user_role',
            ]);

        if ($request->filled('batch_id')) $q->where('bi.batch_id', (int)$request->query('batch_id'));
        if ($request->filled('user_id'))  $q->where('bi.user_id',  (int)$request->query('user_id'));
        if ($request->filled('assign_status')) $q->where('bi.assign_status', $request->query('assign_status'));
        if ($request->filled('role_in_batch')) $q->where('bi.role_in_batch', $request->query('role_in_batch'));

        if ($request->filled('q')) {
            $term = '%'.trim((string)$request->query('q')).'%';
            $q->where(function($w) use ($term) {
                $w->where('b.title','like',$term)
                  ->orWhere('b.batch_code','like',$term)
                  ->orWhere('u.name','like',$term)
                  ->orWhere('u.email','like',$term);
            });
        }

        $total = (clone $q)->count();
        $rows  = $q->orderBy($pg['col'], $pg['dir'])
                   ->offset($pg['offset'])
                   ->limit($pg['perPage'])
                   ->get();

        return $this->ok([
            'items'     => $rows,
            'page'      => $pg['page'],
            'per_page'  => $pg['perPage'],
            'total'     => $total,
        ]);
    }

    /**
     * GET /api/batch-instructors/instructors-of-batch?batch_id=123
     * List assigned instructors for a single batch (for the batch detail page).
     */
    public function instructorsOfBatch(Request $request)
    {
        $v = Validator::make($request->all(), [
            'batch_id' => 'required|integer|exists:batches,id',
        ]);
        if ($v->fails()) return $this->fail($v->errors()->toArray());

        $rows = DB::table('batch_instructors as bi')
            ->join('users as u', 'u.id', '=', 'bi.user_id')
            ->where('bi.batch_id', (int)$request->batch_id)
            ->whereNull('bi.deleted_at')
            ->select([
                'bi.id','bi.uuid','bi.user_id','bi.role_in_batch','bi.assign_status',
                'bi.assigned_at','bi.unassigned_at','bi.created_at','bi.updated_at',
                'u.name','u.email','u.role','u.uuid as user_uuid',
            ])
            ->orderBy('bi.assigned_at','desc')
            ->get();

        return $this->ok($rows);
    }

    /**
     * GET /api/batch-instructors/batches-for-user?user_id=456
     * For your modal: list all batches with an `assigned` flag for this instructor.
     * Optional filter: q (search by title/code), status
     */
    public function batchesForUser(Request $request)
    {
        $v = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);
        if ($v->fails()) return $this->fail($v->errors()->toArray());

        $sub = DB::table('batch_instructors')
            ->where('user_id', (int)$request->user_id)
            ->whereNull('deleted_at')
            ->select('batch_id', 'role_in_batch', 'assign_status', 'assigned_at');

        $q = DB::table('batches as b')
            ->leftJoinSub($sub, 'bi', function($join){
                $join->on('bi.batch_id','=','b.id');
            })
            ->whereNull('b.deleted_at')
            ->select([
                'b.id as batch_id','b.uuid as batch_uuid','b.title','b.batch_code','b.status',
                DB::raw('CASE WHEN bi.batch_id IS NULL THEN 0 ELSE 1 END as assigned'),
                'bi.role_in_batch','bi.assign_status','bi.assigned_at',
            ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string)$request->query('q')).'%';
            $q->where(function($w) use ($term){
                $w->where('b.title','like',$term)->orWhere('b.batch_code','like',$term);
            });
        }
        if ($request->filled('status')) {
            $q->where('b.status', $request->query('status'));
        }

        $rows = $q->orderBy('b.starts_at','desc')->orderBy('b.created_at','desc')->get();

        return $this->ok($rows);
    }

    /**
     * POST /api/batch-instructors/toggle
     * Body: { batch_id:int, user_id:int, assigned:bool, role_in_batch?:string, assign_status?:string }
     * Idempotent: creates/restores on true; soft-deletes on false.
     */
    public function toggle(Request $request)
    {
        $payload = $request->all();
        $v = Validator::make($payload, [
            'batch_id'      => 'required|integer|exists:batches,id',
            'user_id'       => 'required|integer|exists:users,id',
            'assigned'      => 'required|boolean',
            'role_in_batch' => 'nullable|string|in:instructor,tutor,TA,mentor',
            'assign_status' => 'nullable|string|in:active,standby,replaced,removed',
        ]);
        if ($v->fails()) return $this->fail($v->errors()->toArray());

        $uid   = $this->authUserId($request);
        $now   = $this->now();
        $ip    = $request->ip();

        $role  = $payload['role_in_batch'] ?? 'instructor';
        $st    = $payload['assign_status'] ?? 'active';

        return DB::transaction(function () use ($request, $payload, $uid, $now, $ip, $role, $st) {
            $batchId = (int)$payload['batch_id'];
            $userId  = (int)$payload['user_id'];
            $assign  = (bool)$payload['assigned'];

            // Current live assignment?
            $current = DB::table('batch_instructors')
                ->where('batch_id', $batchId)
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->first();

            if ($assign === true) {
                if ($current) {
                    // Already assigned → update role/status if provided
                    $update = [];
                    if ($this->validRoleInBatch($payload['role_in_batch'] ?? null)) {
                        $update['role_in_batch'] = $role;
                    }
                    if ($this->validAssignStatus($payload['assign_status'] ?? null)) {
                        $update['assign_status'] = $st;
                    }
                    if (!empty($update)) {
                        $update['updated_at'] = $now;
                        DB::table('batch_instructors')
                          ->where('id', $current->id)
                          ->update($update);
                    }
                    $this->logActivity($request, 'batch_instructor.toggle.assign.noop', [
                        'batch_id'=>$batchId,'user_id'=>$userId,
                    ]);
                    return $this->ok(['id' => $current->id, 'uuid' => $current->uuid, 'assigned' => true], 'Already assigned');
                }

                // Was there a soft-deleted record? → restore it
                $deleted = DB::table('batch_instructors')
                    ->where('batch_id', $batchId)
                    ->where('user_id', $userId)
                    ->whereNotNull('deleted_at')
                    ->orderByDesc('id')
                    ->first();

                if ($deleted) {
                    DB::table('batch_instructors')
                        ->where('id', $deleted->id)
                        ->update([
                            'deleted_at'    => null,
                            'assigned_at'   => $now,
                            'unassigned_at' => null,
                            'assign_status' => $st,
                            'role_in_batch' => $role,
                            'updated_at'    => $now,
                        ]);

                    $this->logActivity($request, 'batch_instructor.restore', [
                        'id'=>$deleted->id,'batch_id'=>$batchId,'user_id'=>$userId
                    ]);

                    return $this->ok(['id' => $deleted->id, 'uuid' => $deleted->uuid, 'assigned' => true], 'Restored');
                }

                // Fresh insert
                $id = DB::table('batch_instructors')->insertGetId([
                    'uuid'          => (string) Str::uuid(),
                    'batch_id'      => $batchId,
                    'user_id'       => $userId,
                    'role_in_batch' => $role,
                    'assign_status' => $st,
                    'assigned_at'   => $now,
                    'unassigned_at' => null,
                    'created_by'    => $uid,
                    'created_at_ip' => $ip,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                    // metadata defaults handled by migration (engine-specific)
                ]);

                $row = DB::table('batch_instructors')->where('id',$id)->first();
                $this->logActivity($request, 'batch_instructor.assign', [
                    'id'=>$id,'batch_id'=>$batchId,'user_id'=>$userId
                ]);

                return $this->ok(['id' => $row->id, 'uuid' => $row->uuid, 'assigned' => true], 'Assigned');
            }

            // assigned = false → Soft delete if exists
            if ($current) {
                DB::table('batch_instructors')->where('id', $current->id)->update([
                    'assign_status' => 'removed',
                    'unassigned_at' => $now,
                    'deleted_at'    => $now,
                    'updated_at'    => $now,
                ]);
                $this->logActivity($request, 'batch_instructor.unassign', [
                    'id'=>$current->id,'batch_id'=>$batchId,'user_id'=>$userId
                ]);
                return $this->ok(['id' => $current->id, 'uuid' => $current->uuid, 'assigned' => false], 'Unassigned');
            }

            // Already unassigned → idempotent
            $this->logActivity($request, 'batch_instructor.toggle.unassign.noop', [
                'batch_id'=>$batchId,'user_id'=>$userId
            ]);
            return $this->ok(['assigned' => false], 'Already unassigned');
        });
    }

    /**
     * POST /api/batch-instructors/bulk-sync-for-user
     * Body: {
     *   user_id: int,
     *   role_in_batch?: "instructor"|"tutor"|"TA"|"mentor",   // optional default for new assigns
     *   default_assign_status?: "active"|"standby"|"replaced"|"removed",
     *   assignments: [ { batch_id:int, assigned:bool, role_in_batch?:string, assign_status?:string } ... ]
     * }
     * Syncs multiple batch toggles for the same instructor in one transaction.
     */
    public function bulkSyncForUser(Request $request)
    {
        $v = Validator::make($request->all(), [
            'user_id'  => 'required|integer|exists:users,id',
            'role_in_batch' => 'nullable|string|in:instructor,tutor,TA,mentor',
            'default_assign_status' => 'nullable|string|in:active,standby,replaced,removed',
            'assignments' => 'required|array|min:1',
            'assignments.*.batch_id' => 'required|integer|exists:batches,id',
            'assignments.*.assigned' => 'required|boolean',
            'assignments.*.role_in_batch' => 'nullable|string|in:instructor,tutor,TA,mentor',
            'assignments.*.assign_status' => 'nullable|string|in:active,standby,replaced,removed',
        ]);
        if ($v->fails()) return $this->fail($v->errors()->toArray());

        $uid  = $this->authUserId($request);
        $now  = $this->now();
        $ip   = $request->ip();

        $defaultRole = $request->input('role_in_batch', 'instructor');
        $defaultStatus = $request->input('default_assign_status', 'active');

        $userId = (int)$request->input('user_id');
        $items  = $request->input('assignments', []);

        return DB::transaction(function () use ($request, $userId, $items, $defaultRole, $defaultStatus, $uid, $now, $ip) {
            $results = [];

            foreach ($items as $it) {
                $batchId = (int)$it['batch_id'];
                $assign  = (bool)$it['assigned'];
                $role    = $it['role_in_batch'] ?? $defaultRole;
                $st      = $it['assign_status'] ?? $defaultStatus;

                // reuse toggle logic inline (without nested transactions)
                $current = DB::table('batch_instructors')
                    ->where('batch_id', $batchId)
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($assign) {
                    if ($current) {
                        $update = [];
                        if ($this->validRoleInBatch($role)) $update['role_in_batch'] = $role;
                        if ($this->validAssignStatus($st)) $update['assign_status'] = $st;
                        if (!empty($update)) {
                            $update['updated_at'] = $now;
                            DB::table('batch_instructors')->where('id', $current->id)->update($update);
                        }
                        $results[] = ['batch_id'=>$batchId,'assigned'=>true,'id'=>$current->id];
                        continue;
                    }

                    $deleted = DB::table('batch_instructors')
                        ->where('batch_id', $batchId)
                        ->where('user_id', $userId)
                        ->whereNotNull('deleted_at')
                        ->orderByDesc('id')
                        ->first();

                    if ($deleted) {
                        DB::table('batch_instructors')->where('id', $deleted->id)->update([
                            'deleted_at'    => null,
                            'assigned_at'   => $now,
                            'unassigned_at' => null,
                            'assign_status' => $st,
                            'role_in_batch' => $role,
                            'updated_at'    => $now,
                        ]);
                        $results[] = ['batch_id'=>$batchId,'assigned'=>true,'id'=>$deleted->id];
                    } else {
                        $id = DB::table('batch_instructors')->insertGetId([
                            'uuid'          => (string) Str::uuid(),
                            'batch_id'      => $batchId,
                            'user_id'       => $userId,
                            'role_in_batch' => $role,
                            'assign_status' => $st,
                            'assigned_at'   => $now,
                            'unassigned_at' => null,
                            'created_by'    => $uid,
                            'created_at_ip' => $ip,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ]);
                        $results[] = ['batch_id'=>$batchId,'assigned'=>true,'id'=>$id];
                    }
                } else {
                    if ($current) {
                        DB::table('batch_instructors')->where('id', $current->id)->update([
                            'assign_status' => 'removed',
                            'unassigned_at' => $now,
                            'deleted_at'    => $now,
                            'updated_at'    => $now,
                        ]);
                        $results[] = ['batch_id'=>$batchId,'assigned'=>false,'id'=>$current->id];
                    } else {
                        $results[] = ['batch_id'=>$batchId,'assigned'=>false];
                    }
                }
            }

            $this->logActivity($request, 'batch_instructor.bulk_sync', [
                'user_id'=>$userId,'count'=>count($results)
            ]);

            return $this->ok(['results' => $results], 'Bulk sync complete');
        });
    }

    /**
     * POST /api/batch-instructors/restore
     * Body: { id?:int, batch_id?:int, user_id?:int }
     * Restore a soft-deleted assignment (by id OR by pair).
     */
    public function restore(Request $request)
    {
        $v = Validator::make($request->all(), [
            'id'       => 'nullable|integer',
            'batch_id' => 'nullable|integer|exists:batches,id',
            'user_id'  => 'nullable|integer|exists:users,id',
        ]);
        if ($v->fails()) return $this->fail($v->errors()->toArray());

        if (!$request->filled('id') && !($request->filled('batch_id') && $request->filled('user_id'))) {
            return $this->fail(['restore' => ['Provide either id OR (batch_id & user_id).']]);
        }

        $now = $this->now();

        $q = DB::table('batch_instructors')->whereNotNull('deleted_at');
        if ($request->filled('id')) {
            $q->where('id', (int)$request->id);
        } else {
            $q->where('batch_id', (int)$request->batch_id)
              ->where('user_id',  (int)$request->user_id);
        }

        $row = $q->first();
        if (!$row) return $this->fail(['restore' => ['No soft-deleted record found']], 404);

        DB::table('batch_instructors')->where('id', $row->id)->update([
            'deleted_at'    => null,
            'assigned_at'   => $now,
            'unassigned_at' => null,
            'assign_status' => 'active',
            'updated_at'    => $now,
        ]);

        $this->logActivity($request, 'batch_instructor.restore.manual', ['id'=>$row->id]);

        return $this->ok(['id' => $row->id, 'uuid' => $row->uuid], 'Restored');
    }
}
