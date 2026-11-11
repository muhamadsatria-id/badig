<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Attach browser security headers and a per-request CSP nonce.
     *
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(18));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($this->shouldSendHsts($request)) {
            $hsts = 'max-age='.max(0, (int) config('security.headers.hsts.max_age', 31536000));

            $includeSubdomains = (bool) config('security.headers.hsts.include_subdomains', false);
            if ($includeSubdomains) {
                $hsts .= '; includeSubDomains';
            }

            if ($includeSubdomains
                && (int) config('security.headers.hsts.max_age', 31536000) >= 31536000
                && config('security.headers.hsts.preload', false)) {
                $hsts .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        if (config('security.headers.csp.enabled', true)) {
            $header = config('security.headers.csp.report_only', false)
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $response->headers->set($header, $this->contentSecurityPolicy($nonce));
        }

        if ($request->user() || $request->routeIs('login', 'login.process', 'logout')) {
            $response->headers->set('Cache-Control', 'no-store, private, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    private function shouldSendHsts(Request $request): bool
    {
        return (bool) config('security.headers.hsts.enabled', true)
            && app()->environment('production')
            && $request->isSecure();
    }

    private function contentSecurityPolicy(string $nonce): string
    {
        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com",
            "script-src-attr 'none'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://unpkg.com",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: blob: https://tile.openstreetmap.org https://*.tile.openstreetmap.org",
            "connect-src 'self' https://nominatim.openstreetmap.org https://tile.openstreetmap.org https://*.tile.openstreetmap.org",
            "frame-src 'none'",
            "media-src 'self'",
            "manifest-src 'self'",
        ];

        if (app()->environment('production')) {
            $directives[] = 'upgrade-insecure-requests';
        }

        $reportUri = trim((string) config('security.headers.csp.report_uri'));
        if ($reportUri !== '' && Str::startsWith($reportUri, '/')) {
            $directives[] = 'report-uri '.$reportUri;
        }

        return implode('; ', $directives).';';
    }
}
