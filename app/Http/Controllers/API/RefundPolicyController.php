<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RefundPolicyController extends Controller
{
    private function getRefundEntry(): ?object
    {
        return DB::table('refund_policies')
            ->orderBy('id', 'asc')
            ->first();
    }

    private function recordExists(): bool
    {
        return DB::table('refund_policies')->exists();
    }

    /* =========================================================
     | Activity Log (added)
     * ========================================================= */
    private function logActivity(
        Request $request,
        string $activity, // store | update | destroy
        string $note,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $actorRole = $request->attributes->get('auth_role');
        $actorId   = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'       => $actorId ?: 0,
                'performed_by_role'  => $actorRole ?: null,
                'ip'                 => $request->ip(),
                'user_agent'         => (string) $request->userAgent(),
                'activity'           => $activity,
                'module'             => 'RefundPolicy',
                'table_name'         => $tableName,
                'record_id'          => $recordId,
                'changed_fields'     => $changedFields ? json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE) : null,
                'old_values'         => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'         => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'           => $note,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[RefundPolicy] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/refund-policy
     */
    public function index(Request $request)
    {
        $policy = $this->getRefundEntry();

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Refund policy not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'refund_policy' => [
                'id' => (int) $policy->id,
                'title' => (string) $policy->title,
                'content' => (string) $policy->content,
                'created_at' => $policy->created_at ? Carbon::parse($policy->created_at)->toDateTimeString() : null,
                'updated_at' => $policy->updated_at ? Carbon::parse($policy->updated_at)->toDateTimeString() : null,
            ]
        ], 200);
    }

    /**
     * POST /api/refund-policy
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $v->errors()
            ], 422);
        }

        $exists = $this->recordExists();
        $now = Carbon::now();

        if ($exists) {
            $existing = $this->getRefundEntry();
            $before   = $existing ? (array)$existing : null;

            DB::table('refund_policies')
                ->where('id', $existing->id)
                ->update([
                    'title' => $request->input('title'),
                    'content' => $request->input('content'),
                    'updated_at' => $now,
                ]);

            $policy = DB::table('refund_policies')->where('id', $existing->id)->first();

            // ✅ ACTIVITY LOG (POST -> update)
            $this->logActivity(
                $request,
                'update',
                'Updated refund policy (via POST)',
                'refund_policies',
                (int)$existing->id,
                ['title', 'content'],
                $before,
                $policy ? (array)$policy : null
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund policy updated successfully',
                'refund_policy' => [
                    'id' => (int) $policy->id,
                    'title' => (string) $policy->title,
                    'content' => (string) $policy->content,
                    'created_at' => $policy->created_at ? Carbon::parse($policy->created_at)->toDateTimeString() : null,
                    'updated_at' => $policy->updated_at ? Carbon::parse($policy->updated_at)->toDateTimeString() : null,
                ]
            ], 200);
        }

        // CREATE
        $id = DB::table('refund_policies')->insertGetId([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $policy = DB::table('refund_policies')->where('id', $id)->first();

        // ✅ ACTIVITY LOG (POST -> store)
        $this->logActivity(
            $request,
            'store',
            'Created refund policy',
            'refund_policies',
            (int)$id,
            ['title', 'content'],
            null,
            $policy ? (array)$policy : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund policy created successfully',
            'refund_policy' => [
                'id' => (int) $policy->id,
                'title' => (string) $policy->title,
                'content' => (string) $policy->content,
                'created_at' => $policy->created_at ? Carbon::parse($policy->created_at)->toDateTimeString() : null,
                'updated_at' => $policy->updated_at ? Carbon::parse($policy->updated_at)->toDateTimeString() : null,
            ]
        ], 201);
    }

    /**
     * GET /api/refund-policy/check
     */
    public function check(Request $request)
    {
        $exists = $this->recordExists();
        $policy = $this->getRefundEntry();

        $response = [
            'success' => true,
            'exists' => $exists,
            'mode' => $exists ? 'edit' : 'create',
        ];

        if ($exists && $policy) {
            $response['refund_policy'] = [
                'id' => (int) $policy->id,
                'title' => (string) $policy->title,
                'content' => (string) $policy->content,
                'created_at' => $policy->created_at ? Carbon::parse($policy->created_at)->toDateTimeString() : null,
                'updated_at' => $policy->updated_at ? Carbon::parse($policy->updated_at)->toDateTimeString() : null,
            ];
        }

        return response()->json($response, 200);
    }

    /**
     * DELETE /api/refund-policy
     */
    public function destroy(Request $request)
    {
        $exists = $this->recordExists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'No refund policy found to delete'
            ], 404);
        }

        $policy = $this->getRefundEntry();
        $before = $policy ? (array)$policy : null;

        DB::table('refund_policies')->where('id', $policy->id)->delete();

        // ✅ ACTIVITY LOG (DELETE -> destroy)
        $this->logActivity(
            $request,
            'destroy',
            'Deleted refund policy',
            'refund_policies',
            (int)$policy->id,
            null,
            $before,
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund policy deleted successfully'
        ], 200);
    }

    /**
     * PUT /api/refund-policy
     * Update the single refund policy entry
     */
    public function update(Request $request)
    {
        // Validate input
        $v = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $v->errors()
            ], 422);
        }

        // Check if the policy exists
        $policy = $this->getRefundEntry();

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'No refund policy found to update'
            ], 404);
        }

        $before = (array)$policy;

        // Update record
        DB::table('refund_policies')
            ->where('id', $policy->id)
            ->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'updated_at' => Carbon::now(),
            ]);

        // Get updated entry
        $updated = $this->getRefundEntry();

        // ✅ ACTIVITY LOG (PUT/PATCH -> update)
        $this->logActivity(
            $request,
            'update',
            'Updated refund policy (via PUT/PATCH)',
            'refund_policies',
            (int)$policy->id,
            ['title', 'content'],
            $before,
            $updated ? (array)$updated : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund policy updated successfully',
            'refund_policy' => [
                'id' => (int) $updated->id,
                'title' => (string) $updated->title,
                'content' => (string) $updated->content,
                'created_at' => $updated->created_at,
                'updated_at' => $updated->updated_at,
            ]
        ], 200);
    }
}
