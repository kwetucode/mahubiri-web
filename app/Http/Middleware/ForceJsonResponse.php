<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force all API requests to accept JSON responses.
 *
 * This ensures that Laravel (and any exception handler) always returns
 * JSON instead of HTML — even for 403, 404, 500, etc.
 *
 * This is critical when a WAF/reverse-proxy sits in front of the app:
 * if the request reaches Laravel, Laravel will answer in JSON.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept header so Laravel's exception handler renders JSON
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
