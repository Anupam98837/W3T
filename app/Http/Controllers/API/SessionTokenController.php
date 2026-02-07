<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SessionTokenController extends Controller
{
    public function check(Request $request)
    {
        $bearer = (string) $request->bearerToken();

        if (!$bearer) {
            return $this->expired('missing_token');
        }

        $row = $this->resolveSanctumTokenRow($bearer);

        if (!$row) {
            return $this->expired('invalid_token');
        }

        // If you stored expires_at, check it
        if (!empty($row->expires_at) && now()->greaterThan($row->expires_at)) {
            // Optional: revoke/delete token when expired
            DB::table('personal_access_tokens')->where('id', (int) $row->id)->delete();
            return $this->expired('expired');
        }

        // Optional: update last_used_at
        DB::table('personal_access_tokens')
            ->where('id', (int) $row->id)
            ->update(['last_used_at' => now()]);

        $secondsLeft = null;
        if (!empty($row->expires_at)) {
            $secondsLeft = max(0, now()->diffInSeconds($row->expires_at, false));
        }

        return response()->json([
            'success' => true,
            'code'    => 'TOKEN_ACTIVE',
            'message' => 'Token is valid.',
            'data'    => [
                'expires_at'   => $row->expires_at,
                'seconds_left' => $secondsLeft,
                'tokenable_type' => $row->tokenable_type,
                'tokenable_id'   => (int) $row->tokenable_id,
            ],
        ], 200);
    }

    private function expired(string $reason)
    {
        return response()->json([
            'success' => false,
            'code'    => 'TOKEN_EXPIRED',
            'message' => 'Token expired. Login again.',
            'meta'    => [
                'reason' => $reason, // missing_token | invalid_token | expired
            ],
        ], 401);
    }

    /**
     * Supports Sanctum "id|plainTextToken" format + fallback hashed match.
     */
    private function resolveSanctumTokenRow(string $bearer): ?object
    {
        $bearer = trim($bearer);

        // Standard Sanctum: "id|plain"
        if (Str::contains($bearer, '|')) {
            [$id, $plain] = explode('|', $bearer, 2);

            if (!ctype_digit($id) || $plain === '') return null;

            $row = DB::table('personal_access_tokens')->where('id', (int) $id)->first();
            if (!$row) return null;

            $hashed = hash('sha256', $plain);
            if (!hash_equals((string) $row->token, $hashed)) return null;

            return $row;
        }

        // Fallback: treat full bearer as plain and hash it
        $hash = hash('sha256', $bearer);
        return DB::table('personal_access_tokens')->where('token', $hash)->first();
    }
}
