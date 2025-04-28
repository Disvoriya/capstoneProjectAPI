<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationUserController extends Controller
{
    // index для вывода и поиска всех тех кто не участвует в беседе
    public function index(Request $request, Conversation $conversation)
    {
        $this->validate($request, [
            'search' => 'nullable|string|max:255',
        ]);

        // Проверяем, существует ли переданный объект беседы
        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        // Получаем ID всех пользователей в данной беседе
        $participantIds = $conversation->users()->pluck('users.id');

        // Получаем пользователей, не участвующих в беседе
        $users = \App\Models\User::whereNotIn('id', $participantIds);

        // Фильтрация по поисковому запросу
        if ($request->filled('search')) {
            $search = $request->search;
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        return response()->json( $users->get(), 200);
    }


    //добавление участника в беседу
    public function store(Request $request)
    {
        $this->validate($request, [
            'conversation_id' => 'required|exists:conversations,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        $conversation->users()->attach($request->user_id, [
            'invited_by' => Auth::id(), // авторизованный пользователь значение
            'invited_at' => now(),
        ]);

        return response()->json([
            'message' => 'Пользователь добавлен',
        ], 201);
    }

    // удаление/выход участника из беседы
    public function destroy(Request $request, Conversation $conversation, User $user)
    {
        $conversation->users()->detach($user->id);

        return response()->json(['message' => 'Пользователь удалён из беседы'], 200);
    }


}
