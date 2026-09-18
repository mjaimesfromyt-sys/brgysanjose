<?php

namespace App\Support;

/**
 * Verifies the Paymongo-Signature header that accompanies every webhook
 * request (https://docs.paymongo.com/docs/developer-tools-webhook-setup-management).
 *
 * Header format:  t=<unix timestamp>,te=<test-mode signature>,li=<live-mode signature>
 * Signature:      HMAC-SHA256 over "<t>.<raw body>" using the webhook secret.
 */
class PayMongoSignature
{
    /**
     * @param  string  $header  Raw Paymongo-Signature header value
     * @param  string  $payload Raw request body (must be the exact bytes received)
     * @param  string  $secret  The webhook signing secret (whsec_...)
     * @param  int  $toleranceSeconds  Reject timestamps further from now than this
     */
    public static function verify(
        string $header,
        string $payload,
        string $secret,
        int $toleranceSeconds = 600,
    ): bool {
        if ($secret === '' || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $chunk) {
            [$key, $value] = array_pad(explode('=', trim($chunk), 2), 2, '');
            $parts[trim($key)] = trim($value);
        }

        $timestamp = $parts['t'] ?? '';
        $testSignature = $parts['te'] ?? '';
        $liveSignature = $parts['li'] ?? '';

        if ($timestamp === '' || (! is_numeric($timestamp)) || ($testSignature === '' && $liveSignature === '')) {
            return false;
        }

        // Replay protection: a very old timestamp is stale no matter what.
        if (abs(time() - (int) $timestamp) > $toleranceSeconds) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return ($testSignature !== '' && hash_equals($expected, $testSignature))
            || ($liveSignature !== '' && hash_equals($expected, $liveSignature));
    }

    /**
     * Build a valid header for a payload — used by tests and local tooling.
     */
    public static function for(string $payload, string $secret, ?int $timestamp = null, string $mode = 'test'): string
    {
        $t = $timestamp ?? time();
        $signature = hash_hmac('sha256', $t . '.' . $payload, $secret);

        return $mode === 'test'
            ? "t={$t},te={$signature},li="
            : "t={$t},te=,li={$signature}";
    }
}
