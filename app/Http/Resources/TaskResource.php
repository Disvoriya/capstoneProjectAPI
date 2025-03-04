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
            'category' => $this->category->name,
            'due_date' => $this->due_date,
            'assigned_to' => [
                'photo_file' => $this->projectUser->user->photo_file,
                'last_name' => $this->projectUser->user->last_name,
                'id' => $this->projectUser->user->id,
                'project_user_id' => $this->projectUser->id,
            ],
        ];
    }
}
