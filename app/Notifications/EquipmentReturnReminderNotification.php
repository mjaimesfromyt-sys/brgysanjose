<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EquipmentReturnReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly EquipmentRental $rental,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $rental = $this->rental;
        $items  = $rental->items->map(fn ($line) => "{$line->quantity}× {$line->equipment->name}")->implode(', ');
        $dueAt  = $rental->due_at?->format('g:i A');

        return [
            'title' => 'Equipment return reminder',
            'body'  => "Reminder: your rented equipment ({$items}) is due for return today at {$dueAt}. Please bring the items to the Barangay Hall before closing time.",
            'url'   => route('rentals.index'),
            'icon'  => 'clock',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rental = $this->rental;
        $items  = $rental->items->map(fn ($line) => "{$line->quantity}× {$line->equipment->name}")->implode(', ');
        $dueAt  = $rental->due_at?->format('F d, Y g:i A');

        return (new MailMessage)
            ->subject('Equipment return reminder — Barangay San Jose')
            ->greeting("Hi {$notifiable->name},")
            ->line("This is a friendly reminder that your rented equipment ({$items}) is due for return soon.")
            ->line("Return deadline: {$dueAt}")
            ->line('Please bring the items to the Barangay Hall before 5:00 PM to avoid late fees.')
            ->action('View my rentals', route('rentals.index'))
            ->line('Thank you for your cooperation.');
    }
}
