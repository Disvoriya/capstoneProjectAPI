<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Отчет проекта - ПроектТест</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h3 {
            color: #343a40;
        }
        p {
            line-height: 1.5;
            color: #6c757d;
        }
        .d-flex {
            display: flex;
        }
        .flex-column {
            flex-direction: column;
        }
        .align-items-start {
            align-items: flex-start;
        }
        .justify-content-start {
            justify-content: flex-start;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        .table-section {
            margin-top: 20px;
        }
        .text-bold {
            font-weight: bold;
        }
        .gray-color {
            color: gray;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul > li {
            text-indent: 30px;
        }
        ul > li:before {
            content: "-";
            text-indent: -5px;
        }
    </style>
</head>

<body>
<div class="container h-100">
    <div class="mt-3 d-flex flex-column align-items-start justify-content-start info">
        <h1>{{ $project->name }}</h1>
        <p>Создатель проекта: {{ $project->creator->first_name }} {{ $project->creator->last_name }}</p>
        <p>Дата начала: {{ \Carbon\Carbon::parse($project->start_date)->format('d-m-Y') }}</p>
        <p>Дата окончания: {{ \Carbon\Carbon::parse($project->end_date)->format('d-m-Y') }}</p>
        <p>Количество участников: {{ $participants->count() }}</p>
    </div>
    <div class="mt-3 d-flex flex-column align-items-start justify-content-start info">
        <p>{{ $project->description }}</p>
    </div>
    <h3 class="mt-3">Участники и задачи проекта</h3>
    <table class="table table-bordered table-section bill-tbl">
        <thead>
        <tr>
            <th>Участник</th>
            <th>Количество задач</th>
            <th>Сколько из них сделано</th>
            <th>Процент выполнения</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($participants as $participant)
            @php
                $participantTasks = $participant->tasks;
                $count = $participantTasks->count();
                $completedCount = $participantTasks->where('status', 'done')->count();
                $progress = $count ? round(($completedCount / $count) * 100, 2) : 0; // Процент выполнения
            @endphp
            <tr>
                <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                <td>{{ $count }}</td>
                <td>{{ $completedCount }}</td>
                <td>{{ $progress }}%</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td><strong>Итого</strong></td>
            <td><strong>{{ $totalTasks }}</strong></td>
            <td><strong>{{ $completedTasks }}</strong></td>
            <td><strong>{{ $totalTasks ? round(($completedTasks / $totalTasks) * 100, 2) : 0 }}%</strong></td>
        </tr>
        </tfoot>
    </table>
    <div class="mt-3">
        <h3>Задачи:</h3>
        <ul>
            @foreach ($tasks as $task)
                <li>
                    {{ $task->title }} - Назначено: {{ $task->user ? $task->user->first_name . ' ' . $task->user->last_name : 'Не назначено' }}
                    <span>Статус: {{ $task->status }}</span> - Дата выполнения: {{ \Carbon\Carbon::parse($task->due_date)->format('d-m-Y') }}
                </li>
            @endforeach
        </ul>
    </div>
    <p class="m-0 text-bold w-100">Дата создания отчета - <span class="gray-color">{{ now()->format('d-m-Y') }}</span></p>
    <p class="m-0 text-bold w-100">Создан отчет - <span class="gray-color">{{ $creator->first_name . ' ' . $creator->last_name }}</span></p>
</div>

</body>

</html>

