<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewDonationReceived extends Notification
{
    use Queueable;

    public Donation $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_donation',
            'donation_id' => $this->donation->id,
            'donation_uuid' => $this->donation->uuid,
            'donor_name' => $this->donation->user?->name ?? 'Anonyme',
            'amount' => $this->donation->formatted_amount,
            'recipient_name' => $this->donation->recipient_name,
            'title' => 'Nouveau don reçu',
            'message' => ($this->donation->user?->name ?? 'Quelqu\'un') . ' a fait un don de ' . $this->donation->formatted_amount,
            'action_url' => '/admin/donations',
        ];
    }
}
