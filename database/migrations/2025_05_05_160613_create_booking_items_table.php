<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_items', function (Blueprint $table) {
            // Composite primary key bisa jadi opsi, tapi id biasa lebih mudah untuk Eloquent
            $table->id();

            // Foreign key ke bookings
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->onDelete('cascade'); // Jika booking dihapus, detail itemnya ikut terhapus

            // Foreign key ke items
            $table->foreignId('item_id')
                ->constrained('items')
                ->onDelete('restrict'); // Jangan hapus item jika masih ada di booking aktif?

            $table->unsignedInteger('quantity'); // Jumlah item ini yg dibooking
            $table->decimal('price_per_item', 10, 2); // Harga item saat booking (jaga2 jika harga item berubah)

            $table->timestamps(); // Kapan item ini ditambahkan ke booking

            // Pastikan kombinasi booking_id dan item_id unik untuk mencegah duplikasi
            $table->unique(['booking_id', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_items');
    }
};
