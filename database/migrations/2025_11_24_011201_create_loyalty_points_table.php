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
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points'); // Bisa positif (earn) atau negatif (redeem)
            $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted']);
            $table->string('source'); // appointment, referral, bonus, etc
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->date('expires_at')->nullable(); // Poin bisa expire
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_points');
    }
};
