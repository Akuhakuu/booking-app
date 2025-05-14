<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Untuk upload logo
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StoreController extends Controller
{
    // ID default untuk record toko (karena kita hanya punya 1)
    private const STORE_RECORD_ID = 1;

    /**
     * Menampilkan form untuk mengedit detail toko.
     * Jika belum ada data toko, akan dibuatkan record kosong.
     */
    public function edit(): View
    {
        // Coba ambil data toko, jika tidak ada, buat record baru dengan ID default
        $store = Store::firstOrCreate(
            ['id' => self::STORE_RECORD_ID],
            ['name' => config('app.name', 'Nama Toko Default')] // Nilai default saat pertama kali dibuat
        );

        return view('admin.store.edit', compact('store'));
    }

    /**
     * Mengupdate detail toko.
     */
    public function update(Request $request): RedirectResponse
    {
        $store = Store::find(self::STORE_RECORD_ID);
        if (!$store) {
            // Seharusnya tidak terjadi jika edit() sudah benar
            return redirect()->route('admin.store.edit')->with('error', 'Data toko tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048', // Max 2MB
            'tagline' => 'nullable|string|max:255',
            'operational_hours' => 'nullable|string|max:500',
        ]);

        // Handle upload logo
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($store->logo_path && Storage::disk('public')->exists($store->logo_path)) {
                Storage::disk('public')->delete($store->logo_path);
            }
            // Simpan logo baru
            $path = $request->file('logo')->store('store_logos', 'public');
            $validated['logo_path'] = $path;
        }

        // Jika checkbox remove_logo dicentang
        if ($request->boolean('remove_logo') && $store->logo_path) {
            if (Storage::disk('public')->exists($store->logo_path)) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $validated['logo_path'] = null;
        }


        $store->update($validated);

        return redirect()->route('admin.store.edit')->with('success', 'Informasi toko berhasil diperbarui.');
    }
}
