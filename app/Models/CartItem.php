<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Hashidable; // <-- IMPORT TRAIT HASHIDABLE

class CartItem extends Model
{
    use HasFactory, Hashidable; // <-- GUNAKAN TRAIT HASHIDABLE
    
    public const MAX_QUANTITY = 100; // Ganti nama jadi lebih umum
    // ... sisa model ..
    protected $table = 'cart_items';

    protected $fillable = [
        'customer_id',
        'item_id',
        'quantity',
    ];

    /**
     * Relasi: CartItem ini milik Customer mana.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relasi: CartItem ini merujuk ke Item mana.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Accessor $cartItem->hashid akan otomatis ada dari trait
}
