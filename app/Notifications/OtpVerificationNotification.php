<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $otpCode,
        public string $userName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // A dedicated view rather than the generic notification lines: the code
        // needs to sit in its own block, above the small print. It wraps itself
        // in the same mail-shell component as every other mail.
        return (new MailMessage)
            ->subject('Your OTP verification code — Barangay San Jose')
            ->view('emails.otp', [
                'residentName' => $this->userName,
                'code'         => $this->otpCode,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'otp_code' => $this->otpCode,
        ];
    }
}
