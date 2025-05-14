<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Hashidable;       // Pastikan Trait ini ada dan diimport
use Vinkla\Hashids\Facades\Hashids; // <-- IMPORT HASHIDS FACADE (PENTING)

class User extends Authenticatable // implements MustVerifyEmail
{
    // Urutan trait bisa disesuaikan, pastikan Hashidable ada
    use HasApiTokens, HasFactory, Notifiable, Hashidable;

    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'address',
        'gender',
        'status',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * (Tambahkan jika ada tipe data spesifik yang perlu di-cast)
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime', // Eloquent handle ini otomatis, tapi bisa eksplisit
        'updated_at' => 'datetime', // Eloquent handle ini otomatis, tapi bisa eksplisit
    ];


    /**
     * Relasi: Booking yang di-handle oleh user (staff) ini.
     */
    public function handledBookings()
    {
        // Pastikan namespace model Booking sudah benar
        return $this->hasMany(Booking::class, 'user_id'); // Asumsi foreign key adalah user_id
    }

    // === METHOD UNTUK ROUTE MODEL BINDING DENGAN HASHID ===

    /**
     * Mengambil model untuk nilai terikat (Route Model Binding).
     * Menangani binding kustom untuk kunci 'hashid'.
     *
     * @param  mixed  $value  Nilai hashid dari parameter route ({user:hashid})
     * @param  string|null  $field  Nama field binding ('hashid' dalam kasus ini)
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Hanya proses jika field binding-nya adalah 'hashid'
        if ($field === 'hashid') {
            $decodedId = Hashids::decode($value);

            // Jika decode gagal atau kosong, return null (Laravel akan handle 404)
            if (empty($decodedId)) {
                return null;
            }

            $id = $decodedId[0]; // Ambil ID asli

            // Cari berdasarkan ID asli menggunakan primary key ('id')
            return $this->find($id);
        }

        // Jika field bukan 'hashid', gunakan logic default parent (biasanya cari by 'id')
        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Menentukan nama kunci default untuk route.
     * Ini penting agar binding default (misalnya {user} tanpa :hashid)
     * tetap mencari berdasarkan primary key ('id').
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id'; // Default route key adalah 'id'
    }
    // =======================================================
}
