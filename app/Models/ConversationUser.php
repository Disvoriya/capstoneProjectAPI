<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'invited_by',
        'invited_at',
    ];

}
