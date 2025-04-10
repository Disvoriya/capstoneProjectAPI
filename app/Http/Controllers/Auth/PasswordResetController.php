<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    // Отправка ссылки для сброса пароля
    public function sendResetLink(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $token = Password::createToken($user);
        $user->notify(new ResetPasswordNotification($token));

        return response()->json(['message' => 'Ссылка для сброса пароля отправлена!'], 200);

    }

    // Сброс пароля
    public function resetPassword(Request $request)
    {
        Log::debug('Запрос на сброс пароля:', $request->all());

        // Валидация
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        Log::debug('валидация ');

        $resetEntry = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        Log::debug('проверка');

        if (!$resetEntry || !Hash::check($request->token, $resetEntry->token)) {
            Log::debug('Токен недействителен или устарел!');
        }

        Log::debug('токен');

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Присваиваем пароль без хеширования
                $user->password = $password; // Прямо присваиваем пароль
                $user->save();
            }
        );

        Log::debug('Статус сброса пароля:', ['status' => $status]);

        return response()->json(['message' => 'Пароль успешно изменен!'], 201);
    }
}
