<?php

namespace App\Http\Controllers;

use App\Models\CompanyUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectUserController extends Controller
{

    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Проект не найден.'], 404);
        }

        $isParticipant = $project->participants()->where('user_id', Auth::id())->exists();

        if (!$isParticipant) {
            return response()->json(['message' => 'Пользователь не является участником проекта.'], 404);
        }

        $systemUser = User::where('email', 'system@example.com')->first();

        $project->tasks()
            ->where('assigned_to', Auth::id())
            ->update(['assigned_to' => $systemUser->id]);

        $project->participants()->detach(Auth::id());

        return response()->json(['message' => 'Пользователь удален из проекта и задачи обновлены.'], 200);

    }


    public function join(Request $request)
    {
        $request->validate([
            'invitation_code' => 'required|string',
        ]);

        $project = Project::where('invitation_code', $request->input('invitation_code'))->first();

        if (!$project) {
            return $this->notFoundResponse();
        }

        if ($project->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Вы уже являетесь участником этого проекта.'], 400);
        }

        if ($project->company_id !== null) {
            $isInCompany = CompanyUser::where('company_id', $project->company_id)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$isInCompany) {
                return response()->json(['error' => 'Чтобы присоединиться к проекту, вы должны быть участником соответствующего рабочего пространства.'], 403);
            }
        }

        $currentParticipantCount = $project->participants()->count();
        if ($currentParticipantCount >= $project->team_size) {
            $project->increment('team_size');
        }

        $project->participants()->attach(Auth::id());

        return response()->json(['message' => 'Вы успешно добавлены в проект.'], 200);
    }
}
