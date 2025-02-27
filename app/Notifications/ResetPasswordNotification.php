<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = url("https://bunker-shop.store". '/password-reset?token=' . $this->token . '&email=' . urlencode($notifiable->email));

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour ' . $notifiable->name . ' 👋')
            ->line('Vous recevez cet e-mail parce que nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.')
            ->action('Réinitialiser mon mot de passe', $resetUrl)
            ->line('Ce lien expirera dans ' . config('auth.passwords.users.expire') . ' minutes.')
            ->line('Si vous n\'avez pas demandé de réinitialisation, aucune action supplémentaire n\'est requise.')
            ->salutation('Cordialement, ' . config('app.name'));
    }
}

