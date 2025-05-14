<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Hashidable;       // Pastikan ini di-import
use Vinkla\Hashids\Facades\Hashids; // <-- IMPORT HASHIDS FACADE
use Illuminate\Database\Eloquent\ModelNotFoundException; // Untuk findOrFail (opsional)

class Category extends Model
{
    use HasFactory, Hashidable; // Pastikan Hashidable digunakan

    /**
     * The table associated with the model.
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Relasi: Item yang termasuk dalam kategori ini.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // === TAMBAHKAN METHOD INI UNTUK ROUTE MODEL BINDING DENGAN HASHID ===
    /**
     * Mengambil model untuk nilai terikat (Route Model Binding).
     * Menangani binding kustom untuk kunci 'hashid'.
     *
     * @param  mixed  $value  Nilai hashid dari parameter route ({category:hashid})
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
            return $this->find($id); // find() akan return null jika tidak ketemu
            // Atau jika ingin langsung 404 jika ID decode tidak ada:
            // return $this->findOrFail($id);
        }

        // Jika field bukan 'hashid', gunakan logic default parent (biasanya cari by 'id')
        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Menentukan nama kunci default untuk route.
     * (Penting agar binding default {category} tetap pakai 'id')
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id'; // Default route key adalah 'id'
    }
    // =======================================================================
}
