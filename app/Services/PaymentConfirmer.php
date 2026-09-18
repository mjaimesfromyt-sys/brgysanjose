<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DocumentRequest;
use App\Models\EquipmentRental;
use App\Notifications\BookingStatusNotification;
use App\Notifications\DocumentRequestStatusNotification;
use App\Notifications\EquipmentRentalStatusNotification;
use App\Support\ClaimCode;
use App\Support\Notify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Marks a record paid from a *verified* source (PayMongo webhook, or a
 * callback that re-checks the session with PayMongo's API), and fires the
 * same side effects the admin "mark paid" flows use — exactly once, thanks
 * to the unpaid -> paid transition guard.
 */
class PaymentConfirmer
{
    /**
     * Map "checkout session id" -> [$table, $type]. The id may live in
     * paymongo_checkout_session_id (correct column) or, for records created
     * by the current live code, in payment_reference.
     */
    private const MODELS = [
        'bookings' => Booking::class,
        'document_requests' => DocumentRequest::class,
        'equipment_rentals' => EquipmentRental::class,
    ];

    public function confirmByCheckoutSession(string $sessionId, string $channel, string $paymentId): bool
    {
        foreach (self::MODELS as $table => $model) {
            // $model::where(...)->first() would be nicer but the columns
            // genuinely differ per record type, so query both columns.
            $record = $model::where('paymongo_checkout_session_id', $sessionId)
                ->orWhere('payment_reference', $sessionId)
                ->first();

            if ($record === null) {
                continue;
            }

            return $this->confirm($record, $channel, $paymentId);
        }

        Log::warning('PayMongo webhook: no record found for checkout session', [
            'checkout_session_id' => $sessionId,
        ]);

        return false;
    }

    /**
     * @param  Booking|DocumentRequest|EquipmentRental  $record
     */
    public function confirm($record, string $channel, string $paymentId): bool
    {
        return DB::transaction(function () use ($record, $channel, $paymentId) {
            // Lock the row so a webhook + browser callback racing each other
            // cannot both perform the unpaid -> paid transition.
            $record = $record->newQuery()->whereKey($record->getKey())->lockForUpdate()->first();

            if ($record === null || $record->payment_status === 'paid') {
                return false; // Already handled: webhook is idempotent.
            }

            // Capture the legacy checkout-session id BEFORE payment_reference
            // gets overwritten with the real payment id below.
            $legacySessionId = $this->checkoutSessionIdFor($record);

            $record->payment_status = 'paid';
            $record->payment_channel = $channel ?: ($record->payment_channel ?? 'qrph');
            // Store the real payment id for the refund API; the checkout
            // session id stays in paymongo_checkout_session_id.
            $record->payment_reference = $paymentId;
            $record->paymongo_checkout_session_id = $record->paymongo_checkout_session_id ?: $legacySessionId;

            if ($record instanceof Booking || $record instanceof DocumentRequest) {
                if (! $record->claim_code) {
                    $record->claim_code = ClaimCode::next($record->getTable());
                }
            }

            $record->save();

            $this->afterPaid($record);

            return true;
        }, 3);
    }

    /**
     * Side effects after a successful unpaid -> paid transition.
     */
    private function afterPaid($record): void
    {
        if ($record instanceof EquipmentRental) {
            // Matches the admin "mark paid" flow: paid rentals remove stock.
            $record->deductStock();
            Notify::send($record->user, new EquipmentRentalStatusNotification($record, 'payment_confirmed'));

            return;
        }

        if ($record instanceof Booking) {
            $record->load('facility');
            Notify::send($record->user, new BookingStatusNotification($record, 'payment_confirmed'));

            return;
        }

        if ($record instanceof DocumentRequest) {
            $record->load('transactionType');
            Notify::send($record->user, new DocumentRequestStatusNotification($record, 'payment_confirmed'));
        }
    }

    private function checkoutSessionIdFor($record): ?string
    {
        // The live controllers store the checkout session id in
        // payment_reference when creating the session.
        return str_starts_with((string) $record->payment_reference, 'cs_')
            ? $record->payment_reference
            : null;
    }
}
