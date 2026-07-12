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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('category'); // exam, holiday, event, general, admission, urgent
            $table->string('attachment_path')->nullable();
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->string('target_audience')->default('all'); // all, students, teachers, parents
            $table->string('posted_by')->nullable();
            $table->string('status')->default('active'); // active, inactive, draft
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
