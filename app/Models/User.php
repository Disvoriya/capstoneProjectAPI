<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'last_name',
        'first_name',
        'patronymic',
        'email',
        'password',
        'photo_file',
        'competence',
        'api_token',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        'created_at',
    ];

    protected function casts():array
    {
        return [
            'password'=>'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Определяет, какие задачи назначены пользователю
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, ProjectUser::class, 'user_id', 'assigned_to', 'id', 'id');
    }


    // Определяет, какой проект был создан пользователем
    public function projects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    // Определяет, в каких проектах пользователь участвует
    public function participatedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('id');
    }

    public function lastMessage()
    {
        return $this->hasMany(PrivateChatMessage::class, 'incoming_msg_id')
            ->where('outgoing_msg_id', Auth::id())
            ->orWhere('outgoing_msg_id', Auth::id())
            ->orderBy('created_at', 'desc');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_users', 'user_id', 'conversation_id');
    }

}
