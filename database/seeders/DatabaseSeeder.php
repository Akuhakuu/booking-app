<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {

        $this->call([
            UserSeeder::class, // Tambahkan seeder ke sini
            CategorySeeder::class, // Tambahkan seeder ke sini
            BrandSeeder::class, // Tambahkan seeder ke sini
            ItemSeeder::class, // Tambahkan seeder ke sini
        ]);
    }
}
