<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Hashidable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException; // Opsional

class Booking extends Model
{
    use HasFactory, Hashidable;

    protected $table = 'bookings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_code',
        'customer_id',
        'user_id',          // Untuk admin yang memproses, bisa null di awal
        'start_date',       // Tanggal mulai sewa
        'start_time',       // Waktu mulai sewa (pengambilan)
        'end_date',         // Ekspektasi tanggal pengembalian (start_date + rental_days)
        'return_date',      // Aktual tanggal pengembalian (diisi admin)
        'return_time',      // Aktual waktu pengembalian (diisi admin)
        'total_price',
        'payment_status',
        'rental_status',
        'notes',            // Catatan dari customer
        'admin_notes',      // Catatan dari admin
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date'    => 'datetime:Y-m-d', // Hanya tanggal
        'start_time'    => 'datetime:H:i',   // Hanya jam:menit (disimpan sebagai TIME di DB)
        'end_date'      => 'datetime:Y-m-d', // Hanya tanggal
        'return_date'   => 'datetime:Y-m-d', // Hanya tanggal, nullable
        'return_time'   => 'datetime:H:i',   // Hanya jam:menit, nullable (disimpan sebagai TIME di DB)
        'total_price'   => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'booking_items')
            ->withPivot('quantity', 'price_per_item')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    public function latestSuccessfulPayment()
    {
        return $this->hasOne(Payment::class)->whereIn('transaction_status', ['settlement', 'capture'])->latestOfMany();
    }

    public function latestPendingPayment()
    {
        return $this->hasOne(Payment::class)->where('transaction_status', 'pending')->latestOfMany();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'hashid') {
            $decodedId = Hashids::decode($value);
            if (empty($decodedId)) {
                return null;
            }
            $id = $decodedId[0];
            return $this->find($id);
        }
        return parent::resolveRouteBinding($value, $field);
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
}
