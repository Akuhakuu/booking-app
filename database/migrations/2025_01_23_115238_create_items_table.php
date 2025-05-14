<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama item wajib
            // Harga sewa per periode (misal per hari). Gunakan decimal untuk uang.
            // Angka pertama total digit, kedua jumlah digit di belakang koma
            $table->decimal('rental_price', 10, 2); // Ganti nama kolom agar lebih jelas
            $table->text('description')->nullable(); // Deskripsi boleh kosong, pakai text

            // Foreign key ke categories
            $table->foreignId('category_id')
                ->constrained('categories') // Mereferensikan tabel 'categories'
                ->onDelete('restrict'); // Jangan hapus kategori jika masih ada item terkait

            // Foreign key ke brands
            $table->foreignId('brand_id')
                ->constrained('brands') // Mereferensikan tabel 'brands'
                ->onDelete('restrict'); // Jangan hapus brand jika masih ada item terkait

            $table->unsignedInteger('stock')->default(0); // Stok harus angka non-negatif
            $table->string('img')->nullable(); // Path atau URL gambar, boleh kosong
            $table->string('status')->default('available'); // Status item: available, rented, maintenance, etc.
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items'); // Pastikan down() method ada
    }
};
