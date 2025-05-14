<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Jika perlu factory
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import BelongsToMany relation
use App\Traits\Hashidable; // Import Trait jika ingin ID pivot bisa di-hash

class BookingItem extends Pivot
{
    use HasFactory, Hashidable; // Hashidable opsional untuk pivot

    /**
     * The table associated with the model.
     */
    protected $table = 'booking_items';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true; // Karena pakai ->id() di migrasi

    /**
     * The attributes that are mass assignable.
     * (Hanya kolom tambahan di pivot, BUKAN foreign key)
     */
    protected $fillable = [
        'quantity',
        'price_per_item',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price_per_item' => 'decimal:2',
    ];

    // Relasi ke Booking atau Item bisa ditambahkan di sini jika perlu
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'booking_items') // Nama tabel pivot
            ->withPivot('quantity', 'price_per_item')    // <-- INI KUNCINYA
            ->withTimestamps();                           // Opsional untuk created_at/updated_at di pivot
    }
}
