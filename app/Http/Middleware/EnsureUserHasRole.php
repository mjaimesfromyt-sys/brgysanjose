<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Ang Super Admin naay access sa tanang admin ug official modules
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Susiha kon ang role sa user anaa ba sa gitugotan nga roles
        if (! in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access. Only Super Admin can access this page.');
        }

        return $next($request);
    }
}