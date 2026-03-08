<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AudioUploadService
{
    /**
     * Handle audio upload from either base64 string or UploadedFile
     *
     * @param string|UploadedFile $audio Base64 encoded audio data or UploadedFile
     * @param string|null $ownerFolder Owner folder path (e.g. 'churches/cepac' or 'preachers/ministry-name')
     * @return string The URL of the uploaded audio file
     * @throws InvalidArgumentException
     */
    public function handleAudioUpload(string|UploadedFile $audio, ?string $ownerFolder = null): string
    {
        if ($audio instanceof UploadedFile) {
            return $this->handleUploadedAudioFile($audio, $ownerFolder);
        }

        return $this->handleBase64Audio($audio, $ownerFolder);
    }

    /**
     * Handle base64 audio upload and return the URL
     *
     * @param string $base64Audio Base64 encoded audio data
     * @param string|null $ownerFolder Owner folder path
     * @return string The URL of the uploaded audio file
     * @throws InvalidArgumentException
     */
    private function handleBase64Audio(string $base64Audio, ?string $ownerFolder = null): string
    {
        // Extract the audio data from base64
        if (preg_match('/^data:audio\/(\w+);base64,/', $base64Audio, $matches)) {
            $extension = $this->normalizeAudioExtension($matches[1]);
            $audioData = substr($base64Audio, strpos($base64Audio, ',') + 1);
            $audioData = base64_decode($audioData);
            if ($audioData === false) {
                throw new InvalidArgumentException('Invalid base64 audio data');
            }

            // Validate audio file size (max 100MB)
            if (strlen($audioData) > 100 * 1024 * 1024) {
                throw new InvalidArgumentException('Audio file too large. Maximum size is 100MB');
            }

            // Generate unique filename with owner-based folder structure
            $filename = $this->generateAudioPath($ownerFolder, $extension);

            // Store the audio file
            Storage::disk('public')->put($filename, $audioData);

            // Return the relative path (not full URL)
            return 'storage/' . $filename;
        }

        throw new InvalidArgumentException('Invalid base64 audio format. Expected format: data:audio/{type};base64,{data}');
    }

    /**
     * Handle uploaded audio file and return the URL
     *
     * @param UploadedFile $audio Uploaded audio file
     * @param string|null $ownerFolder Owner folder path
     * @return string The URL of the uploaded audio file
     * @throws InvalidArgumentException
     */
    private function handleUploadedAudioFile(UploadedFile $audio, ?string $ownerFolder = null): string
    {
        // Validate the uploaded file
        $this->validateUploadedAudioFile($audio);

        // Get file extension
        $extension = $audio->getClientOriginalExtension();
        $extension = $this->normalizeAudioExtension($extension);

        // Generate unique filename with owner-based folder structure
        $filename = $this->generateAudioPath($ownerFolder, $extension);

        // Store the audio file
        $audio->storeAs('', $filename, 'public');

        // Return the relative path (not full URL)
        return 'storage/' . $filename;
    }

    /**
     * Generate the audio file storage path.
     *
     * @param string|null $ownerFolder Owner folder (e.g. 'churches/cepac')
     * @param string $extension File extension
     * @return string
     */
    private function generateAudioPath(?string $ownerFolder, string $extension): string
    {
        if ($ownerFolder) {
            // New structure: {ownerFolder}/sermons/audio/{random}_sermon.{ext}
            return $ownerFolder . '/sermons/audio/' . Str::random(32) . '_sermon.' . $extension;
        }

        // Legacy fallback: sermons/audio/{YEAR}/{random}_sermon.{ext}
        return 'sermons/audio/' . date('Y') . '/' . Str::random(32) . '_sermon.' . $extension;
    }

    /**
     * Delete audio file from storage
     *
     * @param string $fileUrl The path of the file to delete (e.g., storage/sermons/audio/2025/file.mp3)
     * @return bool
     */
    public function deleteAudioFile(string $fileUrl): bool
    {
        try {
            // Remove 'storage/' prefix if present to get the actual disk path
            $relativePath = str_replace('storage/', '', $fileUrl);

            // Also handle full URLs (legacy support)
            if (str_contains($fileUrl, '://')) {
                $relativePath = str_replace(asset('storage/'), '', $fileUrl);
                $relativePath = str_replace('storage/', '', $relativePath);
            }

            // Delete if exists
            if (Storage::disk('public')->exists($relativePath)) {
                return Storage::disk('public')->delete($relativePath);
            }

            return true; // File doesn't exist, consider as successfully deleted
        } catch (\Exception $e) {
            // Log the error but don't throw exception to avoid breaking the flow
            Log::warning("Failed to delete audio file: {$fileUrl}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get audio file information
     *
     * @param string $audioUrl URL of the audio file
     * @return array|null File information or null if file doesn't exist
     */
    public function getAudioFileInfo(string $audioUrl): ?array
    {
        try {
            $relativePath = str_replace(asset('storage/'), '', $audioUrl);

            if (!Storage::disk('public')->exists($relativePath)) {
                return null;
            }

            $size = Storage::disk('public')->size($relativePath);
            $lastModified = Storage::disk('public')->lastModified($relativePath);

            return [
                'path' => $relativePath,
                'size' => $size,
                'size_formatted' => $this->formatFileSize($size),
                'last_modified' => date('Y-m-d H:i:s', $lastModified),
                'exists' => true
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate uploaded audio file
     *
     * @param UploadedFile $audio
     * @throws InvalidArgumentException
     */
    private function validateUploadedAudioFile(UploadedFile $audio): void
    {
        // Check if file was uploaded successfully
        if (!$audio->isValid()) {
            throw new InvalidArgumentException('Audio file upload failed: ' . $audio->getErrorMessage());
        }

        // Validate file size (max 200MB)
        if ($audio->getSize() > 200 * 1024 * 1024) {
            throw new InvalidArgumentException('Audio file too large. Maximum size is 200MB');
        }

        // Validate MIME type
        $allowedMimeTypes = [
            'audio/mpeg',
            'audio/mp3',
            'audio/wav',
            'audio/x-wav',
            'audio/mp4',
            'audio/m4a',
            'audio/x-m4a',
            'audio/aac',
            'audio/ogg',
            'audio/vorbis',
            'audio/flac',
            'audio/x-flac',
            'application/ogg',
            'application/octet-stream',
            'video/mp4', // some m4a files report as video/mp4
        ];

        $detectedMime = $audio->getMimeType();
        $extension = strtolower($audio->getClientOriginalExtension());
        $allowedExtensions = ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'flac'];

        Log::debug('Audio upload MIME check', [
            'detected_mime' => $detectedMime,
            'extension' => $extension,
            'original_name' => $audio->getClientOriginalName(),
        ]);

        // Accept if MIME is in whitelist, OR if MIME is generic but extension is valid
        if (!in_array($detectedMime, $allowedMimeTypes) && !in_array($extension, $allowedExtensions)) {
            throw new InvalidArgumentException('Invalid audio file type. Allowed types: MP3, WAV, M4A, AAC, OGG, FLAC');
        }
    }

    /**
     * Normalize audio file extension
     *
     * @param string $extension
     * @return string
     **/
    private function normalizeAudioExtension(string $extension): string
    {
        $allowedExtensions = ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'flac'];
        $extension = strtolower($extension);

        // Handle special cases
        $extensionMap = [
            'mpeg' => 'mp3',
            'mp4' => 'm4a',
            'x-m4a' => 'm4a',
            'x-flac' => 'flac',
        ];

        if (isset($extensionMap[$extension])) {
            $extension = $extensionMap[$extension];
        }

        if (!in_array($extension, $allowedExtensions)) {
            throw new InvalidArgumentException("Unsupported audio format: {$extension}. Allowed formats: " . implode(', ', $allowedExtensions));
        }

        return $extension;
    }

    /**
     * Format file size in human readable format
     *
     * @param int $size Size in bytes
     * @return string
     **/
    private function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Check if the input is a base64 audio string
     *
     * @param mixed $input
     * @return bool
     **/
    public function isBase64AudioString($input): bool
    {
        return is_string($input) && preg_match('/^data:audio\/\w+;base64,/', $input);
    }

    /**
     * Check if the input is an uploaded audio file
     *
     * @param mixed $input
     * @return bool
     **/
    public function isUploadedAudioFile($input): bool
    {
        if (!($input instanceof UploadedFile)) {
            return false;
        }

        $mimeType = $input->getMimeType();
        return str_starts_with($mimeType, 'audio/');
    }
}
