<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number'); // Wajib untuk kontak?
            $table->string('password'); // Jika customer bisa login
            $table->string('email')->unique();
            $table->text('address'); // Wajib? text jika bisa panjang
            $table->string('gender')->nullable();
            $table->string('status')->default('active');
            // Mungkin perlu email_verified_at juga jika mereka login?
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            // Note: Pertimbangkan gabung dengan tabel users jika fungsinya tumpang tindih
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers'); // Pastikan down() method ada
    }
};
