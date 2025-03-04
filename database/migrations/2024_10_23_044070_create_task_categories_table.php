<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTaskCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('task_categories')->insert([
            ['name' => 'Долгосрочные'],
            ['name' => 'Краткосрочные'],
            ['name' => 'Работа'],
            ['name' => 'Личные задачи'],
            ['name' => 'Учеба'],
            ['name' => 'Проекты'],
            ['name' => 'Здоровье и фитнес'],
            ['name' => 'Домашние дела'],
            ['name' => 'Развлечения'],
            ['name' => 'Другое'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('task_categories');
    }
}
