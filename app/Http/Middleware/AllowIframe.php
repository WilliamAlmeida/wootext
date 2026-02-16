<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowIframe
{
    /**
     * Handle an incoming request and add CSP frame-ancestors header allowing the hosting origin.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Origins allowed to embed the app via iframe
        $allowed = config('services.chatwoot.allowed_origins', []);

        $frameAncestors = implode(' ', array_merge(["'self'"], $allowed));

        // Use Content-Security-Policy frame-ancestors (modern, preferred)
        $response->headers->set('Content-Security-Policy', "frame-ancestors {$frameAncestors};");

        return $response;
    }
}
