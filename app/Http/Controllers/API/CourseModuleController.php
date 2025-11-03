<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function __construct()
    {
        // Route middleware is preferred, but keep this for parity with your other controllers if needed.
        // In routes/api.php, also add: ->middleware('checkRole:admin,super_admin')
    }

    /* =========================================================
     |                       LIST
     |  GET /api/course-modules
     |  ?course_id&status&q&sort=-created_at&page=1&per_page=20
     |=========================================================*/
    public function index(Request $r)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin'])) return $resp;

        $q         = trim((string)$r->query('q', ''));
        $status    = $r->query('status');
        $courseId  = $r->query('course_id');
        $page      = max(1, (int)$r->query('page', 1));
        $perPage   = min(100, max(1, (int)$r->query('per_page', 20)));
        $sort      = (string)$r->query('sort', '-created_at');

        // Sorting
        $dir = Str::startsWith($sort, '-') ? 'desc' : 'asc';
        $col = ltrim($sort, '-');
        if (!in_array($col, self::SORTABLE, true)) $col = 'created_at';

        $builder = DB::table('course_modules')
            ->whereNull('deleted_at');

        if ($courseId !== null && $courseId !== '') {
            $builder->where('course_id', (int)$courseId);
        }

        if ($status && in_array($status, self::STATUSES, true)) {
            $builder->where('status', $status);
        }

        if ($q !== '') {
            $builder->where(function ($qb) use ($q) {
                $qb->where('title', 'like', '%' . $q . '%');
            });
        }

        $total = (clone $builder)->count();
        $rows  = $builder
            ->orderBy($col, $dir)
            ->orderBy('id', 'desc')
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
     |                       SHOW
     |  GET /api/course-modules/{id|uuid}
     |=========================================================*/
    public function show(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin'])) return $resp;

        $row = $this->findModule($idOrUuid);
        if (!$row) {
            return response()->json(['message' => 'Course module not found'], 404);
        }

        return response()->json(['data' => $row]);
    }

    /* =========================================================
     |                       CREATE
     |  POST /api/course-modules
     |  Body: { course_id, title, short_description?, long_description?,
     |          order_no?, status?, metadata? }
     |=========================================================*/
    public function store(Request $r)
    {
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
            'metadata'          => ['nullable'], // JSON; validated in normalize
        ];

        $data = $r->all();
        $validated = validator($data, $rules)->validate();

        // Normalize
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

            // Activity log (best-effort)
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
        if ($resp = $this->requireRole($r, ['admin','super_admin'])) return $resp;

        $row = $this->findModule($idOrUuid);
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

        // Build update set
        $set = [];
        foreach (['course_id','title','short_description','long_description','order_no','status'] as $k) {
            if (array_key_exists($k, $payload)) $set[$k] = $payload[$k];
        }
        if (array_key_exists('metadata', $payload)) {
            $set['metadata'] = $this->normalizeJson($payload['metadata']);
        }
        if (empty($set)) {
            return response()->json(['data' => $row]); // nothing to change
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
     |                       DELETE (Soft)
     |  DELETE /api/course-modules/{id|uuid}
     |=========================================================*/
    public function destroy(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin'])) return $resp;

        $row = $this->findModule($idOrUuid);
        if (!$row) return response()->json(['message' => 'Course module not found'], 404);

        $actor = $this->actor($r);

        try {
            DB::beginTransaction();

            DB::table('course_modules')
                ->where('id', $row->id)
                ->update([
                    'status'     => 'archived',
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            $deleted = DB::table('course_modules')->where('id', $row->id)->first();

            $this->logActivity($actor, 'destroy', 'course_modules', $row->id, $row, $deleted);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('course_modules.destroy failed', ['e' => $e]);
            return response()->json(['message' => 'Failed to delete course module'], 500);
        }

        return response()->json(['message' => 'Course module archived']);
    }

    /* =========================================================
     |                       REORDER (Optional but useful)
     |  POST /api/course-modules/reorder
     |  Body: { course_id, ids: [moduleId1, moduleId2, ...] }
     |        or { course_id, orders: { id: position, ... } }
     |=========================================================*/
    public function reorder(Request $r)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin'])) return $resp;

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

    private function findModule(string $idOrUuid)
    {
        $q = DB::table('course_modules')->whereNull('deleted_at');

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
        // Fallback: store as string (or null)
        return null;
    }

    private function actor(Request $r): array
    {
        // Set by CheckRole middleware in your project (as per your other controllers)
        $role  = (string) ($r->attributes->get('auth_role') ?? '');
        $id    = (int)    ($r->attributes->get('auth_tokenable_id') ?? 0);
        return ['id' => $id ?: null, 'role' => $role];
    }

    private function requireRole(Request $r, array $roles)
    {
        $role = (string) ($r->attributes->get('auth_role') ?? '');
        if (!$role || !in_array($role, $roles, true)) {
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
}
