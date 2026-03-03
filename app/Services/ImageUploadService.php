<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageUploadService
{
    private ImageOptimizerService $optimizer;

    public function __construct(ImageOptimizerService $optimizer)
    {
        $this->optimizer = $optimizer;
    }

    /**
     * Handle image upload from either base64 string or UploadedFile
     *
     * @param string|UploadedFile $image Base64 encoded image data or UploadedFile
     * @param string $storageType Storage type (covers, logos, avatars, etc.)
     * @return string The URL of the uploaded image file
     * @throws InvalidArgumentException
     */
    public function handleImageUpload(string|UploadedFile $image, string $storageType = 'covers'): string
    {
        if ($image instanceof UploadedFile) {
            return $this->handleUploadedImageFile($image, $storageType);
        }

        return $this->handleBase64Image($image, $storageType);
    }

    /**
     * Handle base64 image upload and return the URL
     *
     * @param string $base64Image Base64 encoded image data
     * @param string $storageType Storage type (covers, logos, avatars, etc.)
     * @return string The URL of the uploaded image file
     * @throws InvalidArgumentException
     */
    private function handleBase64Image(string $base64Image, string $storageType = 'covers'): string
    {
        $imageData = null;
        $extension = null;

        // Case 1: Extract the image data and extension from base64 with data URL prefix
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $extension = $this->normalizeImageExtension($matches[1]);
            $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
            $imageData = base64_decode($imageData);
        }
        // Case 2: Handle raw base64 string (without data URL prefix)
        elseif (preg_match('/^[A-Za-z0-9+\/]*={0,2}$/', $base64Image)) {
            $imageData = base64_decode($base64Image, true);

            if ($imageData !== false) {
                // Try to detect image type from the binary data
                $imageInfo = @getimagesizefromstring($imageData);
                if ($imageInfo !== false) {
                    // Map IMAGETYPE constants to extensions
                    $extension = match ($imageInfo[2]) {
                        IMAGETYPE_JPEG => 'jpg',
                        IMAGETYPE_PNG => 'png',
                        IMAGETYPE_GIF => 'gif',
                        IMAGETYPE_WEBP => 'webp',
                        default => 'jpg' // fallback to jpg
                    };
                } else {
                    $imageData = false; // Not a valid image
                }
            }
        }

        if ($imageData === false || $imageData === null) {
            throw new InvalidArgumentException('Invalid base64 image data or format');
        }

        // Validate image file size (max 5MB)
        if (strlen($imageData) > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Image file too large. Maximum size is 5MB');
        }

        // Generate storage path and filename
        $storagePath = $this->generateStoragePath($storageType, 'image');
        $filename = $this->generateFilename($storageType, $extension);
        $fullPath = $storagePath . $filename;

        // Store the image
        Storage::disk('public')->put($fullPath, $imageData);

        // Optimize and generate thumbnail
        $this->optimizer->optimize($fullPath, $storageType);
        $this->optimizer->generateThumbnail($fullPath, $storageType);

        // Return the relative path (not full URL)
        return 'storage/' . $fullPath;
    }

    /**
     * Handle uploaded image file and return the URL
     *
     * @param UploadedFile $image Uploaded image file
     * @param string $storageType Storage type (covers, logos, avatars, etc.)
     * @return string The URL of the uploaded image file
     * @throws InvalidArgumentException
     */
    private function handleUploadedImageFile(UploadedFile $image, string $storageType = 'covers'): string
    {
        // Validate the uploaded file
        $this->validateUploadedImageFile($image);

        // Get file extension
        $extension = $image->getClientOriginalExtension();
        $extension = $this->normalizeImageExtension($extension);

        // Generate storage path and filename
        $storagePath = $this->generateStoragePath($storageType, 'image');
        $filename = $this->generateFilename($storageType, $extension);
        $fullPath = $storagePath . $filename;

        // Store the image
        $image->storeAs('', $fullPath, 'public');

        // Optimize and generate thumbnail
        $this->optimizer->optimize($fullPath, $storageType);
        $this->optimizer->generateThumbnail($fullPath, $storageType);

        // Return the relative path (not full URL)
        return 'storage/' . $fullPath;
    }

    /**
     * Delete image file from storage
     *
     * @param string $fileUrl The path of the file to delete (e.g., storage/users/avatars/2025/file.jpg)
     * @return bool
     */
    public function deleteImageFile(string $fileUrl): bool
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
                $deleted = Storage::disk('public')->delete($relativePath);
                // Also delete thumbnail
                $this->optimizer->deleteThumbnail($relativePath);
                return $deleted;
            }

            return true; // File doesn't exist, consider as successfully deleted
        } catch (\Exception $e) {
            // Log the error but don't throw exception to avoid breaking the flow
            Log::warning("Failed to delete image file: {$fileUrl}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete multiple image files from storage
     *
     * @param array $fileUrls Array of file URLs to delete
     * @return array Array of results ['success' => count, 'failed' => count]
     */
    public function deleteMultipleImageFiles(array $fileUrls): array
    {
        $results = ['success' => 0, 'failed' => 0];

        foreach ($fileUrls as $url) {
            if ($this->deleteImageFile($url)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Validate uploaded image file
     *
     * @param UploadedFile $image
     * @throws InvalidArgumentException
     **/
    private function validateUploadedImageFile(UploadedFile $image): void
    {
        // Check if file was uploaded successfully
        if (!$image->isValid()) {
            throw new InvalidArgumentException('Image file upload failed: ' . $image->getErrorMessage());
        }

        // Validate file size (max 5MB)
        if ($image->getSize() > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Image file too large. Maximum size is 5MB');
        }

        // Validate MIME type
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
            'image/gif'
        ];

        if (!in_array($image->getMimeType(), $allowedMimeTypes)) {
            throw new InvalidArgumentException('Invalid image file type. Allowed types: JPEG, PNG, WebP, GIF');
        }
    }

    /**
     * Normalize image file extension
     *
     * @param string $extension
     * @return string
     **/
    private function normalizeImageExtension(string $extension): string
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower($extension);

        if (!in_array($extension, $allowedExtensions)) {
            throw new InvalidArgumentException("Unsupported image format: {$extension}. Allowed formats: " . implode(', ', $allowedExtensions));
        }

        return $extension;
    }

    /**
     * Get list of supported storage types
     *
     * @return array
     */
    public function getSupportedStorageTypes(): array
    {
        return [
            'covers' => 'Sermon covers (sermons/covers/)',
            'sermon_covers' => 'Sermon covers (sermons/covers/)',
            'logos' => 'Church logos (churches/logos/)',
            'church_logos' => 'Church logos (churches/logos/)',
            'church_covers' => 'Church covers (churches/covers/)',
            'avatars' => 'User avatars (users/avatars/)',
            'user_avatars' => 'User avatars (users/avatars/)',
            'profiles' => 'User profiles (users/profiles/)',
            'images' => 'Generic images (uploads/images/)',
            'files' => 'Generic files (uploads/files/)',
        ];
    }

    /**
     * Check if the input is a base64 image string
     *
     * @param mixed $input
     * @return bool
     **/
    public function isBase64ImageString($input): bool
    {
        return is_string($input) && preg_match('/^data:image\/\w+;base64,/', $input);
    }

    /**
     * Check if the input is an uploaded image file
     *
     * @param mixed $input
     * @return bool
     **/
    public function isUploadedImageFile($input): bool
    {
        if (!($input instanceof UploadedFile)) {
            return false;
        }

        $mimeType = $input->getMimeType();
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Generate storage path based on storage type
     *
     * @param string $storageType Storage type (covers, logos, avatars, etc.)
     * @param string $fileType File type (image or audio)
     * @return string
     * @throws InvalidArgumentException
     */
    private function generateStoragePath(string $storageType, string $fileType = 'image'): string
    {
        $pathMappings = [
            // Sermon related
            'covers' => 'sermons/covers',
            'sermon_covers' => 'sermons/covers',

            // Church related
            'logos' => 'churches/logos',
            'church_logos' => 'churches/logos',
            'church_covers' => 'churches/covers',

            // User related
            'avatars' => 'users/avatars',
            'user_avatars' => 'users/avatars',
            'profiles' => 'users/profiles',

            // Generic
            'images' => 'uploads/images',
            'files' => 'uploads/files',
        ];

        if (!isset($pathMappings[$storageType])) {
            throw new InvalidArgumentException("Unsupported storage type: {$storageType}. Supported types: " . implode(', ', array_keys($pathMappings)));
        }

        // Use only year for folder structure: avatars/2025/
        return $pathMappings[$storageType] . '/' . date('Y') . '/';
    }

    /**
     * Generate filename based on storage type and extension
     *
     * @param string $storageType Storage type
     * @param string $extension File extension
     * @return string
     */
    private function generateFilename(string $storageType, string $extension): string
    {
        $prefixes = [
            'covers' => 'cover',
            'sermon_covers' => 'cover',
            'logos' => 'logo',
            'church_logos' => 'logo',
            'church_covers' => 'church_cover',
            'avatars' => 'avatar',
            'user_avatars' => 'avatar',
            'profiles' => 'profile',
            'images' => 'image',
            'files' => 'file',
        ];

        $prefix = $prefixes[$storageType] ?? 'file';
        return Str::random(32) . '_' . $prefix . '.' . $extension;
    }

    /**
     * Get the thumbnail URL for a given image URL.
     *
     * @param string|null $imageUrl The original image url (e.g. "storage/sermons/covers/2026/file.jpg")
     * @return string|null The thumbnail url or null
     */
    public function getThumbnailUrl(?string $imageUrl): ?string
    {
        if (!$imageUrl) {
            return null;
        }

        // Extract disk path from storage url
        $diskPath = str_replace('storage/', '', $imageUrl);
        $thumbPath = $this->optimizer->buildThumbnailPath($diskPath);

        if (Storage::disk('public')->exists($thumbPath)) {
            return 'storage/' . $thumbPath;
        }

        // Fallback: return original if thumbnail doesn't exist
        return $imageUrl;
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
}
