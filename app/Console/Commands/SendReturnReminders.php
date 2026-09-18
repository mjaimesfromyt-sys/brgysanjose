<?php

namespace App\Console\Commands;

use App\Models\EquipmentRental;
use App\Notifications\EquipmentReturnReminderNotification;
use App\Support\Notify;
use Illuminate\Console\Command;

class SendReturnReminders extends Command
{
    protected $signature = 'rentals:return-reminder';
    protected $description = 'Send reminder 30 minutes before equipment rental is due';

    public function handle(): int
    {
        $rentals = EquipmentRental::where('status', 'released')
            ->whereNotNull('due_at')
            ->whereNull('returned_at')
            ->whereNull('return_reminded_at')
            ->whereBetween('due_at', [now(), now()->addMinutes(45)])
            ->with('user', 'items.equipment')
            ->get();

        $sent = 0;

        foreach ($rentals as $rental) {
            Notify::send(
                $rental->user,
                new EquipmentReturnReminderNotification($rental)
            );

            $rental->update(['return_reminded_at' => now()]);
            $sent++;
        }

        $this->info("Return reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
