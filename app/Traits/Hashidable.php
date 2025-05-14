<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- Tambahkan import ini (untuk gaya Laravel 9+)

trait Hashidable
{
    /**
     * Get the value of the model's route key.
     * Used for implicit route model binding.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        // Encode the primary key using the default Hashids connection
        return Hashids::encode($this->getKey());
    }

    /**
     * Retrieve the model for a bound value.
     * Used for implicit route model binding.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Decode the value using the default Hashids connection
        $decoded = Hashids::decode($value);

        // If decoding fails or returns an empty array, abort
        if (empty($decoded)) {
            // Anda bisa menambahkan pesan yang lebih deskriptif
            abort(404, 'Resource tidak ditemukan.');
        }

        // Find the model by the decoded ID using the appropriate key name
        return $this->where($field ?? $this->getKeyName(), $decoded[0])->firstOrFail();
    }

    /**
     * Accessor untuk mendapatkan atribut hashid secara langsung.
     * Ini memungkinkan Anda menggunakan $model->hashid di kode Anda (misal: controller, view).
     *
     * Menggunakan sintaks Attribute Laravel 9+ (Disarankan)
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function hashid(): Attribute // <-- TAMBAHKAN METHOD INI
    {
        return Attribute::make(
            get: fn() => Hashids::encode($this->getKey()),
            // Anda tidak perlu setter biasanya, jadi cukup 'get'
        );
    }

    /*
    // --- ATAU ---
    // Jika Anda menggunakan Laravel versi lebih lama atau preferensi lain:
    // Menggunakan sintaks accessor tradisional (Berfungsi di semua versi Laravel)
->
    public function getHashidAttribute() // <-- ATAU TAMBAHKAN METHOD INI
    {
        return Hashids::encode($this->getKey());
    }
    */
}
