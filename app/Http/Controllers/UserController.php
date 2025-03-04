<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return UserResource::collection($users);
    }


    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
            'patronymic' => $data['patronymic'],
            'email' => $data['email'],
            'password' => $data['password'],
            'photo_file' => $data['photo_file'] ?? null,
            'role_id' => $data['role_id'],
        ]);

        if ($request->hasFile('photo_file')) {
            $path = $request->file('photo_file')->store('/', 'public');
            $user->photo_file = $path;
        }

        $user->save();

        return response()->json(['data' => ['id' => $user->id, 'status' => 'Пользователь создан']], 201);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }
        return new UserResource($user);
    }

    /*
    public function getUserActivity($userId, $period)
    {
        $query = UserActivity::where('user_id', $userId);

        switch ($period) {
            case 'week':
                // Получаем активности за последние 7 дней
                $query->where('activity_time', '>=', now()->subWeek());
                $groupBy = 'DAYOFWEEK(activity_time)'; // Группируем по номеру дня недели
                break;
            case 'month':
                // Получаем активности за последний месяц
                $query->where('activity_time', '>=', now()->subMonth());
                $groupBy = 'DATE_FORMAT(activity_time, "%Y-%m")';
                break;
            case 'year':
                // Получаем активности за последний год
                $query->where('activity_time', '>=', now()->subYear());
                $groupBy = 'YEAR(activity_time)';
                break;
            default:
                return response()->json(['error' => 'Invalid period specified'], 400);
        }

        // Выполняем группировку и подсчет активности
        $activities = $query->selectRaw("$groupBy as period, COUNT(*) as activity_count")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Получаем текстовые представления дней недели
        $daysOfWeek = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        // Форматируем данные для графиков
        $formattedData = $activities->map(function ($activity) use ($daysOfWeek) {
            return [
                'period' => $daysOfWeek[$activity->period - 1], // Преобразуем номер дня в текст
                'activity_count' => (int) $activity->activity_count, // Убедимся, что это целое число
            ];
        });

        // Возвращаем данные в формате JSON
        return response()->json($formattedData);
    }
    public function showUserActivity(Request $request, $userId)
    {
        $period = $request->get('period');
        return $this->getUserActivity($userId, $period);
    }
    */

    public function getUserActivity($userId)
    {
        UserActivity::where('activity_time', '<', now()->subWeek())->delete();

        $query = UserActivity::where('user_id', $userId)
            ->where('activity_time', '>=', now()->subWeek());

        $activities = $query->selectRaw('DAYOFWEEK(activity_time) as period, COUNT(*) as activity_count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $daysOfWeek = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        $formattedData = $activities->map(function ($activity) use ($daysOfWeek) {
            return [
                'period' => $daysOfWeek[$activity->period - 1],
                'activity_count' => (int) $activity->activity_count,
            ];
        });

        return response()->json($formattedData);
    }

    public function showUserActivity(Request $request, $userId)
    {
        return $this->getUserActivity($userId);
    }


}
