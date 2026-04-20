<?php
// C:\xampp\htdocs\ForgotPassword\app\Contracts\PasswordResetMailer.php

namespace App\Contracts;

interface PasswordResetMailer
{
    public function sendResetLink(string $email, string $resetUrl): void;
}