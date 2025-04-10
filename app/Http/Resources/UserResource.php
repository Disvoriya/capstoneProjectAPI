<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $tasks = $this->tasks;
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'done')->count();
        $taskInProgress = $tasks->where('status', '!=', 'done');

        return [
            'id' => $this->id,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'patronymic' => $this->patronymic,
            'email' => $this->email,
            'competence' => $this->competence,
            'photo_file' => $this->photo_file,
            'role' => $this->role->name,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'tasks' => TaskResource::collection($taskInProgress),
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setData($this->toArray($request));
    }
}
