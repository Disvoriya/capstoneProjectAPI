<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\ConversationUserController;
use App\Http\Controllers\Chat\PrivateChatController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\CompanyUserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\ProjectUserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------|
| API Routes                                                               |
|--------------------------------------------------------------------------|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [UserController::class, 'show']);
    Route::get('/user/activity', [UserController::class, 'showUserActivity']);

    Route::prefix('settings')->group(function () {
        Route::post('/personal', [SettingsController::class, 'updatePersonal']);
        Route::post('/password', [SettingsController::class, 'updatePassword']);
        Route::post('/delete-account', [SettingsController::class, 'deleteAccount']);
        Route::post('/notifications', [SettingsController::class, 'updateNotificationSettings']);
    });

    Route::apiResource('projects', ProjectController::class);
    Route::prefix('projects')->group(function () {
        Route::post('/join', [ProjectUserController::class, 'join']);
        Route::apiResource('/participants', ProjectUserController::class);
    });

    Route::apiResource('tasks', TaskController::class);
    Route::get('/project/tasks/{projectId}', [TaskController::class, 'index']);

    Route::apiResource('attachments', AttachmentController::class);
    Route::get('/attachments/{id}/download', [AttachmentController::class, 'download']);

    Route::get('/user/notification', [NotificationsController::class, 'getNotifications']);
    Route::post('/notifications/mark-all-read', [NotificationsController::class, 'markAllAsRead']);
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

    Route::apiResource('company', CompanyController::class);
    Route::get('/my-companies', [CompanyUserController::class, 'getUserCompanies']); // Получить компании пользователя

    Route::prefix('company')->group(function () {
        Route::post('/join', [CompanyUserController::class, 'join']);
        Route::get('/{companyId}/request-leave', [CompanyUserController::class, 'requestLeave']);
        Route::get('/{companyId}/participants', [CompanyUserController::class, 'getCompanyUsers']); // Получить всех пользователей компании
        Route::post('/{companyId}/participant', [CompanyUserController::class, 'store']); // Создать запись
        Route::get('/{companyId}/participant', [CompanyUserController::class, 'show']); // Получить информацию о авторизованном пользователе в конкретной компании
        Route::put('/{companyId}/participant/{userId}', [CompanyUserController::class, 'update']);
        Route::delete('/{companyId}/{userId}', [CompanyUserController::class, 'destroy']); // Удалить пользователя из компании
    });

    Route::get('/upcoming-events', [EventController::class, 'upcomingEvents']);
    Route::get('/events', [EventController::class, 'monthlyEvents']);

});
