<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan customer yg login
use Illuminate\Support\Facades\Hash;  // Untuk hashing password baru
use App\Models\Customer; // Model Customer
use Illuminate\Validation\Rule;      // Untuk Rule validasi
use Illuminate\Validation\Rules\Password; // Untuk aturan password
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Semua method di controller ini memerlukan autentikasi customer.
     */
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * Menampilkan halaman edit profil customer.
     *
     * @return \Illuminate\View\View
     */
    public function edit(): View
    {
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        $genders = ['male' => 'Laki-laki', 'female' => 'Perempuan', 'other' => 'Lainnya']; // Pilihan gender

        // Anda perlu membuat view: resources/views/customer/profile/edit.blade.php
        return view('customer.profile.edit', compact('customer', 'genders'));
    }

    /**
     * Mengupdate data profil customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        // Validasi data input
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Email tidak divalidasi untuk diubah, hanya ditampilkan
            'phone_number' => ['required', 'string', 'max:20'], // Sesuaikan validasi nomor HP
            'address' => ['required', 'string', 'max:1000'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            // Password opsional, tapi jika diisi harus valid dan ada konfirmasi
            'current_password' => ['nullable', 'required_with:new_password', 'current_password:customer'], // Cek password lama jika password baru diisi
            'new_password' => ['nullable', 'required_with:current_password', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ], [
            'current_password.current_password' => 'Password lama yang Anda masukkan salah.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        DB::beginTransaction();
        try {
            // Update data profil dasar
            $customer->name = $validatedData['name'];
            $customer->phone_number = $validatedData['phone_number'];
            $customer->address = $validatedData['address'];
            $customer->gender = $validatedData['gender'];

            // Update password jika field new_password diisi dan valid
            if (!empty($validatedData['new_password'])) {
                $customer->password = Hash::make($validatedData['new_password']);
            }

            $customer->save(); // Simpan semua perubahan

            DB::commit();

            Log::info("Customer profile updated successfully for customer ID: {$customer->id}");
            return redirect()->route('customer.profile.edit') // Kembali ke halaman edit profil
                ->with('success', 'Profil Anda berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating customer profile: ' . $e->getMessage(), ['customer_id' => $customer->id]);
            return redirect()->back()
                ->withInput() // Kembalikan input lama
                ->with('error', 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage());
        }
    }
}
