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

        // Add X-Content-Type-Options header to prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Add X-Frame-Options to prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Add X-XSS-Protection header for older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Add Referrer-Policy for privacy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Add Content-Security-Policy for XSS protection (allow external resources for development)
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.tailwindcss.com; img-src 'self' data:; font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; connect-src 'self';");

        // Ensure Content-Type has charset for proper encoding
        if (!$response->headers->has('Content-Type') || strpos($response->headers->get('Content-Type'), 'charset=') === false) {
            $contentType = $response->headers->get('Content-Type', 'text/html');
            if (strpos($contentType, 'charset=') === false) {
                $response->headers->set('Content-Type', $contentType . '; charset=utf-8');
            }
        }

        return $response;
    }
}
