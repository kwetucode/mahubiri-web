<?php

namespace App\Services;

use getID3;
use Illuminate\Support\Facades\Log;

class AudioMetaService
{
    /**
     * Extract audio metadata using getID3
     *
     * @param string $audioPath Absolute path to the audio file
     * @return array|null
     */
    public function extractMeta(string $audioPath): ?array
    {
        try {
            $getID3 = new getID3();
            $info = $getID3->analyze($audioPath);

            $duration = $info['playtime_seconds'] ?? null;
            $durationFormatted = $info['playtime_string'] ?? null;
            $mimeType = $info['mime_type'] ?? null;
            $size = $info['filesize'] ?? null;
            $bitrate = $info['audio']['bitrate'] ?? null;
            $audioFormat = $info['audio']['dataformat'] ?? null;

            return [
                'duration' => $duration,
                'duration_formatted' => $durationFormatted,
                'mime_type' => $mimeType,
                'size' => $size,
                'audio_bitrate' => $bitrate,
                'audio_format' => $audioFormat,
            ];
        } catch (\Throwable $e) {
            Log::error('Audio meta extraction failed', ['error' => $e->getMessage(), 'file' => $audioPath]);
            return null;
        }
    }
}
