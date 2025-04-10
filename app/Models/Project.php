<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'team_size',
        'note',
        'invitation_code',
        'created_by',
        'company_id',
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];

    // Определяет, какие задачи принадлежат проекту
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Определяет создателя проекта
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Определяет участников проекта
    public function participants()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('id');
    }

    // Определяет этапы пренадлежащие проекту
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
