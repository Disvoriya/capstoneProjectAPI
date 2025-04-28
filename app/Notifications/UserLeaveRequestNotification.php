<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserLeaveRequestNotification extends Notification
{
    use Queueable;

    protected $leaver;
    protected $companyId;

    public function __construct($leaver, $companyId)
    {
        $this->leaver = $leaver;
        $this->companyId = $companyId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $fullName = "{$this->leaver->last_name} {$this->leaver->first_name} {$this->leaver->patronymic}";
        return (new MailMessage)
            ->subject('Заявка на увольнение')
            ->line("Пользователь {$fullName} подал заявку на увольнение из компании.")
            ->line("Email: {$this->leaver->email}")
            ->action('Открыть панель управления', url("/companyForm/{$this->companyId}"))
            ->line('Пожалуйста, рассмотрите эту заявку и примите решение.');

    }

    public function toArray($notifiable)
    {
        $fullName = "{$this->leaver->last_name} {$this->leaver->first_name} {$this->leaver->patronymic}";

        return [
            'user_id' => $this->leaver->id,
            'message' => "{$fullName} подал заявку на увольнение.",
            'company_id' => $this->companyId,
        ];
    }
}
