<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ThumbnailHelper;
use App\Http\Resources\ChurchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SermonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'preacher_name' => $this->preacher_name,
            'description' => $this->description,
            'audio_url' => $this->audio_url ? asset($this->audio_url) : null,
            'cover_url' => $this->cover_url ? asset($this->cover_url) : null,
            'cover_thumbnail_url' => ThumbnailHelper::url($this->cover_url),
            'duration' => $this->duration,
            'size' => $this->size,
            'size_formatted' => $this->formatSize($this->size),
            'mime_type' => $this->mime_type,
            'audio_bitrate' => $this->audio_bitrate,
            'duration_formatted' => $this->duration_formatted,
            'audio_format' => $this->audio_format,
            'color' => $this->color,
            'popularity_score' => $this->popularity_score,
            'views_count' => $this->views_count ?? 0,
            'is_published' => $this->is_published,
            'church' => $this->whenLoaded('church', fn() => new ChurchResource($this->church)),
            'category' => $this->whenLoaded('category', fn() => new CategorySermonResource($this->category)),
            'is_favorite' => $this->checkIfFavorite(),
            'moment' => DateHelper::timeAgo($this->created_at),
            'created_at' => DateHelper::formatFrench($this->created_at, 'd/m/Y H:i:s'),
            'created_at_full' => DateHelper::formatFullFrench($this->created_at),
        ];
    }

    /**
     * Check if sermon is favorited by authenticated user.
     * Uses the scoped "currentUserFavorite" relation (single row) when
     * eager-loaded, avoiding N+1 queries entirely.
     */
    private function checkIfFavorite(): bool
    {
        // 1. Explicit attribute set by a sub-query
        if (isset($this->is_favorite)) {
            return (bool) $this->is_favorite;
        }

        // 2. Scoped eager-load: currentUserFavorite (single row, most efficient)
        if ($this->relationLoaded('currentUserFavorite')) {
            return $this->currentUserFavorite !== null;
        }

        // 3. Legacy: full favoritedBy collection already loaded
        if ($this->relationLoaded('favoritedBy')) {
            $userId = Auth::id();
            return $userId
                ? $this->favoritedBy->contains('user_id', $userId)
                : false;
        }

        // 4. No relation loaded — return false to avoid N+1 query
        //    (callers should eager-load currentUserFavorite)
        return false;
    }

    private function formatSize($sizeInBytes)
    {
        if ($sizeInBytes >= 1073741824) {
            return number_format($sizeInBytes / 1073741824, 2) . ' GB';
        } elseif ($sizeInBytes >= 1048576) {
            return number_format($sizeInBytes / 1048576, 2) . ' MB';
        } elseif ($sizeInBytes >= 1024) {
            return number_format($sizeInBytes / 1024, 2) . ' KB';
        } else {
            return $sizeInBytes . ' bytes';
        }
    }
}
