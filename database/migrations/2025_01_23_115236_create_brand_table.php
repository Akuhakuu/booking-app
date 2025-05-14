<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Nama tabel sebaiknya plural: 'brands'
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nama brand sebaiknya unik & wajib
            $table->timestamps();
        });
    }

    public function down()
    {
        // Harus drop tabel yang benar
        Schema::dropIfExists('brands');
    }
};
