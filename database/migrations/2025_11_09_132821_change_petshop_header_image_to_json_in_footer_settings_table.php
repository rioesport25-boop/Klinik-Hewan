<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->json('petshop_header_images')->nullable()->after('petshop_header_image');
        });

        // Copy existing data if any
        DB::statement('UPDATE footer_settings SET petshop_header_images = JSON_ARRAY(petshop_header_image) WHERE petshop_header_image IS NOT NULL');

        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn('petshop_header_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->string('petshop_header_image')->nullable()->after('gallery_parallax_bg');
        });

        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn('petshop_header_images');
        });
    }
};
