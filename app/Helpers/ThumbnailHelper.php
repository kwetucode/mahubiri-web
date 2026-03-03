<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ThumbnailHelper
{
    /**
     * Get the full asset URL for a thumbnail of the given storage path.
     * Falls back to the original image URL if thumbnail doesn't exist.
     *
     * @param string|null $storageUrl e.g. "storage/sermons/covers/2026/abc_cover.jpg"
     * @return string|null Full asset URL or null
     */
    public static function url(?string $storageUrl): ?string
    {
        if (!$storageUrl) {
            return null;
        }

        $diskPath = str_replace('storage/', '', $storageUrl);
        $dir = dirname($diskPath);
        $filename = basename($diskPath);
        $thumbPath = $dir . '/thumbs/' . $filename;

        if (Storage::disk('public')->exists($thumbPath)) {
            return asset('storage/' . $thumbPath);
        }

        // Fallback to original
        return asset($storageUrl);
    }
}
