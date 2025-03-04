<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UnauthorizedException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'error' => [
                'code' => 401,
                'message' => 'Login failed',
            ]
        ], 401);
    }
}