<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->resource['user']['id'],
                'name' => $this->resource['user']['name'],
                'email' => $this->resource['user']['email'],
                'avatar_url' => $this->resource['user']['avatar_url'],
            ],
            'listening_stats' => [
                'sermons_listened_count' => $this->resource['listening_stats']['sermons_listened_count'],
                'total_listening_time_seconds' => $this->resource['listening_stats']['total_listening_time_seconds'],
                'total_listening_time_formatted' => $this->resource['listening_stats']['total_listening_time_formatted'],
                'favorites_count' => $this->resource['listening_stats']['favorites_count'],
                'completed_sermons_count' => $this->resource['listening_stats']['completed_sermons_count'],
            ],
            'activity' => [
                'last_activity_at' => $this->resource['activity']['last_activity_at'],
                'is_active_listener' => $this->resource['activity']['is_active_listener'],
            ],
        ];
    }
}
