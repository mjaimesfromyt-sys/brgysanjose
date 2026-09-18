<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EquipmentRentalOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly EquipmentRental $rental,
        private readonly string $event,
        private readonly int $hoursOverdue = 0,
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
        $url    = route('rentals.index');

        return [
            'title' => 'Equipment return overdue',
            'body'  => "Your rented equipment ({$items}) is {$this->hoursOverdue} hour(s) past the return deadline. Please return the items before the 48-hour mark to avoid additional charges.",
            'url'   => $url,
            'icon'  => 'alert',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rental   = $this->rental;
        $items    = $rental->items->map(fn ($line) => "{$line->quantity}× {$line->equipment->name}")->implode(', ');
        $dueAt    = $rental->due_at?->format('F d, Y g:i A');
        $deadline = $rental->released_at?->copy()->addHours(48)->format('F d, Y g:i A');

        return (new MailMessage)
            ->subject('Equipment return overdue — Barangay San Jose')
            ->greeting("Hi {$notifiable->name},")
            ->line("Our records show that your rented equipment ({$items}) has not yet been returned.")
            ->line("Return deadline: {$dueAt}")
            ->line("Currently {$this->hoursOverdue} hour(s) overdue.")
            ->line("Please return the items on or before {$deadline}. Rentals not returned by then will be charged an additional day's rental fee.")
            ->action('View my rentals', route('rentals.index'))
            ->line('Thank you for your cooperation.');
    }
}
