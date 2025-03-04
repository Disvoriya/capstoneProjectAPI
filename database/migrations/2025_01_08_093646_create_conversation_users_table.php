<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversation_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('cascade');; // Кто пригласил
            $table->timestamp('invited_at')->useCurrent(); // Когда пригласили
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversation_users');
    }
};
