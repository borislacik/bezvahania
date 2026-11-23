<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers for the public website (docs/brief.md, section 4.5).
 *
 * The Content Security Policy runs in report-only mode for now; Google Analytics is allowed
 * because it is loaded after cookie consent.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy-Report-Only', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https://www.googletagmanager.com https://*.google-analytics.com",
            "connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com",
            "font-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
