<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use App\Services\PayMongoService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRequestStatusNotification extends Notification
{
    /**
     * @param 'validated'|'rejected'|'payment_confirmed' $event
     */
    public function __construct(
        private readonly DocumentRequest $documentRequest,
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
        $request = $this->documentRequest;
        $name    = $request->transactionType->name;
        $url     = route('requests.index');

        return match ($this->event) {
            'validated' => [
                'title' => 'Document ready to claim',
                'body'  => "Your request for \"{$name}\" has been validated. Claim code: {$request->claim_code}. Bring a valid ID when claiming.",
                'url'   => $url,
                'icon'  => 'file-text',
            ],
            'rejected' => [
                'title' => 'Request not approved',
                'body'  => "Your request for \"{$name}\" was not approved."
                           . ($request->admin_remarks ? " Reason: {$request->admin_remarks}" : ''),
                'url'   => $url,
                'icon'  => 'file-text',
            ],
            'payment_confirmed' => [
                'title' => 'Payment received',
                'body'  => "We've received your payment for \"{$name}\". Your request is now being processed.",
                'url'   => $url,
                'icon'  => 'file-text',
            ],
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->documentRequest;
        $name    = $request->transactionType->name;

        $lines = [
            ['label' => $name, 'value' => '₱' . number_format($request->transactionType->fee ?? 0, 2)],
        ];
        if ($request->payment_method !== 'cash') {
            $lines[] = ['label' => 'Transaction Fee', 'value' => '₱' . number_format(PayMongoService::transactionFee(), 2)];
        }

        return match ($this->event) {
            'validated' => (new MailMessage)
                ->subject("Ready to claim: {$name} — Barangay San Jose")
                ->greeting("Hi {$notifiable->name},")
                ->line("Your request for \"{$name}\" has been validated and is ready to claim at the barangay hall.")
                ->line("Claim code: {$request->claim_code}")
                ->action('View my requests', route('requests.index'))
                ->line('Please bring a valid ID when claiming.'),

            'rejected' => (new MailMessage)
                ->subject("Request rejected: {$name} — Barangay San Jose")
                ->greeting("Hi {$notifiable->name},")
                ->line("Your request for \"{$name}\" was not approved.")
                ->when($request->admin_remarks, fn ($mail) => $mail->line("Reason: {$request->admin_remarks}"))
                ->action('View my requests', route('requests.index')),

            'payment_confirmed' => (new MailMessage)
                ->subject("Payment received: {$name} — Barangay San Jose")
                ->view('emails.receipt', ['receipt' => [
                    'title'            => 'Payment Receipt',
                    'residentName'     => $notifiable->name,
                    'date'             => $request->updated_at,
                    'intro'            => [
                        "We've received your payment for \"{$name}\".",
                        'Your request is now being processed.',
                    ],
                    'lines'            => $lines,
                    'amount'           => $request->amount_due,
                    'paymentMethod'    => $request->payment_method,
                    'paymentChannel'   => $request->payment_channel,
                    'paymentReference' => $request->payment_reference,
                    'claimCode'        => null,
                    'note'             => null,
                    'ctaLabel'         => 'View my requests',
                    'ctaUrl'           => route('requests.index'),
                ]]),
        };
    }
}
