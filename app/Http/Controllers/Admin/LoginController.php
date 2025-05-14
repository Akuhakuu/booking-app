<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // <-- Sesuaikan dengan path model User Anda

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login admin.
     */
    public function showLogin()
    {
        // Pastikan view ada di resources/views/admin/login.blade.php
        return view('admin.login');
    }

    /**
     * Menangani proses login admin.
     */
    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => ['required', 'email'], // Bisa array atau string
            'password' => ['required'],
        ]);

        // Ambil nilai 'remember' (true jika dicentang, false jika tidak)
        $remember = $request->filled('remember');

        // 2. Coba Autentikasi menggunakan guard 'web' (default)
        if (Auth::guard('web')->attempt($credentials, $remember)) {

            // 3. Cek Status User SETELAH berhasil autentikasi password
            $user = Auth::guard('web')->user(); // Ambil user yang sudah terautentikasi

            // Ganti 'active' sesuai dengan nilai status aktif di database Anda
            if ($user->status !== 'active') {
                // Jika status tidak aktif, logout paksa dan redirect kembali
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Kirim pesan error spesifik menggunakan session flash
                return back()
                    ->withInput($request->only('email', 'remember')) // Kirim kembali email & remember
                    ->with('error', 'Akun Anda saat ini tidak aktif. Hubungi administrator.');
            }

            // 4. Regenerasi Session & Redirect ke Dashboard jika login & status OK
            $request->session()->regenerate();

            // Pastikan route 'admin.dashboard' terdefinisi
            return redirect()->intended(route('admin.dashboard')); // Gunakan intended()

        }

        // 5. Jika autentikasi gagal (email/password salah)
        return back()
            ->withInput($request->only('email', 'remember')) // Kirim kembali email & remember
            ->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.', // Pesan error lebih spesifik
            ]);
    }

    /**
     * Menangani proses logout admin.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirect ke halaman login setelah logout
        return redirect()->route('admin.login')->with('success', 'Anda telah berhasil logout.');
    }
}
