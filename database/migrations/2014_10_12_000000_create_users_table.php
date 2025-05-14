<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Lebih umum 'name' saja
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone_number')->nullable(); // Mungkin boleh kosong?
            $table->text('address')->nullable(); // text jika bisa panjang, mungkin boleh kosong?
            $table->string('gender')->nullable(); // Mungkin boleh kosong?
            $table->string('status')->default('active'); // Default status?
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
