<?php
// C:\xampp\htdocs\ForgotPassword\app\Services\LogPasswordResetMailer.php

namespace App\Services;

use App\Contracts\PasswordResetMailer;
use Illuminate\Support\Facades\Log;

class LogPasswordResetMailer implements PasswordResetMailer
{
    public function sendResetLink(string $email, string $resetUrl): void
    {
        Log::channel('daily')->info('PASSWORD_RESET_LINK', [
            'email'     => $email,
            'reset_url' => $resetUrl,
        ]);
    }
}