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
            ['name' => 'Разработка'],
            ['name' => 'Программирование'],
            ['name' => 'Веб-дизайн'],
            ['name' => 'UI/UX'],
            ['name' => 'Тестирование'],
            ['name' => 'Проектный менеджмент'],
            ['name' => 'Документация'],
            ['name' => 'Аналитика'],
            ['name' => 'DevOps'],
            ['name' => 'Исследования и прототипирование'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('task_categories');
    }
}
