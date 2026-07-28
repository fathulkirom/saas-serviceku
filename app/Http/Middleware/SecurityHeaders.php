<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response instanceof Response) {
            return $response;
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content Security Policy - lebih longgar di local karena Vite
        if (app()->environment('local')) {
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: http:",
                "style-src 'self' 'unsafe-inline' https: http:",
                "img-src 'self' data: blob: https: http:",
                "font-src 'self' https: http: data:",
                "connect-src 'self' https: http: ws: wss:",
                "frame-ancestors 'none'",
                "form-action 'self'",
            ];
        } else {
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.bunny.net https://static.cloudflareinsights.com https://cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com",
                "img-src 'self' data: blob: https:",
                "font-src 'self' https://fonts.bunny.net https://fonts.gstatic.com data:",
                "connect-src 'self' https: http: ws: wss:",
                "frame-ancestors 'none'",
                "form-action 'self'",
            ];
        }
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // HSTS (Hanya untuk HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Hapus header server default
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
