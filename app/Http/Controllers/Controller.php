<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

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

    public function getDefaultPermissions($role)
    {
        // Базовые действия для всех пользователей
        $basePermissions = [
            'view_company', // Просмотр компании
            'view_project', // Просмотр проектов
            'view_task',    // Просмотр задач
        ];
      //  надо создать уведомление создателю проекта(а еще у проекта есть company_id, то тому кто имеет права)

        // Дополнительные права в зависимости от роли
        $rolePermissions = [
            'Owner' => [
                'manage_company', // Управление компанией
                'manage_projects', // Управление проектами
                'manage_team',     // Управление командой в компании
                'create_tasks',    // Создание задач
            ],
            'Admin' => [
                'manage_company',
                'manage_projects', // Управление проектами
                'manage_team',     // Управление командой в компании
            ],
            'Manager' => [
                'manage_projects', // Управление проектами
                'create_tasks',    // Создание задач
            ],
            'Developer' => [
                'edit_task',       // Редактирование задач
            ],
            'Designer' => [
                'edit_task',       // Редактирование задач
            ],
            'HR' => [
                'manage_team',     // Управление командой в компании
            ],
            'Other' => [], // Остальные роли не имеют прав по умолчанию
        ];

        return array_merge($basePermissions, $rolePermissions[$role] ?? []);
    }

    public function checkCompanyPermission($user, $companyId, $permissionKey)
    {
        $permissionMessages = [
            'create_tasks'       => 'У вас нет прав для создания задач.',
            'manage_company'     => 'У вас нет доступа к управлению компанией.',
            'manage_projects'    => 'У вас нет прав для управления проектами.',
            'manage_team'        => 'У вас нет прав для управления сотрудниками компании.',
            'edit_task'          => 'У вас нет прав для редактирования задач.',
            'view_company'       => 'У вас нет прав для просмотра компании.',
            'view_project'       => 'У вас нет прав для просмотра проектов.',
            'view_task'          => 'У вас нет прав для просмотра задач.',
        ];


        $companyUser = DB::table('company_users')
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->first();

        if (!$companyUser) {
            return response()->json([
                'message' => 'Пользователь не найден в данной компании.'
            ], 404);
        }

        $decoded = json_decode($companyUser->permissions, true);

        if (is_string($decoded)) {
            $permissions = json_decode($decoded, true);
        } else {
            $permissions = $decoded;
        }

        if (!is_array($permissions)) {
            return response()->json([
                'message' => 'Ошибка чтения прав доступа: некорректный JSON.'
            ], 500);
        }

        if (!in_array($permissionKey, $permissions)) {
            return response()->json([
                'message' => $permissionMessages[$permissionKey] ?? 'У вас нет прав для выполнения данного действия.'
            ], 403);
        }

        return true;
    }

}
