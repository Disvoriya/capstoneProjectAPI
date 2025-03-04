<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'incoming_msg_id',
        'outgoing_msg_id',
        'message',
        'is_read',
        'reply_to_id',
        'read_at',
    ];

    // Связь с отправителем сообщения
    public function sender()
    {
        return $this->belongsTo(User::class, 'outgoing_msg_id');
    }

    // Связь с пользователем, которому адресовано сообщение
    public function receiver()
    {
        return $this->belongsTo(User::class, 'incoming_msg_id');
    }

    // Связь с сообщением, на которое был ответ
    public function replyTo()
    {
        return $this->belongsTo(PrivateChatMessage::class, 'reply_to_id')->with('sender');
    }
}
