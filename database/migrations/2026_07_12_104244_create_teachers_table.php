<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id')->unique();
            $table->string('full_name');
            $table->date('dob');
            $table->string('mobile');
            $table->string('email')->unique();
            $table->text('qualifications');
            $table->json('subjects')->nullable();
            $table->json('classes')->nullable();
            $table->date('date_of_joining');
            $table->string('designation');
            $table->json('salary_structure')->nullable();
            $table->text('address');
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
