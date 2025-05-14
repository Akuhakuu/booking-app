<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $table = 'stores'; // Eksplisit jika nama tabel berbeda dari konvensi

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'tagline',
        'operational_hours',
        // tambahkan fillable lain jika ada
    ];

    // Opsional: Casts jika ada tipe data khusus
    // protected $casts = [
    //     'operational_hours' => 'array', // Jika disimpan sebagai JSON
    // ];
}
