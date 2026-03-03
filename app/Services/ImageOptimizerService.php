<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageOptimizerService
{
    /**
     * Max dimensions for different image types
     */
    private const MAX_DIMENSIONS = [
        'covers'        => ['width' => 800, 'height' => 800],
        'sermon_covers' => ['width' => 800, 'height' => 800],
        'logos'         => ['width' => 400, 'height' => 400],
        'church_logos'  => ['width' => 400, 'height' => 400],
        'church_covers' => ['width' => 800, 'height' => 800],
        'avatars'       => ['width' => 400, 'height' => 400],
        'user_avatars'  => ['width' => 400, 'height' => 400],
        'profiles'      => ['width' => 400, 'height' => 400],
        'images'        => ['width' => 1200, 'height' => 1200],
        'files'         => ['width' => 1200, 'height' => 1200],
    ];

    /**
     * Thumbnail dimensions
     */
    private const THUMBNAIL_DIMENSIONS = [
        'covers'        => ['width' => 200, 'height' => 200],
        'sermon_covers' => ['width' => 200, 'height' => 200],
        'logos'         => ['width' => 100, 'height' => 100],
        'church_logos'  => ['width' => 100, 'height' => 100],
        'church_covers' => ['width' => 200, 'height' => 200],
        'avatars'       => ['width' => 100, 'height' => 100],
        'user_avatars'  => ['width' => 100, 'height' => 100],
        'profiles'      => ['width' => 100, 'height' => 100],
        'images'        => ['width' => 300, 'height' => 300],
        'files'         => ['width' => 300, 'height' => 300],
    ];

    /**
     * JPEG quality for optimized images (0-100)
     */
    private const QUALITY_FULL = 80;
    private const QUALITY_THUMBNAIL = 70;

    /**
     * Optimize an image stored on the public disk.
     * Resizes to max dimensions and compresses.
     *
     * @param string $diskPath Path relative to the public disk (e.g. "sermons/covers/2026/file.jpg")
     * @param string $storageType The storage type for dimension lookup
     * @return bool
     */
    public function optimize(string $diskPath, string $storageType = 'images'): bool
    {
        try {
            $absolutePath = storage_path('app/public/' . $diskPath);

            if (!file_exists($absolutePath)) {
                return false;
            }

            $imageInfo = @getimagesize($absolutePath);
            if ($imageInfo === false) {
                return false;
            }

            [$origWidth, $origHeight, $type] = $imageInfo;
            $maxDims = self::MAX_DIMENSIONS[$storageType] ?? self::MAX_DIMENSIONS['images'];

            // Skip if already within limits and is JPEG (already compressed)
            if ($origWidth <= $maxDims['width'] && $origHeight <= $maxDims['height'] && $type === IMAGETYPE_JPEG) {
                return true;
            }

            $source = $this->createImageFromFile($absolutePath, $type);
            if (!$source) {
                return false;
            }

            // Calculate new dimensions maintaining aspect ratio
            [$newWidth, $newHeight] = $this->calculateDimensions(
                $origWidth, $origHeight, $maxDims['width'], $maxDims['height']
            );

            // Resize if needed
            if ($newWidth !== $origWidth || $newHeight !== $origHeight) {
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                $this->preserveTransparency($resized, $type);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                imagedestroy($source);
                $source = $resized;
            }

            // Save as optimized JPEG (best compression for photos) or keep PNG for transparency
            $this->saveOptimizedImage($source, $absolutePath, $type, self::QUALITY_FULL);
            imagedestroy($source);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Image optimization failed', [
                'path' => $diskPath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate a thumbnail for a stored image.
     *
     * @param string $diskPath Path relative to the public disk
     * @param string $storageType The storage type for dimension lookup
     * @return string|null Thumbnail disk path or null on failure
     */
    public function generateThumbnail(string $diskPath, string $storageType = 'images'): ?string
    {
        try {
            $absolutePath = storage_path('app/public/' . $diskPath);

            if (!file_exists($absolutePath)) {
                return null;
            }

            $imageInfo = @getimagesize($absolutePath);
            if ($imageInfo === false) {
                return null;
            }

            [$origWidth, $origHeight, $type] = $imageInfo;
            $thumbDims = self::THUMBNAIL_DIMENSIONS[$storageType] ?? self::THUMBNAIL_DIMENSIONS['images'];

            $source = $this->createImageFromFile($absolutePath, $type);
            if (!$source) {
                return null;
            }

            [$thumbWidth, $thumbHeight] = $this->calculateDimensions(
                $origWidth, $origHeight, $thumbDims['width'], $thumbDims['height']
            );

            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
            $this->preserveTransparency($thumb, $type);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);
            imagedestroy($source);

            // Build thumbnail path: insert "thumbs/" before filename
            $thumbPath = $this->buildThumbnailPath($diskPath);
            $thumbAbsolutePath = storage_path('app/public/' . $thumbPath);

            // Ensure thumbnail directory exists
            $thumbDir = dirname($thumbAbsolutePath);
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }

            $this->saveOptimizedImage($thumb, $thumbAbsolutePath, $type, self::QUALITY_THUMBNAIL);
            imagedestroy($thumb);

            return $thumbPath;
        } catch (\Throwable $e) {
            Log::warning('Thumbnail generation failed', [
                'path' => $diskPath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete a thumbnail for a given image path.
     */
    public function deleteThumbnail(string $diskPath): bool
    {
        try {
            $thumbPath = $this->buildThumbnailPath($diskPath);
            if (Storage::disk('public')->exists($thumbPath)) {
                return Storage::disk('public')->delete($thumbPath);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Build thumbnail path from original path.
     * e.g. "sermons/covers/2026/abc_cover.jpg" → "sermons/covers/2026/thumbs/abc_cover.jpg"
     */
    public function buildThumbnailPath(string $diskPath): string
    {
        $dir = dirname($diskPath);
        $filename = basename($diskPath);
        return $dir . '/thumbs/' . $filename;
    }

    /**
     * Calculate proportional dimensions within max bounds.
     */
    private function calculateDimensions(int $origW, int $origH, int $maxW, int $maxH): array
    {
        if ($origW <= $maxW && $origH <= $maxH) {
            return [$origW, $origH];
        }

        $ratioW = $maxW / $origW;
        $ratioH = $maxH / $origH;
        $ratio = min($ratioW, $ratioH);

        return [
            (int) round($origW * $ratio),
            (int) round($origH * $ratio),
        ];
    }

    /**
     * Create GD image resource from file.
     */
    private function createImageFromFile(string $path, int $type): \GdImage|false
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default        => false,
        };
    }

    /**
     * Preserve transparency for PNG/GIF/WebP images.
     */
    private function preserveTransparency(\GdImage $image, int $type): void
    {
        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefill($image, 0, 0, $transparent);
        }
    }

    /**
     * Save optimized image to disk with appropriate format.
     */
    private function saveOptimizedImage(\GdImage $image, string $path, int $type, int $quality): void
    {
        match ($type) {
            IMAGETYPE_PNG  => imagepng($image, $path, min(9, (int) round((100 - $quality) / 10))),
            IMAGETYPE_GIF  => imagegif($image, $path),
            IMAGETYPE_WEBP => imagewebp($image, $path, $quality),
            default        => imagejpeg($image, $path, $quality),
        };
    }
}
