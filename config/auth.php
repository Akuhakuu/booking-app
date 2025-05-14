<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Opsi ini mengontrol guard autentikasi default dan broker reset password
    | untuk aplikasi Anda. Anda dapat mengubah default ini sesuai kebutuhan
    | tetapi ini adalah awal yang solid untuk sebagian besar aplikasi.
    |
    */

    'defaults' => [
        'guard' => 'web', // Guard default saat menggunakan Auth::user(), Auth::check(), dll.
        'passwords' => 'users', // Broker password reset default (untuk admin/user biasa)
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Selanjutnya, Anda dapat menentukan setiap guard autentikasi untuk aplikasi Anda.
    | Tentu saja, konfigurasi default yang hebat telah ditentukan untuk
    | Anda di sini yang menggunakan penyimpanan sesi dan provider pengguna Eloquent.
    |
    | Semua driver autentikasi memiliki provider pengguna. Ini mendefinisikan bagaimana
    | pengguna sebenarnya diambil dari database Anda atau mekanisme penyimpanan lain
    | yang digunakan oleh aplikasi ini untuk data pengguna persisten.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [ // Guard untuk Admin (dan mungkin user umum jika digabung nanti)
            'driver' => 'session',
            'provider' => 'users', // Menggunakan provider 'users' di bawah
        ],

        'customer' => [ // Guard KHUSUS untuk Customer
            'driver' => 'session',
            'provider' => 'customers', // Menggunakan provider 'customers' di bawah
        ],

        // 'api' => [ // Contoh guard untuk API jika Anda menggunakan token
        //     'driver' => 'token',
        //     'provider' => 'users',
        //     'hash' => false,
        // ],

        // 'sanctum' => [ // Contoh guard untuk Sanctum
        //    'driver' => 'sanctum',
        //    'provider' => null, // Sanctum menangani provider secara berbeda
        //],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Semua driver autentikasi memiliki provider pengguna. Ini mendefinisikan bagaimana
    | pengguna sebenarnya diambil dari database Anda atau mekanisme penyimpanan lain
    | yang digunakan oleh aplikasi ini untuk data pengguna persisten.
    |
    | Jika Anda memiliki beberapa tabel atau model pengguna, Anda dapat mengonfigurasi beberapa
    | sumber yang mewakili setiap model / tabel. Sumber-sumber ini kemudian dapat
    | ditetapkan ke guard autentikasi tambahan yang telah Anda tentukan.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [ // Provider untuk Admin (menggunakan model User)
            'driver' => 'eloquent',
            'model' => App\Models\User::class, // **PASTIKAN PATH INI BENAR** (sesuai model Admin/User Anda)
            // 'table' => 'users', // <-- Tidak perlu jika nama tabel standar (users) dan model = User
        ],

        'customers' => [ // Provider untuk Customer (menggunakan model Customer)
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class, // **PASTIKAN PATH INI BENAR** (sesuai model Customer Anda)
            // 'table' => 'customers', // <-- Tidak perlu jika nama tabel standar (customers) dan model = Customer
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Anda dapat menentukan beberapa konfigurasi reset password jika Anda memiliki lebih dari
    | satu tabel atau model pengguna di aplikasi dan Anda ingin memiliki pengaturan
    | reset password terpisah berdasarkan jenis pengguna tertentu.
    |
    | Waktu kedaluwarsa adalah jumlah menit token dianggap valid.
    | Opsi konfigurasi ini harus tetap tidak berubah kecuali Anda memiliki
    | persyaratan khusus aplikasi.
    |
    | Supported: "database", "eloquent"
    |
    */

    'passwords' => [
        // Broker reset password untuk Admin/User (provider 'users')
        'users' => [
            'provider' => 'users', // Harus cocok dengan nama provider di atas
            'table' => 'password_resets', // Tabel standar untuk menyimpan token reset
            'expire' => 60, // Token valid selama 60 menit
            'throttle' => 60, // Tunggu 60 detik sebelum mencoba request reset lagi
        ],

        // Broker reset password untuk Customer (provider 'customers')
        'customers' => [
            'provider' => 'customers', // Harus cocok dengan nama provider di atas
            'table' => 'password_resets', // Bisa menggunakan tabel yang sama
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan jumlah detik sebelum jendela konfirmasi
    | password kedaluwarsa dan pengguna diminta untuk memasukkan kembali password
    | mereka melalui layar konfirmasi password. Secara default, batas waktu
    | berlangsung selama tiga jam.
    |
    */

    'password_timeout' => 10800, // 3 jam dalam detik

];
