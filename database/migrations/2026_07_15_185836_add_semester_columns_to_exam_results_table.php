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
        Schema::table('exam_results', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['student_id']);

            // Drop unique constraint
            $table->dropUnique('exam_results_student_id_exam_name_unique');

            // Add semester relations
            $table->foreignId('semester_id')->nullable()->after('student_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('semester_exam_id')->nullable()->after('semester_id')->constrained('semester_exams')->onDelete('cascade');

            // Set exam_name to nullable just in case
            $table->string('exam_name')->nullable()->change();

            // Re-add student_id foreign key
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');

            // Add new unique constraint
            $table->unique(['student_id', 'semester_id', 'semester_exam_id'], 'exam_results_stu_sem_exam_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropUnique('exam_results_stu_sem_exam_unique');
            $table->dropForeign(['student_id']);
            $table->dropForeign(['semester_id']);
            $table->dropForeign(['semester_exam_id']);
            $table->dropColumn(['semester_id', 'semester_exam_id']);

            $table->string('exam_name')->nullable(false)->change();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unique(['student_id', 'exam_name']);
        });
    }
};
