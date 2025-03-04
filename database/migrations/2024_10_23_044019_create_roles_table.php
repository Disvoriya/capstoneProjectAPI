<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id(); 
            $table->string('name', 100); 
            $table->string('code', 50); 
        });

        DB::table('roles')->insert([
            ['name' => 'Администратор', 'code' => 'admin'],
            ['name' => 'Пользователь', 'code' => 'user']
        ]);
    }


    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
