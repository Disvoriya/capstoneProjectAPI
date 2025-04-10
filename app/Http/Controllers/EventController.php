<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{

    // Метод для получения ближайших событий (вертикальный список)
    public function upcomingEvents()
    {
        $userId = Auth::id();

        $events = DB::table('projects')
            ->join('project_user', 'projects.id', '=', 'project_user.project_id')
            ->select('projects.id', 'projects.name AS event_name', 'projects.start_date AS event_date', DB::raw("'Проект' AS event_type"))
            ->where('project_user.user_id', $userId)
            ->where('projects.start_date', '>=', now())
            ->union(
                DB::table('tasks')
                    ->select('tasks.id', 'tasks.title AS event_name', 'tasks.due_date AS event_date', DB::raw("'Задача' AS event_type"))
                    ->where('tasks.assigned_to', $userId)
                    ->where('tasks.due_date', '>=', now())
            )
            ->orderBy('event_date', 'asc')
            ->limit(5)
            ->get();

        return response()->json($events);
    }

    public function monthlyEvents()
    {
        $userId = Auth::id();

        $events = DB::table('projects')
            ->join('project_user', 'projects.id', '=', 'project_user.project_id')
            ->select('projects.id', 'projects.name AS title', 'projects.start_date AS date', DB::raw("'project' AS type"))
            ->where('project_user.user_id', $userId)
            ->union(
                DB::table('tasks')
                    ->select('tasks.id', 'tasks.title AS title', 'tasks.due_date AS date', DB::raw("'task' AS type"))
                    ->where('tasks.assigned_to', $userId)
            )
            ->orderBy('date')
            ->get();

        return response()->json($events);


    }

}
