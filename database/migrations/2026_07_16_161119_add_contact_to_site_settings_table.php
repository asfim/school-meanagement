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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('contact_address')->nullable()->after('about_stats');
            $table->string('contact_phone')->nullable()->after('contact_address');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->text('contact_hours')->nullable()->after('contact_email'); // Store working hours/days
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['contact_address', 'contact_phone', 'contact_email', 'contact_hours']);
        });
    }
};
