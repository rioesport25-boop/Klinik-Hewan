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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->integer('rating'); // 1-5 stars
            $table->text('comment')->nullable();
            $table->enum('service_quality', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->enum('cleanliness', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->enum('friendliness', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->boolean('is_visible')->default(true); // Admin bisa hide jika inappropriate
            $table->timestamp('verified_at')->nullable(); // Verifikasi review asli
            $table->timestamps();
            
            $table->index(['doctor_id', 'is_visible']);
            $table->index(['rating', 'is_visible']);
            $table->unique('appointment_id'); // 1 appointment = 1 review
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
