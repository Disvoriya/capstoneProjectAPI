<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $searchQuery = $request->input('search');

        $conversations = $user->conversations()
            ->with(['users', 'latestMessage'])
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            })
            ->get()
            ->map(function ($conversation) {
                $lastMessage = $conversation->latestMessage;
                return [
                    'id' => $conversation->id,
                    'name' => $conversation->name,
                    'picture' => $conversation->picture,
                    'last_message' => $lastMessage ? [
                        'message' => $lastMessage->message,
                        'created_at' => $this->formatMessageTime($lastMessage->created_at),
                    ] : null
                ];
            });

        return response()->json($conversations);
    }

    public function show($id)
    {
        $conversation = Conversation::with(['users' => function ($query) {
            $query->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.photo_file', 'users.competence')
                ->withPivot('invited_by', 'invited_at');
        }])->findOrFail($id);

        $usersMap = $conversation->users->pluck('first_name', 'id')->toArray();
        $usersLastNameMap = $conversation->users->pluck('last_name', 'id')->toArray();

        $conversation->users->transform(function ($user) use ($usersMap, $usersLastNameMap) {
            $invitedById = $user->pivot->invited_by;
            $user->pivot->invited_by = $invitedById
                ? ($usersMap[$invitedById] . ' ' . $usersLastNameMap[$invitedById])
                : 'Создатель беседы';
            return $user;
        });

        return response()->json($conversation);
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'picture' => 'nullable|image|max:2048',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
        ]);

        $picture = null;
        if ($request->hasFile('picture')) {
            $picture = $request->file('picture')->store('/pictureConversation', 'public');
        }

        // Создание беседы
        $conversation = Conversation::create([
            'picture' => $picture,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'creator_id' => Auth::id()
        ]);

        // Добавление создателя в список участников
        ConversationUser::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'invited_by' => null,
            'invited_at' => now(),
        ]);

        if (!empty($validated['participants'])) {
            $participantsData = collect($validated['participants'])->map(function ($userId) use ($conversation) {
                return [
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'invited_by' => Auth::id(),
                    'invited_at' => now(),
                ];
            });

            ConversationUser::insert($participantsData->toArray());
        }

        return response()->json(['message' => 'Беседа успешно создана'], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'picture' => 'sometimes|nullable|image|max:2048',
            'name' => 'sometimes|string',
            'description' => 'sometimes|string',
        ]);

        $conversation = Conversation::findOrFail($id);

        if ($request->hasFile('picture')) {
            Storage::disk('public')->delete($conversation->picture);
            $picture = $request->file('picture')->store('/pictureConversation', 'public');
            $conversation->picture = $picture;
        }

        $conversation->name = $request->input('name');
        $conversation->description = $request->input('description');

        $conversation->save();

        return response()->json($conversation,200);
    }

    public function destroy($id)
    {
        Conversation::destroy($id);
        return response()->json(null, 204);
    }

    public function getMessages($converId)
    {
        $conversation = Conversation::with([
            'messages.user:id,first_name,last_name,photo_file',
            'messages.replyTo.user:id,first_name,last_name'
        ])->findOrFail($converId);

        $messages = $conversation->messages->map(function ($message) {
            return [
                'id' => $message->id,
                'text' => $message->message,
                'created_at' => $message->created_at->toDateTimeString(),
                'sender' => [
                    'id' => $message->user->id,
                    'first_name' => $message->user->first_name,
                    'last_name' => $message->user->last_name,
                    'photo_file' => $message->user->photo_file,
                ],
                'reply_to' => $message->replyTo ? [
                    'id' => $message->replyTo->id,
                    'text' => $message->replyTo->message,
                    'sender' => [
                        'id' => $message->replyTo->user->id,
                        'first_name' => $message->replyTo->user->first_name,
                        'last_name' => $message->replyTo->user->last_name,
                    ],
                ] : null,
            ];
        });

        return response()->json($messages);
    }

    public function sendMessage(Request $request, $converId)
    {
        $request->validate([
            'message' => 'required|string',
            'reply_to_id' => 'nullable|exists:conversation_messages,id',
        ]);

        $conversation = Conversation::findOrFail($converId);

        $message = new ConversationMessage();
        $message->conversation_id = $conversation->id;
        $message->user_id = Auth::id();
        $message->message = $request->message;

        if ($request->has('reply_to_id')) {
            $message->reply_to_id = $request->reply_to_id;
        }

        $message->save();

        return response()->json($message, 201);
    }

    public function putMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'sometimes|string',
        ]);

        $message = ConversationMessage::findOrFail($id);
        $message->message = $request->message;
        $message->save();

        return response()->json($message, 201);
    }

    public function delMessage($id)
    {
        $message = ConversationMessage::findOrFail($id);

        $message->delete();

        return response()->json('Сообщение удалено', 201);
    }

    public function addUsers(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);

        $validated = $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
        ]);

        $existingUsers = $conversation->users()->pluck('users.id')->toArray();
        $newUsers = array_diff($validated['users'], $existingUsers);

        $participantsData = collect($newUsers)->map(function ($userId) use ($conversation) {
            return [
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'invited_by' => Auth::id(),
                'invited_at' => now(),
            ];
        });

        if ($participantsData->isNotEmpty()) {
            ConversationUser::insert($participantsData->toArray());
        }

        return response()->json([
            'message' => 'Пользователи добавлены в беседу',
            'conversation' => $conversation->load('users'),
        ]);
    }

    public function removeUsers(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);

        $validated = $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
        ]);

        ConversationUser::where('conversation_id', $id)
            ->whereIn('user_id', $validated['users'])
            ->delete();

        return response()->json([
            'message' => 'Пользователи удалены из беседы',
            'conversation' => $conversation->load('users'),
        ]);
    }

}
