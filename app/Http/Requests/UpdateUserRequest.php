<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'patronymic' => 'required|string|max:100',
            'email' => 'required|string|unique:users,email|max:255',
            'photo_file' => 'nullable|image|mimes:jpg,jpeg,png',
        ];
    }
}
