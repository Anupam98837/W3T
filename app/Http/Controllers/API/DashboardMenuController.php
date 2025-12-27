<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Exception;

class DashboardMenuController extends Controller
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
     * - strip a leading 'admin' or 'admin/module' or 'admin/dashboard-menu' prefix (case-insensitive)
     * - trim and limit to 255 chars
     * Returns the normalized suffix (no leading slash).
     */
    private function normalizeHrefForStorage($rawHref)
    {
        $rawHref = (string) ($rawHref ?? '');
        $normalized = preg_replace('#^/+#', '', trim($rawHref));
        // remove "admin", "admin/module", "admin/dashboard-menu"
        $normalized = preg_replace('#^admin(?:/(?:module|dashboard-menu))?/?#i', '', $normalized);
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
     * Build base query for dashboard menu with common filters
     */
    protected function baseQuery(Request $request, $includeDeleted = false)
    {
        $q = DB::table('dashboard_menu');
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
    $cols = [
        'id',
        'uuid',

        // ✅ REQUIRED for tree structure
        'parent_id',

        // ✅ REQUIRED for your rule: privileges go to children where is_dropdown_head = 0
        // (Only include if column exists so it won't break older DBs)
        // We'll push it conditionally below.

        // ✅ Optional but recommended for correct ordering in tree
        // We'll push it conditionally below.

        'name',
        'description',
        'status',
        'created_by',
        'created_at_ip',
        'created_at',
        'updated_at',
    ];

    // ✅ Put href after name (or around description like you wanted)
    if (Schema::hasColumn('dashboard_menu', 'href')) {
        // after name
        $nameIndex = array_search('name', $cols, true);
        if ($nameIndex !== false) {
            array_splice($cols, $nameIndex + 1, 0, ['href']);
        } else {
            $cols[] = 'href';
        }
    }

    // ✅ Keep dropdown flag if exists
    if (Schema::hasColumn('dashboard_menu', 'is_dropdown_head')) {
        // place after parent_id
        $pidIndex = array_search('parent_id', $cols, true);
        array_splice($cols, ($pidIndex !== false ? $pidIndex + 1 : 2), 0, ['is_dropdown_head']);
    }

    // ✅ Keep position if exists (helps order children properly)
    if (Schema::hasColumn('dashboard_menu', 'position')) {
        // place after is_dropdown_head if present, else after parent_id
        $after = array_search('is_dropdown_head', $cols, true);
        if ($after === false) $after = array_search('parent_id', $cols, true);
        array_splice($cols, ($after !== false ? $after + 1 : 2), 0, ['position']);
    }

    if ($includeDeletedAt) {
        $cols[] = 'deleted_at';
    }

    return $cols;
}


    /**
     * List dashboard menu items (active / all non-deleted). Accepts: per_page, page, q, status, sort, with_privileges
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));
        $includePrivileges = filter_var($request->query('with_privileges', false), FILTER_VALIDATE_BOOLEAN);

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

        $selectCols = $this->moduleSelectColumns(false);
        $query = $query->select($selectCols);

        $paginator = $query->paginate($perPage);
        $out = $this->paginatorToArray($paginator);

        // normalize hrefs in returned data (if column exists)
        if (Schema::hasColumn('dashboard_menu', 'href') && !empty($out['data'])) {
            foreach ($out['data'] as &$m) {
                $m->href = $this->normalizeHrefForResponse($m->href ?? '');
            }
        }

        if ($includePrivileges && !empty($out['data'])) {
            $ids = collect($out['data'])->pluck('id')->filter()->all();

            // NOTE: privileges table still uses module_id (assumed unchanged)
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

        $query = $this->baseQuery($request, true)
            ->whereNotNull('dashboard_menu.deleted_at')
            ->select($this->moduleSelectColumns(true));

        $paginator = $query->paginate($perPage);
        $out = $this->paginatorToArray($paginator);

        // normalize hrefs in returned data (if column exists)
        if (Schema::hasColumn('dashboard_menu', 'href') && !empty($out['data'])) {
            foreach ($out['data'] as &$m) {
                $m->href = $this->normalizeHrefForResponse($m->href ?? '');
            }
        }

        if ($includePrivileges && !empty($out['data'])) {
            $ids = collect($out['data'])->pluck('id')->filter()->all();
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
 * Store a new dashboard menu item
 */
public function store(Request $request)
{
    $v = Validator::make($request->all(), [
        'parent_id' => [
            'nullable',
            'integer',
            Rule::exists('dashboard_menu', 'id')->whereNull('deleted_at'),
        ],

        'name' => [
            'required',
            'string',
            'max:150',
            Rule::unique('dashboard_menu', 'name')->whereNull('deleted_at'),
        ],

        'href'            => 'nullable|string|max:255',
        'description'     => 'nullable|string',
        'status'          => 'nullable|string|max:20',
        'icon_class'      => 'nullable|string|max:120',
        'is_dropdown_head'=> 'nullable|in:0,1',

        // ✅ new / supported by migration
        'position'        => 'nullable|integer|min:0',
    ]);

    if ($v->fails()) {
        return response()->json(['errors' => $v->errors()], 422);
    }

    $actor = $this->actor($request);
    $ip    = $request->ip();

    try {
        $id = DB::transaction(function () use ($request, $actor, $ip) {

            // normalize href for storage; store NULL when empty
            $hrefNorm = null;
            if ($request->has('href')) {
                $tmp = $this->normalizeHrefForStorage($request->input('href'));
                $hrefNorm = ($tmp === '') ? null : $tmp;
            }

            // normalize parent_id strictly (avoid "" being inserted)
            $parentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;

            // ✅ position:
            // If provided, use it. Else auto-append within siblings (max+1)
            $position = null;
            if ($request->filled('position')) {
                $position = (int) $request->input('position');
            } else {
                $maxPos = DB::table('dashboard_menu')
                    ->whereNull('deleted_at')
                    ->where(function ($q) use ($parentId) {
                        if ($parentId === null) $q->whereNull('parent_id');
                        else $q->where('parent_id', $parentId);
                    })
                    ->max('position');

                $position = is_null($maxPos) ? 0 : ((int)$maxPos + 1);
            }

            $payload = [
                'uuid'            => (string) Str::uuid(),
                'parent_id'       => $parentId,
                'position'        => $position,

                'name'            => trim((string) $request->input('name')),
                'href'            => $hrefNorm,
                'description'     => $request->input('description'),
                'status'          => $request->input('status', 'Active'),
                'icon_class'      => $request->input('icon_class'),
                'is_dropdown_head'=> (int) $request->input('is_dropdown_head', 0),

                // ✅ audit (new migration has these)
                'created_by'      => $actor['id'] ?: null,
                'created_at_ip'   => $ip,
                'updated_at_ip'   => $ip,

                // timestamps
                'created_at'      => now(),
                'updated_at'      => now(),

                // soft delete
                'deleted_at'      => null,
            ];

            return DB::table('dashboard_menu')->insertGetId($payload);
        });

        $module = DB::table('dashboard_menu')
            ->where('id', $id)
            ->first();

        // normalize href for response
        if ($module && property_exists($module, 'href')) {
            $module->href = $this->normalizeHrefForResponse($module->href);
        }

        return response()->json(['module' => $module], 201);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'Could not create menu item',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    /**
     * Attempt to resolve dashboard menu item by id, uuid or slug
     */
    protected function resolveModule($identifier, $includeDeleted = false)
    {
        $query = DB::table('dashboard_menu');
        if (! $includeDeleted) $query->whereNull('deleted_at');

        if (ctype_digit((string)$identifier)) {
            $query->where('id', (int)$identifier);
        } elseif (Str::isUuid((string)$identifier)) {
            $query->where('uuid', (string)$identifier);
        } else {
            if (Schema::hasColumn('dashboard_menu', 'slug')) {
                $query->where('slug', (string)$identifier);
            } else {
                return null;
            }
        }

        return $query->first();
    }

    /**
     * Show single dashboard menu item
     */
    public function show(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, false);
        if (! $module) return response()->json(['message' => 'Menu item not found'], 404);

        if (isset($module->href)) {
            $module->href = $this->normalizeHrefForResponse($module->href);
        }

        if (filter_var($request->query('with_privileges', false), FILTER_VALIDATE_BOOLEAN)) {
            $privileges = DB::table('privileges')
                ->where('module_id', $module->id)
                ->whereNull('deleted_at')
                ->get();
            $module->privileges = $privileges;
        }

        return response()->json(['module' => $module]);
    }
/**
 * Full update (PATCH/PUT)
 */
public function update(Request $request, $identifier)
{
    $module = $this->resolveModule($identifier, false);
    if (! $module) return response()->json(['message' => 'Menu item not found'], 404);

    $v = Validator::make($request->all(), [
        'parent_id' => [
            'sometimes',
            'nullable',
            'integer',
            Rule::exists('dashboard_menu', 'id')->whereNull('deleted_at'),
            function ($attr, $val, $fail) use ($module) {
                if ($val !== null && (int)$val === (int)$module->id) {
                    $fail('parent_id cannot be same as the item id.');
                }
            }
        ],

        'name' => [
            'sometimes', 'required', 'string', 'max:150',
            Rule::unique('dashboard_menu', 'name')
                ->ignore($module->id)
                ->whereNull('deleted_at'),
        ],

        'href'            => 'sometimes|nullable|string|max:255',
        'description'     => 'sometimes|nullable|string',
        'status'          => 'sometimes|nullable|string|max:20',
        'icon_class'      => 'sometimes|nullable|string|max:120',
        'is_dropdown_head'=> 'sometimes|nullable|in:0,1',

        // ✅ new migration support
        'position'        => 'sometimes|integer|min:0',
    ]);

    if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

    $actor = $this->actor($request);
    $ip    = $request->ip();

    try {
        DB::transaction(function () use ($request, $module, $actor, $ip) {

            $update = [];

            if ($request->has('parent_id')) {
                // normalize: "" => null
                $update['parent_id'] = $request->filled('parent_id')
                    ? (int) $request->input('parent_id')
                    : null;
            }

            if ($request->has('name')) {
                $update['name'] = trim((string) $request->input('name'));
            }

            if ($request->has('description')) {
                $update['description'] = $request->input('description');
            }

            if ($request->has('status')) {
                $update['status'] = $request->input('status');
            }

            if ($request->has('icon_class')) {
                $update['icon_class'] = $request->input('icon_class');
            }

            if ($request->has('is_dropdown_head')) {
                $update['is_dropdown_head'] = (int) $request->input('is_dropdown_head', 0);
            }

            if ($request->has('position')) {
                $update['position'] = (int) $request->input('position', 0);
            }

            // href: normalize for storage; store NULL when empty
            if ($request->has('href')) {
                $tmp = $this->normalizeHrefForStorage($request->input('href'));
                $update['href'] = ($tmp === '') ? null : $tmp;
            }

            // nothing meaningful?
            if (empty($update)) {
                throw new \RuntimeException('Nothing to update');
            }

            // ✅ audit + timestamps per new migration
            $update['updated_by']    = $actor['id'] ?: null;
            $update['updated_at_ip'] = $ip;
            $update['updated_at']    = now();

            DB::table('dashboard_menu')
                ->where('id', $module->id)
                ->whereNull('deleted_at')
                ->update($update);
        });

        $module = DB::table('dashboard_menu')->where('id', $module->id)->first();

        // normalize href for response
        if ($module && property_exists($module, 'href')) {
            $module->href = $this->normalizeHrefForResponse($module->href);
        }

        return response()->json(['module' => $module]);

    } catch (\RuntimeException $e) {
        if ($e->getMessage() === 'Nothing to update') {
            return response()->json(['message' => 'Nothing to update'], 400);
        }
        return response()->json(['message' => 'Could not update menu item', 'error' => $e->getMessage()], 500);

    } catch (Exception $e) {
        return response()->json(['message' => 'Could not update menu item', 'error' => $e->getMessage()], 500);
    }
}

    /**
     * Archive a dashboard menu item (set status = 'archived')
     */
    public function archive(Request $request, $identifier)
    {
        $module = $this->resolveModule($identifier, false);
        if (! $module) return response()->json(['message' => 'Menu item not found'], 404);

        try {
            DB::table('dashboard_menu')->where('id', $module->id)->update(['status' => 'archived', 'updated_at' => now()]);
            return response()->json(['message' => 'Menu item archived']);
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
        if (! $module) return response()->json(['message' => 'Menu item not found'], 404);

        try {
            DB::table('dashboard_menu')->where('id', $module->id)->update(['status' => 'Active', 'updated_at' => now()]);
            return response()->json(['message' => 'Menu item unarchived']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not unarchive', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Soft-delete menu item — keeps privileges soft-delete
     */
    public function destroy(Request $request, $identifier)
{
    $module = $this->resolveModule($identifier, false);
    if (! $module) {
        return response()->json(['message' => 'Menu item not found or already deleted'], 404);
    }

    try {
        DB::transaction(function () use ($module) {
            $now = now();

            // Soft delete dashboard_menu
            DB::table('dashboard_menu')
                ->where('id', $module->id)
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            // Soft delete page_privilege (ONLY if table exists)
            if (Schema::hasTable('page_privilege')) {
                DB::table('page_privilege')
                    ->where('dashboard_menu_id', $module->id)
                    ->whereNull('deleted_at')
                    ->update([
                        'deleted_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
        });

        return response()->json(['message' => 'Menu item soft-deleted']);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Could not delete menu item',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function restore(Request $request, $identifier)
{
    $module = $this->resolveModule($identifier, true);
    if (! $module || $module->deleted_at === null) {
        return response()->json(['message' => 'Menu item not found or not deleted'], 404);
    }

    try {
        DB::transaction(function () use ($module) {
            $now = now();

            // Restore dashboard_menu
            DB::table('dashboard_menu')
                ->where('id', $module->id)
                ->update([
                    'deleted_at' => null,
                    'updated_at' => $now,
                ]);

            // Restore page_privilege (ONLY if table exists)
            if (Schema::hasTable('page_privilege')) {
                DB::table('page_privilege')
                    ->where('dashboard_menu_id', $module->id)
                    ->whereNotNull('deleted_at')
                    ->update([
                        'deleted_at' => null,
                        'updated_at' => $now,
                    ]);
            }
        });

        $module = DB::table('dashboard_menu')->where('id', $module->id)->first();
        if (isset($module->href)) {
            $module->href = $this->normalizeHrefForResponse($module->href);
        }

        return response()->json(['module' => $module, 'message' => 'Menu item restored']);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Could not restore menu item',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function forceDelete(Request $request, $identifier)
{
    $module = $this->resolveModule($identifier, true);
    if (! $module) return response()->json(['message' => 'Menu item not found'], 404);

    try {
        DB::transaction(function () use ($module) {

            // Hard delete page_privilege first (optional; cascade also covers it)
            if (Schema::hasTable('page_privilege')) {
                DB::table('page_privilege')
                    ->where('dashboard_menu_id', $module->id)
                    ->delete();
            }

            // Hard delete dashboard_menu
            DB::table('dashboard_menu')
                ->where('id', $module->id)
                ->delete();
        });

        return response()->json(['message' => 'Menu item permanently deleted']);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Could not permanently delete menu item',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Reorder menu items — expects { ids: [id1,id2,id3,...] }
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
                    if (Schema::hasColumn('dashboard_menu', 'order_no')) {
                        DB::table('dashboard_menu')->where('id', $id)->update(['order_no' => $idx, 'updated_at' => now()]);
                    }
                }
            });
            return response()->json(['message' => 'Order updated']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not update order', 'error' => $e->getMessage()], 500);
        }
    }
/**
 * Return dashboard menu as TREE with privileges attached ONLY to children
 * where is_dropdown_head == 0.
 *
 * Output:
 * [
 *   { id, name, ..., children:[...], privileges:[...] },
 *   ...
 * ]
 */
public function allWithPrivileges(Request $request)
{
    $modules = DB::table('dashboard_menu')
        ->whereNull('deleted_at')
        ->select($this->moduleSelectColumns(false))
        ->orderBy('position', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    if ($modules->isEmpty()) {
        return response()->json(['success' => true, 'data' => []]);
    }

    $ids = $modules->pluck('id')->all();

    $privilegesByMenuId = DB::table('page_privilege')
        ->whereIn('dashboard_menu_id', $ids)
        ->whereNull('deleted_at')
        ->select(
            'id',
            'uuid',
            'dashboard_menu_id',
            DB::raw('action as name'),
            'action',
            'description',
            'created_at'
        )
        ->orderBy('action', 'asc')
        ->get()
        ->groupBy('dashboard_menu_id');

    $byId = [];
    $byParent = [];

    foreach ($modules as $m) {
        $m->href = $m->href ? $this->normalizeHrefForResponse($m->href) : '';
        $m->children = [];
        $m->privileges = [];

        $byId[$m->id] = $m;
        $byParent[$m->parent_id][] = $m->id;
    }

    $makeTree = function ($pid) use (&$makeTree, &$byParent, &$byId, $privilegesByMenuId) {
        $nodes = [];

        foreach ($byParent[$pid] ?? [] as $id) {
            $node = $byId[$id];

            // recurse
            $node->children = $makeTree($node->id);

            // privileges only if NOT dropdown head
            if ((int)($node->is_dropdown_head ?? 0) === 0) {
                $node->privileges = $privilegesByMenuId[$node->id] ?? collect([]);
            }

            $nodes[] = $node;
        }

        return $nodes;
    };

    return response()->json([
        'success' => true,
        'data' => array_merge(
            $makeTree(null), // ✅ REAL ROOTS
            $makeTree(0)
        ),
    ]);
}


    public function tree(Request $r)
{
    $onlyActive = (int) $r->query('only_active', 0) === 1;

    $q = DB::table('dashboard_menu')
        ->whereNull('deleted_at');

    // ✅ Your table does NOT have `active` column.
    // Use `status` (default 'Active') from your migration.
    if ($onlyActive) {
        $q->whereRaw('LOWER(status) = ?', ['active']);
        // or: $q->where('status', 'Active');
    }

    $rows = $q->orderBy('position', 'asc')
              ->orderBy('id', 'asc')
              ->get();

    // Build tree in memory
    $byParent = [];
    foreach ($rows as $row) {
        $pid = $row->parent_id ?? 0; // keep your existing root handling
        $byParent[$pid][] = $row;
    }

    $make = function ($pid) use (&$make, &$byParent) {
        $nodes = $byParent[$pid] ?? [];
        foreach ($nodes as $n) {
            $n->children = $make($n->id);
        }
        return $nodes;
    };

    return response()->json([
        'success' => true,
        'data' => $make(0),
    ]);
}

}
          