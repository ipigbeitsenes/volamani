<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Attach standard security headers to every web response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Content-Security-Policy. The app relies on inline <style>/<script> and
        // style="" attributes throughout, so 'unsafe-inline' is required for
        // script/style; the policy still pins external script origins to the
        // CDN, blocks plugins (object-src none), and prevents <base> hijacking.
        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
                "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com data:",
                "img-src 'self' data: https:",
                "connect-src 'self' https://api.paystack.co",
                "frame-ancestors 'self'",
                // Paystack: the pay button posts to us, then we 302-redirect the
                // buyer to Paystack's hosted checkout. Without these origins the
                // browser silently blocks that redirect and "nothing happens".
                "form-action 'self' https://checkout.paystack.com https://*.paystack.com",
                "base-uri 'self'",
                "object-src 'none'",
            ]));
        }

        // Only meaningful over TLS; avoid pinning HTTPS on local HTTP dev.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
