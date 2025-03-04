<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'user_activity'; // Укажите имя таблицы
    protected $fillable = ['user_id', 'activity_type', 'activity_time'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
