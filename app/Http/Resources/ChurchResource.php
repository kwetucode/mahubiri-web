<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
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
            'logo_url' => $this->logo_url,
            'description' => $this->description,
            'creator' => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email,
            ],
            'moment' => DateHelper::timeAgo($this->created_at),
            'created_at' => DateHelper::formatFrench($this->created_at, 'd/m/Y H:i:s'),
            'created_at_full' => DateHelper::formatFullFrench($this->created_at),

        ];
    }
}
