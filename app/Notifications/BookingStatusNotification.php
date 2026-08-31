<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Services\PayMongoService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification
{
    /**
     * @param 'approved'|'rejected'|'payment_confirmed' $event
     */
    public function __construct(
        private readonly Booking $booking,
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
        $booking = $this->booking;
        $name    = $booking->facility->name;
        $date    = $booking->start_date->format('M d, Y');
        $url     = route('bookings.index');

        return match ($this->event) {
            'approved' => [
                'title' => 'Booking approved',
                'body'  => "Your booking for \"{$name}\" on {$date} is approved. Claim code: {$booking->claim_code}.",
                'url'   => $url,
                'icon'  => 'calendar-check',
            ],
            'rejected' => [
                'title' => 'Booking not approved',
                'body'  => "Your booking for \"{$name}\" on {$date} was not approved."
                           . ($booking->admin_remarks ? " Reason: {$booking->admin_remarks}" : ''),
                'url'   => $url,
                'icon'  => 'calendar-check',
            ],
            'payment_confirmed' => [
                'title' => 'Payment received',
                'body'  => "We've received your payment for \"{$name}\". Your booking is now pending admin approval.",
                'url'   => $url,
                'icon'  => 'calendar-check',
            ],
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $name    = $booking->facility->name;

        $lines = [
            ['label' => $name, 'value' => '₱' . number_format($booking->facility->fee ?? 0, 2)],
        ];
        if ($booking->payment_method !== 'cash') {
            $lines[] = ['label' => 'Transaction Fee', 'value' => '₱' . number_format(PayMongoService::transactionFee(), 2)];
        }

        return match ($this->event) {
            'approved' => (new MailMessage)
                ->subject("Booking approved: {$name} — Barangay San Jose")
                ->greeting("Hi {$notifiable->name},")
                ->line("Your booking for \"{$name}\" on {$booking->start_date->format('M d, Y')} has been approved and is ready to claim.")
                ->line("Claim code: {$booking->claim_code}")
                ->action('View my bookings', route('bookings.index')),

            'rejected' => (new MailMessage)
                ->subject("Booking rejected: {$name} — Barangay San Jose")
                ->greeting("Hi {$notifiable->name},")
                ->line("Your booking for \"{$name}\" on {$booking->start_date->format('M d, Y')} was not approved.")
                ->when($booking->admin_remarks, fn ($mail) => $mail->line("Reason: {$booking->admin_remarks}"))
                ->action('View my bookings', route('bookings.index')),

            'payment_confirmed' => (new MailMessage)
                ->subject("Payment received: {$name} — Barangay San Jose")
                ->view('emails.receipt', ['receipt' => [
                    'title'            => 'Payment Receipt',
                    'residentName'     => $notifiable->name,
                    'date'             => $booking->updated_at,
                    'intro'            => [
                        "We've received your payment for your booking of \"{$name}\".",
                        'Your booking is now pending admin approval.',
                    ],
                    'lines'            => $lines,
                    'amount'           => $booking->amount_due,
                    'paymentMethod'    => $booking->payment_method,
                    'paymentChannel'   => $booking->payment_channel,
                    'paymentReference' => $booking->payment_reference,
                    'claimCode'        => null,
                    'note'             => null,
                    'ctaLabel'         => 'View my bookings',
                    'ctaUrl'           => route('bookings.index'),
                ]]),
        };
    }
}
