<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function getNotifications()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден.'], 404);
        }

        $notifications = $user->notifications->sortByDesc('created_at');

        $unreadNotifications = $notifications->filter(function ($notification) {
            return $notification->read_at === null;
        })->take(5);

        $readNotifications = $notifications->filter(function ($notification) {
            return $notification->read_at !== null;
        })->take(5 - $unreadNotifications->count());

        $finalNotifications = $unreadNotifications->merge($readNotifications);

        $result = $finalNotifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'created_at' => $notification->created_at,
                'read_at' => $notification->read_at,
            ];
        });

        return response()->json($result);
    }


    public function markAllAsRead()
    {
        $user = Auth::user();

        $user->unreadNotifications->each(function ($notification) {
            $notification->markAsRead();
        });

        return response()->json(['message' => 'Все уведомления помечены как прочитанные.']);
    }


}
