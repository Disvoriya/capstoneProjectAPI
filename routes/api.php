<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ConversationUserController;
use App\Http\Controllers\PrivateChatController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\InvoiceController;

/*
|--------------------------------------------------------------------------|
| API Routes                                                               |
|--------------------------------------------------------------------------|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user/{userId}', [UserController::class, 'show']);
    Route::get('/user/{userId}/activity', [UserController::class, 'showUserActivity']);
    Route::get('/user/projects/{userId}', [ProjectController::class, 'userProjects']);
    Route::get('project/{userId}', [ProjectController::class, 'index']);

    Route::post('/settings/personal', [SettingsController::class, 'updatePersonal']);
    Route::post('/settings/password', [SettingsController::class, 'updatePassword']);
    Route::post('/settings/general', [SettingsController::class, 'updateGeneralSettings']);
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotificationSettings']);

    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/add-participant', [ProjectController::class, 'joinProject']);

    Route::apiResource('tasks', TaskController::class);
    Route::get('/project/tasks/{projectId}', [TaskController::class, 'index']);

//    Route::apiResource('chat', ChatMessagesController::class);
//    Route::get('/chat/{userId}', [ChatMessagesController::class, 'getMessages']);

    Route::get('/user/{userId}/notification', [NotificationsController::class, 'getNotifications']);
    Route::post('/notification/{notificationId}', [NotificationsController::class, 'markAsRead']);
    Route::get('report/{projectId}', [InvoiceController::class, 'Invoice']);


    Route::prefix('private-chats')->group(function () {
        Route::get('/', [PrivateChatController::class, 'index']);
        Route::apiResource('/messages', PrivateChatController::class);
    });

    Route::prefix('conversations')->group(function () {
        Route::get('/', [ConversationController::class, 'index']);
        Route::post('/', [ConversationController::class, 'store']);
        Route::get('/{id}', [ConversationController::class, 'show']);
        Route::patch('/{id}', [ConversationController::class, 'update']);
        Route::delete('/{id}', [ConversationController::class, 'destroy']);

        Route::get('/{conversation}/participants/', [ConversationUserController::class, 'index']);
        Route::post('/participants/', [ConversationUserController::class, 'store']);
        Route::delete('/{conversation}/participants/{user}', [ConversationUserController::class, 'destroy']);

        Route::get('/{converId}/messages', [ConversationController::class, 'getMessages']);
        Route::post('/{converId}/messages', [ConversationController::class, 'sendMessage']);
        Route::put('/messages/{id}', [ConversationController::class, 'putMessage']);
        Route::delete('/messages/{id}', [ConversationController::class, 'delMessage']);
    });

});
