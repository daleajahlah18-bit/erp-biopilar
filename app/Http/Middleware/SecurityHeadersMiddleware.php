<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Check if response is a standard Response object (to avoid errors on BinaryFileResponse, etc)
        if (method_exists($response, 'header')) {
            // Prevent Clickjacking
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            
            // Prevent MIME Sniffing
            $response->header('X-Content-Type-Options', 'nosniff');
            
            // Ensure Referrer is only sent on same origin
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            
            // Strict Transport Security (HSTS)
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            
            // Basic Content Security Policy
            $response->header('Content-Security-Policy', "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data: blob:; img-src 'self' data: https: blob:; font-src 'self' data: https:;");
            
            // Permissions Policy
            $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        }

        return $response;
    }
}
