<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('activity_type');
            $table->timestamp('activity_time')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_activity');
    }
};
