<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id(); // Meskipun hanya 1 baris, id tetap berguna
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable(); // Path ke file logo
            $table->text('tagline')->nullable();
            $table->text('operational_hours')->nullable(); // Jam operasional
            // Tambahkan kolom lain yang Anda perlukan (misal: NPWP, rekening bank, dll.)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stores');
    }
};
