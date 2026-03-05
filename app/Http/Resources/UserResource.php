<?php

namespace App\Http\Resources;

use App\Helpers\ThumbnailHelper;
use App\Models\Church;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $church = Church::active()->where('created_by', $this->id)->first();

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url ? asset($this->avatar_url) : null,
            'avatar_thumbnail_url' => ThumbnailHelper::url($this->avatar_url),
            'email_verified_at' => $this->email_verified_at,
            'is_email_verified' => $this->hasVerifiedEmail(),
            'role' => new RoleResource($this->whenLoaded('role')),
            'church' => $church ? new ChurchResource($church) : null,
            'preacher_profile' => $this->preacherProfile ? new PreacherProfileResource($this->preacherProfile) : null,
        ];

        // Add storage alert for church admins
        if ($church) {
            $status = $church->getStorageStatus();
            $percentage = $church->getStorageUsedPercentage();
            $exceeded = $church->isStorageQuotaExceeded();

            $data['storage_alert'] = [
                'status' => $status,
                'used_percentage' => $percentage,
                'can_upload' => !$exceeded,
                'upgrade_required' => $exceeded,
                'message' => match (true) {
                    $exceeded => 'Votre quota de stockage est épuisé. Veuillez mettre à jour votre abonnement pour continuer à publier.',
                    $status === 'critical' => 'Attention : votre espace de stockage est presque plein (' . $percentage . '% utilisé).',
                    $status === 'warning' => 'Votre espace de stockage commence à se remplir (' . $percentage . '% utilisé).',
                    default => null,
                },
            ];
        }

        return $data;
    }
}
