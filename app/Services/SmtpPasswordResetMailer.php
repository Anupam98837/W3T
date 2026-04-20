<?php

namespace App\Services;

use App\Contracts\PasswordResetMailer;
use App\Mail\PasswordResetLinkMail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SmtpPasswordResetMailer implements PasswordResetMailer
{
    public function sendResetLink(string $email, string $resetUrl): void
    {
        // Save ENV/default mailer settings before overriding config
        $envMailer      = config('mail.default', 'smtp');
        $envFromAddress = config('mail.from.address');
        $envFromName    = config('mail.from.name');

        // Pick default active DB mailer
        $smtp = DB::table('mailer_settings')
            ->where('status', 'active')
            ->where('is_default', 1)
            ->orderByDesc('id')
            ->first();

        // Optional fallback: if no default, pick latest active
        if (!$smtp) {
            $smtp = DB::table('mailer_settings')
                ->where('status', 'active')
                ->orderByDesc('id')
                ->first();
        }

        // If no DB mailer exists, directly use ENV mailer
        if (!$smtp) {
            Mail::mailer($envMailer)
                ->to($email)
                ->send(new PasswordResetLinkMail($resetUrl, $email));

            Log::info('FP_SEND_LINK:MAIL_SOURCE_ENV_NO_DB', [
                'email'      => $email,
                'mail_host'  => config("mail.mailers.{$envMailer}.host"),
                'mailer'     => $envMailer,
                'from'       => $envFromAddress,
            ]);
            return;
        }

        // Try dynamic SMTP first
        try {
            $smtpPassword = !empty($smtp->password)
                ? Crypt::decryptString($smtp->password)
                : null;

            config([
                'mail.mailers.dynamic_smtp' => [
                    'transport'  => $smtp->mailer ?: 'smtp',
                    'host'       => $smtp->host,
                    'port'       => (int) $smtp->port,
                    'encryption' => $smtp->encryption ?: null,
                    'username'   => $smtp->username,
                    'password'   => $smtpPassword,
                    'timeout'    => $smtp->timeout ?: null,
                    'auth_mode'  => null,
                ],
                'mail.from.address' => $smtp->from_address,
                'mail.from.name'    => $smtp->from_name,
            ]);

            Mail::mailer('dynamic_smtp')
                ->to($email)
                ->send(new PasswordResetLinkMail($resetUrl, $email));

            Log::info('FP_SEND_LINK:MAIL_SENT_SUCCESS_DB', [
                'email'     => $email,
                'mailer_id' => $smtp->id,
                'host'      => $smtp->host,
                'username'  => $smtp->username,
            ]);

            return; // done
        } catch (Throwable $dbMailError) {
            Log::warning('FP_SEND_LINK:MAIL_DB_FAILED_TRY_ENV', [
                'email'     => $email,
                'mailer_id' => $smtp->id ?? null,
                'error'     => $dbMailError->getMessage(),
            ]);
        }

        // DB dynamic SMTP failed → fallback to ENV SMTP
        try {
            // Restore ENV from config before fallback send
            config([
                'mail.from.address' => $envFromAddress,
                'mail.from.name'    => $envFromName,
            ]);

            Mail::mailer($envMailer)
                ->to($email)
                ->send(new PasswordResetLinkMail($resetUrl, $email));

            Log::info('FP_SEND_LINK:MAIL_SENT_SUCCESS_ENV_FALLBACK', [
                'email'     => $email,
                'mailer'    => $envMailer,
                'mail_host' => config("mail.mailers.{$envMailer}.host"),
                'from'      => $envFromAddress,
            ]);
        } catch (Throwable $envMailError) {
            Log::error('FP_SEND_LINK:MAIL_BOTH_FAILED', [
                'email'           => $email,
                'db_mailer_id'    => $smtp->id ?? null,
                'db_error_logged' => true,
                'env_mailer'      => $envMailer,
                'env_error'       => $envMailError->getMessage(),
            ]);

            throw $envMailError; // bubble up final failure
        }
    }
}