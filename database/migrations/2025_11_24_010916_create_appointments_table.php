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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique(); // Kode booking unik
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->date('appointment_date'); // Tanggal kunjungan
            $table->time('appointment_time'); // Jam kunjungan
            $table->time('end_time')->nullable(); // Estimasi selesai
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('complaint')->nullable(); // Keluhan utama
            $table->text('diagnosis')->nullable(); // Diagnosis (diisi dokter)
            $table->text('treatment')->nullable(); // Tindakan (diisi dokter)
            $table->text('prescription')->nullable(); // Resep obat
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->decimal('total_cost', 10, 2)->nullable(); // Total biaya
            $table->integer('loyalty_points_earned')->default(0); // Poin didapat
            $table->timestamp('checked_in_at')->nullable(); // Waktu check-in
            $table->timestamp('completed_at')->nullable(); // Waktu selesai
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by')->nullable(); // user/admin
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['doctor_id', 'appointment_date']);
            $table->index(['appointment_date', 'appointment_time']);
            $table->index('booking_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
