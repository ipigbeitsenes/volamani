<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a route when its platform feature has been switched off by an admin
 * (Settings → Features). Usage: ->middleware('feature:wallet'). Returns a
 * friendly notice for browsers, 404 JSON for API/AJAX callers.
 */
class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (feature($feature)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(404, 'This feature is currently unavailable.');
        }

        return response()->view('errors.feature-unavailable', [
            'featureKey' => $feature,
        ], 404);
    }
}
