<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function notFoundResponse()
    {
        return response()->json([
            'message' => 'Not found',
            'code' => 404,
        ], 404);
    }

    public function formatMessageTime($createdAt)
    {
        if (!$createdAt) {
            return null;
        }

        $now = Carbon::now();
        $messageTime = Carbon::parse($createdAt);
        $diffInMinutes = $now->diffInMinutes($messageTime);
        $diffInHours = $now->diffInHours($messageTime);
        $diffInDays = $now->diffInDays($messageTime);

        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' ' . ($diffInMinutes === 1 ? 'минуту' : 'минуты') . ' назад';
        } elseif ($diffInHours < 24) {
            return $diffInHours . ' ' . ($diffInHours === 1 ? 'час' : 'часа') . ' назад';
        } else {
            return $diffInDays . ' ' . ($diffInDays === 1 ? 'день' : 'дня') . ' назад';
        }
    }
}
