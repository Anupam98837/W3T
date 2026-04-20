<?php

namespace App\Providers;

use App\Contracts\PasswordResetMailer;
use App\Services\SmtpPasswordResetMailer;
// use App\Services\LogPasswordResetMailer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Live SMTP mailer — actually sends the email
        $this->app->bind(PasswordResetMailer::class, SmtpPasswordResetMailer::class);

        // Dev/debug only (logs link instead of sending) — swap back when needed:
        // $this->app->bind(PasswordResetMailer::class, LogPasswordResetMailer::class);
    }

    public function boot(): void
    {
        //
    }
}