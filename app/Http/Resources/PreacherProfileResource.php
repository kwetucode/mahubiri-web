<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreacherProfileResource extends JsonResource
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
            'user_id' => $this->user_id,
            'ministry_name' => $this->ministry_name,
            'ministry_type' => $this->ministry_type,
            'avatar_url' => $this->avatar_url ? asset($this->avatar_url) : null,
            'country_name' => $this->country_name,
            'country_code' => $this->country_code,
            'city' => $this->city,
            'social_links' => $this->social_links,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'sermons_count' => $this->sermons_count ?? 0,
        ];
    }
}
