<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        $companies = $user->companies()->get();

        $formattedCompanies = $companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'industry' => $company->industry,
                'team_size' => $company->team_size,
                'logo' => $company->logo,
                'participants' => $company->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'participant_name' => $user->first_name . " " . $user->last_name,
                        'photo_file' => $user->photo_file,
                        'role' => $user->pivot->role,
                        'status' => $user->pivot->status,
                    ];
                }),
            ];
        });

        return response()->json($formattedCompanies);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'team_size' => 'required|integer|min:1',
            'industry' => 'required|in:IT,Finance,Healthcare,Education,Retail,Construction,Marketing,Consulting,Manufacturing,Entertainment,Other',
            'logo' => 'nullable|image|max:1024',
            'role' => 'required|in:Owner,Admin',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companyLogos', 'public');
        }

        do {
            $invitationCode = Str::random(10);
        } while (Project::where('invitation_code', $invitationCode)->exists());


        $company = Company::create([
            'name' => $validated['name'],
            'team_size' => $validated['team_size'],
            'logo' => $logoPath,
            'industry' => $validated['industry'],
            'invitation_code' => $invitationCode,
        ]);

        $role = $validated['role'];
        $permissions = $this->getDefaultPermissions($role);

        CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => Auth::id(),
            'role' => $role,
            'permissions' => json_encode($permissions),
        ]);

        return response()->json(['message' => 'Компания успешно зарегистрирована'], 201);
    }


    public function show($companyId)
    {
        $company = Company::findOrFail($companyId);
        $userId = Auth::id();
        $companyUser = CompanyUser::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return response()->json([
            'id' => $company->id,
            'name' => $company->name,
            'industry' => $company->industry,
            'team_size' => $company->team_size,
            'logo' => $company->logo,
            'invitation_code' => $company->invitation_code,
            'role' => $companyUser->role,
            'participants' => $company->users
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->first_name . " " . $user->last_name,
                        'photo' => $user->photo_file,
                        'role' => $user->pivot->role,
                        'status' => $user->pivot->status,
                    ];
                }),
        ]);
    }


    public function update(Request $request, $companyId)
    {
        Log::debug('Запрос:', $request->all());
        $company = Company::findOrFail($companyId);
        $userId = Auth::id();

        $companyUser = CompanyUser::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->first();

        if (!$companyUser || !in_array($companyUser->role, ['Owner', 'Admin'])) {
            return response()->json(['error' => 'У вас нет прав для изменения этой компании.'], 403);
        }

        $validated = $request->validate([
            'name' => "sometimes|string|max:255|unique:companies,name,{$companyId}",
            'team_size' => 'sometimes|integer|min:1',
            'industry' => 'sometimes|in:IT,Finance,Healthcare,Education,Retail,Construction,Marketing,Consulting,Manufacturing,Entertainment,Other',
            'logo' => 'nullable|image|max:1024',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companyLogos', 'public');
        }

        $company->update($validated);

        return response()->json(['message' => 'Компания успешно обновлена', 'company' => $company], 200);
    }


    public function destroy($companyId)
    {
        $company = Company::findOrFail($companyId);

        if (Auth::id() !== $company->owner_id) {
            return response()->json(['error' => 'Вы не можете удалить эту компанию.'], 403);
        }

        CompanyUser::where('company_id', $companyId)->delete();

        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return response()->json(['message' => 'Компания успешно удалена'], 200);
    }
}
