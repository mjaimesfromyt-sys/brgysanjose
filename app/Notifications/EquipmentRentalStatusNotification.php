<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use App\Services\PayMongoService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRentalStatusNotification extends Notification
{
    /**
     * @param 'approved'|'released'|'rejected'|'payment_confirmed' $event
     */
    public function __construct(
        private readonly EquipmentRental $rental,
        private readonly string $event,
    ) {
    }

    public function via(object $notifiable): array
    {
        // 'database' first so the in-app notification is stored even if the
        // mail channel later throws (SMTP down); Notify::send() swallows that.
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $rental = $this->rental;
        $items  = $rental->items->map(fn ($line) => "{$line->quantity}× {$line->equipment->name}")->implode(', ');
        $url    = route('rentals.index');

        return match ($this->event) {
            'approved' => [
                'title' => 'Rental approved',
                'body'  => "Your equipment rental request ({$items}) has been approved. We'll notify you again once the items are ready for release.",
                'url'   => $url,
                'icon'  => 'package',
            ],
            'released' => [
                'title' => 'Equipment ready to claim',
                'body'  => "Your equipment rental ({$items}) is ready to claim at the barangay hall. Claim code: {$rental->claim_code}.",
                'url'   => $url,
                'icon'  => 'package',
            ],
            'rejected' => [
                'title' => 'Rental not approved',
                'body'  => "Your equipment rental request ({$items}) was not approved."
                           . ($rental->admin_remarks ? " Reason: {$rental->admin_remarks}" : ''),
                'url'   => $url,
                'icon'  => 'package',
            ],
            'payment_confirmed' => [
                'title' => 'Payment received',
                'body'  => "We've received your payment for your equipment rental ({$items}). Your rental is now pending admin approval.",
                'url'   => $url,
                'icon'  => 'package',
            ],
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rental = $this->rental;
        $items  = $rental->items->map(fn ($line) => "{$line->quantity}× {$line->equipment->name}")->implode(', ');

        $lines = $rental->items->map(fn ($line) => [
            'label' => "{$line->quantity}× {$line->equipment->name}",
            'value' => '₱' . number_format(($line->equipment->fee ?? 0) * $line->quantity, 2),
        ])->all();
        if ($rental->payment_method !== 'cash') {
            $lines[] = ['label' => 'Transaction Fee', 'value' => '₱' . number_format(PayMongoService::transactionFee(), 2)];
        }

        return match ($this->event) {
            'approved' => (new MailMessage)
                ->subject('Rental approved — Barangay San Jose')
                ->greeting("Hi {$notifiable->name},")
                ->line("Your equipment rental request ({$items}) has been approved.")
                ->line('We will notify you again once the items are ready for release.')
                ->action('View my rentals', route('rentals.index')),

            'released' => (new MailMessage)
                ->subject('Equipment ready to claim — Barangay San Jose')
                ->greeting("Hi {$notifiable->name},")
                ->line("Your equipment rental ({$items}) is ready to claim at the barangay hall.")
                ->line("Claim code: {$rental->claim_code}")
                ->action('View my rentals', route('rentals.index')),

            'rejected' => (new MailMessage)
                ->subject('Rental rejected — Barangay San Jose')
                ->greeting("Hi {$notifiable->name},")
                ->line("Your equipment rental request ({$items}) was not approved.")
                ->when($rental->admin_remarks, fn ($mail) => $mail->line("Reason: {$rental->admin_remarks}"))
                ->action('View my rentals', route('rentals.index')),

            'payment_confirmed' => (new MailMessage)
                ->subject('Payment received — Barangay San Jose')
                ->view('emails.receipt', ['receipt' => [
                    'title'            => 'Payment Receipt',
                    'residentName'     => $notifiable->name,
                    'date'             => $rental->updated_at,
                    'intro'            => [
                        "We've received your payment for your equipment rental ({$items}).",
                        'Your rental is now pending admin approval.',
                    ],
                    'lines'            => $lines,
                    'amount'           => $rental->amount_due,
                    'paymentMethod'    => $rental->payment_method,
                    'paymentChannel'   => $rental->payment_channel,
                    'paymentReference' => $rental->payment_reference,
                    'claimCode'        => null,
                    'note'             => null,
                    'ctaLabel'         => 'View my rentals',
                    'ctaUrl'           => route('rentals.index'),
                ]]),
        };
    }
}
