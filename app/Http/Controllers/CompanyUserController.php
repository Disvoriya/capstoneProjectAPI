<?php

namespace App\Http\Controllers;

use App\Models\CompanyUser;
use App\Models\Company;
use App\Notifications\UserDemotedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompanyUserController extends Controller
{
    // Получить всех пользователей конкретной компании
    public function getCompanyUsers($companyId)
    {
        return response()->json(CompanyUser::where('company_id', $companyId)->get());
    }

    // Получить все компании, в которых работает пользователь
    public function getUserCompanies()
    {
        $user = Auth::user();
        $companies = $user->companies()->get(); // Принудительно загружаем данные
        return response()->json($companies);
    }

    public function join(Request $request)
    {
        $request->validate([
            'invitation_code' => 'required|string',
        ]);

        $company = Company::where('invitation_code', $request->input('invitation_code'))->first();

        if (!$company) {
            return response()->json(['error' => 'Пригласительный код не найден.'], 404);
        }

        if ($company->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Вы уже являетесь участником этого рабочего пространства.'], 400);
        }

        return response()->json([
            'message' => 'Код верный. Заполните дополнительные данные.',
            'company_id' => $company->id
        ], 200);
    }

    // Создать новую запись пользователя в компании
    public function store(Request $request, $company_id)
    {
        $validated = $request->validate([
            'role' => 'required|in:Owner,Admin,Manager,Developer,Designer,HR,Other',
            'status' => 'required|in:Active,Inactive'
        ]);

        $userId = Auth::id();

        $permissions = $this->getDefaultPermissions($validated['role']);

        CompanyUser::create([
            'company_id' => $company_id,
            'user_id' => $userId,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'permissions' => json_encode($permissions ?? [])
        ]);

        return response()->json([
            'message' => 'Вы успешно присоединились к рабочему пространству'
        ], 201);

    }

    // Получить информацию о авторизованном пользователе в конкретной компании
    public function show($companyId)
    {
        $userId = Auth::id();
        $companyUser = CompanyUser::where('company_id', $companyId)->where('user_id', $userId)->first();
        return response()->json($companyUser);
    }

    public function update(Request $request, $companyId, $userId)
    {
        $validated = $request->validate([
            'role' => 'required|in:Owner,Admin,Manager,Developer,Designer,HR,Other',
        ]);

        $companyUser = CompanyUser::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $newPermissions = $this->getDefaultPermissions($validated['role']);
        $companyUser->update([
            'role' => $validated['role'],
            'permissions' => json_encode($newPermissions),
        ]);

        return response()->json(['message' => "Роль пользователя обновлен, права обнавлены в соотвествие."]);
    }

    // Изменить статус пользователя на "Inactive" вместо удаления
    public function destroy($companyId, $userId)
    {
        $companyUser = CompanyUser::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $company = Company::find($companyId);

        if (!$company) {
            return response()->json(['error' => 'Компания не найдена.'], 404);
        }

        $newPermissions = $this->getDefaultPermissions('Other');
        $companyUser->status = 'Inactive';
        $companyUser->terminated_at = now()->addWeeks(2);
        $companyUser->permissions = json_encode($newPermissions);
        $companyUser->save();

        $user = $companyUser->user;
        $user->notify(new UserDemotedNotification($company->name, $newPermissions));

        return response()->json(['message' => "Статус пользователя обновлен до неактивного, права ограничены."]);
    }

}
