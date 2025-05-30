<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->foreignId('category_id')->constrained('task_categories')->onDelete('cascade');
            $table->enum('status', ['to_do', 'in_progress', 'on_correction', 'done'])->default('to_do');
            $table->foreignId('assigned_to')->constrained('project_user')->onDelete('cascade');
            $table->foreignId('author')->constrained('project_user')->onDelete('cascade');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
