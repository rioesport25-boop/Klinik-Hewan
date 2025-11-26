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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Nama hewan
            $table->enum('species', ['dog', 'cat', 'bird', 'rabbit', 'hamster', 'other']); // Jenis hewan
            $table->string('breed')->nullable(); // Ras
            $table->date('birth_date')->nullable(); // Tanggal lahir
            $table->decimal('weight', 5, 2)->nullable(); // Berat (kg)
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('color')->nullable();
            $table->string('photo')->nullable();
            $table->text('medical_history')->nullable(); // Riwayat medis
            $table->text('allergies')->nullable(); // Alergi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
