<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Brute-force protection for the login endpoint.
 *
 * Laravel's throttle:5,1 allows 5 attempts/minute per IP, but a distributed
 * attacker rotates IPs. This adds a SECOND layer keyed by identifier
 * (email) so one account can't be hammered from many IPs, with a rolling
 * decay and a clear lockout message.
 *
 * Keyed: sha1(lower(email)) — never stores the raw email in the cache key.
 */
class StrongerThrottle
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = strtolower(trim((string) $request->input('email', '')));

        if ($email !== '') {
            $key = 'login-attempts:' . sha1($email);

            if (RateLimiter::tooManyAttempts($key, 8)) {
                $seconds = RateLimiter::availableIn($key);
                return back()->withErrors([
                    'email' => "Too many failed attempts for this account. Try again in {$seconds} seconds.",
                ])->withInput($request->only('email'));
            }

            // We only count FAILED attempts — the login controller reports
            // success via a flash key we do NOT see here, so instead we hit
            // the limiter here and clear it on successful login (see
            // LoginController::store).
            RateLimiter::hit($key, 300); // 5-minute decay window
        }

        return $next($request);
    }
}
