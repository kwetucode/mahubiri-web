<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SermonSearchResource extends JsonResource
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
            'audio_url' => $this->audio_url,
            'stream_url' => route('sermons.stream', ['sermon' => $this->id]),
            'cover_url' => $this->cover_url,
            'duration' => $this->duration,
            'duration_formatted' => $this->duration_formatted,
            'audio_format' => $this->audio_format,
            'audio_bitrate' => $this->audio_bitrate,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'color' => $this->color,
            'popularity_score' => $this->popularity_score,
            'views_count' => $this->views_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Related data
            'church' => [
                'id' => $this->church->id,
                'name' => $this->church->name,
                'abbreviation' => $this->church->abbreviation,
                'logo_url' => $this->church->logo_url,
                'city' => $this->church->city,
                'country_name' => $this->church->country_name,
            ],

            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],

            // Search relevance data
            'match_type' => $this->when(isset($this->match_type), $this->match_type),
            'relevance_score' => $this->when(isset($this->relevance_score), $this->relevance_score),
        ];
    }
}
