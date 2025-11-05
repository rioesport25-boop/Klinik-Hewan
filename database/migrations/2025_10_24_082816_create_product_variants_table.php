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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name'); // Contoh: "Merah - L", "1kg", "500ml"
            $table->string('size')->nullable(); // Ukuran (S, M, L, XL, 1kg, 500g, dll)
            $table->string('color')->nullable(); // Warna
            $table->string('sku')->unique()->nullable(); // SKU khusus variant
            $table->decimal('price_adjustment', 15, 2)->default(0); // +/- dari harga dasar
            $table->integer('stock')->default(0); // Stok variant
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
