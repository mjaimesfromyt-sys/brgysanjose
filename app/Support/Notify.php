<?php

namespace App\Support;

use App\Jobs\DeliverNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationDispatcher;

class Notify
{
    /**
     * Send a notification without slowing down or breaking the request that
     * triggered it (approve / reject / mark paid / refund).
     *
     *  - The in-app (database) part is written immediately — a single local
     *    INSERT — so the bell and sidebar counts are fresh on the next page
     *    load.
     *  - Email (and any other channel) is pushed onto the queue and sent by
     *    a background worker. The click no longer waits ~4s for SMTP.
     *
     * A queue worker must be running for email to go out:
     *     php artisan queue:work
     * With no worker the jobs simply wait in the `jobs` table; nothing breaks
     * and the in-app notification still shows.
     *
     * Delivery failures are logged, never thrown — the underlying action is
     * already committed by the time we notify.
     */
    public static function send(object $notifiable, Notification $notification): void
    {
        $channels = method_exists($notification, 'via')
            ? (array) $notification->via($notifiable)
            : [];

        if (in_array('database', $channels, true)) {
            try {
                NotificationDispatcher::sendNow($notifiable, $notification, ['database']);
            } catch (\Throwable $e) {
                self::logFailure($notification, $e, 'database');
            }
        }

        $queuedChannels = array_values(array_diff($channels, ['database']));

        if ($queuedChannels !== []) {
            DeliverNotification::dispatch($notifiable, $notification, $queuedChannels);
        }
    }

    private static function logFailure(Notification $notification, \Throwable $e, string $channels): void
    {
        Log::error('Notification delivery failed', [
            'notification' => $notification::class,
            'channels'     => $channels,
            'error'        => $e->getMessage(),
        ]);
    }
}
