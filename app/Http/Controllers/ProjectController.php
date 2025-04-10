<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\CompanyUser;
use App\Models\Project;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $search = $request->input('search');
        $filter = $request->input('filter', 'all');

        $projects = Project::with('participants', 'company')
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            })
            ->when($filter === 'work', function ($query) {
                $query->whereNotNull('company_id');
            })
            ->when($filter === 'personal', function ($query) {
                $query->whereNull('company_id');
            })
            ->get();

        $formattedProjects = $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'created_by' => $project->creator->first_name . " " . $project->creator->last_name,
                'created_by_photo_file' => $project->creator->photo_file,
                'name' => $project->name,
                'description' => $project->description,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'team_size' => $project->team_size,
                'note' => $project->note,
                'company_id' => $project->company_id,
                'company_name' => $project->company ? $project->company->name : null,
                'invitation_code' => $project->invitation_code,
                'participants' => $project->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'participant_name' => $participant->first_name . " " . $participant->last_name,
                        'photo_file' => $participant->photo_file,
                    ];
                })
            ];
        });

        return response()->json($formattedProjects);
    }

    public function store(StoreProjectRequest $request)
    {
        $user = auth()->user();
        $validatedData = $request->validated();

        do {
            $invitationCode = Str::random(10);
        } while (Project::where('invitation_code', $invitationCode)->exists());

        $validatedData['invitation_code'] = $invitationCode;
        $validatedData['created_by'] = $user->id;

        $project = Project::create($validatedData);

        $project->participants()->attach(Auth::id());

        UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => 'store_project',
        ]);

        return new ProjectResource($project);
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);
        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, $id)
    {
        $project = Project::find($id);

        if(!$project){
            return $this->notFoundResponse();
        }

        $project->update($request->validated());

        $user = auth()->user();

        UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => 'update_project',
        ]);

        return new ProjectResource($project);
    }

    public function destroy($id)
    {
        $project = Project::with(['tasks', 'participants'])->find($id);

        if (!$project) {
            return $this->notFoundResponse();
        }

        if ($project->created_by !== Auth::id()) {
            return response()->json(['message' => 'Только создатель проекта может его удалить.'], 403);
        }

        $project->participants()->detach();

        $project->tasks()->delete();

        $project->delete();

        return response()->json(['message' => 'Проект успешно удален.'], 200);
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
