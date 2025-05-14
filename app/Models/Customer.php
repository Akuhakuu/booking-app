<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Hashidable;       // Import Trait Hashidable Anda
use App\Models\Booking;
use App\Models\Payment;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids; // <-- IMPORT HASHIDS FACADE
use Illuminate\Database\Eloquent\ModelNotFoundException; // Untuk findOrFail (opsional)


class Customer extends Authenticatable
{
    use HasFactory, Notifiable, Hashidable;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'phone_number',
        'password',
        'email',
        'address',
        'gender',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function payments() // Sebaiknya tambahkan return type hint: HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Booking::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }


    // === TAMBAHKAN METHOD INI JIKA ROUTE ADMIN CUSTOMERS MENGGUNAKAN {customer:hashid} ===
    /**
     * Mengambil model untuk nilai terikat (Route Model Binding).
     * Menangani binding kustom untuk kunci 'hashid'.
     *
     * @param  mixed  $value  Nilai hashid dari parameter route ({customer:hashid})
     * @param  string|null  $field  Nama field binding ('hashid' dalam kasus ini)
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Hanya proses jika field binding-nya adalah 'hashid'
        if ($field === 'hashid') {
            $decodedId = Hashids::decode($value);

            // Jika decode gagal atau kosong, return null (Laravel handle 404)
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
     * (Penting agar binding default {customer} tetap pakai 'id')
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id'; // Default route key adalah 'id'
    }
    // ==============================================================================
}
