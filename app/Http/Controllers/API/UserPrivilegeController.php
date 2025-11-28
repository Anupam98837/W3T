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
     private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }
    /**
     * Sync privileges for a user.
     * Accepts:
     *  - user_id (int) OR user_uuid (uuid)
     *  - privileges: array of privilege_id integers
     */
    public function sync(Request $r)
    {
        $data = $r->validate([
            'user_id'       => 'sometimes|integer|exists:users,id',
            'user_uuid'     => 'sometimes|uuid|exists:users,uuid',
            'privileges'    => 'required|array',
            'privileges.*'  => 'integer|exists:privileges,id',
        ]);

        // Resolve numeric user id if only uuid provided
        if (empty($data['user_id']) && !empty($data['user_uuid'])) {
            $userId = (int) DB::table('users')->where('uuid', $data['user_uuid'])->value('id');
        } else {
            $userId = (int) ($data['user_id'] ?? 0);
        }

        if (! $userId) {
            return response()->json(['error' => 'User not found'], 404);
        }

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

            // Fetch user uuid for response
            $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

            return response()->json([
                'message' => 'Privileges synced successfully.',
                'user_uuid' => $userUuid,
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
     *   - user_id OR user_uuid
     *   - privilege_id OR privilege_uuid
     *
     * Revives a soft-deleted mapping if present, otherwise inserts new.
     */
    public function assign(Request $r)
    {
        $data = $r->validate([
            'user_id'        => 'sometimes|integer|exists:users,id',
            'user_uuid'      => 'sometimes|uuid|exists:users,uuid',
            'privilege_id'   => 'sometimes|integer|exists:privileges,id',
            'privilege_uuid' => 'sometimes|uuid|exists:privileges,uuid',
        ]);

        // Resolve user id
        if (empty($data['user_id']) && !empty($data['user_uuid'])) {
            $userId = (int) DB::table('users')->where('uuid', $data['user_uuid'])->value('id');
        } else {
            $userId = (int) ($data['user_id'] ?? 0);
        }

        if (! $userId) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Resolve privilege id
        if (!empty($data['privilege_id'])) {
            $privId = (int) $data['privilege_id'];
        } elseif (!empty($data['privilege_uuid'])) {
            $privId = DB::table('privileges')
                ->where('uuid', $data['privilege_uuid'])
                ->whereNull('deleted_at')
                ->value('id');
        } else {
            return response()->json(['message' => 'privilege_id or privilege_uuid is required'], 422);
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

            $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

            return response()->json(['message' => 'Privilege assigned', 'user_uuid' => $userUuid], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not assign privilege', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * Soft-delete a single privilege row.
     * Accepts either:
     *   - uuid (mapping uuid)
     *   OR
     *   - user_id OR user_uuid + privilege_id
     */
    public function destroy(Request $r)
    {
        $r->validate([
            'uuid'          => 'sometimes|uuid|exists:user_privileges,uuid',
            'user_id'       => 'sometimes|integer|exists:users,id',
            'user_uuid'     => 'sometimes|uuid|exists:users,uuid',
            'privilege_id'  => 'sometimes|required_with:user_id|integer|exists:privileges,id',
        ]);

        $query = DB::table('user_privileges')->whereNull('deleted_at');
        $now = now();

        $userUuid = null;

        if ($r->filled('uuid')) {
            // Try to discover the user id for the mapping so we can return the user's uuid
            $mapping = DB::table('user_privileges')->where('uuid', $r->input('uuid'))->first();
            if ($mapping) {
                $userUuid = DB::table('users')->where('id', $mapping->user_id)->value('uuid');
            }

            $query->where('uuid', $r->input('uuid'));
        } else {
            // Resolve numeric user id either by user_id or user_uuid
            if ($r->filled('user_id')) {
                $userId = (int) $r->input('user_id');
            } elseif ($r->filled('user_uuid')) {
                $userId = (int) DB::table('users')->where('uuid', $r->input('user_uuid'))->value('id');
                if (!$userId) {
                    return response()->json(['message' => 'User not found'], 404);
                }
            } else {
                return response()->json(['message' => 'user_id or user_uuid is required'], 422);
            }

            $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

            $query->where('user_id', $userId)
                  ->where('privilege_id', $r->input('privilege_id'));
        }

        $affected = $query->update([
            'deleted_at' => $now,
            'updated_at' => $now,
        ]);

        if ($affected === 0) {
            return response()->json(['message' => 'Privilege mapping not found.'], 404);
        }

        return response()->json(['message' => 'Privilege removed successfully.', 'user_uuid' => $userUuid]);
    }

    /**
     * List all active privileges for a user.
     * Returns user_privileges joined with privileges table for convenience.
     * Accepts user_id OR user_uuid
     */
    public function list(Request $r)
    {
        $r->validate([
            'user_id'   => 'sometimes|integer|exists:users,id',
            'user_uuid' => 'sometimes|uuid|exists:users,uuid',
        ]);

        if ($r->filled('user_id')) {
            $userId = (int) $r->user_id;
        } elseif ($r->filled('user_uuid')) {
            $userId = (int) DB::table('users')->where('uuid', $r->user_uuid)->value('id');
            if (!$userId) {
                return response()->json(['message' => 'User not found'], 404);
            }
        } else {
            return response()->json(['message' => 'user_id or user_uuid is required'], 422);
        }

        $rows = DB::table('user_privileges as up')
            ->join('privileges as p', 'p.id', '=', 'up.privilege_id')
            ->where('up.user_id', $userId)
            ->whereNull('up.deleted_at')
            ->whereNull('p.deleted_at')
            ->select([
                'up.uuid as mapping_uuid',
                'up.privilege_id',
                'p.uuid as privilege_uuid',
                DB::raw('p.action as privilege_name'),
                'p.action as privilege_action',
                'p.description as privilege_description',
                'up.created_at'
            ])
            ->orderBy('up.created_at', 'desc')
            ->get();

        $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

        return response()->json([
            'user_uuid' => $userUuid,
            'data' => $rows,
        ]);
    }

    /**
     * Unassign (soft-delete) a single privilege from a user.
     * Accepts:
     *   - mapping_uuid
     *   OR
     *   - user_id OR user_uuid + privilege_id OR privilege_uuid
     */
    public function unassign(Request $r)
    {
        $data = $r->validate([
            'mapping_uuid'   => 'sometimes|uuid|exists:user_privileges,uuid',
            'user_id'        => 'sometimes|integer|exists:users,id',
            'user_uuid'      => 'sometimes|uuid|exists:users,uuid',
            'privilege_id'   => 'sometimes|integer|exists:privileges,id',
            'privilege_uuid' => 'sometimes|uuid|exists:privileges,uuid',
        ]);

        $now = now();

        // --- Case 1: Directly via mapping UUID ---
        if ($r->filled('mapping_uuid')) {
            $mapping = DB::table('user_privileges')->where('uuid', $r->mapping_uuid)->first();
            $userUuid = null;
            if ($mapping) {
                $userUuid = DB::table('users')->where('id', $mapping->user_id)->value('uuid');
            }

            $affected = DB::table('user_privileges')
                ->where('uuid', $r->mapping_uuid)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'updated_at' => $now]);

            return $affected
                ? response()->json(['message' => 'Privilege unassigned.', 'user_uuid' => $userUuid])
                : response()->json(['message' => 'Privilege not found.'], 404);
        }

        // --- Case 2: Using user_id/user_uuid + privilege_id/privilege_uuid ---
        if (!$r->filled('user_id') && !$r->filled('user_uuid')) {
            return response()->json(['message' => 'user_id or user_uuid is required when mapping_uuid is not provided'], 422);
        }

        // Resolve user id
        if ($r->filled('user_id')) {
            $userId = (int) $r->user_id;
        } else {
            $userId = (int) DB::table('users')->where('uuid', $r->user_uuid)->value('id');
            if (!$userId) {
                return response()->json(['message' => 'User not found'], 404);
            }
        }

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

        $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

        // Perform soft-delete
        $affected = DB::table('user_privileges')
            ->where('user_id', $userId)
            ->where('privilege_id', $privId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $now, 'updated_at' => $now]);

        return $affected
            ? response()->json(['message' => 'Privilege unassigned.', 'user_uuid' => $userUuid])
            : response()->json(['message' => 'Privilege mapping not found.'], 404);
    }

    /**
     * GET /api/users/{idOrUuid}
     * Accept numeric ID OR UUID
     */
     public function show($idOrUuid)
{
    if (is_numeric($idOrUuid)) {
        $user = DB::table('users')->where('id', $idOrUuid)->whereNull('deleted_at')->first();
    } else {
        $user = DB::table('users')->where('uuid', $idOrUuid)->whereNull('deleted_at')->first();
    }

    if (! $user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json(['user' => $user]);
}

    /**
     * GET /api/users/by-uuid?uuid=...
     */
    public function byUuid(Request $request)
    {
        $request->validate(['uuid' => 'required|uuid']);

        $user = DB::table('users')
            ->where('uuid', $request->uuid)
            ->whereNull('deleted_at')
            ->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user]);
    }
}
