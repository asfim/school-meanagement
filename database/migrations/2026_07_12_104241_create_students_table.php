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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('full_name_en');
            $table->string('full_name_native');
            $table->date('dob');
            $table->string('gender');
            $table->string('parent_name');
            $table->string('parent_mobile');
            $table->text('permanent_address');
            $table->text('current_address');
            $table->string('class');
            $table->string('section');
            $table->integer('roll_number');
            $table->date('admission_date');
            $table->string('blood_group')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('emergency_contact');
            $table->string('status')->default('active'); // active, transferred, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
