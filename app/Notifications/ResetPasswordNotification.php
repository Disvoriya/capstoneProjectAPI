<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public $token; // Добавляем свойство
    protected $expireTime;

    public function __construct($token)
    {
        $this->token = $token;
        $this->expireTime = config('auth.passwords.users.expire');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = url("/auth?token={$this->token}&email={$notifiable->email}");
        $expireTime = now()->addMinutes($this->expireTime)->format('d.m.Y H:i');

        return (new MailMessage)
            ->subject('Восстановление пароля')
            ->greeting('Здравствуйте!')
            ->line('Вы запросили сброс пароля. Нажмите на кнопку ниже, чтобы создать новый пароль.')
            ->line("Срок действия ссылки истекает: **$expireTime**")
            ->action('Сбросить пароль', $resetUrl)
            ->line('Если вы не запрашивали сброс пароля, проигнорируйте это сообщение.');
    }
}
