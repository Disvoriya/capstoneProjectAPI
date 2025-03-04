<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{


    public function rules()
    {
        return [
            'email' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator->errors()->toArray());
    }
}

