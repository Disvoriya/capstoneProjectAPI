<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->enum('theme', ['light', 'dark'])->default('light');
            $table->enum('language', ['en', 'ru', 'fr', 'es'])->default('ru');
            // $table->string('timezone')->default('UTC');

            $table->json('notifications')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
