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
        return (new MailMessage)
            ->subject('Your OTP Verification Code - ' . config('app.name'))
            ->greeting("Hello {$this->userName},")
            ->line('Thank you for registering with ' . config('app.name') . '.')
            ->line('Please use the following OTP code to verify your email address:')
            ->line('<strong style="font-size: 24px; letter-spacing: 4px; color: #2563eb;">' . $this->otpCode . '</strong>')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this code, please ignore this email.')
            ->salutation('Best regards,' . "\n" . config('app.name') . ' Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'otp_code' => $this->otpCode,
        ];
    }
}