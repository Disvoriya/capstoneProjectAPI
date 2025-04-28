<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\PrivateChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivateChatController extends Controller
{
    // Получение всех пользователей (кроме авторизованного) с возможностью поиска в МЫСЛИ
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $searchQuery = $request->input('search');

        $users = User::where('id', '!=', $currentUser->id) // Исключаем текущего пользователя
        ->when($searchQuery, function ($query) use ($searchQuery) {
            $query->where(function ($subQuery) use ($searchQuery) {
                $subQuery->where('last_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('first_name', 'like', '%' . $searchQuery . '%');
            });
        })
            ->get()
            ->map(function ($user) use ($currentUser) {
                // Получение последнего сообщения между текущим и выводимым пользователями
                $lastMessage = PrivateChatMessage::where(function ($query) use ($currentUser, $user) {
                    $query->where('incoming_msg_id', $user->id)
                        ->where('outgoing_msg_id', $currentUser->id);
                })
                    ->orWhere(function ($query) use ($currentUser, $user) {
                        $query->where('incoming_msg_id', $currentUser->id)
                            ->where('outgoing_msg_id', $user->id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                return [
                    'user' => $user,
                    'last_message' => $lastMessage ? [
                        'message' => $lastMessage->message,
                        'created_at' => $this->formatMessageTime($lastMessage->created_at),
                    ] : null,
                    'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
                ];
            })
            ->sortByDesc('last_message_time')
            ->values();

        return response()->json($users);
    }

    // Создание личного чата -> отправка сообщений
    public function store(Request $request)
    {
        $request->validate([
            'incoming_msg_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'reply_to_id' => 'nullable|exists:private_chat_messages,id',
        ]);

        $chat = new PrivateChatMessage();
        $chat->incoming_msg_id = $request->incoming_msg_id;
        $chat->outgoing_msg_id = Auth::id();
        $chat->message = $request->message;

        if ($request->has('reply_to_id')) {
            $chat->reply_to_id = $request->reply_to_id;
        }

        $chat->save();

        return $this->index($request);
    }


    // Получение личного чата
    public function show($id)
    {
        $messages = PrivateChatMessage::with(['replyTo.sender', 'sender'])
        ->where(function ($query) use ($id) {
            $query->where('incoming_msg_id', Auth::id())
                ->where('outgoing_msg_id', $id);
        })
            ->orWhere(function ($query) use ($id) {
                $query->where('incoming_msg_id', $id)
                    ->where('outgoing_msg_id', Auth::id());
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $messages->each(function ($message) {
            if ($message->incoming_msg_id == Auth::id() && !$message->is_read) {
                $message->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            }
        });

        return response()->json($messages->map(function ($message) {
            return [
                'id' => $message->id,
                'text' => $message->message,
                'created_at' => $message->created_at,
                'is_read' => $message->is_read,
                'sender' => [
                    'id' => $message->sender->id,
                    'first_name' => $message->sender->first_name,
                    'last_name' => $message->sender->last_name,
                    'photo_file' => $message->sender->photo_file,
                ],
                'reply_to' => $message->replyTo ? [
                    'id' => $message->replyTo->id,
                    'text' => $message->replyTo->message,
                    'sender' => [
                        'id' => $message->replyTo->sender->id,
                        'first_name' => $message->replyTo->sender->first_name,
                        'last_name' => $message->replyTo->sender->last_name,
                    ],
                ] : null,
            ];
        }));
    }

    public function destroy($id)
    {
        $message = PrivateChatMessage::findOrFail($id);

        if ($message->outgoing_msg_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized to update this message'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Сообщение удалено'], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'message' => 'sometimes|string',
            'is_pinned' => 'sometimes|boolean'
        ]);

        $message = PrivateChatMessage::findOrFail($id);

        if ($message->outgoing_msg_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized to update this message'], 403);
        }

        if ($request->has('message')) {
            $message->message = $request->message;
        }

        if ($request->has('is_pinned')) {
            $message->is_pinned = $request->is_pinned;
        }

        $message->save();

        return response()->json($message);
    }


}
