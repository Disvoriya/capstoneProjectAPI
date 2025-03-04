<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'picture',
        'name',
        'description',
        'creator_id',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_users', 'conversation_id', 'user_id')
            ->withPivot('invited_by', 'invited_at');
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ConversationMessage::class)->latestOfMany();
    }


}
