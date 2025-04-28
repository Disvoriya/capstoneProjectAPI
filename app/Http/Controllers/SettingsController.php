<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Models\CompanyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    // Обновление персональной информации пользователя
    public function updatePersonal(Request $request)
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'patronymic' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:255|unique:users,email,' . Auth::id(),
            'competence' => 'sometimes|in:UI/UX Дизайнер,Разработчик,Контент-менеджер,Маркетолог,Тестировщик,Проектный менеджер,Аналитик,Системный администратор',
            'photo_file' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $updateData = $request->only('first_name', 'last_name', 'patronymic', 'email', 'competence');

        if ($request->hasFile('photo_file')) {
            if ($user->photo_file) {
                Storage::disk('public')->delete($user->photo_file);
            }
            $photoPath = $request->file('photo_file')->store('/imageUsers', 'public');
            $updateData['photo_file'] = $photoPath;
        }

        $user->update($updateData);

        return response()->json(['message' => 'Личная информация успешно обновлена.']);
    }

    // Изменение пароля пользователя
    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user = Auth::user();
        $user->password = $request->new_password;
        $user->save();

        return response()->json(['message' => 'Пароль успешно обновлен.']);
    }

    // Удаление аккаунта
    public function deleteAccount()
    {
        $user = Auth::user();

        DB::transaction(function () use ($user) {
            // Анонимизация пользователя (но оставляем user_id)
            $user->update([
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'patronymic' => null,
                'email' => 'deleted_' . $user->id . '@example.com',
                'password' => null,
                'photo_file' => null,
                'api_token' => null,
                'competence' => 'Тестировщик',
                'role_id' => null,
            ]);

            // Удаление связей с компаниями, проектами и настройками
            DB::table('company_users')->where('user_id', $user->id)->delete();
            DB::table('project_user')->where('user_id', $user->id)->delete();
            DB::table('settings')->where('user_id', $user->id)->delete();
            DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
            DB::table('user_activity')->where('user_id', $user->id)->delete();

            // Обновление проектов и задач
            DB::table('projects')->where('created_by', $user->id)->update(['created_by' => null]);
            DB::table('tasks')->where('assigned_to', $user->id)->update(['assigned_to' => null]);

            // НЕ трогаем `conversation_messages` и `private_chat_messages`, чтобы сохранить user_id
        });

        return response()->json(['message' => 'Аккаунт успешно анонимизирован'], 200);
    }

    // Обновление настроек уведомлений
    public function updateNotificationSettings(Request $request)
    {
        $request->validate([
            'notifications' => 'required|array',
        ]);

        $setting = Setting::where('user_id', Auth::id())->first();
        if (!$setting) {
            $setting = new Setting();
            $setting->user_id = Auth::id();
        }
        $setting->notifications = json_encode($request->notifications);
        $setting->save();

        return response()->json(['message' => 'Настройки уведомлений успешно обновлены.']);
    }

}
