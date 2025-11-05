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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Tripay Data
            $table->string('tripay_reference')->unique()->nullable(); // Reference dari Tripay
            $table->string('tripay_merchant_ref')->nullable(); // Merchant reference
            $table->string('payment_method')->nullable(); // QRIS, VA BCA, VA BNI, dll
            $table->string('payment_channel')->nullable(); // Channel code dari Tripay
            $table->string('payment_name')->nullable(); // Nama metode pembayaran

            // Informasi Pembayaran
            $table->decimal('amount', 15, 2); // Jumlah yang harus dibayar
            $table->decimal('fee', 15, 2)->default(0); // Fee payment gateway
            $table->decimal('total_amount', 15, 2); // Total termasuk fee

            // Status
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending');

            // Informasi Pembayaran (untuk VA/QRIS)
            $table->string('payment_code')->nullable(); // VA number atau QRIS code
            $table->string('qr_url')->nullable(); // URL QR Code
            $table->string('checkout_url')->nullable(); // URL checkout Tripay

            // Waktu
            $table->timestamp('expired_at')->nullable(); // Waktu expired
            $table->timestamp('paid_at')->nullable(); // Waktu dibayar

            // Response dari Tripay
            $table->json('tripay_response')->nullable(); // Simpan full response dari Tripay

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
