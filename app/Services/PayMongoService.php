<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayMongoService
{
    private readonly string $secretKey;

    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? config('services.paymongo.secret');
    }

    /**
     * Create a hosted Checkout Session scoped to a single payment method
     * (e.g. gcash, paymaya, or the bank-transfer aggregators dob/brankas)
     * and return its id plus the URL to redirect the payer to.
     */
    public function createCheckoutSession(
        array $lineItems,
        array $paymentMethodTypes,
        string $description,
        string $successUrl,
        string $cancelUrl,
        ?string $referenceNumber = null,
    ): array {
        $response = $this->client()->post('/checkout_sessions', [
            'data' => [
                'attributes' => array_filter([
                    'line_items'           => $lineItems,
                    'payment_method_types' => $paymentMethodTypes,
                    'success_url'          => $successUrl,
                    'cancel_url'           => $cancelUrl,
                    'description'          => $description,
                    'reference_number'     => $referenceNumber,
                    'show_line_items'      => true,
                    'show_description'     => true,
                    'send_email_receipt'   => false,
                ]),
            ],
        ])->throw();

        $data = $response->json('data');

        return [
            'id'           => $data['id'],
            'checkout_url' => $data['attributes']['checkout_url'],
        ];
    }

    public function retrieveCheckoutSession(string $id): array
    {
        return $this->client()->get("/checkout_sessions/{$id}")->throw()->json('data');
    }

    /**
     * Refund a previously paid payment (the `pay_...` id we stored as
     * payment_reference). Amount is in pesos; PayMongo wants centavos.
     *
     * @param  string  $reason  one of: duplicate, fraudulent, requested_by_customer, others
     * @return array{id:string, status:?string, amount:int}
     */
    public function refundPayment(string $paymentId, float|string $amountPesos, string $reason = 'requested_by_customer'): array
    {
        $response = $this->client()->post('/refunds', [
            'data' => [
                'attributes' => [
                    'amount'     => self::toCentavos($amountPesos),
                    'payment_id' => $paymentId,
                    'reason'     => $reason,
                ],
            ],
        ])->throw();

        $data = $response->json('data');

        return [
            'id'     => $data['id'],
            'status' => $data['attributes']['status'] ?? null,
            'amount' => $data['attributes']['amount'] ?? 0,
        ];
    }

    public function isPaid(array $sessionData): bool
    {
        foreach ($sessionData['attributes']['payments'] ?? [] as $payment) {
            if (($payment['attributes']['status'] ?? null) === 'paid') {
                return true;
            }
        }

        return ($sessionData['attributes']['payment_intent']['attributes']['status'] ?? null) === 'succeeded';
    }

    /**
     * The actual instrument the payer used (gcash, paymaya, dob, brankas, card, ...),
     * read from the paid payment record rather than assumed from what we requested.
     */
    public function paidChannel(array $sessionData): ?string
    {
        return $this->paidPayment($sessionData)['attributes']['source']['type'] ?? null;
    }

    public function paidReference(array $sessionData): ?string
    {
        return $this->paidPayment($sessionData)['id'] ?? null;
    }

    /**
     * Convert a peso amount into the centavo integer PayMongo's API expects.
     */
    public static function toCentavos(float|string $pesos): int
    {
        return (int) round(((float) $pesos) * 100);
    }

    /**
     * Flat convenience fee charged on top of the item amount for every
     * cashless (GCash/PayMaya/bank transfer) checkout.
     */
    public static function transactionFee(): float
    {
        return (float) config('services.paymongo.transaction_fee', 0);
    }

    /**
     * The "Transaction Fee" line item shown alongside the item(s) being paid for.
     */
    public static function transactionFeeLineItem(): array
    {
        return [
            'name'     => 'Transaction Fee',
            'amount'   => self::toCentavos(self::transactionFee()),
            'currency' => 'PHP',
            'quantity' => 1,
        ];
    }

    /**
     * Map our own payment_method value to the PayMongo payment_method_types
     * that should be offered on the hosted checkout page.
     */
    public static function methodTypesFor(string $ourMethod): array
    {
        // Only QRPh is activated on our live PayMongo account. A QRPh page is
        // payable from GCash, Maya and any InstaPay bank app, so it serves all
        // of our cashless options. Restore the per-channel mapping below once
        // GCash / Maya / online banking are approved in the dashboard.
        return match ($ourMethod) {
            'gcash', 'paymaya', 'bank_transfer' => ['qrph'],
            default                             => [],
        };
    }

    private function paidPayment(array $sessionData): ?array
    {
        foreach ($sessionData['attributes']['payments'] ?? [] as $payment) {
            if (($payment['attributes']['status'] ?? null) === 'paid') {
                return $payment;
            }
        }

        return null;
    }

    private function client()
    {
        if (! $this->secretKey) {
            throw new RuntimeException('PayMongo secret key is not configured (PAYMONGO_SECRET_KEY).');
        }

        return Http::withBasicAuth($this->secretKey, '')
            ->baseUrl('https://api.paymongo.com/v1')
            ->acceptJson();
    }
}
