<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Exception;

class ModuleController extends Controller
{
    /**
     * Normalize actor information from request (compatible with previous pattern)
     */
    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_tokenable_uuid') ?? ($r->user()->uuid ?? '')),
        ];
    }

    /**
     * Build base query for modules with common filters
     */
    protected function baseQuery(Request $request, $includeDeleted = false)
    {
        $q = DB::table('modules');
        if (! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        // optional course_id filter
        if ($request->filled('course_id')) {
            $q->where('course_id', $request->query('course_id'));
        }

        // search q -> title or short_description
        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('short_description', 'like', $term);
            });
        }

        // status explicit
        if ($request->filled('status')) {
            $q->where('status', $request->query('status'));
        }

        // sort
        $sort = $request->query('sort', '-created_at');
        $dir = 'desc';
        $col = 'created_at';
        if ($sort[0] === '-') {
            $col = ltrim($sort, '-'); $dir = 'desc';
        } else { $col = $sort; $dir = 'asc'; }
        // whitelist sortable columns
        $allowed = ['created_at','order_no','title','id'];
        if (! in_array($col, $allowed)) { $col = 'created_at'; }
        $q->orderBy($col, $dir);

        return $q;
    }

    /**
     * Format paginator->toArray style response similar to front-end expectations
     */
    protected function paginatorToArray($paginator)
    {
        return [
            'data' => $paginator->items(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * List modules (active / all non-deleted). Accepts: per_page, page, q, course_id, status, sort, with_privileges
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));
        $includePrivileges = filter_var($request->query('with_privileges', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, false)->select('id','uuid','course_id','title','short_description','order_no','status','created_at','updated_at');

        $paginator = $query->paginate($perPage);
        $out = $this->paginatorToArray($paginator);

        if ($includePrivileges && !empty($out['data'])) {
            $ids = collect($out['data'])->pluck('id')->filter()->all();
            $privs = DB::table('privileges')->whereIn('module_id', $ids)->whereNull('deleted_at')->get()->groupBy('module_id');
            foreach ($out['data'] as &$m) {
                $m->privileges = $privs->has($m->id) ? $privs[$m->id] : [];
            }
        }

        return response()->json($out);
    }

    /**
     * Archived list (status = 'archived')
     */
    public function archived(Request $request)
    {
        $request->merge(['status' => 'archived']);
        return $this->index($request);
    }

    /**
     * Bin (soft-deleted rows)
     */
    public function bin(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));
        $includePrivileges = filter_var($request->query('with_privileges', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, true)->whereNotNull('modules.deleted_at')
            ->select('id','uuid','course_id','title','short_description','order_no','status','created_at','updated_at','deleted_at');

        $paginator = $query->paginate($perPage);
        $out = $this->paginatorToArray($paginator);

        if ($includePrivileges && !empty($out['data'])) {
            $ids = collect($out['data'])->pluck('id')->filter()->all();
            $privs = DB::table('privileges')->whereIn('module_id', $ids)->whereNull('deleted_at')->get()->groupBy('module_id');
            foreach ($out['data'] as &$m) {
                $m->privileges = $privs->has($m->id) ? $privs[$m->id] : [];
            }
        }

        return response()->json($out);
    }

    /**
     * Store a new module (uses fields expected by your front-end)
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:modules,title,NULL,id,deleted_at,NULL',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'metadata' => 'nullable|string',
            'order_no' => 'nullable|integer',
            'status' => ['nullable', Rule::in(['draft','published','archived'])],
            'course_id' => 'nullable|integer',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $actor = $this->actor($request);
        $ip = $request->ip();

        try {
            $id = DB::transaction(function () use ($request, $actor, $ip) {
                $payload = [
                    'uuid' => (string) Str::uuid(),
                    'course_id' => $request->input('course_id'),
                    'title' => $request->input('title'),
                    'short_description' => $request->input('short_description'),
                    'long_description' => $request->input('long_description'),
                    'metadata' => $request->input('metadata'),
                    'order_no' => $request->input('order_no', 0),
                    'status' => $request->input('status', 'draft'),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $actor['id'] ?: null,
                    'created_at_ip' => $ip,
                    'deleted_at' => null,
                ];

                if (Schema::hasColumn('modules', 'created_by_type')) {
                    $payload['created_by_type'] = $actor['type'] ?: null;
                }
                if (Schema::hasColumn('modules', 'created_by_role')) {
                    $payload['created_by_role'] = $actor['role'] ?: null;
                }
                if (Schema::hasColumn('modules', 'created_by_uuid')) {
                    $payload['created_by_uuid'] = $actor['uuid'] ?: null;
                }

                return DB::table('modules')->insertGetId($payload);
            });

            $module = DB::table('modules')->where('id', $id)->first();
            return response()->json(['module' => $module], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not create module', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Attempt to resolve module by id, uuid or slug
     */
    protected function resolveModule($identifier, $includeDeleted = false)
    {
        $query = DB::table('modules');
        if (! $includeDeleted) $query->whereNull('deleted_at');

        if (ctype_digit((string)$identifier)) {
            $query->where('id', (int)$identifier);
        } elseif (Str::isUuid((string)$identifier)) {
            $query->where('uuid', (string)$identifier);
        } else {
            if (Schema::hasColumn('modules', 'slug')) {
                $query->where('slug', (string)$identifier);
            } else {
                return null;
            }
        }

        return $query->first();
    }

    /**
     * Show single module
     */
    public function show(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, false);
        if (! $module) return response()->json(['message' => 'Module not found'], 404);

        if (filter_var($request->query('with_privileges', false), FILTER_VALIDATE_BOOLEAN)) {
            $privileges = DB::table('privileges')->where('module_id', $module->id)->whereNull('deleted_at')->get();
            $module->privileges = $privileges;
        }

        return response()->json(['module' => $module]);
    }

    /**
     * Full update (PATCH/PUT) — accepts front-end fields and applies changes
     */
    public function update(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, false);
        if (! $module) return response()->json(['message' => 'Module not found'], 404);

        $v = Validator::make($request->all(), [
            'title' => [
                'sometimes','required','string','max:255',
                Rule::unique('modules')->ignore($module->id)->whereNull('deleted_at'),
            ],
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'metadata' => 'nullable|string',
            'order_no' => 'nullable|integer',
            'status' => ['nullable', Rule::in(['draft','published','archived'])],
            'course_id' => 'nullable|integer',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $actor = $this->actor($request);

        $update = array_filter([
            'course_id' => $request->has('course_id') ? $request->input('course_id') : null,
            'title' => $request->has('title') ? $request->input('title') : null,
            'short_description' => $request->has('short_description') ? $request->input('short_description') : null,
            'long_description' => $request->has('long_description') ? $request->input('long_description') : null,
            'metadata' => $request->has('metadata') ? $request->input('metadata') : null,
            'order_no' => $request->has('order_no') ? $request->input('order_no') : null,
            'status' => $request->has('status') ? $request->input('status') : null,
            'updated_at' => now(),
        ], function ($v) { return $v !== null; });

        if (Schema::hasColumn('modules', 'updated_by')) {
            $update['updated_by'] = $actor['id'] ?: null;
        }
        if (Schema::hasColumn('modules', 'updated_by_type')) {
            $update['updated_by_type'] = $actor['type'] ?: null;
        }
        if (Schema::hasColumn('modules', 'updated_by_role')) {
            $update['updated_by_role'] = $actor['role'] ?: null;
        }
        if (Schema::hasColumn('modules', 'updated_by_uuid')) {
            $update['updated_by_uuid'] = $actor['uuid'] ?: null;
        }

        if (empty($update) || (count($update) === 1 && array_key_exists('updated_at', $update))) {
            return response()->json(['message' => 'Nothing to update'], 400);
        }

        try {
            DB::transaction(function () use ($module, $update) {
                DB::table('modules')->where('id', $module->id)->update($update);
            });
            $module = DB::table('modules')->where('id', $module->id)->first();
            return response()->json(['module' => $module]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not update module', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Archive a module (set status = 'archived')
     */
    public function archive(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, false);
        if (! $module) return response()->json(['message' => 'Module not found'], 404);

        try {
            DB::table('modules')->where('id', $module->id)->update(['status' => 'archived', 'updated_at' => now()]);
            return response()->json(['message' => 'Module archived']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not archive', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Unarchive (set status to draft)
     */
    public function unarchive(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, false);
        if (! $module) return response()->json(['message' => 'Module not found'], 404);

        try {
            DB::table('modules')->where('id', $module->id)->update(['status' => 'draft', 'updated_at' => now()]);
            return response()->json(['message' => 'Module unarchived']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not unarchive', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Soft-delete module (existing destroy behaviour) — keeps privileges soft-delete
     */
    public function destroy(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, false);
        if (! $module) return response()->json(['message' => 'Module not found or already deleted'], 404);

        $actor = $this->actor($request);

        try {
            DB::transaction(function () use ($module, $actor) {
                $update = ['deleted_at' => now(), 'updated_at' => now()];
                if (Schema::hasColumn('modules', 'updated_by')) {
                    $update['updated_by'] = $actor['id'] ?: null;
                }
                DB::table('modules')->where('id', $module->id)->update($update);

                $privUpdate = ['deleted_at' => now(), 'updated_at' => now()];
                if (Schema::hasColumn('privileges', 'updated_by')) {
                    $privUpdate['updated_by'] = $actor['id'] ?: null;
                }
                DB::table('privileges')->where('module_id', $module->id)->whereNull('deleted_at')->update($privUpdate);
            });

            return response()->json(['message' => 'Module soft-deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not delete module', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore soft-deleted module
     */
    public function restore(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, true);
        if (! $module || $module->deleted_at === null) return response()->json(['message' => 'Module not found or not deleted'], 404);

        $actor = $this->actor($request);

        try {
            DB::transaction(function () use ($module, $actor) {
                $update = ['deleted_at' => null, 'updated_at' => now()];
                if (Schema::hasColumn('modules', 'updated_by')) {
                    $update['updated_by'] = $actor['id'] ?: null;
                }
                DB::table('modules')->where('id', $module->id)->update($update);

                $privUpdate = ['deleted_at' => null, 'updated_at' => now()];
                if (Schema::hasColumn('privileges', 'updated_by')) {
                    $privUpdate['updated_by'] = $actor['id'] ?: null;
                }
                DB::table('privileges')->where('module_id', $module->id)->whereNotNull('deleted_at')->update($privUpdate);
            });

            $module = DB::table('modules')->where('id', $module->id)->first();
            return response()->json(['module' => $module, 'message' => 'Module restored']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not restore', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Force delete permanently (irreversible)
     */
    public function forceDelete(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, true);
        if (! $module) return response()->json(['message' => 'Module not found'], 404);

        try {
            DB::transaction(function () use ($module) {
                DB::table('privileges')->where('module_id', $module->id)->delete();
                DB::table('modules')->where('id', $module->id)->delete();
            });
            return response()->json(['message' => 'Module permanently deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not permanently delete', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reorder modules — expects { ids: [id1,id2,id3,...] }
     * It will update order_no according to array position (0..n-1)
     */
    public function reorder(Request $request)
    {
        $v = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|min:1',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $ids = $request->input('ids');

        try {
            DB::transaction(function () use ($ids) {
                foreach ($ids as $idx => $id) {
                    DB::table('modules')->where('id', $id)->update(['order_no' => $idx, 'updated_at' => now()]);
                }
            });
            return response()->json(['message' => 'Order updated']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not update order', 'error' => $e->getMessage()], 500);
        }
    }
}
