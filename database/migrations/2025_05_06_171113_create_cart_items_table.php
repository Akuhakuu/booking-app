<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            // Relasi ke Customer (jika customer dihapus, item cart-nya juga hilang)
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            // Relasi ke Item
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete(); // Atau restrictOnDelete jika item tidak boleh dihapus saat ada di cart?
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps(); // created_at, updated_at

            // Pastikan kombinasi customer dan item unik, agar tidak ada duplikasi item yg sama per customer
            $table->unique(['customer_id', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
};
