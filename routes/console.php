<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 👉 Check overdue equipment rentals every hour
Schedule::command('rentals:check-overdue')->hourly();
Schedule::command('rentals:return-reminder')->dailyAt('16:30');

// 👉 Process queued emails/notifications
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();
