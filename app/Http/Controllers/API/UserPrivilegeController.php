<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserPrivilegeController extends Controller
{
    /**
     * Sync privileges for a user.
     * Expects:
     *  user_id: int
     *  privileges: array of privilege_id integers
     */
    public function sync(Request $r)
    {
        $data = $r->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'privileges'    => 'required|array',
            'privileges.*'  => 'integer|exists:privileges,id',
        ]);

        $userId = (int) $data['user_id'];
        $newPrivileges = array_values(array_unique($data['privileges']));
        $now = now();

        try {
            $result = DB::transaction(function () use ($userId, $newPrivileges, $now) {
                // current active privilege ids
                $current = DB::table('user_privileges')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->pluck('privilege_id')
                    ->map(fn ($v) => (int) $v)
                    ->toArray();

                // soft-deleted existing mappings for this user (privilege_id => id)
                $soft = DB::table('user_privileges')
                    ->where('user_id', $userId)
                    ->whereNotNull('deleted_at')
                    ->whereIn('privilege_id', $newPrivileges)
                    ->pluck('id', 'privilege_id') // privilege_id => id
                    ->all();

                // Calculate differences
                $toInsert = array_values(array_diff($newPrivileges, $current)); // privilege_ids to ensure active
                $toDelete = array_values(array_diff($current, $newPrivileges)); // privilege_ids to soft-delete

                $revived = [];
                $actuallyInserted = [];

                // First, revive any soft-deleted rows among $toInsert
                if (!empty($toInsert)) {
                    $toRevive = array_values(array_intersect($toInsert, array_keys($soft)));
                    if (!empty($toRevive)) {
                        foreach ($toRevive as $privId) {
                            $rowId = $soft[$privId];
                            DB::table('user_privileges')
                                ->where('id', $rowId)
                                ->update(['deleted_at' => null, 'updated_at' => $now]);
                            $revived[] = $privId;
                        }
                    }

                    // For remaining privilege ids not revived, insert new rows
                    $toActuallyInsert = array_values(array_diff($toInsert, $revived));
                    if (!empty($toActuallyInsert)) {
                        $inserts = [];
                        foreach ($toActuallyInsert as $privId) {
                            $inserts[] = [
                                'uuid'         => (string) Str::uuid(),
                                'user_id'      => $userId,
                                'privilege_id' => $privId,
                                'created_at'   => $now,
                                'updated_at'   => $now,
                            ];
                        }
                        DB::table('user_privileges')->insert($inserts);
                        $actuallyInserted = $toActuallyInsert;
                    }
                }

                // Soft delete removed privilege mappings
                if (!empty($toDelete)) {
                    DB::table('user_privileges')
                        ->where('user_id', $userId)
                        ->whereIn('privilege_id', $toDelete)
                        ->whereNull('deleted_at')
                        ->update([
                            'deleted_at' => $now,
                            'updated_at' => $now,
                        ]);
                }

                return [
                    'revived' => $revived,
                    'inserted' => $actuallyInserted,
                    'removed' => $toDelete,
                ];
            });

            return response()->json([
                'message' => 'Privileges synced successfully.',
                'revived' => $result['revived'],
                'added'   => $result['inserted'],
                'removed' => $result['removed'],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Could not sync privileges', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * Assign a single privilege to a user.
     * Accepts:
     *   - user_id + privilege_id
     *   - user_id + privilege_uuid
     *
     * Revives a soft-deleted mapping if present, otherwise inserts new.
     */
    public function assign(Request $r)
    {
        $data = $r->validate([
            'user_id'        => 'required|integer|exists:users,id',
            'privilege_id'   => 'sometimes|integer|exists:privileges,id',
            'privilege_uuid' => 'sometimes|uuid|exists:privileges,uuid',
        ]);

        $userId = (int) $data['user_id'];

        // Resolve privilege id
        if (!empty($data['privilege_id'])) {
            $privId = (int) $data['privilege_id'];
        } else {
            $privId = DB::table('privileges')
                ->where('uuid', $data['privilege_uuid'])
                ->whereNull('deleted_at')
                ->value('id');
        }

        if (! $privId) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        $now = now();

        try {
            DB::transaction(function () use ($userId, $privId, $now) {
                // Look for any mapping (active or soft-deleted)
                $existing = DB::table('user_privileges')
                    ->where('user_id', $userId)
                    ->where('privilege_id', $privId)
                    ->first();

                if ($existing) {
                    // If soft-deleted, revive it
                    if ($existing->deleted_at !== null) {
                        DB::table('user_privileges')->where('id', $existing->id)
                            ->update(['deleted_at' => null, 'updated_at' => $now]);
                    }
                    // if already active, nothing to do
                } else {
                    // Insert new mapping
                    DB::table('user_privileges')->insert([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $userId,
                        'privilege_id' => $privId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

            return response()->json(['message' => 'Privilege assigned'], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not assign privilege', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * Soft-delete a single privilege row.
     * Accepts either:
     *   - uuid
     *   - user_id + privilege_id
     */
    public function destroy(Request $r)
    {
        $r->validate([
            'uuid'         => 'sometimes|uuid|exists:user_privileges,uuid',
            'user_id'      => 'sometimes|integer|exists:users,id',
            'privilege_id' => 'sometimes|required_with:user_id|integer|exists:privileges,id',
        ]);

        $query = DB::table('user_privileges')->whereNull('deleted_at');
        $now = now();

        if ($r->filled('uuid')) {
            $query->where('uuid', $r->input('uuid'));
        } else {
            $query->where('user_id', $r->input('user_id'))
                  ->where('privilege_id', $r->input('privilege_id'));
        }

        $affected = $query->update([
            'deleted_at' => $now,
            'updated_at' => $now,
        ]);

        if ($affected === 0) {
            return response()->json(['message' => 'Privilege mapping not found.'], 404);
        }

        return response()->json(['message' => 'Privilege removed successfully.']);
    }

    /**
     * List all active privileges for a user.
     * Returns user_privileges joined with privileges table for convenience.
     */
    public function list(Request $r)
    {
        $r->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $rows = DB::table('user_privileges as up')
            ->join('privileges as p', 'p.id', '=', 'up.privilege_id')
            ->where('up.user_id', $r->user_id)
            ->whereNull('up.deleted_at')
            ->whereNull('p.deleted_at')
            ->select([
                'up.uuid as mapping_uuid',
                'up.privilege_id',
                'p.uuid as privilege_uuid',
                'p.name as privilege_name',
                'p.action as privilege_action',
                'p.description as privilege_description',
                'up.created_at'
            ])
            ->orderBy('up.created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }
    /**
 * Unassign (soft-delete) a single privilege from a user.
 * Accepts:
 *   - mapping_uuid
 *   OR
 *   - user_id + privilege_id
 *   OR
 *   - user_id + privilege_uuid
 */
public function unassign(Request $r)
{
    $data = $r->validate([
        'mapping_uuid'   => 'sometimes|uuid|exists:user_privileges,uuid',
        'user_id'        => 'sometimes|integer|exists:users,id',
        'privilege_id'   => 'sometimes|integer|exists:privileges,id',
        'privilege_uuid' => 'sometimes|uuid|exists:privileges,uuid',
    ]);

    $now = now();

    // --- Case 1: Directly via mapping UUID ---
    if ($r->filled('mapping_uuid')) {
        $affected = DB::table('user_privileges')
            ->where('uuid', $r->mapping_uuid)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $now, 'updated_at' => $now]);

        return $affected
            ? response()->json(['message' => 'Privilege unassigned.'])
            : response()->json(['message' => 'Privilege not found.'], 404);
    }

    // --- Case 2: Using user_id + privilege_id / privilege_uuid ---
    if (!$r->filled('user_id')) {
        return response()->json(['message' => 'user_id is required when mapping_uuid is not provided'], 422);
    }

    $userId = (int) $r->user_id;

    // Resolve privilege id
    if ($r->filled('privilege_id')) {
        $privId = (int) $r->privilege_id;
    } elseif ($r->filled('privilege_uuid')) {
        $privId = DB::table('privileges')
            ->where('uuid', $r->privilege_uuid)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privId) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }
    } else {
        return response()->json(['message' => 'Either privilege_id or privilege_uuid is required'], 422);
    }

    // Perform soft-delete
    $affected = DB::table('user_privileges')
        ->where('user_id', $userId)
        ->where('privilege_id', $privId)
        ->whereNull('deleted_at')
        ->update(['deleted_at' => $now, 'updated_at' => $now]);

    return $affected
        ? response()->json(['message' => 'Privilege unassigned.'])
        : response()->json(['message' => 'Privilege mapping not found.'], 404);
}

}
