<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
<<<<<<< HEAD
=======
use Illuminate\Support\Arr;
>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
use Exception;

class PrivilegeController extends Controller
{
    /**
     * List privileges (filter by module_id optional - accepts module id or uuid)
     */
     public function index(Request $request)
    {
        try {
            $perPage = max(1, min(200, (int) $request->query('per_page', 20)));
            $moduleKey = $request->query('module_id');

            // Build select columns defensively (include module name)
            $cols = [
                'privileges.id',
                'privileges.uuid',
                'privileges.module_id',
                'privileges.action',
                'privileges.description',
                'privileges.created_at',
                'privileges.updated_at',
                'modules.name as module_name',
            ];
            if (Schema::hasColumn('privileges', 'order_no')) {
                $cols[] = 'privileges.order_no';
            }
            if (Schema::hasColumn('privileges', 'status')) {
                $cols[] = 'privileges.status';
            }

            $query = DB::table('privileges')
                ->leftJoin('modules', 'modules.id', '=', 'privileges.module_id')
                ->whereNull('privileges.deleted_at')
                ->select($cols);

            if ($moduleKey) {
                if (ctype_digit((string)$moduleKey)) {
                    $query->where('privileges.module_id', (int)$moduleKey);
                } elseif (Str::isUuid((string)$moduleKey)) {
                    $module = DB::table('modules')->where('uuid', (string)$moduleKey)->whereNull('deleted_at')->first();
                    if ($module) {
                        $query->where('privileges.module_id', $module->id);
                    } else {
                        return response()->json([
                            'data' => [],
                            'pagination' => ['page' => 1, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1],
                        ]);
                    }
                } else {
                    // ignore invalid moduleKey
                }
            }

            // stable order for pagination
            $paginator = $query->orderBy('privileges.id', 'desc')->paginate($perPage);

            return response()->json([
                'data' => $paginator->items(),
                'pagination' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            // Log error
            try {
                \Log::error('PrivilegeController::index exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            } catch (\Throwable $inner) {
                // ignore logging failure
            }

            // DEBUG INFO: include message and short trace (remove in production)
            $trace = collect($e->getTrace())->map(function ($t) {
                return Arr::only($t, ['file', 'line', 'function', 'class']);
            })->all();

            return response()->json([
                'message' => 'Server error fetching privileges (see logs)',
                'error' => $e->getMessage(),
                'trace' => $trace,
            ], 500);
        }
    }
    /**
     * Bin (soft-deleted privileges)
     */
    public function bin(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $query = DB::table('privileges')->whereNotNull('deleted_at')->orderBy('deleted_at', 'desc');

<<<<<<< HEAD
        if ($moduleKey) {
            // moduleKey can be numeric id or uuid
            if (ctype_digit((string)$moduleKey)) {
                $query->where('module_id', (int)$moduleKey);
            } elseif (Str::isUuid((string)$moduleKey)) {
                $module = DB::table('modules')->where('uuid', (string)$moduleKey)->whereNull('deleted_at')->first();
                if ($module) {
                    $query->where('module_id', $module->id);
                } else {
                    // no module found -> return empty paginator
                    return response()->json([
                        'data' => [],
                        'pagination' => ['page' => 1, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1],
                    ]);
                }
            } else {
                // if not id/uuid, ignore filter (or you may treat as slug if you have one)
            }
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);
        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Bin (soft-deleted privileges)
     */
    public function bin(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $query = DB::table('privileges')->whereNotNull('deleted_at')->orderBy('deleted_at', 'desc');

        $paginator = $query->paginate($perPage);

=======
        $paginator = $query->paginate($perPage);

>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Store a new privilege (action unique per module). Accepts module_id as id or uuid.
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'module_id' => 'required',
            'action' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // Resolve module_id: allow numeric id or uuid
        $rawModule = $request->input('module_id');
        $module = null;
        if (ctype_digit((string)$rawModule)) {
            $module = DB::table('modules')->where('id', (int)$rawModule)->whereNull('deleted_at')->first();
        } elseif (Str::isUuid((string)$rawModule)) {
            $module = DB::table('modules')->where('uuid', (string)$rawModule)->whereNull('deleted_at')->first();
        } else {
            return response()->json(['errors' => ['module_id' => ['Invalid module identifier']]], 422);
        }

        if (!$module) {
            return response()->json(['errors' => ['module_id' => ['Module not found']]], 422);
        }

        $moduleId = (int) $module->id;
        $action = trim($request->input('action'));

        // Composite uniqueness (module_id + action)
        $exists = DB::table('privileges')
            ->where('module_id', $moduleId)
            ->where('action', $action)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Action already exists for this module'], 409);
        }

        $userId = optional($request->user())->id ?? null;
        $ip = $request->ip();

        try {
            $id = DB::transaction(function () use ($moduleId, $action, $request, $userId, $ip) {
                $payload = [
                    'uuid' => (string) Str::uuid(),
                    'module_id' => $moduleId,
                    'action' => $action,
                    'description' => $request->input('description'),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $userId,
                    'created_at_ip' => $ip,
                    'deleted_at' => null,
                ];

<<<<<<< HEAD
                // add order_no if the schema supports it and request provided one
=======
>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
                if (Schema::hasColumn('privileges', 'order_no') && $request->has('order_no')) {
                    $payload['order_no'] = (int) $request->input('order_no');
                }

                return DB::table('privileges')->insertGetId($payload);
            });

            $priv = DB::table('privileges')->where('id', $id)->first();
            return response()->json(['privilege' => $priv], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not create privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Resolve privilege by numeric id or uuid.
     */
    protected function resolvePrivilege($identifier, $includeDeleted = false)
    {
        $q = DB::table('privileges');
        if (! $includeDeleted) $q->whereNull('deleted_at');

        if (ctype_digit((string)$identifier)) {
            $q->where('id', (int)$identifier);
        } elseif (Str::isUuid((string)$identifier)) {
            $q->where('uuid', (string)$identifier);
        } else {
            return null;
        }

        return $q->first();
    }

    /**
     * Show privilege (accepts id or uuid)
     */
    public function show(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, false);
        if (!$priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }
        return response()->json(['privilege' => $priv]);
    }

    /**
     * Update privilege (accepts id or uuid). module_id may be id or uuid.
     */
    public function update(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, false);
        if (!$priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'module_id' => 'sometimes|required',
            'action' => 'sometimes|required|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // determine new module id (if provided) else keep existing
        $newModuleId = $priv->module_id;
        if ($request->has('module_id')) {
            $rawModule = $request->input('module_id');
            $module = null;
            if (ctype_digit((string)$rawModule)) {
                $module = DB::table('modules')->where('id', (int)$rawModule)->whereNull('deleted_at')->first();
            } elseif (Str::isUuid((string)$rawModule)) {
                $module = DB::table('modules')->where('uuid', (string)$rawModule)->whereNull('deleted_at')->first();
            } else {
                return response()->json(['errors' => ['module_id' => ['Invalid module identifier']]], 422);
            }
            if (!$module) {
                return response()->json(['errors' => ['module_id' => ['Module not found']]], 422);
            }
            $newModuleId = (int)$module->id;
        }

        $newAction = $request->has('action') ? trim($request->input('action')) : $priv->action;

        // Check composite uniqueness (except current record)
        $exists = DB::table('privileges')
            ->where('module_id', $newModuleId)
            ->where('action', $newAction)
            ->whereNull('deleted_at')
            ->where('id', '!=', $priv->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Action already exists for this module'], 409);
        }

        $update = array_filter([
            'module_id' => $request->has('module_id') ? $newModuleId : null,
            'action' => $request->has('action') ? $newAction : null,
            'description' => $request->has('description') ? $request->input('description') : null,
            'updated_at' => now(),
        ], function ($v) { return $v !== null; });

        if (empty($update) || (count($update) === 1 && array_key_exists('updated_at', $update))) {
            return response()->json(['message' => 'Nothing to update'], 400);
        }

        try {
            DB::transaction(function () use ($priv, $update) {
                DB::table('privileges')->where('id', $priv->id)->update($update);
            });

            $priv = DB::table('privileges')->where('id', $priv->id)->first();
            return response()->json(['privilege' => $priv]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not update privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Soft delete privilege (accepts id or uuid)
     */
    public function destroy(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, false);
        if (!$priv) {
            return response()->json(['message' => 'Privilege not found or already deleted'], 404);
        }

        try {
            DB::table('privileges')->where('id', $priv->id)->update(['deleted_at' => now(), 'updated_at' => now()]);
            return response()->json(['message' => 'Privilege soft-deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not delete privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore privilege (accepts id or uuid)
     */
    public function restore(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, true);
        if (!$priv || $priv->deleted_at === null) {
            return response()->json(['message' => 'Privilege not found or not deleted'], 404);
        }

        try {
            DB::table('privileges')->where('id', $priv->id)->update(['deleted_at' => null, 'updated_at' => now()]);
            $priv = DB::table('privileges')->where('id', $priv->id)->first();
            return response()->json(['privilege' => $priv, 'message' => 'Privilege restored']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not restore privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
<<<<<<< HEAD
=======
     * Archived privileges (status = 'archived', not soft-deleted)
     */
    public function archived(Request $request)
    {
        try {
            $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

            // ensure we select only existing columns to avoid "unknown column" issues
            $cols = [
                'privileges.id',
                'privileges.uuid',
                'privileges.module_id',
                'privileges.action',
                'privileges.description',
                'privileges.created_at',
                'privileges.updated_at'
            ];
            if (Schema::hasColumn('privileges', 'order_no')) {
                $cols[] = 'privileges.order_no';
            }
            if (Schema::hasColumn('privileges', 'status')) {
                $cols[] = 'privileges.status';
            }

            $query = DB::table('privileges')
                ->whereNull('deleted_at')
                ->select($cols)
                ->where(function ($q) {
                    if (Schema::hasColumn('privileges', 'status')) {
                        $q->where('privileges.status', 'archived');
                    } else {
                        // no status column -> return empty
                        $q->whereRaw('0 = 1');
                    }
                })
                ->orderBy('privileges.id', 'desc');

            $paginator = $query->paginate($perPage);

            return response()->json([
                'data' => $paginator->items(),
                'pagination' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('PrivilegeController::archived exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            return response()->json(['message' => 'Server error fetching archived privileges'], 500);
        }
    }

    /**
>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
     * Archive a privilege (set status = 'archived') - only if `status` column exists
     */
    public function archive(Request $request, $identifier)
    {
        if (! Schema::hasColumn('privileges', 'status')) {
            return response()->json(['message' => 'Archive not supported for privileges (no status column)'], 400);
        }

        $priv = $this->resolvePrivilege($identifier, false);
        if (!$priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        try {
            DB::table('privileges')->where('id', $priv->id)->update(['status' => 'archived', 'updated_at' => now()]);
            return response()->json(['message' => 'Privilege archived']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not archive privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Unarchive a privilege (set status = 'draft') - only if `status` column exists
     */
    public function unarchive(Request $request, $identifier)
    {
        if (! Schema::hasColumn('privileges', 'status')) {
            return response()->json(['message' => 'Unarchive not supported for privileges (no status column)'], 400);
        }

        $priv = $this->resolvePrivilege($identifier, false);
        if (!$priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        try {
            DB::table('privileges')->where('id', $priv->id)->update(['status' => 'draft', 'updated_at' => now()]);
            return response()->json(['message' => 'Privilege unarchived']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not unarchive privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Force delete permanently (irreversible)
     */
    public function forceDelete(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, true);
        if (!$priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        try {
            DB::transaction(function () use ($priv) {
                DB::table('privileges')->where('id', $priv->id)->delete();
            });
            return response()->json(['message' => 'Privilege permanently deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not permanently delete privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reorder privileges — expects { ids: [id1,id2,id3,...] }
     * It will update order_no according to array position (0..n-1)
     * Requires privileges.order_no column to exist.
     */
    public function reorder(Request $request)
    {
        if (! Schema::hasColumn('privileges', 'order_no')) {
            return response()->json(['message' => 'Reorder not supported: privileges.order_no column missing'], 400);
        }

        $v = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|min:1',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $ids = $request->input('ids');

        try {
            DB::transaction(function () use ($ids) {
                foreach ($ids as $idx => $id) {
                    DB::table('privileges')->where('id', $id)->update(['order_no' => $idx, 'updated_at' => now()]);
                }
            });
            return response()->json(['message' => 'Order updated']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not update order', 'error' => $e->getMessage()], 500);
        }
    }
<<<<<<< HEAD
=======

    /**
     * Return privileges for a specific module (accepts numeric id or uuid)
     */
    public function forModule($identifier, Request $request = null)
    {
        try {
            // resolve module id
            $module = null;
            if (ctype_digit((string)$identifier)) {
                $module = DB::table('modules')->where('id', (int)$identifier)->whereNull('deleted_at')->first();
            } elseif (Str::isUuid((string)$identifier)) {
                $module = DB::table('modules')->where('uuid', (string)$identifier)->whereNull('deleted_at')->first();
            }

            if (!$module) {
                return response()->json([
                    'data' => [],
                    'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1],
                ], 200);
            }

            $perPage = max(1, min(200, (int) request()->query('per_page', 20)));

            $query = DB::table('privileges')
                ->whereNull('deleted_at')
                ->where('module_id', $module->id)
                ->orderBy('id', 'desc');

            $paginator = $query->paginate($perPage);

            return response()->json([
                'data' => $paginator->items(),
                'pagination' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('PrivilegeController::forModule error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            return response()->json(['message' => 'Unable to fetch privileges for module'], 500);
        }
    }
>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
}
