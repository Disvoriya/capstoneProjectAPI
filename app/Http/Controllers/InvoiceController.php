<?php

namespace App\Http\Controllers;

use App\Models\Project;
use PDF;

class InvoiceController extends Controller
{
    public function Invoice($projectId)
    {
        $project = Project::with(['creator', 'participants', 'tasks.user'])
            ->findOrFail($projectId);

        $tasks = $project->tasks;
        $participants = $project->participants;

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'done')->count();
        $notCompletedTasks = $totalTasks - $completedTasks;

        $data = [
            'project' => $project,
            'tasks' => $tasks,
            'participants' => $participants,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'notCompletedTasks' => $notCompletedTasks,
            'creator' => auth()->user(),
        ];

        $pdf = PDF::loadView('invoice_pdf', $data);
        $pdf->setOption('defaultFont', 'Secession');

        $projectName = str_replace(' ', '_', $project->name);
        $currentDate = now()->format('Y-m-d');
        $fileName = "{$projectName}_Отчет_от_{$currentDate}.pdf";

        return $pdf->download($fileName);
    }

}
