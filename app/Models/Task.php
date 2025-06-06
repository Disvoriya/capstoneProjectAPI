<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'due_date',
        'category_id',
        'status',
        'assigned_to',
        'author'
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];

    protected $casts = [

    ];

    // Определяет, к какому пользователю назначена задача
    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function projectUser()
    {
        return $this->belongsTo(ProjectUser::class, 'assigned_to');
    }
    public function authorRelation()
    {
        return $this->belongsTo(ProjectUser::class, 'author');
    }


    // Определяет, к какому проекту принадлежит задача
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Определяет отношение к категории задачи
    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

}
