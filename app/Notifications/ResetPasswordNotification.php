<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * How long the signed reset link stays valid, in minutes.
     */
    public const EXPIRY_MINUTES = 60;

    public function __construct(
        public string $token,
        public string $email,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // The reset form route itself is signed and time-limited, so the
        // token page cannot be reused after the link expires.
        $url = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(self::EXPIRY_MINUTES),
            ['token' => $this->token, 'email' => $this->email],
        );

        return (new MailMessage)
            ->subject('Reset Your Password - ' . config('app.name'))
            ->greeting("Hello {$notifiable->first_name},")
            ->line('We received a request to reset the password for your ' . config('app.name') . ' account.')
            ->line('Click the button below to choose a new password:')
            ->action('Reset Password', $url)
            ->line('This link will expire in ' . self::EXPIRY_MINUTES . ' minutes.')
            ->line('If you did not request a password reset, you can safely ignore this email — your current password will keep working.')
            ->salutation('Best regards,' . "\n" . config('app.name') . ' Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
