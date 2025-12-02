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
     * Normalize an incoming href for storage:
     * - strip leading slashes
     * - strip a leading 'admin' or 'admin/module' prefix (case-insensitive)
     * - trim and limit to 255 chars
     * Returns the normalized suffix (no leading slash).
     */
    private function normalizeHrefForStorage($rawHref)
    {
        $rawHref = (string) ($rawHref ?? '');
        $normalized = preg_replace('#^/+#', '', trim($rawHref));
        $normalized = preg_replace('#^admin(?:/module)?/?#i', '', $normalized);
        return mb_substr($normalized, 0, 255);
    }

    /**
     * Convert stored href suffix into a response-friendly href:
     * - If empty -> return empty string
     * - If absolute http(s) URL -> return as-is
     * - Otherwise prepend a single leading slash so it's root-relative (e.g. "/coursesModule/manage")
     */
    private function normalizeHrefForResponse($href)
    {
        $href = (string) ($href ?? '');
        if ($href === '') return '';
        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }
        return '/' . ltrim($href, '/');
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

        // search q -> name or description
        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
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
        if (is_string($sort) && $sort !== '') {
            if ($sort[0] === '-') {
                $col = ltrim($sort, '-'); $dir = 'desc';
            } else { $col = $sort; $dir = 'asc'; }
        }

        // whitelist sortable columns
        $allowed = ['created_at','name','id'];
        if (! in_array($col, $allowed, true)) { $col = 'created_at'; }
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
     * Helper to build a safe select array including href only if column exists.
     */
    protected function moduleSelectColumns($includeDeletedAt = false)
    {
        $cols = ['id','uuid','name','description','status','created_by','created_at_ip','created_at','updated_at'];
        if (Schema::hasColumn('modules', 'href')) {
            // place href after name/description — array splice puts it before status (after description)
            array_splice($cols, 3, 0, ['href']);
        }
        if ($includeDeletedAt) {
            $cols[] = 'deleted_at';
        }
        return $cols;
    }

    /**
     * List modules (active / all non-deleted). Accepts: per_page, page, q, status, sort, with_privileges
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));
        $includePrivileges = filter_var($request->query('with_privileges', false), FILTER_VALIDATE_BOOLEAN);

        // build base query (your baseQuery likely has joins/filters)
        $query = $this->baseQuery($request, false);

        // STATUS handling:
        // - if caller passed ?status=... we respect it (including ?status=archived)
        // - if no status provided, exclude archived rows by default
        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'archived') {
                $query->where('status', 'archived');
            } else {
                $query->where('status', $status);
            }
        } else {
            // default: exclude archived
            $query->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'archived');
            });
        }

        // select columns present in your migration
        $selectCols = $this->moduleSelectColumns(false);
        $query = $query->select($selectCols);

        $paginator = $query->paginate($perPage);
        $out = $this->paginatorToArray($paginator);

        // normalize hrefs in returned data (if column exists)
        if (Schema::hasColumn('modules', 'href') && !empty($out['data'])) {
            foreach ($out['data'] as &$m) {
                $m->href = $this->normalizeHrefForResponse($m->href ?? '');
            }
        }

        if ($includePrivileges && !empty($out['data'])) {
            $ids = collect($out['data'])->pluck('id')->filter()->all();
            // ensure privileges for these module ids (adjust column names if different)
            $privs = DB::table('privileges')
                ->whereIn('module_id', $ids)
                ->whereNull('deleted_at')
                ->get()
                ->groupBy('module_id');

            foreach ($out['data'] as &$m) {
                $m->privileges = $privs->has($m->id) ? $privs[$m->id] : [];
            }
        }

        return response()->json($out);
    }

    /**
     * Archived list (status = 'archived' or 'Archived')
     */
    public function archived(Request $request)
    {
        // we let caller pass archived value, but default to 'archived' for convenience
        $request->merge(['status' => $request->query('status', 'archived')]);
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
            ->select($this->moduleSelectColumns(true));

        $paginator = $query->paginate($perPage);
        $out = $this->paginatorToArray($paginator);

        // normalize hrefs in returned data (if column exists)
        if (Schema::hasColumn('modules', 'href') && !empty($out['data'])) {
            foreach ($out['data'] as &$m) {
                $m->href = $this->normalizeHrefForResponse($m->href ?? '');
            }
        }

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
            'name' => 'required|string|max:150|unique:modules,name,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:20',
            'href' => 'nullable|string|max:255', // accept suffix or full path; we'll normalize server-side
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
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'status' => $request->input('status', 'Active'),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $actor['id'] ?: null,
                    'created_at_ip' => $ip,
                    'deleted_at' => null,
                ];

                // set href only if column exists — normalize to store only the suffix (no leading slash,
                // and strip any leading 'admin' or 'admin/module' prefix). This keeps DB values consistent.
                if (Schema::hasColumn('modules', 'href')) {
                    $payload['href'] = $this->normalizeHrefForStorage($request->input('href', ''));
                }

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
            // normalize href for response
            if (isset($module->href)) {
                $module->href = $this->normalizeHrefForResponse($module->href);
            }
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

        // normalize href for response
        if (isset($module->href)) {
            $module->href = $this->normalizeHrefForResponse($module->href);
        }

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
            'name' => [
                'sometimes','required','string','max:150',
                Rule::unique('modules')->ignore($module->id)->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:20',
            'href' => 'sometimes|nullable|string|max:255',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $actor = $this->actor($request);

        $update = array_filter([
            'name' => $request->has('name') ? $request->input('name') : null,
            'description' => $request->has('description') ? $request->input('description') : null,
            'status' => $request->has('status') ? $request->input('status') : null,
            'updated_at' => now(),
        ], function ($v) { return $v !== null; });

        // include href only when column exists and request has it — normalize same as store()
        if (Schema::hasColumn('modules', 'href') && $request->has('href')) {
            $update['href'] = $this->normalizeHrefForStorage($request->input('href', ''));
        }

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
            // normalize href for response
            if (isset($module->href)) {
                $module->href = $this->normalizeHrefForResponse($module->href);
            }
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
     * Unarchive (set status to 'Active' / default)
     */
    public function unarchive(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, false);
        if (! $module) return response()->json(['message' => 'Module not found'], 404);

        try {
            DB::table('modules')->where('id', $module->id)->update(['status' => 'Active', 'updated_at' => now()]);
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
            // normalize href for response
            if (isset($module->href)) {
                $module->href = $this->normalizeHrefForResponse($module->href);
            }
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
     * It will update order according to array position (0..n-1)
     *
     * Note: your migration doesn't include an order_no column. If you add it later,
     * enable the update code below. For now this will return an error if order_no doesn't exist.
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
                    // only update if column exists
                    if (Schema::hasColumn('modules', 'order_no')) {
                        DB::table('modules')->where('id', $id)->update(['order_no' => $idx, 'updated_at' => now()]);
                    }
                }
            });
            return response()->json(['message' => 'Order updated']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not update order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Return all modules with their active privileges (no pagination).
     */
    public function allWithPrivileges(Request $request)
    {
        // fetch modules (non-deleted)
        $modules = DB::table('modules')
            ->whereNull('deleted_at')
            ->select($this->moduleSelectColumns(false))
            ->orderBy('name', 'asc')
            ->get();

        if ($modules->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $ids = $modules->pluck('id')->filter()->all();

        // fetch active privileges for these modules
        // NOTE: alias `action` -> `name` so frontend expecting `name` keeps working
        $privileges = DB::table('privileges')
            ->whereIn('module_id', $ids)
            ->whereNull('deleted_at')
            ->select('id','uuid','module_id', DB::raw('action as name'), 'action','description','created_at')
            ->orderBy('action','asc') // order by the actual column
            ->get()
            ->groupBy('module_id');

        // attach privileges (empty array when none) and normalize hrefs
        $out = $modules->map(function ($m) use ($privileges) {
            $m->privileges = $privileges->has($m->id) ? $privileges[$m->id]->values() : collect([]);
            $m->href = isset($m->href) ? $this->normalizeHrefForResponse($m->href) : '';
            return $m;
        });

        return response()->json(['data' => $out->values()]);
    }
}
