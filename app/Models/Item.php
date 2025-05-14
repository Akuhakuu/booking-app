<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Hashidable;       // Pastikan ini di-import
use Vinkla\Hashids\Facades\Hashids; // <-- IMPORT HASHIDS FACADE
use Illuminate\Database\Eloquent\ModelNotFoundException; // Untuk findOrFail (opsional)
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Untuk relasi
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Untuk relasi
use Illuminate\Database\Eloquent\Relations\HasMany; // Untuk relasi

class Item extends Model
{
    use HasFactory, Hashidable; // Pastikan Hashidable digunakan

    protected $table = 'items';

    protected $fillable = [
        'name',
        'rental_price',
        'description',
        'category_id',
        'brand_id',
        'stock',
        'img',
        'status',
    ];

    protected $casts = [
        'rental_price' => 'decimal:2',
        'stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_items')
            ->withPivot('quantity', 'price_per_item')
            ->withTimestamps();
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // === TAMBAHKAN METHOD INI JIKA ROUTE ADMIN ITEMS MENGGUNAKAN {item:hashid} ===
    /**
     * Mengambil model untuk nilai terikat (Route Model Binding).
     * Menangani binding kustom untuk kunci 'hashid'.
     *
     * @param  mixed  $value  Nilai hashid dari parameter route ({item:hashid})
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
     * (Penting agar binding default {item} tetap pakai 'id')
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id'; // Default route key adalah 'id'
    }
    // =======================================================================
}
