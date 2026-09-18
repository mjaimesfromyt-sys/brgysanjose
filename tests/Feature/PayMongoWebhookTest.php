<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DocumentRequest;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\TransactionType;
use App\Models\User;
use App\Notifications\EquipmentRentalStatusNotification;
use App\Support\PayMongoSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PayMongoWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test_secret_123';
    private const ENDPOINT = '/webhooks/paymongo';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.paymongo.webhook_secret' => self::SECRET]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeBody(string $type, array $resource): string
    {
        return json_encode([
            'data' => [
                'attributes' => [
                    'type' => $type,
                    'data' => $resource,
                ],
            ],
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * POST a raw payload; $header === null sends a correctly-signed header.
     */
    private function postRaw(string $body, ?string $header = null): TestResponse
    {
        return $this->call(
            'POST',
            self::ENDPOINT,
            [],
            [],
            [],
            [
                'HTTP_PAYMONGO_SIGNATURE' => $header ?? PayMongoSignature::for($body, self::SECRET),
                'CONTENT_TYPE' => 'application/json',
            ],
            $body,
        );
    }

    private function postEvent(string $type, array $resource): TestResponse
    {
        return $this->postRaw($this->makeBody($type, $resource));
    }

    /**
     * A payment resource as PayMongo reports it on a paid event.
     */
    private function paidPayment(string $sessionId, string $paymentId = 'pay_123', string $status = 'paid'): array
    {
        return [
            'id' => $paymentId,
            'attributes' => [
                'status' => $status,
                'source' => ['type' => 'gcash'],
                'checkout_session' => ['id' => $sessionId],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Security
    // ------------------------------------------------------------------

    public function test_rejects_request_without_valid_signature(): void
    {
        $body = $this->makeBody('checkout_session.payment.paid', $this->paidPayment('cs_x'));

        $this->postRaw($body, 't=123,te=deadbeef,li=')->assertStatus(401);
        $this->postRaw($body, '')->assertStatus(401);
    }

    public function test_rejects_stale_timestamp(): void
    {
        $body = $this->makeBody('checkout_session.payment.paid', $this->paidPayment('cs_x'));
        $staleHeader = PayMongoSignature::for($body, self::SECRET, time() - 7200);

        $this->postRaw($body, $staleHeader)->assertStatus(401);
    }

    public function test_returns_503_when_webhook_secret_not_configured(): void
    {
        config(['services.paymongo.webhook_secret' => null]);

        $body = $this->makeBody('checkout_session.payment.paid', $this->paidPayment('cs_x'));

        $this->postRaw($body)->assertStatus(503);
    }

    // ------------------------------------------------------------------
    // Confirmation per record type
    // ------------------------------------------------------------------

    public function test_confirms_booking_paid_and_assigns_claim_code(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $booking = Booking::create([
            'user_id' => $user->id,
            'facility_id' => Facility::create(['name' => 'Hall', 'fee' => 500, 'is_active' => true])->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'purpose' => 'Meeting',
            'status' => 'pending',
            'payment_method' => 'gcash',
            'amount_due' => 510,
            'payment_status' => 'unpaid',
            // Live controllers store the checkout session id in payment_reference.
            'payment_reference' => 'cs_test_abc',
        ]);

        $this->postEvent('checkout_session.payment.paid', $this->paidPayment('cs_test_abc', 'pay_001'))
            ->assertOk();

        $booking->refresh();
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('pay_001', $booking->payment_reference);
        $this->assertSame('gcash', $booking->payment_channel);
        $this->assertSame('cs_test_abc', $booking->paymongo_checkout_session_id);
        $this->assertNotNull($booking->claim_code);
        $this->assertStringStartsWith('BRGY-', $booking->claim_code);

        Notification::assertSentTo($user, \App\Notifications\BookingStatusNotification::class);
    }

    public function test_confirms_document_request_paid(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $request = $user->documentRequests()->create([
            'transaction_type_id' => TransactionType::create(['name' => 'Clearance', 'fee' => 50, 'requires_residency' => false])->id,
            'purpose' => 'Job application',
            'status' => 'pending',
            'payment_method' => 'gcash',
            'amount_due' => 70,
            'payment_status' => 'unpaid',
            'paymongo_checkout_session_id' => 'cs_test_doc',
        ]);

        $this->postEvent('checkout_session.payment.paid', $this->paidPayment('cs_test_doc', 'pay_002'))
            ->assertOk();

        $request->refresh();
        $this->assertSame('paid', $request->payment_status);
        $this->assertSame('pay_002', $request->payment_reference);
        $this->assertNotNull($request->claim_code);
    }

    public function test_confirms_rental_paid_and_deducts_stock(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $equipment = Equipment::create(['name' => 'Chairs', 'fee' => 20, 'total_stock' => 10, 'is_active' => true]);

        $rental = $user->equipmentRentals()->create([
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'purpose' => 'Event',
            'status' => 'pending',
            'payment_method' => 'gcash',
            'amount_due' => 120,
            'payment_status' => 'unpaid',
            'paymongo_checkout_session_id' => 'cs_test_rental',
        ]);

        $rental->items()->create(['equipment_id' => $equipment->id, 'quantity' => 3]);

        $this->postEvent('payment.paid', $this->paidPayment('cs_test_rental', 'pay_003'))
            ->assertOk();

        $rental->refresh();
        $this->assertSame('paid', $rental->payment_status);
        $this->assertSame(7, $equipment->fresh()->total_stock);

        Notification::assertSentTo($user, EquipmentRentalStatusNotification::class);
    }

    // ------------------------------------------------------------------
    // Idempotency & edge cases
    // ------------------------------------------------------------------

    public function test_second_identical_event_is_ignored(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $equipment = Equipment::create(['name' => 'Tables', 'fee' => 50, 'total_stock' => 5, 'is_active' => true]);

        $rental = $user->equipmentRentals()->create([
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'purpose' => 'Event',
            'status' => 'pending',
            'payment_method' => 'gcash',
            'amount_due' => 250,
            'payment_status' => 'unpaid',
            'paymongo_checkout_session_id' => 'cs_test_dup',
        ]);

        $rental->items()->create(['equipment_id' => $equipment->id, 'quantity' => 2]);

        $event = $this->paidPayment('cs_test_dup', 'pay_004');

        $this->postEvent('checkout_session.payment.paid', $event)->assertOk();
        $this->postEvent('checkout_session.payment.paid', $event)->assertOk();
        $this->postEvent('payment.paid', $event)->assertOk();

        // Stock deducted exactly once.
        $this->assertSame(3, $equipment->fresh()->total_stock);
    }

    public function test_unknown_checkout_session_is_accepted_without_error(): void
    {
        $this->postEvent('checkout_session.payment.paid', $this->paidPayment('cs_unknown'))
            ->assertOk();
    }

    public function test_failed_payment_event_does_not_confirm(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $request = $user->documentRequests()->create([
            'transaction_type_id' => TransactionType::create(['name' => 'Indigency', 'fee' => 50, 'requires_residency' => false])->id,
            'status' => 'pending',
            'payment_method' => 'gcash',
            'amount_due' => 70,
            'payment_status' => 'unpaid',
            'paymongo_checkout_session_id' => 'cs_test_failed',
        ]);

        $this->postEvent('payment.failed', $this->paidPayment('cs_test_failed', 'pay_005', 'failed'))
            ->assertOk();

        $this->assertSame('unpaid', $request->fresh()->payment_status);
    }

    public function test_malformed_payload_is_accepted_gracefully(): void
    {
        $this->postEvent('not.a.real.event', [])->assertOk();
    }
}
