<?php

namespace App\Notifications;

use App\Models\Church;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewChurchCreated extends Notification
{
    use Queueable;

    public $church;

    /**
     * Create a new notification instance.
     */
    public function __construct(Church $church)
    {
        $this->church = $church;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_church',
            'church_id' => $this->church->id,
            'church_name' => $this->church->name,
            'church_city' => $this->church->city,
            'church_country' => $this->church->country,
            'title' => 'Nouvelle église créée',
            'message' => "{$this->church->name} a été ajoutée à la plateforme",
            'action_url' => "/churches/{$this->church->id}",
        ];
    }
}
