<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationDispatcher;

/**
 * Delivers the slow channels of a notification (email) off the web request,
 * so an approve / reject / mark-paid click returns immediately instead of
 * waiting ~4s for the SMTP handshake.
 *
 * Requires a queue worker to be running (`php artisan queue:work`).
 */
class DeliverNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    /**
     * @param  list<string>  $channels
     */
    public function __construct(
        public object $notifiable,
        public Notification $notification,
        public array $channels,
    ) {
    }

    public function handle(): void
    {
        NotificationDispatcher::sendNow($this->notifiable, $this->notification, $this->channels);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Queued notification delivery failed', [
            'notification' => $this->notification::class,
            'channels'     => implode(',', $this->channels),
            'error'        => $e->getMessage(),
        ]);
    }
}
