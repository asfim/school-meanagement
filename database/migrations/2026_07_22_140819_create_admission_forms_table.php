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
        Schema::create('admission_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->string('phone_number');
            $table->string('program');
            $table->string('semester')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('parent_contact')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_forms');
    }
};
