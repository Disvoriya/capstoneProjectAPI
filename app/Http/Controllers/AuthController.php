<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->password === $credentials['password']) {
            $user->tokens()->delete();
            $token = $user->createToken('api_token')->plainTextToken;
            $user->api_token = $token;
            $user->save();

            return response()->json([
                'data' => [
                    'user_id' => $user->id,
                    'role_id' => $user->role_id,
                    'user_token' => $token,
                ]
            ], 200);
        }

        return response()->json(['message' => 'Ошибка авторизации'], 401);
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $photoPath = $request->file('photo_file')->store('/imageUsers', 'public');

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'patronymic' => $data['patronymic'],
            'competence' => $data['competence'],
            'email' => $data['email'],
            'password' => $data['password'],
            'photo_file' => $photoPath,
            'role_id' => $data['role_id'] ?? 2,
        ]);

        $token = $user->createToken('api_token')->plainTextToken;
        $user->api_token = $token;
        $user->save();

        UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => 'register',
        ]);

        Setting::create([
            'user_id' => $user->id
        ]);

        return response()->json([
            'data' => [
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'user_token' => $token,
            ]
        ], 201);

    }

    public function logout(Request $request)
    {
        // Проверяем, что пользователь аутентифицирован
        if (Auth::check()) {
            Auth::user()->tokens()->delete(); // Удаление всех токенов
            return response()->json(null, 204); // Ответ с кодом 204 No Content
        }

        return response()->json(['message' => 'Unauthorized'], 401); // Если пользователь не аутентифицирован
    }

}
