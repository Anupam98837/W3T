<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Contracts\PasswordResetMailer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /* =========================================================
     | Constructor — mailer injected (swap anytime)
     * ========================================================= */

    public function __construct(protected PasswordResetMailer $mailer) {}

    /* =========================================================
     | Activity Log
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
        string $activity,
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
                'performed_by'      => $a['id'] ?: 0,
                'performed_by_role' => $a['role'] ?: null,
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,
                'module'            => 'ForgotPassword',
                'table_name'        => $tableName,
                'record_id'         => $recordId,
                'changed_fields'    => $changedFields ? json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $oldValues  ? json_encode($oldValues,  JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues  ? json_encode($newValues,  JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[ForgotPassword] user_data_activity_log insert failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /* =========================================================
     | API 1 — POST /api/auth/forgot-password/send-link
     | body: { email }
     * ========================================================= */

    public function sendLink(Request $r)
    {
        $reqId = (string) Str::uuid();

        Log::channel('daily')->info('FP_SEND_LINK:HIT', [
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

        Log::channel('daily')->info('FP_SEND_LINK:AFTER_VALIDATE', [
            'request_id' => $reqId,
            'email'      => $email,
        ]);

        // Never reveal if email exists or not
        $genericMessage = 'If this email exists in our system, a password reset link has been sent.';

        $userExists = DB::table('users')->where('email', $email)->exists();

        Log::channel('daily')->info('FP_SEND_LINK:USER_EXISTS_CHECK', [
            'request_id'  => $reqId,
            'email'       => $email,
            'user_exists' => (bool) $userExists,
        ]);

        if (!$userExists) {
            $this->logActivity(
                $r,
                'store',
                'Forgot password link requested — user not found (silent success)',
                'password_reset_token',
                null,
                ['email'],
                null,
                ['email' => $email, 'request_id' => $reqId]
            );

            Log::channel('daily')->warning('FP_SEND_LINK:USER_NOT_FOUND_SILENT_SUCCESS', [
                'request_id' => $reqId,
                'email'      => $email,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => $genericMessage,
                'data'    => ['request_id' => $reqId],
            ]);
        }

        // Invalidate all previous active tokens for this email
        $invalidated = DB::table('password_reset_token')
            ->where('email', $email)
            ->where('is_valid', 1)
            ->update(['is_valid' => 0]);

        Log::channel('daily')->info('FP_SEND_LINK:INVALIDATED_OLD_TOKENS', [
            'request_id'        => $reqId,
            'email'             => $email,
            'invalidated_count' => (int) $invalidated,
        ]);

        // Generate raw token — only hash goes into DB
        $rawToken  = Str::random(64);
        $tokenHash = Hash::make($rawToken);

        $now       = Carbon::now();
        $expiresAt = $now->copy()->addMinutes(10); // ✅ valid for 10 minutes only

        Log::channel('daily')->info('FP_SEND_LINK:TOKEN_GENERATED', [
            'request_id' => $reqId,
            'email'      => $email,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);

        try {
            DB::table('password_reset_token')->insert([
                'email'      => $email,
                'token'      => $tokenHash,
                'expires_at' => $expiresAt,
                'is_valid'   => 1,
                'used_at'    => null,
                'created_at' => $now,
            ]);

            Log::channel('daily')->info('FP_SEND_LINK:INSERT_OK', [
                'request_id' => $reqId,
                'email'      => $email,
            ]);

            $this->logActivity(
                $r,
                'store',
                'Password reset token generated and stored — valid 10 minutes',
                'password_reset_token',
                null,
                ['email', 'expires_at', 'is_valid'],
                null,
                [
                    'email'      => $email,
                    'expires_at' => $expiresAt->toDateTimeString(),
                    'is_valid'   => 1,
                    'request_id' => $reqId,
                ]
            );
        } catch (\Throwable $e) {
            Log::channel('daily')->error('FP_SEND_LINK:INSERT_FAILED', [
                'request_id' => $reqId,
                'email'      => $email,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to generate reset link. Please try again.',
                'data'    => ['request_id' => $reqId],
            ], 500);
        }

        // Build reset URL — raw token embedded, never the hash
       // Build reset URL — uses APP_URL automatically (local/live)
$resetUrl = route('password.reset', [
    'token' => $rawToken,
    'email' => $email,
]);

Log::channel('daily')->info('FP_SEND_LINK:RESET_URL_BUILT', [
    'request_id' => $reqId,
    'email'      => $email,
    // DEV ONLY — remove reset_url log line in production
    'reset_url'  => $resetUrl,
]);

        // Hand off to mailer — plug your real mailer in AppServiceProvider
        $this->mailer->sendResetLink($email, $resetUrl);

        Log::channel('daily')->info('FP_SEND_LINK:MAILER_CALLED', [
            'request_id' => $reqId,
            'email'      => $email,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => $genericMessage,
            'data'    => [
                'request_id'         => $reqId,
                'expires_in_minutes' => 10,
            ],
        ]);
    }

    /* =========================================================
     | API 2 — POST /api/auth/forgot-password/reset
     | body: { email, token, password, password_confirmation }
     * ========================================================= */

    public function resetPassword(Request $r)
    {
        $reqId = (string) Str::uuid();

        Log::channel('daily')->info('FP_RESET:HIT', [
            'request_id' => $reqId,
            'method'     => $r->method(),
            'path'       => $r->path(),
            'ip'         => $r->ip(),
            'ts'         => now()->toDateTimeString(),
        ]);

        $r->validate([
            'email'    => ['required', 'email', 'max:255'],
            'token'    => ['required', 'string', 'min:10'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email    = strtolower(trim($r->email));
        $rawToken = $r->token;

        // Fetch latest valid unused token for this email
        $row = DB::table('password_reset_token')
            ->where('email', $email)
            ->where('is_valid', 1)
            ->whereNull('used_at')
            ->orderByDesc('created_at')
            ->first();

        Log::channel('daily')->info('FP_RESET:RECORD_FETCH', [
            'request_id'   => $reqId,
            'email'        => $email,
            'record_found' => (bool) $row,
        ]);

        if (!$row) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This reset link is invalid or has expired.',
            ], 422);
        }

        // ✅ Expiry check — 10 minutes
        if (Carbon::parse($row->expires_at)->isPast()) {
            DB::table('password_reset_token')
                ->where('id', $row->id)
                ->update(['is_valid' => 0]);

            $this->logActivity(
                $r,
                'update',
                'Reset token expired (10 min window passed) — invalidated',
                'password_reset_token',
                (int) $row->id,
                ['is_valid'],
                ['is_valid' => 1, 'email' => $email],
                ['is_valid' => 0, 'email' => $email]
            );

            Log::channel('daily')->warning('FP_RESET:TOKEN_EXPIRED', [
                'request_id' => $reqId,
                'email'      => $email,
                'expired_at' => $row->expires_at,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'This reset link is invalid or has expired.',
            ], 422);
        }

        // Token hash check
        if (!Hash::check($rawToken, $row->token)) {
            Log::channel('daily')->warning('FP_RESET:TOKEN_MISMATCH', [
                'request_id' => $reqId,
                'email'      => $email,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'This reset link is invalid or has expired.',
            ], 422);
        }

        // Confirm user still exists
        $userRow = DB::table('users')->select('id')->where('email', $email)->first();

        if (!$userRow) {
            DB::table('password_reset_token')
                ->where('id', $row->id)
                ->update(['is_valid' => 0]);

            $this->logActivity(
                $r,
                'update',
                'User not found during reset — invalidated record',
                'password_reset_token',
                (int) $row->id,
                ['is_valid'],
                ['is_valid' => 1, 'email' => $email],
                ['is_valid' => 0, 'email' => $email]
            );

            Log::channel('daily')->error('FP_RESET:USER_NOT_FOUND', [
                'request_id' => $reqId,
                'email'      => $email,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        // Update password
        DB::table('users')
            ->where('email', $email)
            ->update([
                'password'   => Hash::make($r->password),
                'updated_at' => Carbon::now(),
            ]);

        Log::channel('daily')->info('FP_RESET:PASSWORD_UPDATED', [
            'request_id' => $reqId,
            'email'      => $email,
            'user_id'    => $userRow->id,
        ]);

        $this->logActivity(
            $r,
            'update',
            'Password reset successful — user password updated',
            'users',
            (int) $userRow->id,
            ['password', 'updated_at'],
            ['email' => $email],
            ['email' => $email]
        );

        // Mark token as used + invalidate
        DB::table('password_reset_token')
            ->where('id', $row->id)
            ->update([
                'is_valid' => 0,
                'used_at'  => Carbon::now(),
            ]);

        $this->logActivity(
            $r,
            'update',
            'Reset token marked used and invalidated after successful reset',
            'password_reset_token',
            (int) $row->id,
            ['is_valid', 'used_at'],
            ['is_valid' => 1, 'used_at' => null,              'email' => $email],
            ['is_valid' => 0, 'used_at' => Carbon::now()->toDateTimeString(), 'email' => $email]
        );

        Log::channel('daily')->info('FP_RESET:TOKEN_INVALIDATED', [
            'request_id' => $reqId,
            'email'      => $email,
            'record_id'  => $row->id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Your password has been successfully updated.',
        ]);
    }
}