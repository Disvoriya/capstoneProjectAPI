<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function getNotifications($userId)
    {
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


    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Уведомление не найдено.'], 404);
        }

        $notification->read_at = now();
        $notification->save();

        return response()->json(['message' => 'Уведомление отмечено как прочитанное.']);
    }

}
