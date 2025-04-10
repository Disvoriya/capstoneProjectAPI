<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'team_size',
        'logo',
        'industry',
        'invitation_code'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_users')
            ->withPivot(['role', 'status', 'permissions', 'joined_at'])
            ->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'company_id');
    }

}
