<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ThumbnailHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChurchResource extends JsonResource
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
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'visionary_name' => $this->visionary_name,
            'logo_url' => $this->logo_url ? asset($this->logo_url) : null,
            'logo_thumbnail_url' => ThumbnailHelper::url($this->logo_url),
            'description' => $this->description,
            'country_name' => $this->country_name,
            'country_code' => $this->country_code,
            'city' => $this->city,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'creator' => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email,
            ],
            'sermon_count' => $this->sermons_count ?? 0,
            'listened_sermon_count' => $this->listened_sermons_count ?? 0,
            'total_views' => $this->total_views ?? 0,
            'storage_quota' => $this->resource->getStorageQuotaSummary(),
            'moment' => DateHelper::timeAgo($this->created_at),
            'created_at' => DateHelper::formatFrench($this->created_at, 'd/m/Y H:i:s'),
            'created_at_full' => DateHelper::formatFullFrench($this->created_at),
        ];
    }
}
