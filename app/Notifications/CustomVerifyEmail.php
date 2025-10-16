<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CustomVerifyEmail extends Notification
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
            ->subject(Lang::get('Code de vérification email'))
            ->greeting(Lang::get('Bonjour !'))
            ->line(Lang::get('Merci de vous être inscrit ! Pour vérifier votre adresse email, utilisez le code ci-dessous :'))
            ->line('## **' . $this->code . '**')
            ->line(Lang::get('Ce code est valide pendant 15 minutes.'))
            ->line(Lang::get('Entrez ce code dans l\'application pour activer votre compte.'))
            ->line(Lang::get('Si vous n\'avez pas créé de compte, ignorez cet email.'))
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
            'email' => $notifiable->getEmailForVerification(),
        ];
    }
}
