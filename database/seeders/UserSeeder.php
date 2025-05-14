<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Seed data for Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'), // Gunakan bcrypt otomatis dari Laravel
            'phone_number' => '08123456789',
            'address' => 'Jl. Admin No. 1',
            'gender' => 'Laki-laki',
            'status' => 'active',
        ]);

        // Seed data for Customer
        Customer::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('customer123'), // Gunakan bcrypt otomatis dari Laravel
            'phone_number' => '08129876543',
            'address' => 'Jl. Customer No. 2',
            'gender' => 'Perempuan',
            'status' => 'active',
        ]);
    }
}
