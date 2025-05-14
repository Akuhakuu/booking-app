<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Pastikan tabel 'customers' dan 'users' sudah ada sebelum migrasi ini dijalankan.
        // Jika belum, urutkan file migrasi atau buat migrasinya terlebih dahulu.

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Admin yg handle

            // Detail Waktu Penyewaan
            $table->date('start_date');         // Tanggal mulai sewa (diisi customer)
            $table->time('start_time');         // Waktu mulai sewa/pengambilan (diisi customer)
            $table->date('end_date');           // Ekspektasi tanggal pengembalian (dihitung: start_date + rental_days)

            // Detail Waktu Pengembalian Aktual (diisi admin)
            $table->date('return_date')->nullable(); // Tanggal aktual barang dikembalikan
            $table->time('return_time')->nullable(); // Waktu aktual barang dikembalikan

            $table->decimal('total_price', 12, 2);

            // Status
            $table->string('payment_status')->default('pending'); // Contoh: pending, paid, failed, cancelled, expired, deny
            $table->string('rental_status')->default('pending_confirmation');
            // Contoh: pending_confirmation, confirmed, ready_to_pickup, picked_up, active,
            // returned, completed, completed_with_issue,
            // cancelled_by_customer, cancelled_by_admin, cancelled_payment_issue

            // Catatan
            $table->text('notes')->nullable();          // Catatan dari customer
            $table->text('admin_notes')->nullable();    // Catatan dari admin

            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
