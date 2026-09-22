<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline browser security headers on every response.
 *
 * - X-Frame-Options / frame-ancestors: clickjacking protection.
 * - X-Content-Type-Options: stops MIME sniffing of uploads.
 * - Referrer-Policy: never leak full URLs (with codes/ids) to third parties.
 * - Permissions-Policy: disable unused powerful browser features.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'; object-src 'none'; base-uri 'self'");

        return $response;
    }
}
