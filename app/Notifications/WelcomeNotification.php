<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class WelcomeNotification extends Notification // Removed ShouldQueue for immediate sending
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        $userName = $this->user->name ?? 'Cher utilisateur';

        return (new MailMessage)
            ->subject('Bienvenue dans notre communauté !')
            ->greeting("Bonjour {$userName},")
            ->line('Félicitations ! Votre compte a été créé avec succès et votre email a été vérifié.')
            ->line('Nous sommes ravis de vous accueillir dans notre communauté.')
            ->line('Vous pouvez maintenant vous connecter et profiter de toutes nos fonctionnalités :')
            ->line('• Gérer votre profil')
            ->line('• Accéder aux contenus de votre église')
            ->line('• Écouter les sermons')
            ->line('• Participer à la vie communautaire')
            ->action('Se connecter', url('/login'))
            ->line('Si vous avez des questions, n\'hésitez pas à nous contacter.')
            ->line('Que Dieu vous bénisse !')
            ->salutation('L\'équipe Neno');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'welcome_sent_at' => now()
        ];
    }
}
