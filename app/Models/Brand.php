<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Hashidable; // <-- Pastikan ini di-import


class Brand extends Model
{
    use HasFactory, Hashidable; // <-- Pastikan ini digunakan

    /**
     * The table associated with the model.
     */
    protected $table = 'brands';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Relasi: Item yang termasuk dalam brand ini.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Accessor 'hashid' biasanya sudah disediakan oleh Trait Hashidable
    // public function getHashidAttribute() { ... } // Tidak perlu manual umumnya
}
