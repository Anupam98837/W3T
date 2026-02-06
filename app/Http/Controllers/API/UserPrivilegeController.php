<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;

class UserPrivilegeController extends Controller
{
    /* =========================
     * Actor helper (who is doing the action)
     * ========================= */
    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    /* =========================
     * Utilities
     * ========================= */

    /** Normalize menu href for UI (keeps http(s) absolute; otherwise ensures single leading slash). */
    private function normalizeHrefForResponse($href): string
    {
        $href = (string) ($href ?? '');
        if ($href === '') return '';
        if (preg_match('#^https?://#i', $href)) return $href;
        return '/' . ltrim($href, '/');
    }

    private function resolveUserIdFromRequest(array $data): int
    {
        if (!empty($data['user_id'])) return (int) $data['user_id'];

        if (!empty($data['user_uuid'])) {
            return (int) DB::table('users')->where('uuid', $data['user_uuid'])->value('id');
        }

        return 0;
    }

    private function getUserUuid(int $userId): ?string
    {
        return DB::table('users')->where('id', $userId)->value('uuid');
    }

    /**
     * Get user_privileges row.
     * - $withTrashed=true  => include soft-deleted rows (needed for revive)
     * - $withTrashed=false => only active row
     */
    private function getUserPrivilegeRow(int $userId, bool $withTrashed = true): ?object
{
    $q = DB::table('user_privileges')->where('user_id', $userId);
    if (!$withTrashed) $q->whereNull('deleted_at');

    // ✅ Always pick the newest row (prevents random "first()" row)
    return $q->orderByDesc('id')->first();
}


    /** Simple guard: actor can view self or (admin/super_admin) can view others. */
    private function canViewUserModules(array $actor, int $targetUserId): bool
    {
        if ($actor['id'] === $targetUserId) return true;
        return in_array($actor['role'], ['admin', 'super_admin'], true);
    }

    /* ============================================================
     * TREE STORAGE HELPERS (✅ IMPORTANT)
     * We store TREE JSON in user_privileges.privileges (JSON column)
     * ============================================================ */

    /**
     * Decode DB stored privileges JSON.
     * Accepts:
     * - array (already decoded by driver) => return
     * - string json => json_decode
     * - old format [1,2,3] => convert to minimal tree
     */
    private function decodeStoredPrivileges($value): array
    {
        if ($value === null || $value === '') return [];

        // If mysql returns already decoded array (rare), accept it.
        if (is_array($value)) $arr = $value;
        else {
            $arr = json_decode((string) $value, true);
        }

        if (!is_array($arr)) return [];

        // old flat format: [1,2,3]
        $isFlat = true;
        foreach ($arr as $v) {
            if (!is_numeric($v)) { $isFlat = false; break; }
        }
        if ($isFlat) {
            $privObjs = array_map(fn($x)=>['id'=>(int)$x,'action'=>null], $arr);
            return [[
                'id' => 0,
                'type' => 'header',
                'children' => [[
                    'id' => 0,
                    'type' => 'page',
                    'privileges' => $privObjs
                ]]
            ]];
        }

        return $arr;
    }

    /**
     * Extract unique privilege IDs from a tree.
     * Also extract id=>action mapping from tree privilege objects if provided.
     *
     * Returns:
     * [
     *   'ids' => [1,2,3],
     *   'map' => [ 1=>'add', 2=>'edit' ]
     * ]
     */
    private function extractPrivilegeIdsFromTree(array $tree): array
    {
        $ids = [];
        $map = [];

        $walk = function ($nodes) use (&$walk, &$ids, &$map) {
            foreach ($nodes as $node) {
                if (isset($node['privileges']) && is_array($node['privileges'])) {
                    foreach ($node['privileges'] as $p) {
                        if (is_array($p) && isset($p['id'])) {
                            $pid = (int) $p['id'];
                            if ($pid > 0) {
                                $ids[] = $pid;
                                if (!empty($p['action'])) $map[$pid] = (string) $p['action'];
                            }
                        } elseif (is_numeric($p)) {
                            $pid = (int) $p;
                            if ($pid > 0) $ids[] = $pid;
                        }
                    }
                }

                if (!empty($node['children']) && is_array($node['children'])) {
                    $walk($node['children']);
                }
            }
        };

        $walk($tree);

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($x)=>$x>0)));
        sort($ids);

        return ['ids' => $ids, 'map' => $map];
    }

    /** Build id=>action map from DB table (authoritative) for privilege IDs. */
    private function actionMapFromDb(array $privIds): array
    {
        if (empty($privIds)) return [];

        $rows = DB::table('page_privilege')
            ->whereIn('id', $privIds)
            ->whereNull('deleted_at')
            ->select('id', 'action')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r->id] = (string) ($r->action ?? '');
        }
        return $map;
    }

    /**
     * Normalize incoming tree:
     * - keep only id/type/children/privileges
     * - ensure privileges stored as [{id, action}] (DB action is authoritative)
     */
    private function normalizeIncomingTree(array $tree): array
    {
        $extracted = $this->extractPrivilegeIdsFromTree($tree);
        $ids = $extracted['ids'];

        $dbActionMap = $this->actionMapFromDb($ids);

        $normalizeNode = function ($node) use (&$normalizeNode, $dbActionMap) {
            $out = [];

            $out['id'] = isset($node['id']) ? (int) $node['id'] : 0;
            if (!empty($node['type'])) $out['type'] = (string) $node['type'];

            if (!empty($node['privileges']) && is_array($node['privileges'])) {
                $privs = [];
                foreach ($node['privileges'] as $p) {
                    if (is_array($p) && isset($p['id'])) {
                        $pid = (int) $p['id'];
                        if ($pid > 0) {
                            $privs[] = [
                                'id'     => $pid,
                                'action' => $dbActionMap[$pid] ?? ($p['action'] ?? null),
                            ];
                        }
                    } elseif (is_numeric($p)) {
                        $pid = (int) $p;
                        if ($pid > 0) {
                            $privs[] = [
                                'id'     => $pid,
                                'action' => $dbActionMap[$pid] ?? null,
                            ];
                        }
                    }
                }

                if (!empty($privs)) {
                    $tmp = [];
                    foreach ($privs as $pp) $tmp[$pp['id']] = $pp; // unique by id
                    $out['privileges'] = array_values($tmp);
                }
            }

            if (!empty($node['children']) && is_array($node['children'])) {
                $children = [];
                foreach ($node['children'] as $c) $children[] = $normalizeNode($c);
                if (!empty($children)) $out['children'] = $children;
            }

            return $out;
        };

        $clean = [];
        foreach ($tree as $n) $clean[] = $normalizeNode($n);
        return $clean;
    }

    /**
     * ✅ Upsert (insert or update) the user_privileges row.
     * - Revives soft-deleted row
     * - DOES NOT touch created_at on update
     * - Stores tree into `privileges` JSON column (your migration)
     */
    private function upsertUserPrivilegesRow(Request $r, int $userId, array $tree, array $actor, $now): object
{
    // ✅ Keep only ONE row per user: newest row wins
    $rows = DB::table('user_privileges')
        ->where('user_id', $userId)
        ->orderByDesc('id')
        ->get(['id','deleted_at']);

    $keep = $rows->first();

    // Soft-delete all other rows (active OR trashed) to avoid future confusion
    $dupIds = $rows->skip(1)->pluck('id')->all();
    if (!empty($dupIds)) {
        DB::table('user_privileges')
            ->whereIn('id', $dupIds)
            ->update([
                'deleted_at' => $now,
                'updated_at' => $now,
            ]);
    }

    $payloadToStore = json_encode($tree);

    if ($keep) {
        DB::table('user_privileges')
            ->where('id', $keep->id)
            ->update([
                'privileges'    => $payloadToStore,
                'assigned_by'   => $actor['id'] ?: null,
                'created_at_ip' => $r->ip(),
                'deleted_at'    => null, // ✅ revive the kept row if it was trashed
                'updated_at'    => $now,
            ]);

        return DB::table('user_privileges')->where('id', $keep->id)->first();
    }

    $id = DB::table('user_privileges')->insertGetId([
        'uuid'          => (string) Str::uuid(),
        'user_id'       => $userId,
        'privileges'    => $payloadToStore,
        'assigned_by'   => $actor['id'] ?: null,
        'created_at_ip' => $r->ip(),
        'created_at'    => $now,
        'updated_at'    => $now,
    ]);

    return DB::table('user_privileges')->where('id', $id)->first();
}

/**
 * Build TREE structure in required format:
 * [
 *   { id: headerId, type:'header', children:[
 *       { id: pageId, type:'page', privileges:[{id,action},...] }
 *   ]}
 * ]
 *
 * This guarantees DB stores header->page->privileges mapping.
 */
private function buildTreeFromPrivilegeIds(array $privIds): array
{
    $privIds = array_values(array_unique(array_filter(array_map('intval', $privIds), fn($x)=>$x>0)));
    if (empty($privIds)) return [];

    // Pull mapping from DB
    $rows = DB::table('page_privilege as p')
        ->join('dashboard_menu as m', 'm.id', '=', 'p.dashboard_menu_id')
        ->whereIn('p.id', $privIds)
        ->whereNull('p.deleted_at')
        ->whereNull('m.deleted_at')
        ->select([
            'p.id as priv_id',
            'p.action as priv_action',
            'p.dashboard_menu_id as page_id',
            'm.parent_id',
            'm.is_dropdown_head',
        ])
        ->get();

    // headerId => pageId => [privileges...]
    $bucket = [];

    foreach ($rows as $r) {
        $pageId = (int) $r->page_id;

        // header logic:
        // - if page has parent => that is header
        // - else if page itself is dropdown head => header = page
        // - else header = 0 (no parent)
        $headerId = 0;
        if (!is_null($r->parent_id)) {
            $headerId = (int) $r->parent_id;
        } elseif ((int)($r->is_dropdown_head ?? 0) === 1) {
            $headerId = $pageId;
        }

        $bucket[$headerId][$pageId][] = [
            'id' => (int) $r->priv_id,
            'action' => (string) ($r->priv_action ?? null),
        ];
    }

    // Build final tree
    ksort($bucket);
    $tree = [];

    foreach ($bucket as $headerId => $pages) {
        ksort($pages);
        $children = [];

        foreach ($pages as $pageId => $privs) {
            // unique privileges by id
            $tmp = [];
            foreach ($privs as $p) $tmp[(int)$p['id']] = $p;
            $privs = array_values($tmp);

            $children[] = [
                'id' => (int) $pageId,
                'type' => 'page',
                'privileges' => $privs,
            ];
        }

        $tree[] = [
            'id' => (int) $headerId,
            'type' => 'header',
            'children' => $children,
        ];
    }

    return $tree;
}

  public function sync(Request $r)
{
    $data = $r->validate([
        'user_id'   => 'sometimes|integer|exists:users,id',
        'user_uuid' => 'sometimes|uuid|exists:users,uuid',

        'tree' => 'sometimes|array|min:0',

        // header
        'tree.*.id' => 'required|integer|min:0',
        'tree.*.type' => 'required|string|in:header',
        'tree.*.children' => 'required|array|min:1',

        // page
        'tree.*.children.*.id' => 'required|integer|min:0',
        'tree.*.children.*.type' => 'required|string|in:page',
        'tree.*.children.*.privileges' => 'required|array|min:0',

        // privileges
        'tree.*.children.*.privileges.*.id' => 'required|integer|exists:page_privilege,id',
        'tree.*.children.*.privileges.*.action' => 'nullable|string',
    ]);

    $userId = $this->resolveUserIdFromRequest($data);
    if (!$userId) return response()->json(['error' => 'User not found'], 404);

    $actor = $this->actor($r);
    $now   = now();

    // normalize actions using DB (authoritative)
    $newTree = $this->normalizeIncomingTree($data['tree']);
    $newIds  = $this->extractPrivilegeIdsFromTree($newTree)['ids'];

    try {
        $result = DB::transaction(function () use ($r, $userId, $newTree, $newIds, $actor, $now) {

            $row = $this->getUserPrivilegeRow($userId, true);
            $currentTree = $row ? $this->decodeStoredPrivileges($row->privileges ?? null) : [];
            $curIds = $this->extractPrivilegeIdsFromTree($currentTree)['ids'];

            $addedIds   = array_values(array_diff($newIds, $curIds));
            $removedIds = array_values(array_diff($curIds, $newIds));

            $actionMap = $this->actionMapFromDb(array_values(array_unique(array_merge($newIds, $curIds))));
            $added   = array_map(fn($id)=>['id'=>(int)$id, 'action'=>($actionMap[(int)$id] ?? null)], $addedIds);
            $removed = array_map(fn($id)=>['id'=>(int)$id, 'action'=>($actionMap[(int)$id] ?? null)], $removedIds);

            // ✅ store EXACT tree sent (normalized)
            $savedRow = $this->upsertUserPrivilegesRow($r, $userId, $newTree, $actor, $now);

            return [
                'row' => $savedRow,
                'added' => $added,
                'removed' => $removed,
                'saved_ids' => $newIds,
                'saved_tree' => $newTree,
            ];
        });

        return response()->json([
            'message'    => 'Privileges synced successfully (tree stored).',
            'user_uuid'  => $this->getUserUuid($userId),
            'user_privileges_uuid' => $result['row']->uuid ?? null,
            'added'      => $result['added'],
            'removed'    => $result['removed'],
            'saved_count'=> count($result['saved_ids']),
            'saved_ids'  => $result['saved_ids'],
            'tree'       => $result['saved_tree'],
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'error'  => 'Could not sync privileges',
            'detail' => $e->getMessage()
        ], 500);
    }
}
public function assign(Request $r)
{
    $data = $r->validate([
        'user_id'   => 'sometimes|integer|exists:users,id',
        'user_uuid' => 'sometimes|uuid|exists:users,uuid',

        'privilege_id'     => 'sometimes|integer|exists:page_privilege,id',
        'privilege_ids'    => 'sometimes|array|min:1',
        'privilege_ids.*'  => 'integer|exists:page_privilege,id',

        'tree'             => 'sometimes|array',
        'tree.*.id'        => 'required_with:tree|integer',
    ]);

    $userId = $this->resolveUserIdFromRequest($data);
    if (!$userId) return response()->json(['message' => 'User not found'], 404);

    $actor = $this->actor($r);
    $now   = now();

    $incomingIds = [];

    if (!empty($data['privilege_id'])) $incomingIds[] = (int) $data['privilege_id'];
    if (!empty($data['privilege_ids']) && is_array($data['privilege_ids'])) {
        foreach ($data['privilege_ids'] as $pid) $incomingIds[] = (int) $pid;
    }

    // If they send tree, take ids from it too
    if (!empty($data['tree']) && is_array($data['tree'])) {
        $normTree = $this->normalizeIncomingTree($data['tree']);
        $incomingIds = array_merge($incomingIds, $this->extractPrivilegeIdsFromTree($normTree)['ids']);
    }

    $incomingIds = array_values(array_unique(array_filter(array_map('intval', $incomingIds), fn($x)=>$x>0)));
    if (empty($incomingIds)) return response()->json(['message' => 'No privileges found in payload.'], 422);

    try {
        $result = DB::transaction(function () use ($r, $userId, $incomingIds, $actor, $now) {

            $row = $this->getUserPrivilegeRow($userId, true);
            $currentTree = $row ? $this->decodeStoredPrivileges($row->privileges ?? null) : [];
            $curIds = $this->extractPrivilegeIdsFromTree($currentTree)['ids'];

            $mergedIds = array_values(array_unique(array_merge($curIds, $incomingIds)));
            sort($mergedIds);

            // ✅ KEY: Build the required header->page tree from DB mapping
            $finalTree = $this->buildTreeFromPrivilegeIds($mergedIds);

            // store
            $savedRow = $this->upsertUserPrivilegesRow($r, $userId, $finalTree, $actor, $now);

            // response diff
            $actionMap = $this->actionMapFromDb($mergedIds);
            $addedIds = array_values(array_diff($mergedIds, $curIds));
            $added = array_map(fn($id)=>['id'=>(int)$id, 'action'=>($actionMap[(int)$id] ?? null)], $addedIds);

            return [
                'row' => $savedRow,
                'added' => $added,
                'tree' => $finalTree,
                'ids' => $mergedIds,
            ];
        });

        return response()->json([
            'message'     => 'Privilege(s) assigned (tree stored).',
            'user_uuid'   => $this->getUserUuid($userId),
            'user_privileges_uuid' => $result['row']->uuid ?? null,
            'added'       => $result['added'],
            'saved_count' => count($result['ids']),
            'saved_ids'   => $result['ids'],
            'tree'        => $result['tree'],
        ], 201);

    } catch (\Throwable $e) {
        return response()->json(['message' => 'Could not assign privilege', 'detail' => $e->getMessage()], 500);
    }
}


    /* ============================================================
     * UNASSIGN (remove one page_privilege id from stored tree)
     * ============================================================ */
    public function unassign(Request $r)
    {
        $data = $r->validate([
            'user_id'      => 'sometimes|integer|exists:users,id',
            'user_uuid'    => 'sometimes|uuid|exists:users,uuid',
            'privilege_id' => 'required|integer|exists:page_privilege,id',
        ]);

        $userId = $this->resolveUserIdFromRequest($data);
        if (!$userId) return response()->json(['message' => 'User not found'], 404);

        $privId = (int) $data['privilege_id'];
        $actor  = $this->actor($r);
        $now    = now();

        try {
            $affected = DB::transaction(function () use ($r, $userId, $privId, $actor, $now) {

                $row = $this->getUserPrivilegeRow($userId, false);
                if (!$row) return 0;

                $tree = $this->decodeStoredPrivileges($row->privileges ?? null);

                $removeFromNodes = function (&$nodes) use (&$removeFromNodes, $privId) {
                    foreach ($nodes as &$node) {
                        if (!empty($node['privileges']) && is_array($node['privileges'])) {
                            $node['privileges'] = array_values(array_filter($node['privileges'], function ($p) use ($privId) {
                                if (is_array($p) && isset($p['id'])) return (int) $p['id'] !== $privId;
                                if (is_numeric($p)) return (int) $p !== $privId;
                                return true;
                            }));
                        }
                        if (!empty($node['children']) && is_array($node['children'])) {
                            $removeFromNodes($node['children']);
                        }
                    }
                };

                $beforeIds = $this->extractPrivilegeIdsFromTree($tree)['ids'];
                $removeFromNodes($tree);
                $afterIds = $this->extractPrivilegeIdsFromTree($tree)['ids'];

                if (count($afterIds) === count($beforeIds)) return 0;

                $finalTree = $this->normalizeIncomingTree($tree);

                DB::table('user_privileges')
                    ->where('id', $row->id)
                    ->update([
                        'privileges'    => json_encode($finalTree),
                        'assigned_by'   => $actor['id'] ?: null,
                        'created_at_ip' => $r->ip(),
                        'updated_at'    => $now,
                    ]);

                return 1;
            });

            return $affected
                ? response()->json(['message' => 'Privilege unassigned.', 'user_uuid' => $this->getUserUuid($userId)])
                : response()->json(['message' => 'Privilege not found for this user.'], 404);

        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not unassign privilege', 'detail' => $e->getMessage()], 500);
        }
    }

    /* ============================================================
     * DESTROY (soft-delete whole row)
     * ============================================================ */
    public function destroy(Request $r)
    {
        $data = $r->validate([
            'uuid'      => 'sometimes|uuid|exists:user_privileges,uuid',
            'user_id'   => 'sometimes|integer|exists:users,id',
            'user_uuid' => 'sometimes|uuid|exists:users,uuid',
        ]);

        $now = now();

        try {
            $affected = 0;
            $userId = 0;

            if (!empty($data['uuid'])) {
                $row = DB::table('user_privileges')->where('uuid', $data['uuid'])->first();
                if (!$row) return response()->json(['message' => 'Not found'], 404);

                $userId = (int) $row->user_id;

                $affected = DB::table('user_privileges')
                    ->where('uuid', $data['uuid'])
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);
            } else {
                $userId = $this->resolveUserIdFromRequest($data);
                if (!$userId) return response()->json(['message' => 'user_id or user_uuid (or uuid) is required'], 422);

                $affected = DB::table('user_privileges')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);
            }

            if (!$affected) {
                return response()->json(['message' => 'User privilege record not found (or already deleted).'], 404);
            }

            return response()->json([
                'message'   => 'User privileges removed successfully.',
                'user_uuid' => $this->getUserUuid($userId),
            ]);

        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not remove user privileges', 'detail' => $e->getMessage()], 500);
        }
    }

    /* ============================================================
     * LIST (return stored tree + flat IDs + privilege rows)
     * ============================================================ */
    public function list(Request $r)
    {
        $data = $r->validate([
            'user_id'   => 'sometimes|integer|exists:users,id',
            'user_uuid' => 'sometimes|uuid|exists:users,uuid',
        ]);

        $userId = $this->resolveUserIdFromRequest($data);
        if (!$userId) return response()->json(['message' => 'user_id or user_uuid is required'], 422);

        $row = DB::table('user_privileges')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();

        $tree = $row ? $this->decodeStoredPrivileges($row->privileges ?? null) : [];
        $flatIds = $this->extractPrivilegeIdsFromTree($tree)['ids'];

        $privs = [];
        if (!empty($flatIds)) {
            $privs = DB::table('page_privilege')
                ->whereIn('id', $flatIds)
                ->whereNull('deleted_at')
                ->select([
                    'id',
                    'uuid',
                    DB::raw('action as name'),
                    'action',
                    'description',
                    'dashboard_menu_id',
                    'created_at',
                ])
                ->orderBy('action', 'asc')
                ->get();
        }

        return response()->json([
            'user_uuid'            => $this->getUserUuid($userId),
            'user_privileges_uuid' => $row->uuid ?? null,

            'tree'               => $tree,
            'flat_privilege_ids' => $flatIds,

            'data'               => $privs,
        ]);
    }

    /* ============================================================
     * USERS (utility endpoints)
     * ============================================================ */
    public function show($idOrUuid)
    {
        if (is_numeric($idOrUuid)) {
            $user = DB::table('users')->where('id', $idOrUuid)->whereNull('deleted_at')->first();
        } else {
            $user = DB::table('users')->where('uuid', $idOrUuid)->whereNull('deleted_at')->first();
        }

        if (!$user) return response()->json(['message' => 'User not found'], 404);

        return response()->json(['user' => $user]);
    }

    public function byUuid(Request $request)
    {
        $request->validate(['uuid' => 'required|uuid']);

        $user = DB::table('users')
            ->where('uuid', $request->uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) return response()->json(['message' => 'User not found'], 404);

        return response()->json(['user' => $user]);
    }

    /* ============================================================
     * DASHBOARD MENUS from stored privileges (tree->flatten IDs)
     * ============================================================ */

    /** Convenience: current actor’s dashboard menus */
    public function myModules(Request $r)
    {
        $actor = $this->actor($r);
        if (!$actor['id']) return response()->json(['error' => 'Unauthenticated'], 401);

        return $this->modulesFor($r, (int) $actor['id']);
    }

    /** For admins (or self): dashboard menus for a given user via query */
    public function modulesForUser(Request $r)
    {
        $r->validate([
            'user_id'         => 'sometimes|integer|exists:users,id',
            'user_uuid'       => 'sometimes|uuid|exists:users,uuid',
            'with_privileges' => 'sometimes|boolean',
            'status'          => 'sometimes|string', // 'all' | 'archived' | etc
        ]);

        $actor = $this->actor($r);

        $targetUserId = 0;
        if ($r->filled('user_id')) {
            $targetUserId = (int) $r->input('user_id');
        } elseif ($r->filled('user_uuid')) {
            $targetUserId = (int) DB::table('users')->where('uuid', $r->input('user_uuid'))->value('id');
        } else {
            $targetUserId = (int) $actor['id'];
        }

        if (!$targetUserId) return response()->json(['message' => 'User not found'], 404);
        if (!$this->canViewUserModules($actor, $targetUserId)) return response()->json(['error' => 'Forbidden'], 403);

        return $this->modulesFor($r, $targetUserId);
    }

    /** For admins (or self): dashboard menus for a given user via path */
    public function modulesForUserByPath(Request $r, $idOrUuid)
    {
        $actor = $this->actor($r);

        $targetUserId = ctype_digit((string) $idOrUuid)
            ? (int) $idOrUuid
            : (int) DB::table('users')->where('uuid', $idOrUuid)->value('id');

        if (!$targetUserId) return response()->json(['message' => 'User not found'], 404);
        if (!$this->canViewUserModules($actor, $targetUserId)) return response()->json(['error' => 'Forbidden'], 403);

        return $this->modulesFor($r, $targetUserId);
    }

    /** Core query: dashboard_menu derived from page_privilege IDs stored in TREE JSON */
    private function modulesFor(Request $r, int $userId)
    {
        $row = DB::table('user_privileges')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();

        $tree = $row ? $this->decodeStoredPrivileges($row->privileges ?? null) : [];
        $privIds = $this->extractPrivilegeIdsFromTree($tree)['ids'];

        if (empty($privIds)) {
            return response()->json([
                'user_uuid' => $this->getUserUuid($userId),
                'data'      => [],
            ]);
        }

        $menuCols = ['m.id','m.uuid','m.name','m.description','m.created_at','m.updated_at'];

        if (Schema::hasColumn('dashboard_menu', 'href'))             $menuCols[] = 'm.href';
        if (Schema::hasColumn('dashboard_menu', 'status'))           $menuCols[] = 'm.status';
        if (Schema::hasColumn('dashboard_menu', 'icon_class'))       $menuCols[] = 'm.icon_class';
        if (Schema::hasColumn('dashboard_menu', 'parent_id'))        $menuCols[] = 'm.parent_id';
        if (Schema::hasColumn('dashboard_menu', 'is_dropdown_head')) $menuCols[] = 'm.is_dropdown_head';

        if (Schema::hasColumn('dashboard_menu', 'order_no')) { $orderCol = 'm.order_no'; $orderDir = 'asc'; }
        else { $orderCol = 'm.name'; $orderDir = 'asc'; }

        $q = DB::table('page_privilege as p')
            ->join('dashboard_menu as m', 'm.id', '=', 'p.dashboard_menu_id')
            ->whereIn('p.id', $privIds)
            ->whereNull('p.deleted_at')
            ->whereNull('m.deleted_at')
            ->select($menuCols)
            ->distinct();

        if (Schema::hasColumn('dashboard_menu', 'status')) {
            if ($r->filled('status')) {
                $status = (string) $r->query('status');
                if ($status === 'archived') {
                    $q->where('m.status', 'archived');
                } elseif ($status !== 'all') {
                    $q->where('m.status', $status);
                }
            } else {
                $q->where(function ($qq) {
                    $qq->whereNull('m.status')->orWhere('m.status', '!=', 'archived');
                });
            }
        }

        $menus = $q->orderBy($orderCol, $orderDir)->get();

        $withPriv = filter_var($r->query('with_privileges', false), FILTER_VALIDATE_BOOLEAN);
        $privByMenu = collect();

        if ($withPriv && $menus->isNotEmpty()) {
            $menuIds = $menus->pluck('id')->all();

            $privByMenu = DB::table('page_privilege as p')
                ->whereIn('p.id', $privIds)
                ->whereIn('p.dashboard_menu_id', $menuIds)
                ->whereNull('p.deleted_at')
                ->select(
                    'p.id',
                    'p.uuid',
                    'p.dashboard_menu_id',
                    DB::raw('p.action as name'),
                    'p.action',
                    'p.description',
                    'p.created_at'
                )
                ->orderBy('p.action', 'asc')
                ->get()
                ->groupBy('dashboard_menu_id');
        }

        $menus->transform(function ($m) use ($withPriv, $privByMenu) {
            if (isset($m->href)) $m->href = $this->normalizeHrefForResponse($m->href);
            $m->privileges = $withPriv
                ? ($privByMenu->has($m->id) ? $privByMenu[$m->id]->values() : collect([]))
                : collect([]);
            return $m;
        });

        return response()->json([
            'user_uuid' => $this->getUserUuid($userId),
            'data'      => $menus->values(),
        ]);
    }
    
/**
 * GET /api/my/sidebar-menus?with_actions=1
 *
 * Builds sidebar tree from:
 * - user_privileges.privileges (TREE JSON)
 * - dashboard_menu (menu metadata)
 * - page_privilege (optional: actions list)
 *
 * Returns:
 * {
 *   user_uuid: "...",
 *   tree: [
 *     {
 *       id, type:"header", name, href, icon_class, status, is_dropdown_head, children:[
 *         {
 *           id, type:"page", name, href, icon_class, status, parent_id,
 *           privilege_ids:[1,2],
 *           actions:["add","edit"] // only if with_actions=1
 *         }
 *       ]
 *     }
 *   ]
 * }
 */
public function mySidebarMenus(Request $r)
{
    $actor = $this->actor($r);
    if (empty($actor['id'])) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

        // ✅ If admin => return string "all"
        if (($actor['role'] ?? '') === 'admin') {
            return response()->json([
                'user_uuid' => $this->getUserUuid((int)$actor['id']),
                'tree'      => 'all',   // ✅ string response
            ], 200);
        }

    $withActions = filter_var($r->query('with_actions', false), FILTER_VALIDATE_BOOLEAN);

    // 1) Load stored tree for actor
    $row = DB::table('user_privileges')
        ->where('user_id', (int)$actor['id'])
        ->whereNull('deleted_at')
        ->first();

    $storedTree = $row ? $this->decodeStoredPrivileges($row->privileges ?? null) : [];

    // If user has no privileges => sidebar empty
    if (empty($storedTree) || !is_array($storedTree)) {
        return response()->json([
            'user_uuid' => $this->getUserUuid((int)$actor['id']),
            'tree'      => [],
        ], 200);
    }

    // 2) Collect ALL menu IDs present in stored tree (header + pages)
    $menuIds = [];
    foreach ($storedTree as $h) {
        if (is_array($h) && !empty($h['id'])) $menuIds[] = (int)$h['id'];
        if (!empty($h['children']) && is_array($h['children'])) {
            foreach ($h['children'] as $p) {
                if (is_array($p) && !empty($p['id'])) $menuIds[] = (int)$p['id'];
            }
        }
    }
    $menuIds = array_values(array_unique(array_filter($menuIds, fn($x)=>$x>0)));

    // 3) Pull dashboard_menu metadata for those IDs
    $menuCols = ['id','uuid','name','description','created_at','updated_at'];
    if (Schema::hasColumn('dashboard_menu', 'href')) $menuCols[] = 'href';
    if (Schema::hasColumn('dashboard_menu', 'icon_class')) $menuCols[] = 'icon_class';
    if (Schema::hasColumn('dashboard_menu', 'status')) $menuCols[] = 'status';
    if (Schema::hasColumn('dashboard_menu', 'parent_id')) $menuCols[] = 'parent_id';
    if (Schema::hasColumn('dashboard_menu', 'is_dropdown_head')) $menuCols[] = 'is_dropdown_head';
    if (Schema::hasColumn('dashboard_menu', 'order_no')) $menuCols[] = 'order_no';

    $menus = DB::table('dashboard_menu')
        ->whereIn('id', $menuIds)
        ->whereNull('deleted_at')
        ->get($menuCols);

    $menuById = [];
    foreach ($menus as $m) {
        $mid = (int)$m->id;
        $menuById[$mid] = $m;
    }

    // 4) Optional actions map (privilege_id -> action)
    // We'll read privilege ids per page directly from stored tree.
    $actionsByPrivId = [];
    if ($withActions) {
        $allPrivIds = $this->extractPrivilegeIdsFromTree($storedTree)['ids'];
        if (!empty($allPrivIds)) {
            $rows = DB::table('page_privilege')
                ->whereIn('id', $allPrivIds)
                ->whereNull('deleted_at')
                ->get(['id','action']);
            foreach ($rows as $pr) {
                $actionsByPrivId[(int)$pr->id] = strtolower(trim((string)($pr->action ?? '')));
            }
        }
    }

    // 5) Build sidebar tree from STORED tree ordering (your UI ordering is preserved)
    $outTree = [];

    foreach ($storedTree as $headerNode) {
        if (!is_array($headerNode)) continue;

        $hid = (int)($headerNode['id'] ?? 0);
        if ($hid <= 0) continue;

        $hm = $menuById[$hid] ?? null;

        $hOut = [
            'id'   => $hid,
            'type' => 'header',
            'name' => $hm->name ?? ($headerNode['name'] ?? null),
        ];

        if ($hm && isset($hm->href)) $hOut['href'] = $this->normalizeHrefForResponse($hm->href);
        if ($hm && property_exists($hm, 'icon_class')) $hOut['icon_class'] = $hm->icon_class ?? null;
        if ($hm && property_exists($hm, 'status')) $hOut['status'] = $hm->status ?? null;
        if ($hm && property_exists($hm, 'is_dropdown_head')) $hOut['is_dropdown_head'] = (bool)($hm->is_dropdown_head ?? false);

        $hOut['children'] = [];

        $children = $headerNode['children'] ?? [];
        if (is_array($children)) {
            foreach ($children as $pageNode) {
                if (!is_array($pageNode)) continue;

                $pid = (int)($pageNode['id'] ?? 0);
                if ($pid <= 0) continue;

                $pm = $menuById[$pid] ?? null;

                // privilege ids from stored page node
                $pagePrivIds = [];
                if (!empty($pageNode['privileges']) && is_array($pageNode['privileges'])) {
                    foreach ($pageNode['privileges'] as $pp) {
                        $ppid = is_array($pp) ? (int)($pp['id'] ?? 0) : (is_numeric($pp) ? (int)$pp : 0);
                        if ($ppid > 0) $pagePrivIds[] = $ppid;
                    }
                }
                $pagePrivIds = array_values(array_unique($pagePrivIds));

                // If no privileges for this page => do not show it in sidebar
                if (empty($pagePrivIds)) continue;

                $pOut = [
                    'id'           => $pid,
                    'type'         => 'page',
                    'name'         => $pm->name ?? ($pageNode['name'] ?? null),
                    'privilege_ids'=> $pagePrivIds,
                ];

                if ($pm && isset($pm->href)) $pOut['href'] = $this->normalizeHrefForResponse($pm->href);
                if ($pm && property_exists($pm, 'icon_class')) $pOut['icon_class'] = $pm->icon_class ?? null;
                if ($pm && property_exists($pm, 'status')) $pOut['status'] = $pm->status ?? null;
                if ($pm && property_exists($pm, 'parent_id')) $pOut['parent_id'] = $pm->parent_id ? (int)$pm->parent_id : null;

                if ($withActions) {
                    $acts = [];
                    foreach ($pagePrivIds as $ppid) {
                        $a = $actionsByPrivId[(int)$ppid] ?? '';
                        if ($a !== '') $acts[] = $a;
                    }
                    $acts = array_values(array_unique($acts));
                    sort($acts);
                    $pOut['actions'] = $acts;
                }

                $hOut['children'][] = $pOut;
            }
        }

        // If header has no visible pages => skip header
        if (!empty($hOut['children'])) {
            $outTree[] = $hOut;
        }
    }

    return response()->json([
        'user_uuid' => $this->getUserUuid((int)$actor['id']),
        'tree'      => $outTree,
    ], 200);
}
public function myPrivileges(Request $request)
{
    // ✅ same actor-id logic you already use
    $userId = (int) ($request->attributes->get('auth_tokenable_id') ?? optional($request->user())->id ?? 0);
    if ($userId <= 0) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    }

    // Required tables
    if (!Schema::hasTable('user_privileges')) {
        return response()->json(['success' => false, 'message' => 'user_privileges table not found'], 500);
    }
    if (!Schema::hasTable('dashboard_menu')) {
        return response()->json(['success' => false, 'message' => 'dashboard_menu table not found'], 500);
    }
    if (!Schema::hasTable('page_privilege')) {
        return response()->json(['success' => false, 'message' => 'page_privilege table not found'], 500);
    }

    // ✅ One row per user (non-deleted)
    $row = DB::table('user_privileges')
        ->where('user_id', $userId)
        ->when(Schema::hasColumn('user_privileges', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
        ->first();

    if (!$row || empty($row->privileges)) {
        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'data'    => [],
            'flat'    => ['privileges' => []],
            'current' => null,
        ]);
    }

    // Decode JSON tree
    $treeJson = json_decode($row->privileges, true);
    if (!is_array($treeJson) || json_last_error() !== JSON_ERROR_NONE) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid privileges JSON in user_privileges.privileges',
        ], 500);
    }

    /**
     * Collect grants:
     * $grantsByMenu[menu_id] = [
     *   'privilege_ids' => [1,2,3],
     *   'actions'       => ['add','edit',...]
     * ];
     */
    $grantsByMenu = [];

    $walk = function ($nodes) use (&$walk, &$grantsByMenu) {
        if (!is_array($nodes)) return;

        foreach ($nodes as $node) {
            if (!is_array($node)) continue;

            $menuId = isset($node['id']) ? (int)$node['id'] : 0;
            if ($menuId > 0) {
                if (!isset($grantsByMenu[$menuId])) {
                    $grantsByMenu[$menuId] = ['privilege_ids' => [], 'actions' => []];
                }

                // privileges at this node (usually on child/page node)
                if (!empty($node['privileges']) && is_array($node['privileges'])) {
                    foreach ($node['privileges'] as $p) {
                        if (!is_array($p)) continue;

                        if (isset($p['id']) && is_numeric($p['id'])) {
                            $grantsByMenu[$menuId]['privilege_ids'][] = (int)$p['id'];
                        }
                        if (isset($p['action']) && $p['action'] !== '') {
                            $grantsByMenu[$menuId]['actions'][] = strtolower(trim((string)$p['action']));
                        }
                    }
                }
            }

            // recurse children
            if (!empty($node['children']) && is_array($node['children'])) {
                $walk($node['children']);
            }
        }
    };

    $walk($treeJson);

    // No grants => no visible menus
    $allowedMenuIds = array_keys($grantsByMenu);
    if (empty($allowedMenuIds)) {
        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'data'    => [],
            'flat'    => ['privileges' => []],
            'current' => null,
        ]);
    }

    // ✅ Load ALL menus (non-deleted, non-archived) so we can include parent chain
    $menuQuery = DB::table('dashboard_menu')->whereNull('deleted_at');

    if (Schema::hasColumn('dashboard_menu', 'status')) {
        $menuQuery->where(function ($q) {
            $q->whereNull('status')->orWhereRaw("LOWER(status) != 'archived'");
        });
    }

    // Use your selector if exists, else fallback
    $selectCols = method_exists($this, 'moduleSelectColumns')
        ? $this->moduleSelectColumns(false)
        : ['id','uuid','parent_id','name','href','status','icon_class','is_dropdown_head','position','created_at','updated_at'];

    $menus = $menuQuery->select($selectCols)->get();

    if ($menus->isEmpty()) {
        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'data'    => [],
            'flat'    => ['privileges' => []],
            'current' => null,
        ]);
    }

    // id => menu
    $byId = [];
    foreach ($menus as $m) {
        // normalize href (if helper exists)
        if (isset($m->href)) {
            if (method_exists($this, 'normalizeHrefForResponse')) {
                $m->href = $this->normalizeHrefForResponse($m->href);
            } else {
                $m->href = $m->href ? ('/' . ltrim($m->href, '/')) : '';
            }
        }

        $m->children   = [];
        $m->privileges = []; // will attach
        $byId[(int)$m->id] = $m;
    }

    // ✅ Include ancestors so dropdown heads show
    $includeIds = [];
    foreach ($allowedMenuIds as $mid) {
        $cur = (int)$mid;
        $guard = 0;

        while ($cur > 0 && isset($byId[$cur]) && $guard < 60) {
            $includeIds[$cur] = true;

            $parent = $byId[$cur]->parent_id ?? null;
            if ($parent === null || (int)$parent === 0) break;

            $cur = (int)$parent;
            $guard++;
        }
    }

    // ✅ Collect privilege ids + actions from grants
    $wantedPrivIds = [];
    $wantedActionsByMenu = []; // menu_id => set(actions)
    foreach ($grantsByMenu as $mid => $g) {
        if (!empty($g['privilege_ids'])) {
            foreach ($g['privilege_ids'] as $pid) $wantedPrivIds[$pid] = true;
        }
        if (!empty($g['actions'])) {
            foreach ($g['actions'] as $act) $wantedActionsByMenu[$mid][$act] = true;
        }
    }
    $wantedPrivIds = array_keys($wantedPrivIds);

    // ✅ Fetch detailed privileges from page_privilege
    $ppCols = ['id','uuid','dashboard_menu_id','action','description','created_at'];
    if (Schema::hasColumn('page_privilege', 'key'))           $ppCols[] = 'key';
    if (Schema::hasColumn('page_privilege', 'status'))        $ppCols[] = 'status';
    if (Schema::hasColumn('page_privilege', 'order_no'))      $ppCols[] = 'order_no';
    if (Schema::hasColumn('page_privilege', 'assigned_apis')) $ppCols[] = 'assigned_apis';
    if (Schema::hasColumn('page_privilege', 'meta'))          $ppCols[] = 'meta';

    $ppQuery = DB::table('page_privilege')->select($ppCols);

    if (Schema::hasColumn('page_privilege', 'deleted_at')) {
        $ppQuery->whereNull('deleted_at');
    }
    if (Schema::hasColumn('page_privilege', 'status')) {
        $ppQuery->where(function ($q) {
            $q->whereNull('status')->orWhereRaw("LOWER(status) != 'archived'");
        });
    }

    // Prefer ID list if JSON stores IDs
    if (!empty($wantedPrivIds)) {
        $ppQuery->whereIn('id', $wantedPrivIds);
    } else {
        // If JSON only stores actions: load privileges for those menus and filter by action later
        $ppQuery->whereIn('dashboard_menu_id', array_keys($wantedActionsByMenu));
    }

    $privileges = $ppQuery
        ->orderBy('dashboard_menu_id', 'asc')
        ->orderBy('action', 'asc')
        ->get();

    // Group privileges by menu_id, and filter to allowed actions if needed
    $privByMenu = $privileges->groupBy('dashboard_menu_id')->map(function ($list, $menuId) use ($wantedActionsByMenu, $wantedPrivIds) {
        if (!empty($wantedPrivIds)) {
            return $list->values();
        }
        // filter by allowed actions for this menu
        $allowed = $wantedActionsByMenu[(int)$menuId] ?? [];
        if (empty($allowed)) return collect([]);
        return $list->filter(function ($p) use ($allowed) {
            $a = strtolower((string)($p->action ?? ''));
            return isset($allowed[$a]);
        })->values();
    });

    // ✅ Attach privileges to nodes (only to included nodes)
    foreach (array_keys($includeIds) as $id) {
        $node = $byId[$id];

        // only attach privileges where user has grants for that menu_id
        if (isset($grantsByMenu[$id])) {
            // Optional rule: do not attach privileges to dropdown-head nodes
            if ((int)($node->is_dropdown_head ?? 0) === 0) {
                $node->privileges = ($privByMenu[$id] ?? collect([]))->values();
            }
        }
    }

    // ✅ Build parent -> children ONLY for included nodes
    $byParent = [];
    foreach (array_keys($includeIds) as $id) {
        $pid = $byId[$id]->parent_id ?? null;
        $byParent[$pid][] = (int)$id;
    }

    // ✅ Sort children by position then id (if position exists)
    $hasPosition = Schema::hasColumn('dashboard_menu', 'position');
    foreach ($byParent as $pid => $childIds) {
        usort($childIds, function ($a, $b) use ($byId, $hasPosition) {
            if ($hasPosition) {
                $pa = (int)($byId[$a]->position ?? 0);
                $pb = (int)($byId[$b]->position ?? 0);
                if ($pa !== $pb) return $pa <=> $pb;
            }
            return (int)$a <=> (int)$b;
        });
        $byParent[$pid] = $childIds;
    }

    // ✅ Build tree (roots: null and 0)
    $makeTree = function ($pid) use (&$makeTree, &$byParent, &$byId) {
        $nodes = [];
        foreach ($byParent[$pid] ?? [] as $id) {
            $node = $byId[$id];
            $node->children = $makeTree($node->id);
            $nodes[] = $node;
        }
        return $nodes;
    };

    $outTree = array_merge($makeTree(null), $makeTree(0));

    // ✅ Optional: return privileges for a specific current menu (helpful for UI)
    $current = null;

    $menuIdParam  = (int) ($request->query('menu_id') ?? 0);
    $menuHrefParam = trim((string)($request->query('menu_href') ?? $request->query('href') ?? ''));

    if ($menuIdParam > 0 && isset($byId[$menuIdParam])) {
        $m = $byId[$menuIdParam];
        $current = [
            'menu_id'    => (int)$m->id,
            'href'       => (string)($m->href ?? ''),
            'name'       => (string)($m->name ?? ''),
            'privileges' => ($m->privileges ?? []),
            'actions'    => collect($m->privileges ?? [])->pluck('action')->map(fn($a)=>strtolower((string)$a))->values(),
        ];
    } elseif ($menuHrefParam !== '') {
        $menuHrefParam = '/' . ltrim($menuHrefParam, '/');
        // best match: exact or prefix match
        foreach ($byId as $m) {
            $href = (string)($m->href ?? '');
            if (!$href) continue;

            if ($menuHrefParam === $href || str_starts_with($menuHrefParam, rtrim($href,'/') . '/')) {
                $current = [
                    'menu_id'    => (int)$m->id,
                    'href'       => $href,
                    'name'       => (string)($m->name ?? ''),
                    'privileges' => ($m->privileges ?? []),
                    'actions'    => collect($m->privileges ?? [])->pluck('action')->map(fn($a)=>strtolower((string)$a))->values(),
                ];
                break;
            }
        }
    }

    return response()->json([
        'success' => true,
        'user_id' => $userId,
        'data'    => $outTree,     // ✅ visible dashboard menu tree only
        'flat'    => [
            'privileges' => $privileges,  // ✅ optional helper
            'grants'     => $grantsByMenu // ✅ what JSON grants said
        ],
        'current' => $current,
    ]);
}

}
