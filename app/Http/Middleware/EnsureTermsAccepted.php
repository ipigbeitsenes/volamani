<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires buyers and sellers to have accepted the current Terms & Conditions
 * before using the platform. When the terms version is bumped, everyone is
 * re-prompted. Internal staff (admin/support/finance) are not gated, and the
 * policy pages + acceptance flow + sign-out stay reachable so a user can read
 * and decide (or leave) without being trapped.
 */
class EnsureTermsAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasAnyRole(['admin', 'support', 'finance']) || $user->hasAcceptedCurrentTerms()) {
            return $next($request);
        }

        // Routes a not-yet-accepted user must still reach.
        if ($request->routeIs('terms.show', 'terms.accept', 'logout', 'verification.*', 'pages.*', 'buyer-protection')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Please accept the updated Terms & Conditions to continue.');
        }

        return redirect()->route('terms.show');
    }
}
