<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['button_text', 'button_url']);
        });

        Schema::table('banners', function (Blueprint $table) {
            // 'dark' | 'dark-heavy' | 'forest' | 'ink' | 'brass' | 'none'
            $table->string('overlay_color', 20)->default('dark')->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('overlay_color');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
        });
    }
};
