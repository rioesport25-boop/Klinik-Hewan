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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('specifications')->nullable(); // Detail spesifikasi produk
            $table->decimal('price', 15, 2); // Harga dasar
            $table->decimal('compare_price', 15, 2)->nullable(); // Harga coret (untuk diskon)
            $table->integer('stock')->default(0); // Total stok (sum dari variants)
            $table->string('sku')->unique()->nullable(); // Stock Keeping Unit
            $table->decimal('weight', 10, 2)->nullable(); // Berat (gram) - untuk ongkir
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Produk unggulan
            $table->integer('view_count')->default(0); // Jumlah view
            $table->integer('order_count')->default(0); // Jumlah terjual
            $table->decimal('rating_average', 3, 2)->default(0); // Rating rata-rata
            $table->integer('review_count')->default(0); // Jumlah review
            $table->timestamps();
            $table->softDeletes(); // Soft delete untuk history
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
