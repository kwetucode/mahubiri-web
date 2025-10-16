<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CustomResetPasswordNotification extends Notification // Removed ShouldQueue for immediate sending
{
    use Queueable;

    /**
     * The verification code (6 digits).
     *
     * @var string
     */
    public $code;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code)
    {
        $this->code = $code;
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
            ->subject(Lang::get('Code de réinitialisation de mot de passe'))
            ->greeting(Lang::get('Bonjour !'))
            ->line(Lang::get('Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.'))
            ->line(Lang::get('Votre code de vérification est :'))
            ->line('## **' . $this->code . '**')
            ->line(Lang::get('Ce code est valide pendant 15 minutes.'))
            ->line(Lang::get('Entrez ce code dans l\'application pour réinitialiser votre mot de passe.'))
            ->line(Lang::get('Si vous n\'avez pas demandé de réinitialisation de mot de passe, ignorez cet email.'))
            ->salutation(Lang::get('Cordialement,') . "\n" . config('app.name'));
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
            'email' => $notifiable->getEmailForPasswordReset(),
        ];
    }
}
