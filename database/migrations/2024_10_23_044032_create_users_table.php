<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('patronymic')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('photo_file')->nullable();
            $table->enum('competence', ['UI/UX Дизайнер', 'Разработчик',
                'Контент-менеджер', 'Маркетолог', 'Тестировщик', 'Проектный менеджер',
                'Аналитик', 'Системный администратор'])
                ->default('Тестировщик');
            $table->string('api_token')->nullable();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade')->default(2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
