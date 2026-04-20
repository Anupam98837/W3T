<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

class PasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $resetLink,
        public ?string $userEmail = null
    ) {}

    public function build()
    {
        $fromAddr = config('mail.from.address');
        $fromName = config('mail.from.name');

        $m = $this->subject('Password Reset Request')
            ->from($fromAddr, $fromName)
            ->view('emails.passwordResetLink')
            ->with([
                'resetLink' => $this->resetLink,
                'userEmail' => $this->userEmail,
            ]);

        // Better deliverability on many hosts
        $m->withSymfonyMessage(function (SymfonyEmail $message) use ($fromAddr, $fromName) {
            if ($fromAddr) {
                $message->sender(new Address($fromAddr, $fromName ?: ''));
                $message->returnPath($fromAddr);
            }
        });

        return $m;
    }
}