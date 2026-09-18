<?php

namespace App\Http\Controllers;

use App\Services\PaymentConfirmer;
use App\Support\PayMongoSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    /**
     * Events on a Checkout Session that imply money was received.
     */
    private const PAID_EVENTS = [
        'checkout_session.payment.paid',
        'payment.paid',
    ];

    public function __invoke(Request $request, PaymentConfirmer $confirmer)
    {
        $payload = $request->getContent();
        $secret = (string) config('services.paymongo.webhook_secret');

        if ($secret === '') {
            Log::error('PayMongo webhook secret not configured (PAYMONGO_WEBHOOK_SECRET).');

            return response('Webhook not configured', 503);
        }

        if (! PayMongoSignature::verify(
            (string) $request->header('Paymongo-Signature', ''),
            $payload,
            $secret
        )) {
            Log::warning('PayMongo webhook rejected: bad signature', [
                'ip' => $request->ip(),
            ]);

            return response('Invalid signature', 401);
        }

        $event = json_decode($payload, true);
        $type = $event['data']['attributes']['type'] ?? null;
        $resource = $event['data']['attributes']['data'] ?? null;

        Log::info('PayMongo webhook received', ['type' => $type]);

        if ($type === null || $resource === null) {
            return response('OK');
        }

        if (in_array($type, self::PAID_EVENTS, true)) {
            $this->handlePaidEvent($resource, $confirmer);
        }

        return response('OK');
    }

    private function handlePaidEvent(array $resource, PaymentConfirmer $confirmer): void
    {
        $attributes = $resource['attributes'] ?? [];

        if (($attributes['status'] ?? null) !== 'paid') {
            return; // e.g. payment.paid with status other than paid — ignore.
        }

        $paymentId = $resource['id'] ?? null;
        $channel = $attributes['source']['type'] ?? ($attributes['data']['attributes']['source']['type'] ?? null);

        $sessionId = $attributes['checkout_session']['id']
            ?? $attributes['checkout_session_id']
            ?? null;

        if ($sessionId === null) {
            Log::warning('PayMongo webhook: paid event without a checkout session id', [
                'payment_id' => $paymentId,
            ]);

            return;
        }

        $confirmed = $confirmer->confirmByCheckoutSession(
            $sessionId,
            $channel ?? 'qrph',
            $paymentId ?? '',
        );

        if (! $confirmed) {
            Log::info('PayMongo webhook: payment already confirmed or record missing', [
                'checkout_session_id' => $sessionId,
                'payment_id' => $paymentId,
            ]);
        }
    }
}
