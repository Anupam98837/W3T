<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TermsController extends Controller
{
    /**
     * Get the single terms and conditions entry
     */
    private function getTermsEntry(): ?object
    {
        return DB::table('terms_and_conditions')
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * Check if record exists
     */
    private function recordExists(): bool
    {
        return DB::table('terms_and_conditions')->exists();
    }

    /* =========================================================
     | Activity Log (added)
     * ========================================================= */
    private function logActivity(
        Request $request,
        string $activity, // store | update
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
                'module'             => 'TermsAndConditions',
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
            Log::error('[TermsAndConditions] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/terms
     * Public endpoint to view terms
     */
    public function index(Request $request)
    {
        $terms = $this->getTermsEntry();

        if (!$terms) {
            return response()->json([
                'success' => false,
                'message' => 'Terms and conditions not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'terms' => [
                'id' => (int) $terms->id,
                'title' => (string) $terms->title,
                'content' => (string) $terms->content,
                'created_at' => $terms->created_at ? Carbon::parse($terms->created_at)->toDateTimeString() : null,
                'updated_at' => $terms->updated_at ? Carbon::parse($terms->updated_at)->toDateTimeString() : null,
            ]
        ], 200);
    }

    /**
     * POST /api/terms
     * Create or update the single terms record
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
            // UPDATE existing record
            $existing = $this->getTermsEntry();
            $before   = $existing ? (array)$existing : null;

            DB::table('terms_and_conditions')
                ->where('id', $existing->id)
                ->update([
                    'title' => $request->input('title'),
                    'content' => $request->input('content'),
                    'updated_at' => $now,
                ]);

            $terms = DB::table('terms_and_conditions')->where('id', $existing->id)->first();

            // ✅ ACTIVITY LOG (POST -> update)
            $this->logActivity(
                $request,
                'update',
                'Updated terms and conditions (via POST)',
                'terms_and_conditions',
                (int)$existing->id,
                ['title', 'content'],
                $before,
                $terms ? (array)$terms : null
            );

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions updated successfully',
                'terms' => [
                    'id' => (int) $terms->id,
                    'title' => (string) $terms->title,
                    'content' => (string) $terms->content,
                    'created_at' => $terms->created_at ? Carbon::parse($terms->created_at)->toDateTimeString() : null,
                    'updated_at' => $terms->updated_at ? Carbon::parse($terms->updated_at)->toDateTimeString() : null,
                ]
            ], 200);
        } else {
            // CREATE new record
            $id = DB::table('terms_and_conditions')->insertGetId([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $terms = DB::table('terms_and_conditions')->where('id', $id)->first();

            // ✅ ACTIVITY LOG (POST -> store)
            $this->logActivity(
                $request,
                'store',
                'Created terms and conditions',
                'terms_and_conditions',
                (int)$id,
                ['title', 'content'],
                null,
                $terms ? (array)$terms : null
            );

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions created successfully',
                'terms' => [
                    'id' => (int) $terms->id,
                    'title' => (string) $terms->title,
                    'content' => (string) $terms->content,
                    'created_at' => $terms->created_at ? Carbon::parse($terms->created_at)->toDateTimeString() : null,
                    'updated_at' => $terms->updated_at ? Carbon::parse($terms->updated_at)->toDateTimeString() : null,
                ]
            ], 201);
        }
    }

    public function update(Request $request)
    {
        $terms = $this->getTermsEntry();

        if (!$terms) {
            return response()->json([
                'success' => false,
                'message' => 'Terms not found'
            ], 404);
        }

        $before = (array)$terms;

        DB::table('terms_and_conditions')
            ->where('id', $terms->id)
            ->update([
                'title' => $request->title,
                'content' => $request->content,
                'updated_at' => now()
            ]);

        $updated = $this->getTermsEntry();

        // ✅ ACTIVITY LOG (PUT/PATCH -> update)
        $this->logActivity(
            $request,
            'update',
            'Updated terms and conditions (via PUT/PATCH)',
            'terms_and_conditions',
            (int)$terms->id,
            ['title', 'content'],
            $before,
            $updated ? (array)$updated : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Terms updated successfully',
            'terms' => $updated
        ]);
    }

    /**
     * GET /api/terms/check
     * Check if terms exist and return mode
     */
    public function check(Request $request)
    {
        $exists = $this->recordExists();
        $terms = $this->getTermsEntry();

        $response = [
            'success' => true,
            'exists' => $exists,
            'mode' => $exists ? 'edit' : 'create',
        ];

        if ($exists && $terms) {
            $response['terms'] = [
                'id' => (int) $terms->id,
                'title' => (string) $terms->title,
                'content' => (string) $terms->content,
                'created_at' => $terms->created_at ? Carbon::parse($terms->created_at)->toDateTimeString() : null,
                'updated_at' => $terms->updated_at ? Carbon::parse($terms->updated_at)->toDateTimeString() : null,
            ];
        }

        return response()->json($response, 200);
    }

    /**
     * DELETE /api/terms
     * Delete the single terms record
     */
    public function destroy(Request $request)
    {
        $exists = $this->recordExists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'No terms and conditions found to delete'
            ], 404);
        }

        $terms = $this->getTermsEntry();

        DB::table('terms_and_conditions')->where('id', $terms->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions deleted successfully'
        ], 200);
    }
}
