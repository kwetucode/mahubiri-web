<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Intercept non-JSON responses on API routes and convert them to JSON.
 *
 * When a WAF (Cloudflare, AWS WAF, ModSecurity, etc.) or a reverse-proxy
 * sits in front of the Laravel app it may return its own HTML error pages
 * (typically 403, 429, 503). The Flutter client expects JSON, so receiving
 * HTML causes a FormatException crash.
 *
 * This middleware runs *after* the response is generated:
 *   - If the response is already JSON → pass through untouched.
 *   - If the response contains HTML (WAF error page) → convert to a
 *     structured JSON error the client can handle gracefully.
 */
class HandleWafErrors
{
    /**
     * Status-code → user-friendly message map.
     */
    private const STATUS_MESSAGES = [
        401 => 'Authentification requise. Veuillez vous reconnecter.',
        403 => 'Accès refusé par le serveur. Réessayez dans quelques instants.',
        429 => 'Trop de requêtes. Veuillez patienter avant de réessayer.',
        502 => 'Le serveur est temporairement indisponible. Réessayez plus tard.',
        503 => 'Service en maintenance. Réessayez dans quelques instants.',
        504 => 'Le serveur a mis trop de temps à répondre. Réessayez.',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If the response is already JSON, nothing to do
        if ($this->isJsonResponse($response)) {
            return $response;
        }

        // If the response is a successful non-JSON response (e.g. file download,
        // audio stream), let it pass through.
        if ($response->isSuccessful() || $response->isRedirection()) {
            return $response;
        }

        // At this point we have a non-JSON error response (likely from WAF/proxy).
        // Convert it to JSON so the Flutter client can parse it.
        return $this->convertToJson($request, $response);
    }

    /**
     * Check whether the response has a JSON content type.
     */
    private function isJsonResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'application/json')
            || str_contains($contentType, 'application/problem+json');
    }

    /**
     * Convert a non-JSON error response into a structured JSON response.
     */
    private function convertToJson(Request $request, Response $response): JsonResponse
    {
        $status = $response->getStatusCode();
        $body   = $response->getContent();

        // Try to extract a meaningful message from the HTML body
        $extractedMessage = $this->extractMessageFromHtml($body);

        // Detect origin (WAF vs app)
        $origin = $this->detectOrigin($response, $body);

        // Log for debugging
        Log::warning('Non-JSON error response intercepted on API route', [
            'status'     => $status,
            'origin'     => $origin,
            'uri'        => $request->getRequestUri(),
            'method'     => $request->method(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'extracted'  => $extractedMessage,
            'body_size'  => strlen($body),
        ]);

        $message = self::STATUS_MESSAGES[$status]
            ?? $extractedMessage
            ?? 'Une erreur inattendue est survenue. Réessayez.';

        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'error'   => [
                'code'   => $this->errorCodeFromStatus($status),
                'status' => $status,
                'origin' => $origin,
                'retry'  => in_array($status, [429, 502, 503, 504]),
            ],
        ], $status);
    }

    /**
     * Try to pull a human-readable message from an HTML error page.
     */
    private function extractMessageFromHtml(?string $html): ?string
    {
        if (empty($html)) {
            return null;
        }

        // <title>Access Denied</title>
        if (preg_match('/<title>\s*(.+?)\s*<\/title>/i', $html, $m)) {
            return trim(strip_tags($m[1]));
        }

        // <h1>403 Forbidden</h1>
        if (preg_match('/<h[12][^>]*>\s*(.+?)\s*<\/h[12]>/i', $html, $m)) {
            return trim(strip_tags($m[1]));
        }

        return null;
    }

    /**
     * Guess where the error originated from based on response headers.
     */
    private function detectOrigin(Response $response, ?string $body): string
    {
        $server = strtolower($response->headers->get('Server', ''));
        $via    = strtolower($response->headers->get('Via', ''));

        if ($response->headers->has('cf-ray') || str_contains($server, 'cloudflare')) {
            return 'cloudflare';
        }

        if (str_contains($server, 'awselb') || str_contains($via, 'amazo')) {
            return 'aws';
        }

        if (str_contains($server, 'nginx')) {
            return 'nginx';
        }

        if (str_contains($server, 'apache') || str_contains($body ?? '', 'mod_security')) {
            return 'modsecurity';
        }

        return 'unknown_proxy';
    }

    /**
     * Map HTTP status codes to machine-readable error codes.
     */
    private function errorCodeFromStatus(int $status): string
    {
        return match ($status) {
            401     => 'UNAUTHORIZED',
            403     => 'ACCESS_DENIED',
            429     => 'RATE_LIMITED',
            502     => 'BAD_GATEWAY',
            503     => 'SERVICE_UNAVAILABLE',
            504     => 'GATEWAY_TIMEOUT',
            default => 'SERVER_ERROR',
        };
    }
}
