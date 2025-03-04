<?php

namespace App\Exceptions;

use Exception;

class ForbiddenException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'error' => [
                'code' => 403,
                'message' => 'Forbidden for you',
            ]
        ], 403);
    }
}
