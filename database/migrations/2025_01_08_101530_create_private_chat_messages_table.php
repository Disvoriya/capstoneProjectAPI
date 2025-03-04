<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('private_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_msg_id')->constrained('users')->onDelete('cascade');
            //Внешний ключ для входящих сообщений, указывающий на пользователя, который получил сообщение.
            $table->foreignId('outgoing_msg_id')->constrained('users')->onDelete('cascade');
            //Внешний ключ для исходящих сообщений, указывающий на пользователя, который отправил сообщение
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('reply_to_id')->nullable()->constrained('private_chat_messages')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('private_chats');
    }
};
