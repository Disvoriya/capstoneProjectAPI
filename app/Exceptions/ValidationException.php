<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ValidationException extends Exception
{
    protected $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation error', 422);
        $this->errors = $errors;
    }

    public function render($request)
    {
        return response()->json([
            'error' => [
                'code' => 422,
                'message' => 'Validation error',
                'errors' => $this->errors,
            ]
        ], 422);
    }
}