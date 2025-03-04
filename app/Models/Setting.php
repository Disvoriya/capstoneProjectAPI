<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme',
        'language',
        'notifications'
    ];


    /*
    
    $setting = Setting::create([
        'user_id' => $userId,
        'theme' => 'light',
        'language' => 'ru',
        'notifications' => json_encode(['task_updated' => true, 'task_deadline' => true]), // Установите значение при создании
    ]);
        
    */
}
