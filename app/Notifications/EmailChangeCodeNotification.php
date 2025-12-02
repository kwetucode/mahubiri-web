<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeCodeNotification extends Notification
{
    use Queueable;

    private string $code;
    private string $newEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code, string $newEmail)
    {
        $this->code = $code;
        $this->newEmail = $newEmail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirmation de changement d\'email - Mahubiri')
            ->greeting('Bonjour !')
            ->line('Vous avez demandé à changer votre adresse email.')
            ->line('Votre code de vérification est :')
            ->line('**' . $this->code . '**')
            ->line('Ce code expirera dans 15 minutes.')
            ->line('Si vous n\'avez pas demandé ce changement, veuillez ignorer cet email.')
            ->salutation('Cordialement, L\'équipe Mahubiri');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
            'new_email' => $this->newEmail,
        ];
    }
}
