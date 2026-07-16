<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('title');
            $table->string('image_path')->nullable()->after('attachment_path');
        });

        // Backfill slugs for existing notices
        $notices = DB::table('notices')->get();
        foreach ($notices as $notice) {
            $slug = Str::slug($notice->title);
            // Ensure uniqueness
            $count = 1;
            $originalSlug = $slug;
            while (DB::table('notices')->where('slug', $slug)->where('id', '!=', $notice->id)->exists()) {
                $slug = $originalSlug.'-'.$count++;
            }
            DB::table('notices')->where('id', $notice->id)->update(['slug' => $slug]);
        }

        // Make slug non-nullable once populated
        Schema::table('notices', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropColumn(['slug', 'image_path']);
        });
    }
};
