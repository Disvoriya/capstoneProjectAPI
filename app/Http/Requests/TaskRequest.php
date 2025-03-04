<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:task_categories,id',
            'due_date' => 'required|date_format:Y-m-d',
            'assigned_to' => 'nullable|exists:project_user,id',
            'status' => 'required|string|in:to_do,in_progress,on_correction,done',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Заголовок обязателен.',
            'project_id.required' => 'Идентификатор проекта обязателен.',
            'project_id.exists' => 'Неверный идентификатор проекта.',
            'category_id.required' => 'Идентификатор категории обязателен.',
            'category_id.exists' => 'Неверный идентификатор категории.',
            'due_date.required' => 'Дата выполнения обязательна.',
            'due_date.date_format' => 'Дата выполнения должна быть в формате YYYY-MM-DD.',
            'assigned_to.exists' => 'Неверный идентификатор пользователя-исполнителя.',
            'status.required' => 'Статус обязателен.',
            'status.in' => 'Статус должен быть одним из: to_do, in_progress, on_correction, done.',
        ];
    }
}
