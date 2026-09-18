<?php

namespace App\Console\Commands;

use App\Models\EquipmentRental;
use App\Notifications\EquipmentRentalOverdueNotification;
use App\Support\Notify;
use Illuminate\Console\Command;

class CheckOverdueRentals extends Command
{
    protected $signature = 'rentals:check-overdue';
    protected $description = 'Check overdue equipment rentals, send reminders, and compute late fees';

    public function handle(): int
    {
        $rentals = EquipmentRental::where('status', 'released')
            ->whereNotNull('due_at')
            ->whereNull('returned_at')
            ->with('user', 'items.equipment')
            ->get();

        $notified = 0;
        $charged  = 0;

        foreach ($rentals as $rental) {
            $hoursOverdue = now()->diffInHours($rental->due_at, false) * -1;

            if ($hoursOverdue <= 0) {
                continue;
            }

            // 👉 REMINDER: kon overdue na pero wala pa maka-48 hours
            if ($hoursOverdue < 24 && is_null($rental->overdue_notified_at)) {
                Notify::send(
                    $rental->user,
                    new EquipmentRentalOverdueNotification($rental, 'reminder', $hoursOverdue)
                );
                $rental->update(['overdue_notified_at' => now()]);
                $notified++;
                $this->info("Reminder sent for rental #{$rental->id} ({$hoursOverdue}h overdue)");
            }

            // 👉 LATE FEE: mo-sugod ang bayad human sa 48 hours gikan sa release
            if ($hoursOverdue >= 24) {
                $daysLate = (int) ceil($hoursOverdue / 24);
                $lateFee  = $daysLate * (float) $rental->amount_due;

                if ((float) $rental->late_fee !== $lateFee) {
                    $rental->update(['late_fee' => $lateFee]);
                    $charged++;
                    $this->warn("Late fee updated for rental #{$rental->id}: PHP {$lateFee}");
                }
            }
        }

        $this->info("Done. Reminders: {$notified}, Late fees updated: {$charged}");

        return self::SUCCESS;
    }
}
