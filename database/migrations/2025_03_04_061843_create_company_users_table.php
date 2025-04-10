<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('company_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['Owner', 'Admin', 'Manager', 'Developer', 'Designer', 'HR', 'Other']);
            $table->json('permissions')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->dateTime('terminated_at')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('company_users');
    }
};
