<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'uuid' => $this->uuid,
            'amount' => (float) $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'currency' => $this->currency,
            'country_code' => $this->country_code,
            'phone_number' => $this->maskPhoneNumber($this->phone_number),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'failure_reason' => $this->failure_reason,
            'failure_message' => $this->getFailureMessage(),
            'message' => $this->message,
            'is_sandbox' => $this->is_sandbox,
            'recipient' => $this->getRecipientInfo(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    /**
     * Get human-readable status label.
     */
    protected function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'completed' => 'Complété',
            'failed' => 'Échoué',
            default => $this->status,
        };
    }

    /**
     * Get user-friendly failure message.
     */
    protected function getFailureMessage(): ?string
    {
        if ($this->status !== 'failed' || !$this->failure_reason) {
            return null;
        }

        $reason = strtolower($this->failure_reason);

        // Traduire les messages d'erreur courants
        if (str_contains($reason, 'insufficient') || str_contains($reason, 'balance') || str_contains($reason, 'solde')) {
            return 'Solde insuffisant sur votre compte Mobile Money.';
        }

        if (str_contains($reason, 'invalid phone') || str_contains($reason, 'phone number')) {
            return 'Numéro de téléphone invalide.';
        }

        if (str_contains($reason, 'timeout') || str_contains($reason, 'expired')) {
            return 'La transaction a expiré. Veuillez réessayer.';
        }

        if (str_contains($reason, 'cancelled') || str_contains($reason, 'rejected') || str_contains($reason, 'declined')) {
            return 'Transaction annulée ou refusée.';
        }

        if (str_contains($reason, 'network') || str_contains($reason, 'connexion')) {
            return 'Erreur de connexion avec l\'opérateur mobile.';
        }

        // Message par défaut
        return 'Le paiement a échoué. Veuillez réessayer ou contacter le support.';
    }

    /**
     * Mask phone number for privacy.
     */
    protected function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 6) {
            return $phone;
        }

        return substr($phone, 0, 6) . str_repeat('*', $length - 10) . substr($phone, -4);
    }

    /**
     * Get recipient information.
     */
    protected function getRecipientInfo(): ?array
    {
        if ($this->church) {
            return [
                'type' => 'church',
                'id' => $this->church->id,
                'name' => $this->church->name,
                'logo_url' => $this->church->logo_url ? asset($this->church->logo_url) : null,
            ];
        }

        if ($this->preacherProfile) {
            return [
                'type' => 'preacher',
                'id' => $this->preacherProfile->id,
                'name' => $this->preacherProfile->name ?? $this->preacherProfile->user->name ?? 'Prédicateur',
                'avatar_url' => $this->preacherProfile->avatar_url ? asset($this->preacherProfile->avatar_url) : null,
            ];
        }

        return null;
    }
}
