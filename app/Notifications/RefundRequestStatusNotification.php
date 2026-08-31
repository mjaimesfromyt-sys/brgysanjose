<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundRequestStatusNotification extends Notification
{
    /**
     * @param 'submitted'|'admin_new'|'approved'|'rejected'|'refunded' $event
     */
    public function __construct(
        private readonly RefundRequest $refund,
        private readonly string $event,
    ) {
    }

    public function via(object $notifiable): array
    {
        // 'database' first so the in-app notification survives an SMTP outage.
        return ['database', 'mail'];
    }

    private function items(): string
    {
        return $this->refund->rental->items
            ->map(fn ($line) => "{$line->quantity}× {$line->equipment->name}")
            ->implode(', ');
    }

    private function peso(float|string|null $amount): string
    {
        return '₱' . number_format((float) $amount, 2);
    }

    public function toArray(object $notifiable): array
    {
        $refund = $this->refund;
        $items  = $this->items();

        return match ($this->event) {
            'submitted' => [
                'title' => 'Refund request received',
                'body'  => "We've received your cancellation / refund request for your rental ({$items}). The barangay will review it shortly.",
                'url'   => route('rentals.index'),
                'icon'  => 'refund',
            ],
            'admin_new' => [
                'title' => 'New refund request',
                'body'  => "{$refund->user->name} requested a refund for rental #{$refund->equipment_rental_id} ({$items}). Estimated {$this->peso($refund->estimated_amount)}.",
                'url'   => route('admin.refunds.index'),
                'icon'  => 'refund',
            ],
            'approved' => [
                'title' => 'Refund approved',
                'body'  => "Your refund of {$this->peso($refund->amount)} for your rental ({$items}) was approved and is being processed.",
                'url'   => route('rentals.index'),
                'icon'  => 'refund',
            ],
            'rejected' => [
                'title' => 'Refund request not approved',
                'body'  => "Your refund request for your rental ({$items}) was not approved."
                           . ($refund->admin_remarks ? " Reason: {$refund->admin_remarks}" : ''),
                'url'   => route('rentals.index'),
                'icon'  => 'refund',
            ],
            'refunded' => [
                'title' => 'Refund processed',
                'body'  => "{$this->peso($refund->amount)} has been refunded for your rental ({$items})."
                           . ($refund->refund_method === 'online'
                               ? " Reference: {$refund->refund_reference}. Allow a few business days for it to reflect."
                               : " Please claim the cash refund at the barangay hall. Reference: {$refund->refund_reference}."),
                'url'   => route('rentals.index'),
                'icon'  => 'refund',
            ],
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        $refund = $this->refund;
        $items  = $this->items();

        return match ($this->event) {
            'submitted' => (new MailMessage)
                ->subject('Refund request received — Barangay San Jose')
                ->greeting("Hi {$notifiable->name},")
                ->line("We've received your cancellation / refund request for your equipment rental ({$items}).")
                ->line("Reason you gave: \"{$refund->reason}\"")
                ->line("Estimated refund: {$this->peso($refund->estimated_amount)} (the final amount is confirmed by the barangay).")
                ->action('View my rentals', route('rentals.index')),

            'admin_new' => (new MailMessage)
                ->subject('New refund request — Barangay San Jose')
                ->greeting("Hi {$notifiable->name},")
                ->line("{$refund->user->name} has requested a refund for equipment rental #{$refund->equipment_rental_id} ({$items}).")
                ->line("Type: " . ($refund->type === 'early_return' ? 'Early return' : 'Cancellation before release'))
                ->line("Reason: \"{$refund->reason}\"")
                ->line("Estimated refund: {$this->peso($refund->estimated_amount)}")
                ->action('Review refund requests', route('admin.refunds.index')),

            'approved' => (new MailMessage)
                ->subject('Refund approved — Barangay San Jose')
                ->greeting("Hi {$notifiable->name},")
                ->line("Your refund request for your equipment rental ({$items}) has been approved.")
                ->line("Approved refund: {$this->peso($refund->amount)}")
                ->when($refund->admin_remarks, fn ($m) => $m->line("Note: {$refund->admin_remarks}"))
                ->line('We will notify you again once the refund has been processed.')
                ->action('View my rentals', route('rentals.index')),

            'rejected' => (new MailMessage)
                ->subject('Refund request not approved — Barangay San Jose')
                ->greeting("Hi {$notifiable->name},")
                ->line("Your refund request for your equipment rental ({$items}) was not approved.")
                ->when($refund->admin_remarks, fn ($m) => $m->line("Reason: {$refund->admin_remarks}"))
                ->action('View my rentals', route('rentals.index')),

            'refunded' => (new MailMessage)
                ->subject('Refund processed — Barangay San Jose')
                ->greeting("Hi {$notifiable->name},")
                ->line("{$this->peso($refund->amount)} has been refunded for your equipment rental ({$items}).")
                ->line($refund->refund_method === 'online'
                    ? "It was sent back to your original payment method. Reference: {$refund->refund_reference}. Please allow a few business days for it to reflect."
                    : "Please claim the cash refund at the barangay hall. Reference: {$refund->refund_reference}.")
                ->action('View my rentals', route('rentals.index')),
        };
    }
}
