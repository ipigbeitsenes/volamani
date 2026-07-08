<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks buyers who have been suspended for repeated buyer-protection abuse
 * (serial "fake buyer") from starting new purchases. Applied to checkout /
 * order-placement routes. Legitimate account actions are unaffected.
 */
class EnsureBuyerNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->purchasesSuspended()) {
            return redirect()
                ->route('home')
                ->with('error', 'Your account is currently restricted from making purchases due to repeated buyer-protection claims that were not upheld. Please contact support.');
        }

        return $next($request);
    }
}
