<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
            'team_size' => 'required|integer|min:1',
            'created_by' => 'exists:users,id',
            'note' => 'required|string|max:255',
            'invitation_code' => 'unique:projects',
            'company_id' => 'nullable|exists:companies,id',
        ];
    }

    public function messages()
    {
        return [
            'end_date.after' => 'Дата окончание проекта должна быть после даты начала.',
        ];
    }
}
