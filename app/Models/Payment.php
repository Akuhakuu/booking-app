<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Hashidable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinkla\Hashids\Facades\Hashids; // <-- Import Hashids

class Payment extends Model
{
    use HasFactory, Hashidable;

    protected $table = 'payments';

    protected $fillable = [
        'booking_id',
        'customer_id',
        'payment_gateway_order_id',
        'midtrans_transaction_id',
        'payment_type',
        'transaction_status', // Ini status dari Midtrans
        'gross_amount',
        'transaction_time',
        'midtrans_response_payload',
        'notes', // Catatan admin di record payment ini
        // 'status', // Jika Anda ingin status internal terpisah dari Midtrans, tambahkan ini.
        // Tapi untuk sekarang, kita pakai transaction_status dari Midtrans.
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'transaction_time' => 'datetime',
        'midtrans_response_payload' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo // Relasi opsional, bisa via booking
    {
        return $this->belongsTo(Customer::class);
    }

    // === UNTUK ROUTE MODEL BINDING {payment:hashid} ===
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'hashid') {
            $decodedId = Hashids::decode($value);
            if (empty($decodedId)) {
                return null;
            }
            return $this->find($decodedId[0]);
        }
        return parent::resolveRouteBinding($value, $field);
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
    // =================================================
}
