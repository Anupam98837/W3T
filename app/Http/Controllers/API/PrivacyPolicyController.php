<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PrivacyPolicyController extends Controller
{
    /**
     * Get the single privacy policy entry
     */
    private function getPrivacyEntry(): ?object
    {
        return DB::table('privacy_policies')
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * Check if record exists
     */
    private function recordExists(): bool
    {
        return DB::table('privacy_policies')->exists();
    }

    /**
     * GET /api/privacy-policy
     */
    public function index(Request $request)
    {
        $policy = $this->getPrivacyEntry();

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Privacy policy not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'privacy_policy' => [
                'id' => (int) $policy->id,
                'title' => (string) $policy->title,
                'content' => (string) $policy->content,
                'created_at' => $policy->created_at ? Carbon::parse($policy->created_at)->toDateTimeString() : null,
                'updated_at' => $policy->updated_at ? Carbon::parse($policy->updated_at)->toDateTimeString() : null,
            ]
        ], 200);
    }

    /**
     * POST /api/privacy-policy
     * Create or update
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
            $existing = $this->getPrivacyEntry();

            DB::table('privacy_policies')
                ->where('id', $existing->id)
                ->update([
                    'title' => $request->input('title'),
                    'content' => $request->input('content'),
                    'updated_at' => $now,
                ]);

            $policy = DB::table('privacy_policies')->where('id', $existing->id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Privacy policy updated successfully',
                'privacy_policy' => [
                    'id' => (int) $policy->id,
                    'title' => (string) $policy->title,
                    'content' => (string) $policy->content,
                    'created_at' => $policy->created_at ? Carbon::parse($policy->created_at)->toDateTimeString() : null,
                    'updated_at' => $policy->updated_at ? Carbon::parse($policy->updated_at)->toDateTimeString() : null,
                ]
            ], 200);
        }

        // CREATE
        $id = DB::table('privacy_policies')->insertGetId([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $policy = DB::table('privacy_policies')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Privacy policy created successfully',
            'privacy_policy' => [
                'id' => (int) $policy->id,
                'title' => (string) $policy->title,
                'content' => (string) $policy->content,
                'created_at' => $policy->created_at ? Carbon::parse($policy->created_at)->toDateTimeString() : null,
                'updated_at' => $policy->updated_at ? Carbon::parse($policy->updated_at)->toDateTimeString() : null,
            ]
        ], 201);
    }

    /**
     * GET /api/privacy-policy/check
     */
    public function check(Request $request)
    {
        $exists = $this->recordExists();
        $policy = $this->getPrivacyEntry();

        $response = [
            'success' => true,
            'exists' => $exists,
            'mode' => $exists ? 'edit' : 'create',
        ];

        if ($exists && $policy) {
            $response['privacy_policy'] = [
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
     * DELETE /api/privacy-policy
     */
    public function destroy(Request $request)
    {
        $exists = $this->recordExists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'No privacy policy found to delete'
            ], 404);
        }

        $policy = $this->getPrivacyEntry();

        DB::table('privacy_policies')->where('id', $policy->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Privacy policy deleted successfully'
        ], 200);
    }
    /**
 * PUT /api/privacy-policy
 * Update the single privacy policy entry
 */
public function update(Request $request)
{
    // Validate fields
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

    // Check if policy exists
    $policy = $this->getPrivacyEntry();
    if (!$policy) {
        return response()->json([
            'success' => false,
            'message' => 'No privacy policy found to update'
        ], 404);
    }

    // Update the record
    DB::table('privacy_policies')
        ->where('id', $policy->id)
        ->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'updated_at' => Carbon::now(),
        ]);

    // Fetch updated record
    $updated = $this->getPrivacyEntry();

    return response()->json([
        'success' => true,
        'message' => 'Privacy policy updated successfully',
        'privacy_policy' => [
            'id' => (int) $updated->id,
            'title' => $updated->title,
            'content' => $updated->content,
            'created_at' => $updated->created_at,
            'updated_at' => $updated->updated_at,
        ]
    ], 200);
}

}
