<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'anti-bruteforce' => \App\Http\Middleware\StrongerThrottle::class,
        ]);

        // Security headers (clickjacking, MIME sniffing, referrer leakage)
        // and XSS input purification on EVERY request.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->web(append: \App\Http\Middleware\SanitizeInput::class);

        // PayMongo cannot carry a CSRF token; its webhook is authenticated
        // by the Paymongo-Signature header instead.
        $middleware->validateCsrfTokens(except: [
            'webhooks/paymongo',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
