<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use App\Services\AudioMetaService;

class FileUploadService
{
    private AudioUploadService $audioUploadService;
    private ImageUploadService $imageUploadService;
    private AudioMetaService $audioMetaService;

    public function __construct(
        AudioUploadService $audioUploadService,
        ImageUploadService $imageUploadService,
        AudioMetaService $audioMetaService
    ) {
        $this->audioUploadService = $audioUploadService;
        $this->imageUploadService = $imageUploadService;
        $this->audioMetaService = $audioMetaService;
    }

    /**
     * Upload audio and extract meta info
     * @param string|UploadedFile $audio
     * @return array ['audio_url' => string, ...meta]
     */
    public function handleAudioUploadWithMeta(string|UploadedFile $audio): array
    {
        $audioUrl = $this->audioUploadService->handleAudioUpload($audio);
        // Get absolute path for getID3 - use storage_path directly (works without symlink)
        $relativePath = str_replace('storage/', '', $audioUrl);
        $absolutePath = storage_path('app/public/' . $relativePath);

        Log::info('Audio upload - attempting meta extraction', [
            'audio_url' => $audioUrl,
            'relative_path' => $relativePath,
            'absolute_path' => $absolutePath,
            'file_exists' => file_exists($absolutePath),
        ]);

        $meta = $this->audioMetaService->extractMeta($absolutePath) ?? [];

        if (empty($meta) || $meta['duration'] === null) {
            Log::warning('Audio meta extraction returned no duration', [
                'audio_url' => $audioUrl,
                'meta' => $meta,
                'absolute_path' => $absolutePath,
            ]);
        }

        $meta['audio_url'] = $audioUrl;
        return $meta;
    }

    public function handleImageUpload(string|UploadedFile $image, string $storageType = 'covers'): string
    {
        return $this->imageUploadService->handleImageUpload($image, $storageType);
    }

    public function deleteFile(string $fileUrl, string $type = 'audio'): bool
    {
        if ($type === 'audio') {
            return $this->audioUploadService->deleteAudioFile($fileUrl);
        }

        return $this->imageUploadService->deleteImageFile($fileUrl);
    }

    public function deleteMultipleFiles(array $fileUrls, string $type = 'audio'): array
    {
        $results = ['success' => 0, 'failed' => 0];

        foreach ($fileUrls as $url) {
            if ($this->deleteFile($url, $type)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    public function getAudioFileInfo(string $audioUrl): ?array
    {
        return $this->audioUploadService->getAudioFileInfo($audioUrl);
    }

    public function getSupportedStorageTypes(): array
    {
        return $this->imageUploadService->getSupportedStorageTypes();
    }

    /**
     * Get the thumbnail URL for a given image URL.
     */
    public function getThumbnailUrl(?string $imageUrl): ?string
    {
        return $this->imageUploadService->getThumbnailUrl($imageUrl);
    }

    public function isBase64String($input): bool
    {
        return $this->audioUploadService->isBase64AudioString($input) ||
            $this->imageUploadService->isBase64ImageString($input);
    }

    public function isUploadedFile($input): bool
    {
        return $this->audioUploadService->isUploadedAudioFile($input) ||
            $this->imageUploadService->isUploadedImageFile($input);
    }

    public function getFileType(string|UploadedFile $input): string
    {
        if ($input instanceof UploadedFile) {
            $mimeType = $input->getMimeType();
            if (str_starts_with($mimeType, 'audio/')) {
                return 'audio';
            }
            if (str_starts_with($mimeType, 'image/')) {
                return 'image';
            }
            throw new InvalidArgumentException("Unsupported file type: {$mimeType}");
        }

        if (preg_match('/^data:(audio|image)\/\w+;base64,/', $input, $matches)) {
            return $matches[1];
        }

        throw new InvalidArgumentException('Unable to determine file type from input');
    }
}
