<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserDemotedNotification extends Notification
{
    use Queueable;
    protected $companyName;
    protected $permissions;
    public function __construct($companyName, $permissions)
    {
        $this->companyName = $companyName;
        $this->permissions = implode(', ', $permissions);
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Ваш статус в компании изменён')
            ->line("Вы были уволены из компании **{$this->companyName}**.")
            ->line("Ваши новые права: **{$this->permissions}**")
            ->line("Эти права и возможность восстановления будут аннулированы через две недели.")
            ->line('Если у вас есть вопросы, свяжитесь с администратором.')
            ->action('Перейти в аккаунт', url('/auth'))
            ->line('Спасибо, что были с нами!');
    }

    /**
     * Уведомление в базе данных.
     */
    public function toArray($notifiable)
    {
        $translations = [
            "view_company" => "Просмотр компании",
            "view_project" => "Просмотр проекта",
            "view_task" => "Просмотр задач",
            "manage_company" => "Управление компанией",
            "manage_projects" => "Управление проектами",
            "manage_team" => "Управление командой",
            "create_tasks" => "Создание задач",
            "edit_task" => "Редактирование задач",
        ];

        $decodedPermissions = json_decode($this->permissions, true);

        // Перевести каждый ключ в русское значение, если найден
        $translatedPermissions = array_map(function ($perm) use ($translations) {
            return $translations[$perm] ?? $perm;
        }, $decodedPermissions);

        $permissionsString = implode(', ', $translatedPermissions);

        return [
            'message' => "Вы были уволены из компании {$this->companyName}. Ваши новые права: {$permissionsString}.",
        ];
    }

}
