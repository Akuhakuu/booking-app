<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Rename tabel agar lebih standar: 'payments'
        // database/migrations/xxxx_xx_xx_xxxxxx_create_payments_table.php
        // database/migrations/xxxx_xx_xx_xxxxxx_create_payments_table.php
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            $table->string('payment_gateway_order_id')->unique(); // Order ID yang dikirim ke Midtrans (booking_code Anda + timestamp)
            $table->string('midtrans_transaction_id')->nullable()->unique(); // ID Transaksi dari Midtrans (setelah pembayaran)
            $table->string('payment_type')->nullable();      // Misal: qris, bank_transfer, credit_card
            $table->string('transaction_status');    // Status dari Midtrans: pending, settlement, expire, failure, dll.
            $table->decimal('gross_amount', 12, 2); // Jumlah yang dibayar
            $table->timestamp('transaction_time')->nullable(); // Waktu transaksi dari Midtrans
            $table->text('midtrans_response_payload')->nullable(); // Simpan full response dari Midtrans (untuk debug/audit)
            $table->timestamp('expiry_time')->nullable(); // Waktu kedaluwarsa pembayaran
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments'); // Nama tabel yang benar
    }
};
