<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Exception;

class PrivilegeController extends Controller
{
    /**
     * List privileges (filter by module_id optional - accepts module id or uuid)
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));
        $moduleKey = $request->query('module_id');

        $query = DB::table('privileges')->whereNull('deleted_at');

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
                        'meta' => ['page' => 1, 'per_page' => $perPage, 'total' => 0],
                    ]);
                }
            } else {
                // if not id/uuid, ignore filter (or you may treat as slug if you have one)
            }
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);
        return response()->json($paginator->toArray());
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
                return DB::table('privileges')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'module_id' => $moduleId,
                    'action' => $action,
                    'description' => $request->input('description'),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $userId,
                    'created_at_ip' => $ip,
                    'deleted_at' => null,
                ]);
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
            // fallback: if slug exists you can attempt that here; default: no match
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
}
