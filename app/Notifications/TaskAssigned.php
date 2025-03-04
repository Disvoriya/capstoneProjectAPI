<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;


class TaskAssigned extends Notification
{
    use Queueable;

    private $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'message' => 'Вам назначена новая задача!',
        ];
    }
}
