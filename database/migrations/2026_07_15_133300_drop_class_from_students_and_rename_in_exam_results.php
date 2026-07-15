<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'class')) {
                $table->dropColumn('class');
            }
        });

        Schema::table('exam_results', function (Blueprint $table) {
            if (Schema::hasColumn('exam_results', 'class')) {
                $table->renameColumn('class', 'program_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('class')->after('current_address')->nullable();
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->renameColumn('program_name', 'class');
        });
    }
};
