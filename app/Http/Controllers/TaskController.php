<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\ProjectUser;
use App\Models\Setting;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\User;
use App\Models\UserActivity;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index($projectId)
    {
        $tasks = Task::with('category')->where('project_id', $projectId)->get();

        $tasksByStatus = [
            'to_do' => [],
            'in_progress' => [],
            'on_correction' => [],
            'done' => [],
        ];

        foreach ($tasks as $task) {
            $taskResource = new TaskResource($task);
            $tasksByStatus[$task->status][] = $taskResource;
        }

        return response()->json($tasksByStatus);
    }

    public function store(TaskRequest $request)
    {
        $user = auth()->user();
        $project = Project::find($request->project_id);

        if (!$project) {
            return response()->json([
                'message' => 'Проект не существует.'
            ], 404);
        }

        if ($project->company_id !== null) {
            $permissionKey = 'create_tasks';
            $permissionCheckResult = $this->checkCompanyPermission($user, $project->company_id, $permissionKey);
            if ($permissionCheckResult !== true) {
                return $permissionCheckResult;
            }
        }

        $validatedData = $request->validated();
        $task = Task::create($validatedData);

        UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => 'store_task',
        ]);

        $taskEndDate = $task->due_date;

        if ($taskEndDate > $project->end_date) {
            $project->end_date = $taskEndDate;
            $project->save();
        }

        if ($request->assigned_to) {
            $assignedTo = ProjectUser::find($request->assigned_to);
            if (!$assignedTo) {
                return $this->notFoundResponse();
            }

            $assignedUser = User::find($assignedTo->user_id);
            if (!$assignedUser) {
                return $this->notFoundResponse();
            }

            $setting = Setting::where('user_id', $assignedUser->id)->first();
            if ($setting && $setting->notifications) {
                $notifications = json_decode($setting->notifications, true);

                if (!empty($notifications['Новые задачи'])) {
                    $projectUser = ProjectUser::where('project_id', $task->project_id)
                        ->where('user_id', $assignedUser->id)
                        ->first();

                    if ($projectUser) {
                        $assignedUser->notify(new TaskAssigned($task));
                    }
                } else {
                    return response()->json([
                        'message' => 'Задача создана успешно. Уведомление не было отправлено, так как у назначенного пользователя отключены уведомления.'
                    ], 201);
                }
            }
        }

        return response()->json(['message' => 'Задача создана успешно.'], 201);
    }

    public function show($id)
    {
        $task = Task::with(['project', 'user'])->findOrFail($id);
        return new TaskResource($task);
    }

    public function update(TaskRequest $request, $id)
    {
        $user = auth()->user();

        $project = Project::find($request->project_id);

        if ($project->company_id !== null) {
            $permissionKey = 'edit_task';
            $permissionCheckResult = $this->checkCompanyPermission($user, $project->company_id, $permissionKey);
            if ($permissionCheckResult !== true) {
                return $permissionCheckResult;
            }
        }

        $task = Task::findOrFail($id);

        $task->update($request->validated());

        UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => 'update_task',
        ]);


        if ($request->assigned_to) {
            $assignedTo = ProjectUser::find($request->assigned_to);
            $assigned = User::where('id', $assignedTo->user_id)->first();

            if ($assigned) {
                $setting = Setting::where('user_id', $assigned->id)->first();

                if ($setting && $setting->notifications) {
                    $notifications = json_decode($setting->notifications, true);

                    if ($notifications['Новые задачи'] ?? false) {
                        $projectUser = ProjectUser::where('project_id', $task->project_id)
                            ->where('user_id', $assigned->id)
                            ->first();

                        if ($projectUser) {
                            $assigned->notify(new TaskUpdated($task));
                        }
                    } else {
                        return response()->json([
                            'message' => 'Задача создана отредактирована. Уведомление не было отправлено, так как у назначенного пользователя отключены уведомления.'
                        ], 201);
                    }
                }
            } else {
                return $this->notFoundResponse();
            }
        }

        return response()->json(['message' => 'Задача успешно отредактирована.'], 201);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Задача успешно удалена.'], 200);
    }
}
