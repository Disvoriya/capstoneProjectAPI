<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;


class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'last_name' => 'required|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'email' => 'required|string|unique:users,email|max:255',
            'competence' => 'required|in:UI/UX Дизайнер,Разработчик,Контент-менеджер,Маркетолог,Тестировщик,Проектный менеджер,Аналитик,Системный администратор',
            'password' => 'required|string|min:8',
            'photo_file' => 'nullable|file|mimes:jpg,jpeg,png|max:2048'
        ];
    }

}
