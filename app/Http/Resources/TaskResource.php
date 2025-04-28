<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'status_name' => $this->getStatusLabel($this->status),
            'category' => $this->category->name,
            'description' => $this->description,
            'priority' => $this->getPriorityLabel($this->priority),
            'due_date' => $this->due_date,
            'project' => $this->project->name,
            'assigned_to' => [
                'photo_file' => $this->projectUser->user->photo_file,
                'name' => $this->projectUser->user->first_name . ' ' . mb_substr($this->projectUser->user->last_name, 0, 1),
                'last_name' => $this->projectUser->user->last_name,
                'id' => $this->projectUser->user->id,
                'project_user_id' => $this->projectUser->id,
            ],
            'author' => $this->authorRelation && $this->authorRelation->user ? [
                'photo_file' => $this->authorRelation->user->photo_file,
                'name' => $this->authorRelation->user->first_name . ' ' . mb_substr($this->authorRelation->user->last_name, 0, 1),
                'id' => $this->authorRelation->user->id,
                'project_user_id' => $this->authorRelation->id,
            ] : null,
            'attachments_count' => $this->attachments->count(),
            'attachments' => $this->attachments->isNotEmpty()
                ? $this->attachments->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'file_path' => $file->file_path,
                        'file_name' => $file->original_name,
                        'file_size' => $file->size,
                        'file_type' => $file->file_type,
                        'created_at' => $file->created_at->toDateTimeString(),
                    ];
                })
                : null,
        ];
    }

    private function getPriorityLabel($priority)
    {
        return match ($priority) {
            'low' => 'Низкий',
            'medium' => 'Средний',
            'high' => 'Высокий',
            'critical' => 'Срочный',
        };
    }

    private function getStatusLabel($status)
    {
        return match ($status) {
            'to_do' => 'Выдан',
            'in_progress' => 'В процессе',
            'on_correction' => 'На исправлении',
            'done' => 'Выполнен',
        };
    }
}
