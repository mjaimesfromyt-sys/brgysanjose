<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DocumentRequest;
use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalItem;
use App\Models\Facility;
use App\Models\RefundRequest;
use App\Models\TransactionType;
use App\Models\User;
use App\Notifications\BookingStatusNotification;
use App\Notifications\DocumentRequestStatusNotification;
use App\Notifications\EquipmentRentalStatusNotification;
use App\Notifications\OtpVerificationNotification;
use App\Notifications\RefundRequestStatusNotification;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as NotificationDispatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every transactional email goes out in the same barangay shell
 * (resources/views/components/mail-shell.blade.php). These render each notification
 * the mailer would, without touching the database, and check the branding
 * survived.
 */
class BrandedMailTest extends TestCase
{
    private function resident(): User
    {
        $user = new User([
            'first_name' => 'RJ',
            'last_name'  => 'Boniel',
            'email'      => 'resident@example.test',
        ]);
        $user->id = 1;

        return $user;
    }

    private function rental(): EquipmentRental
    {
        $item = new EquipmentRentalItem(['quantity' => 10]);
        $item->setRelation('equipment', new Equipment(['name' => 'Chairs', 'fee' => 5]));

        $rental = new EquipmentRental([
            'payment_method'    => 'gcash',
            'payment_channel'   => 'gcash',
            'payment_reference' => 'pay_test123',
            'amount_due'        => 70,
            'claim_code'        => 'SJ-4821',
        ]);
        $rental->id = 12;
        $rental->setRelation('items', new Collection([$item]));
        $rental->forceFill(['updated_at' => now()]);

        return $rental;
    }

    /**
     * @return array<string, array{0: \Closure(self): Notification}>
     */
    public static function notificationProvider(): array
    {
        $booking = function () {
            $booking = new Booking([
                'start_date'        => '2026-09-01',
                'payment_method'    => 'gcash',
                'payment_channel'   => 'gcash',
                'payment_reference' => 'pay_test123',
                'amount_due'        => 520,
                'claim_code'        => 'SJ-1001',
                'admin_remarks'     => 'Double-booked that morning.',
            ]);
            $booking->setRelation('facility', new Facility(['name' => 'Barangay Gymnasium', 'fee' => 500]));
            $booking->forceFill(['updated_at' => now()]);

            return $booking;
        };

        $documentRequest = function () {
            $request = new DocumentRequest([
                'payment_method'    => 'gcash',
                'payment_channel'   => 'gcash',
                'payment_reference' => 'pay_test123',
                'amount_due'        => 120,
                'claim_code'        => 'SJ-2002',
                'admin_remarks'     => 'Proof of residency was unreadable.',
            ]);
            $request->setRelation('transactionType', new TransactionType(['name' => 'Barangay Clearance', 'fee' => 100]));
            $request->forceFill(['updated_at' => now()]);

            return $request;
        };

        $cases = [];

        foreach (['approved', 'rejected', 'payment_confirmed'] as $event) {
            $cases["booking {$event}"] = [fn () => new BookingStatusNotification($booking(), $event)];
        }

        foreach (['validated', 'rejected', 'payment_confirmed'] as $event) {
            $cases["document request {$event}"] = [fn () => new DocumentRequestStatusNotification($documentRequest(), $event)];
        }

        foreach (['approved', 'released', 'rejected', 'payment_confirmed'] as $event) {
            $cases["rental {$event}"] = [fn (self $test) => new EquipmentRentalStatusNotification($test->rental(), $event)];
        }

        foreach (['submitted', 'admin_new', 'approved', 'rejected', 'refunded'] as $event) {
            $cases["refund {$event}"] = [fn (self $test) => new RefundRequestStatusNotification($test->refund(), $event)];
        }

        $cases['otp verification'] = [fn () => new OtpVerificationNotification('483920', 'RJ Boniel')];

        return $cases;
    }

    private function refund(): RefundRequest
    {
        $refund = new RefundRequest([
            'reason'           => 'Event was moved',
            'type'             => 'cancellation',
            'estimated_amount' => 50,
            'amount'           => 50,
            'refund_method'    => 'online',
            'refund_reference' => 'ref_test123',
            'admin_remarks'    => 'Approved by the punong barangay.',
        ]);
        $refund->equipment_rental_id = 12;
        $refund->setRelation('rental', $this->rental());
        $refund->setRelation('user', $this->resident());

        return $refund;
    }

    /**
     * Render exactly the way Illuminate\Notifications\Channels\MailChannel
     * would: a message carrying its own view is rendered straight, anything
     * else goes through the (overridden) notifications::email view.
     */
    private function render(Notification $notification): string
    {
        $message = $notification->toMail($this->resident());

        return $message->view
            ? view($message->view, $message->viewData)->render()
            : (string) app(Markdown::class)->render($message->markdown, $message->data());
    }

    #[DataProvider('notificationProvider')]
    public function test_every_transactional_email_uses_the_barangay_shell(\Closure $make): void
    {
        $html = $this->render($make($this));

        $this->assertStringContainsString('Barangay San Jose', $html);
        $this->assertStringContainsString('Talibon, Bohol', $html);
        $this->assertStringContainsString('cid:barangay-seal.png', $html);
        $this->assertStringContainsString(config('barangay.email'), $html);
        $this->assertStringContainsString('Hi RJ Boniel,', $html);

        // Laravel's stock theme would repaint the body grey and links blue.
        $this->assertStringNotContainsString('#3869d4', $html);
    }

    public function test_resident_supplied_text_is_escaped_rather_than_rendered(): void
    {
        $refund = $this->refund();
        $refund->reason = '<script>alert(1)</script>';

        $html = $this->render(new RefundRequestStatusNotification($refund, 'submitted'));

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('alert(1)', $html);
    }

    public function test_the_plain_text_alternative_is_readable_text_not_markup(): void
    {
        NotificationDispatcher::sendNow(
            $this->resident(),
            new RefundRequestStatusNotification($this->refund(), 'submitted'),
            ['mail'],
        );

        $sent = Mail::mailer('array')->getSymfonyTransport()->messages();
        $text = quoted_printable_decode($sent[0]->getOriginalMessage()->getTextBody());

        $this->assertStringNotContainsString('<table', $text);
        $this->assertStringNotContainsString('<!DOCTYPE', $text);
        $this->assertStringContainsString('Hi RJ Boniel,', $text);
        $this->assertStringContainsString('View my rentals: ' . route('rentals.index'), $text);
        $this->assertStringContainsString(config('barangay.email'), $text);
    }

    public function test_the_seal_is_attached_inline_to_the_sent_message(): void
    {
        NotificationDispatcher::sendNow(
            $this->resident(),
            new OtpVerificationNotification('483920', 'RJ Boniel'),
            ['mail'],
        );

        $sent = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);

        $raw  = $sent[0]->toString();
        $html = quoted_printable_decode($raw);

        // Symfony swaps "cid:barangay-seal.png" for the generated content id
        // when it assembles the message; both sides have to line up or the
        // seal shows as a broken image.
        $this->assertMatchesRegularExpression('/Content-ID: <([^>]+)>/', $raw);
        preg_match('/Content-ID: <([^>]+)>/', $raw, $contentId);

        $this->assertStringContainsString('src="cid:' . $contentId[1] . '"', $html);
        $this->assertStringContainsString('multipart/related', $raw);
    }
}
