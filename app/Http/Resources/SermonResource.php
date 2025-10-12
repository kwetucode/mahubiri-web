<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Http\Resources\ChurchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'audio_url' => $this->audio_url ? url($this->audio_url) : null,
            'cover_url' => $this->cover_url ? url($this->cover_url) : null,
            'duration' => $this->duration,
            'size' => $this->size,
            'size_formatted' => $this->formatSize($this->size),
            'mime_type' => $this->mime_type,
            'audio_bitrate' => $this->audio_bitrate,
            'duration_formatted' => $this->duration_formatted,
            'audio_format' => $this->audio_format,
            'color' => $this->color,
            'church' => $this->whenLoaded('church', fn() => new ChurchResource($this->church)),
            'moment' => DateHelper::timeAgo($this->created_at),
            'created_at' => DateHelper::formatFrench($this->created_at, 'd/m/Y H:i:s'),
            'created_at_full' => DateHelper::formatFullFrench($this->created_at),
        ];
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
