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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('order_id')->nullable(); // Link ke order (tanpa constraint dulu)
            $table->integer('rating'); // 1-5 bintang
            $table->text('review')->nullable(); // Komentar
            $table->boolean('is_approved')->default(false); // Moderasi review
            $table->timestamps();

            // Satu user hanya bisa review satu kali per produk
            $table->unique(['product_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
