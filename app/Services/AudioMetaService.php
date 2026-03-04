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
            if (!file_exists($audioPath)) {
                Log::error('Audio meta extraction failed: file does not exist', [
                    'file' => $audioPath,
                    'realpath' => realpath($audioPath),
                ]);
                return null;
            }

            $getID3 = new getID3();
            $info = $getID3->analyze($audioPath);

            // Log warnings from getID3 if any
            if (!empty($info['error'])) {
                Log::warning('getID3 reported errors', [
                    'errors' => $info['error'],
                    'file' => $audioPath,
                ]);
            }
            if (!empty($info['warning'])) {
                Log::warning('getID3 reported warnings', [
                    'warnings' => $info['warning'],
                    'file' => $audioPath,
                ]);
            }

            $duration = $info['playtime_seconds'] ?? null;
            $durationFormatted = $info['playtime_string'] ?? null;
            $mimeType = $info['mime_type'] ?? null;
            $size = $info['filesize'] ?? null;
            $bitrate = $info['audio']['bitrate'] ?? null;
            $audioFormat = $info['audio']['dataformat'] ?? null;

            // Fallback: get file size from filesystem if getID3 didn't return it
            if ($size === null && file_exists($audioPath)) {
                $size = filesize($audioPath);
            }

            Log::info('Audio meta extracted', [
                'file' => basename($audioPath),
                'duration' => $duration,
                'size' => $size,
                'mime_type' => $mimeType,
                'format' => $audioFormat,
            ]);

            return [
                'duration' => $duration,
                'duration_formatted' => $durationFormatted,
                'mime_type' => $mimeType,
                'size' => $size,
                'audio_bitrate' => $bitrate,
                'audio_format' => $audioFormat,
            ];
        } catch (\Throwable $e) {
            Log::error('Audio meta extraction failed', [
                'error' => $e->getMessage(),
                'file' => $audioPath,
                'file_exists' => file_exists($audioPath),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
