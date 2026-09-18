<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Pending residents can log in — access is limited elsewhere
        // (ensureActive checks in the request/rental/booking controllers).
        if (in_array($user->status, ['active', 'pending'], true)) {
            return $next($request);
        }

        $message = match ($user->status) {
            'rejected' => 'Your registration was not approved. Please contact the barangay office.',
            default    => 'Your account has been deactivated. Please contact the barangay office.',
        };

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
