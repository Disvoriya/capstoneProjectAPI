<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function index()
    {
        $attachments = Attachment::whereHas('task', function ($query) {
            $query->where('assigned_to', Auth::id());
        })->get();

        $formatted = $attachments->isNotEmpty()
            ? $attachments->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_path' => $file->file_path,
                    'file_name' => $file->original_name,
                    'file_size' => $file->size,
                    'file_type' => $file->file_type,
                    'created_at' => $file->created_at->toDateTimeString(),
                ];
            })
            : null;

        return response()->json([
            'attachments' => $formatted
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        // Определяем ID проекта, если вложение связано с задачей
        $projectId = null;
        if ($request->filled('task_id')) {
            // Получаем проект через задачу
            $task = Task::findOrFail($request->input('task_id'));
            $projectId = $task->project_id;
        }

        // Если вложение не связано с задачей, используем папку пользователя
        $userFolder = "attachments/user_" . Auth::id();

        // Логика для определения пути сохранения файла
        $file = $request->file('file');
        if ($projectId) {
            // Если вложение связано с проектом, сохраняем в папку проекта
            $projectFolder = "attachments/project_{$projectId}";
            $path = $file->store($projectFolder, 'public');
        } else {
            // В противном случае — сохраняем в папку пользователя
            $path = $file->store($userFolder, 'public');
        }

        // Получаем оригинальное имя файла
        $originalName = $file->getClientOriginalName();

        // Создаём запись о вложении
        $attachment = Attachment::create([
            'user_id' => Auth::id(),  // Сохраняем пользователя, который загрузил файл
            'task_id' => $request->input('task_id'),
            'file_path' => $path,
            'original_name' => $originalName,
            'size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'message' => 'Файл успешно загружен.',
            'attachment' => $attachment
        ], 201);
    }

    // Метод для отображения конкретного вложения
    public function show($id)
    {
        $attachment = Attachment::with(['task'])->find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Вложение не найдено'], 404);
        }

        return response()->json([
            'attachment' => $attachment,
            'file_url' => $attachment->file_path, // Полный URL к файлу
        ]);
    }

    // Метод для обновления вложения
    public function update(Request $request, $id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Вложение не найдено'], 404);
        }

        $request->validate([
            'file' => 'nullable|file|max:10240',
            'task_id' => 'nullable|exists:tasks,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        // Обновляем project_id, если он не передан, пробуем через задачу
        $projectId = $request->input('project_id', $attachment->project_id);

        if ($request->filled('task_id')) {
            $task = Task::findOrFail($request->input('task_id'));
            $projectId = $task->project_id;
            $attachment->task_id = $task->id;
        }

        // Если пришёл новый файл — заменим старый
        if ($request->hasFile('file')) {
            // Удалим старый файл, если он есть
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $file = $request->file('file');
            $projectFolder = "attachments/project_{$projectId}";
            $path = $file->store($projectFolder, 'public');

            $attachment->file_path = $path;
            $attachment->original_name = $file->getClientOriginalName();
        }

        // Обновим project_id (если изменился)
        $attachment->project_id = $projectId;
        $attachment->save();

        return response()->json([
            'message' => 'Вложение успешно обновлено',
            'attachment' => $attachment
        ]);
    }

    // Метод для удаления вложения
    public function destroy($id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Вложение не найдено'], 404);
        }

        // Удаление файла из хранилища
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // Удаление записи из базы данных
        $attachment->delete();

        return response()->json(['message' => 'Вложение успешно удалено']);
    }

    public function download($id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Вложение не найдено'], 404);
        }

        // Получаем путь к файлу
        $filePath = storage_path('app/public/' . $attachment->file_path);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Файл не найден'], 404);
        }

        // Возвращаем файл с правильными заголовками
        return response()->download($filePath, $attachment->original_name, [
            'Content-Type' => $attachment->file_type, // MIME тип
            'Content-Disposition' => 'attachment; filename="' . $attachment->original_name . '"'
        ])->header('X-Original-Name', $attachment->original_name);;
    }

}
