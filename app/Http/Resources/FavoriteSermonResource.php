<?php

namespace App\Http\Resources;

use App\Helpers\ThumbnailHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteSermonResource extends JsonResource
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
            'stream_url' => route('api.v1.sermons.stream', ['sermon' => $this->id]),
            'cover_url' => $this->cover_url ? asset($this->cover_url) : null,
            'cover_thumbnail_url' => ThumbnailHelper::url($this->cover_url),
            'duration' => $this->duration,
            'duration_formatted' => $this->duration_formatted,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'audio_format' => $this->audio_format,
            'audio_bitrate' => $this->audio_bitrate,
            'color' => $this->color,
            'views_count' => $this->views_count ?? 0,
            'is_published' => (bool) $this->is_published,
            'is_favorite' => true,
            'church' => $this->whenLoaded('church', function () {
                if (!$this->church) {
                    return null;
                }

                return [
                    'id' => $this->church->id,
                    'name' => $this->church->name,
                    'abbreviation' => $this->church->abbreviation,
                    'logo_url' => $this->church->logo_url ? asset($this->church->logo_url) : null,
                    'logo_thumbnail_url' => ThumbnailHelper::url($this->church->logo_url),
                ];
            }),
            'category' => $this->whenLoaded('category', function () {
                if (!$this->category) {
                    return null;
                }

                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
