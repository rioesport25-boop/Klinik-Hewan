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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama hari libur (e.g., "Hari Raya Idul Fitri")
            $table->date('date'); // Tanggal libur
            $table->text('description')->nullable(); // Keterangan tambahan
            $table->enum('type', ['national', 'religious', 'custom'])->default('national'); // Jenis libur
            $table->boolean('is_active')->default(true); // Status aktif
            $table->boolean('is_recurring')->default(false); // Apakah berulang setiap tahun
            $table->string('color')->default('#ef4444'); // Warna badge (default merah)
            $table->timestamps();

            // Index untuk performa query
            $table->index('date');
            $table->index(['is_active', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
