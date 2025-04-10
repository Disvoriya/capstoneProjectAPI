<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
        'status',
        'terminated_at'
    ];

    protected $casts = [
        'permissions' => 'array',
        'joined_at' => 'datetime',
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];

    // Связь с моделью Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Связь с моделью User
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
