<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    // Обновление персональной информации пользователя
    public function updatePersonal(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'email' => 'sometimes|email|max:255|unique:users,email,' . Auth::id(),
            'competence' => 'required|in:UI/UX Дизайнер,Разработчик,Контент-менеджер,Маркетолог,Тестировщик,Проектный менеджер,Аналитик,Системный администратор',
        ]);

        $user = Auth::user();
        $user->update($request->only('first_name', 'last_name', 'patronymic', 'email', 'competence'));

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

    // Обновление общих настроек (язык)
    public function updateGeneralSettings(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,ru,fr,es',
            'theme'=> 'required|in:light,dark',
        ]);

        $setting = Setting::where('user_id', Auth::id())->first();
        if (!$setting) {
            $setting = new Setting();
            $setting->user_id = Auth::id();
        }
        $setting->update($request->only('language', 'theme'));

        return response()->json(['message' => 'Общие настройки успешно обновлены.']);
    }
    /*
     * <button type="button" class="text-button" :class="{'seting-active-btn': currentForm === 'generalSetting'}" @click="showForm('generalSetting')">Общие</button>
        <form v-show="currentForm === 'generalSetting'" id="generalSetting" method="post" class="mt-4 form-section" @submit.prevent="updateGeneralSettings">
          <div class="mb-3">
            <label for="language" class="form-label">Язык</label>
            <select id="language" class="form-select form-control" v-model="language">
              <option value="en">English</option>
              <option value="ru">Русский</option>
              <option value="fr">Français</option>
              <option value="es">Español</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="theme" class="form-label">Тема</label>
            <select id="theme" class="form-select form-control" v-model="theme">
              <option value="light">Светлая</option>
              <option value="dark">Темная</option>
            </select>
          </div>
          <button type="submit" class="custom-btn">
            Сохранить
          </button>
        </form>

     */

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
    /*
     *           <button type="button" class="text-button" :class="{'seting-active-btn': currentForm === 'notifSetting'}" @click="showForm('notifSetting')">Уведомления</button>
     *         <form v-show="currentForm === 'notifSetting'" id="notifSetting" method="post" class="mt-4 form-section">
          <div class="mb-3">
            <label class="switch mr-2">
              <input type="checkbox">
              <span class="slider"></span>
            </label> Новые задачи
          </div>
          <div class="mb-3">
            <label class="switch mr-2">
              <input type="checkbox" checked>
              <span class="slider"></span>
            </label> Дедлайн задач
          </div>
          <div class="mb-3">
            <label class="switch mr-2">
              <input type="checkbox">
              <span class="slider"></span>
            </label> Обновление задач
          </div>
          <button type="submit" class="custom-btn">
            Сохранить
          </button>
        </form>
     */
}
