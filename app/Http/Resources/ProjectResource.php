<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'team_size' => $this->team_size,
            'note' => $this->note,
            'company_id' => $this->company_id,
            'invitation_code' => $this->invitation_code,
            'participants' => $this->participants->map(function ($participant) {
                return [
                    'project_user_id' => $participant->pivot->id,
                    'user_id' => $participant->id,
                    'last_name' => $participant->last_name,
                    'photo_file' => $participant->photo_file,
                ];
            })
        ];
    }
}
