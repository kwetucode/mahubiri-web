<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Http\Controllers\Controller;
use App\Models\Sermon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SermonAudioStreamController extends Controller
{
    public function __invoke(Request $request, Sermon $sermon): StreamedResponse|\Illuminate\Http\Response
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

        $fileSize = filesize($absolutePath);
        $mimeType = $sermon->mime_type ?: (mime_content_type($absolutePath) ?: 'application/octet-stream');
        $range = $request->header('Range');

        $start = 0;
        $end = $fileSize - 1;
        $status = 200;

        if ($range && preg_match('/bytes=(\d*)-(\d*)/i', $range, $matches)) {
            $rangeStart = $matches[1] !== '' ? (int) $matches[1] : 0;
            $rangeEnd = $matches[2] !== '' ? (int) $matches[2] : $end;

            if ($rangeStart > $rangeEnd || $rangeStart >= $fileSize) {
                return response()->stream(function () {
                }, 416, [
                    'Content-Range' => 'bytes */' . $fileSize,
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $start = max(0, $rangeStart);
            $end = min($rangeEnd, $fileSize - 1);
            $status = 206;
        }

        $length = $end - $start + 1;

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Content-Length' => (string) $length,
            'Cache-Control' => 'public, max-age=2592000, immutable',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Range, Origin, Accept, Authorization',
            'X-Stream-Auth' => 'none',
        ];

        if ($status === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        // HEAD request: return headers only, no body (pre-flight from players)
        if ($request->isMethod('HEAD')) {
            return response()->noContent(200, $headers);
        }

        return response()->stream(function () use ($absolutePath, $start, $length): void {
            $handle = fopen($absolutePath, 'rb');

            if (!$handle) {
                return;
            }

            fseek($handle, $start);

            $remaining = $length;
            $chunkSize = 1024 * 128;

            while (!feof($handle) && $remaining > 0) {
                $readLength = min($chunkSize, $remaining);
                $buffer = fread($handle, $readLength);

                if ($buffer === false) {
                    break;
                }

                echo $buffer;
                flush();
                $remaining -= strlen($buffer);
            }

            fclose($handle);
        }, $status, $headers);
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
