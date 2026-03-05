<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Http\Controllers\Controller;
use App\Models\Sermon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sert les fichiers audio des sermons.
 *
 * Utilise BinaryFileResponse (Symfony) au lieu de response()->stream() :
 * - Gestion NATIVE des Range requests (206 Partial Content)
 * - Support automatique de X-Sendfile (Apache mod_xsendfile) / X-Accel-Redirect (Nginx)
 * - Transfert par le noyau OS (sendfile syscall) au lieu de fread() PHP en boucle
 * - 10-50× plus rapide que le streaming PHP manuel
 *
 * NOTE : L'app Flutter devrait préférer `audio_url` (fichier statique servi
 * directement par Apache) plutôt que ce endpoint. Ce controller sert de fallback.
 */
class SermonAudioStreamController extends Controller
{
    public function __invoke(Request $request, Sermon $sermon): Response
    {
        if (!$sermon->is_published || empty($sermon->audio_url)) {
            abort(404);
        }

        $relativeAudioPath = $this->extractRelativeAudioPath($sermon->audio_url);

        if (!$relativeAudioPath) {
            abort(404);
        }

        $absolutePath = storage_path('app/public/' . $relativeAudioPath);

        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            abort(404);
        }

        $mimeType = $sermon->mime_type ?: (mime_content_type($absolutePath) ?: 'application/octet-stream');

        // HEAD request: return headers only (pre-flight from audio players)
        if ($request->isMethod('HEAD')) {
            $fileSize = filesize($absolutePath);

            return response()->noContent(200, [
                'Content-Type' => $mimeType,
                'Accept-Ranges' => 'bytes',
                'Content-Length' => (string) $fileSize,
                'Cache-Control' => 'public, max-age=2592000, immutable',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                'Access-Control-Allow-Headers' => 'Range, Origin, Accept, Authorization',
                'X-Stream-Auth' => 'none',
            ]);
        }

        // Utiliser BinaryFileResponse : gestion native des Range requests,
        // sendfile syscall, et support X-Sendfile/X-Accel-Redirect automatique.
        $response = new BinaryFileResponse(
            file: $absolutePath,
            status: 200,
            headers: [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=2592000, immutable',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                'Access-Control-Allow-Headers' => 'Range, Origin, Accept, Authorization',
                'X-Stream-Auth' => 'none',
            ],
            public: true,
            autoEtag: true,
        );

        // Active la gestion native des Range requests (HTTP 206 Partial Content)
        $response->headers->set('Accept-Ranges', 'bytes');

        // Traiter le header Range si présent (seek dans le player audio)
        if ($request->headers->has('Range')) {
            $response->headers->set('Content-Range', '');
            $response->prepare($request);
        }

        return $response;
    }

    private function extractRelativeAudioPath(string $audioUrl): ?string
    {
        $path = $audioUrl;

        if (str_starts_with($audioUrl, 'http://') || str_starts_with($audioUrl, 'https://')) {
            $parsedPath = parse_url($audioUrl, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : '';
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (!str_starts_with($path, 'sermons/audio/')) {
            return null;
        }

        return $path;
    }
}
