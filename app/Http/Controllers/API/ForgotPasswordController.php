<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /* =========================================================
     | Activity Log (added)
     * ========================================================= */

    private function activityActor(Request $r): array
    {
        return [
            'role' => $r->attributes->get('auth_role'),
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

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
        $a = $this->activityActor($request);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'       => $a['id'] ?: 0,
                'performed_by_role'  => $a['role'] ?: null,
                'ip'                 => $request->ip(),
                'user_agent'         => (string) $request->userAgent(),
                'activity'           => $activity,
                'module'             => 'ForgotPassword',
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
            Log::error('[ForgotPassword] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/auth/forgot-password/send-otp
     * body: { email }
     */
    public function sendOtp(Request $r) // ✅ use sendOtp (standard)
    {
        $reqId = (string) Str::uuid();

        // ✅ confirm route hit
        Log::channel('daily')->info('FP_SEND_OTP:HIT', [
            'request_id' => $reqId,
            'method'     => $r->method(),
            'path'       => $r->path(),
            'full_url'   => $r->fullUrl(),
            'ip'         => $r->ip(),
            'ua'         => substr((string) $r->userAgent(), 0, 180),
            'ts'         => now()->toDateTimeString(),
        ]);

        $r->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($r->email));

        Log::channel('daily')->info('FP_SEND_OTP:AFTER_VALIDATE', [
            'request_id' => $reqId,
            'email'      => $email,
        ]);

        // ✅ Do not reveal if user exists (security)
        $userExists = DB::table('users')->where('email', $email)->exists();

        Log::channel('daily')->info('FP_SEND_OTP:USER_EXISTS_CHECK', [
            'request_id'  => $reqId,
            'email'       => $email,
            'user_exists' => (bool) $userExists,
        ]);

        if (!$userExists) {
            // ✅ activity log (silent success path)
            $this->logActivity(
                $r,
                'store',
                'Forgot password OTP requested (user not found; silent success)',
                'password_reset_tokens',
                null,
                ['email'],
                null,
                ['email' => $email, 'request_id' => $reqId]
            );

            $response = [
                'status'  => 'success',
                'message' => 'If the account exists, an OTP has been sent.',
                'data'    => [
                    'request_id' => $reqId,
                ],
            ];

            Log::channel('daily')->warning('FP_SEND_OTP:USER_NOT_FOUND_SILENT_SUCCESS', [
                'request_id' => $reqId,
                'email'      => $email,
                'response'   => $response,
            ]);

            return response()->json($response);
        }

        // Invalidate previous active requests
        $invalidated = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('is_valid', 1)
            ->update(['is_valid' => 0]);

        Log::channel('daily')->info('FP_SEND_OTP:INVALIDATED_OLD', [
            'request_id'        => $reqId,
            'email'             => $email,
            'invalidated_count' => (int) $invalidated,
        ]);

        $otp     = (string) random_int(100000, 999999);
        $otpHash = Hash::make($otp);

        // token NOT NULL => placeholder until verify
        $pendingTokenHash = Hash::make(Str::random(40));

        $now          = Carbon::now();
        $otpExpiresAt = $now->copy()->addMinutes(10);

        Log::channel('daily')->info('FP_SEND_OTP:GENERATED', [
            'request_id' => $reqId,
            'email'      => $email,
            'otp'        => $otp, // DEV ONLY
            'expires_at' => $otpExpiresAt->toDateTimeString(),
        ]);

        try {
            DB::table('password_reset_tokens')->insert([
                'email'          => $email,
                'token'          => $pendingTokenHash,
                'created_at'     => $now,
                'otp'            => $otpHash,
                'otp_expires_at' => $otpExpiresAt,
                'is_valid'       => 1,
            ]);

            Log::channel('daily')->info('FP_SEND_OTP:INSERT_OK', [
                'request_id' => $reqId,
                'email'      => $email,
            ]);

            // ✅ activity log (OTP created)
            $this->logActivity(
                $r,
                'store',
                'Forgot password OTP generated',
                'password_reset_tokens',
                null,
                ['email', 'otp_expires_at', 'is_valid'],
                null,
                [
                    'email'          => $email,
                    'otp_expires_at' => $otpExpiresAt->toDateTimeString(),
                    'is_valid'       => 1,
                    'request_id'     => $reqId,
                ]
            );
        } catch (\Throwable $e) {
            Log::channel('daily')->error('FP_SEND_OTP:INSERT_FAILED', [
                'request_id' => $reqId,
                'email'      => $email,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create OTP. Please try again.',
                'data'    => ['request_id' => $reqId],
            ], 500);
        }

        // ✅ expose OTP only in local/debug
        $exposeOtp = app()->environment('local') || (bool) config('app.debug', false);

        $response = [
            'status'  => 'success',
            'message' => 'OTP generated (check server console/log).',
            'data'    => [
                'request_id'         => $reqId,
                'expires_in_minutes' => 10,
            ],
        ];

        if ($exposeOtp) {
            $response['data']['otp'] = $otp;
        }

        // ✅ log final response body (so you can confirm otp is included)
        Log::channel('daily')->info('FP_SEND_OTP:RESPONSE_BODY', [
            'request_id' => $reqId,
            'email'      => $email,
            'expose_otp' => $exposeOtp,
            'response'   => $response,
        ]);

        return response()->json($response);
    }

    /**
     * POST /api/auth/forgot-password/verify-otp
     * body: { email, otp }
     * Returns: reset_token (RAW)
     */
    public function verifyOtp(Request $r)
    {
        $r->validate([
            'email' => ['required','email','max:255'],
            'otp'   => ['required','digits:6'],
        ]);

        $email = strtolower(trim($r->email));
        $otp   = trim($r->otp);

        $row = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('is_valid', 1)
            ->orderByDesc('created_at')
            ->first();

        if (!$row) {
            return response()->json([
                'status'  => 'error',
                'message' => 'OTP not found or expired. Please request a new OTP.',
            ], 422);
        }

        // Expiry check
        if (!empty($row->otp_expires_at) && Carbon::parse($row->otp_expires_at)->isPast()) {
            DB::table('password_reset_tokens')->where('id', $row->id)->update(['is_valid' => 0]);

            // ✅ activity log (expired -> invalidated)
            $this->logActivity(
                $r,
                'update',
                'OTP expired; invalidated reset token request',
                'password_reset_tokens',
                (int) $row->id,
                ['is_valid'],
                ['is_valid' => 1, 'email' => $email],
                ['is_valid' => 0, 'email' => $email]
            );

            return response()->json([
                'status'  => 'error',
                'message' => 'OTP expired. Please request a new OTP.',
            ], 422);
        }

        // OTP check (hashed)
        if (empty($row->otp) || !Hash::check($otp, $row->otp)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid OTP.',
            ], 422);
        }

        // OTP verified => create reset token (raw returned; hash stored)
        $resetTokenRaw  = Str::random(64);
        $resetTokenHash = Hash::make($resetTokenRaw);

        // Allow 30 min reset window (reuse otp_expires_at as token expiry)
        $newExpiry = Carbon::now()->addMinutes(30);

        DB::table('password_reset_tokens')->where('id', $row->id)->update([
            'token'          => $resetTokenHash,
            'otp'            => null,
            'otp_expires_at' => $newExpiry,
        ]);

        // ✅ activity log (OTP verified -> token issued)
        $this->logActivity(
            $r,
            'update',
            'OTP verified; issued reset token',
            'password_reset_tokens',
            (int) $row->id,
            ['otp', 'token', 'otp_expires_at'],
            ['email' => $email, 'otp_expires_at' => (string) $row->otp_expires_at],
            ['email' => $email, 'otp_expires_at' => $newExpiry->toDateTimeString()]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'OTP verified.',
            'data'    => [
                'reset_token'        => $resetTokenRaw,
                'expires_in_minutes' => 30,
            ],
        ]);
    }

    /**
     * POST /api/auth/forgot-password/reset
     * body: { email, reset_token, password, password_confirmation }
     */
    public function resetPassword(Request $r)
    {
        $r->validate([
            'email'       => ['required','email','max:255'],
            'reset_token' => ['required','string','min:10'],
            'password'    => ['required','string','min:8','confirmed'],
        ]);

        $email    = strtolower(trim($r->email));
        $rawToken = $r->reset_token;

        $row = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('is_valid', 1)
            ->orderByDesc('created_at')
            ->first();

        if (!$row) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Reset session not found. Please try again.',
            ], 422);
        }

        // Expiry check (otp_expires_at reused as reset token expiry)
        if (!empty($row->otp_expires_at) && Carbon::parse($row->otp_expires_at)->isPast()) {
            DB::table('password_reset_tokens')->where('id', $row->id)->update(['is_valid' => 0]);

            // ✅ activity log (expired -> invalidated)
            $this->logActivity(
                $r,
                'update',
                'Reset token expired; invalidated reset session',
                'password_reset_tokens',
                (int) $row->id,
                ['is_valid'],
                ['is_valid' => 1, 'email' => $email],
                ['is_valid' => 0, 'email' => $email]
            );

            return response()->json([
                'status'  => 'error',
                'message' => 'Reset token expired. Please request OTP again.',
            ], 422);
        }

        // Token check
        if (empty($row->token) || !Hash::check($rawToken, $row->token)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid reset token.',
            ], 422);
        }

        $userExists = DB::table('users')->where('email', $email)->exists();
        if (!$userExists) {
            DB::table('password_reset_tokens')->where('id', $row->id)->update(['is_valid' => 0]);

            // ✅ activity log (user missing -> invalidated)
            $this->logActivity(
                $r,
                'update',
                'User not found; invalidated reset session',
                'password_reset_tokens',
                (int) $row->id,
                ['is_valid'],
                ['is_valid' => 1, 'email' => $email],
                ['is_valid' => 0, 'email' => $email]
            );

            return response()->json([
                'status'  => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        // (optional for recordId logging only)
        $userRow = DB::table('users')->select('id')->where('email', $email)->first();

        DB::table('users')->where('email', $email)->update([
            'password'   => Hash::make($r->password),
            'updated_at' => Carbon::now(),
        ]);

        // ✅ activity log (password updated)
        $this->logActivity(
            $r,
            'update',
            'Password reset successful (user password updated)',
            'users',
            $userRow ? (int)$userRow->id : null,
            ['password', 'updated_at'],
            ['email' => $email],
            ['email' => $email]
        );

        // Invalidate token
        DB::table('password_reset_tokens')->where('id', $row->id)->update([
            'is_valid' => 0,
            'token'    => Hash::make(Str::random(40)), // token NOT NULL
        ]);

        // ✅ activity log (reset session invalidated)
        $this->logActivity(
            $r,
            'update',
            'Reset session invalidated after password reset',
            'password_reset_tokens',
            (int) $row->id,
            ['is_valid', 'token'],
            ['is_valid' => 1, 'email' => $email],
            ['is_valid' => 0, 'email' => $email]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Password reset successful. Please login with your new password.',
        ]);
    }
}
