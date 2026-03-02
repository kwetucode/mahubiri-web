<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
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
            'stream_url' => route('sermons.stream', ['sermon' => $this->id]),
            'cover_url' => $this->cover_url ? asset($this->cover_url) : null,
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
     * Check if sermon is favorited by authenticated user
     *
     * @return bool
     */
    private function checkIfFavorite(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->isFavoritedBy(Auth::id());
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
