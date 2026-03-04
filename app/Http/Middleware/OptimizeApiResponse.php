<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add cache & performance headers for API responses.
 * Helps Flutter cache responses and reduce bandwidth.
 */
class OptimizeApiResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply to successful JSON API responses
        if (!$response->isSuccessful() || !$this->isJsonResponse($response)) {
            return $response;
        }

        // ETag for conditional requests (Flutter can send If-None-Match)
        // Use crc32 instead of md5 — much faster on large payloads, sufficient for cache validation
        $content = $response->getContent();
        if ($content && strlen($content) < 512000) { // Skip ETag for responses > 500KB
            $etag = '"' . dechex(crc32($content)) . '"';
            $response->headers->set('ETag', $etag);

            // Return 304 Not Modified if ETag matches
            if ($request->header('If-None-Match') === $etag) {
                $response->setStatusCode(304);
                $response->setContent('');
                return $response;
            }
        }

        // Cache control for different route patterns
        $cacheSeconds = $this->getCacheDuration($request);

        if ($cacheSeconds > 0) {
            $response->headers->set('Cache-Control', "public, max-age={$cacheSeconds}, s-maxage={$cacheSeconds}");
        } else {
            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        }

        // Encourage compression
        $response->headers->set('Vary', 'Accept-Encoding, Authorization');

        return $response;
    }

    /**
     * Determine cache duration based on route.
     */
    private function getCacheDuration(Request $request): int
    {
        $path = $request->path();

        // Static-ish content: sermons list, churches, categories → 5 minutes
        if (preg_match('#^api/v1/(sermons|churches|categories)$#', $path)) {
            return 300;
        }

        // Individual sermon detail → 10 minutes
        if (preg_match('#^api/v1/sermons/\d+$#', $path)) {
            return 600;
        }

        // Search results → 2 minutes
        if (str_contains($path, 'search')) {
            return 120;
        }

        // User-specific endpoints → no cache
        if (str_contains($path, 'user') || str_contains($path, 'favorites') || str_contains($path, 'auth')) {
            return 0;
        }

        // Default: 1 minute
        return 60;
    }

    private function isJsonResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'json');
    }
}
