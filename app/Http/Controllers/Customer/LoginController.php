<?php

// === NAMESPACE DIUBAH ===
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// === NAMA CLASS DISESUAIKAN ===
class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:customer')->except('logout');
    }

    /**
     * Menampilkan form login customer.
     */
    public function showLoginForm()
    {
        // === PATH VIEW DIUBAH ===
        return view('customer.login');
    }

    /**
     * Menangani proses login customer.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::guard('customer')->attempt($credentials, $remember)) {
            $customer = Auth::guard('customer')->user();

            if ($customer->status !== 'active') {
                Auth::guard('customer')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => __('Akun Anda saat ini tidak aktif.'),
                ])->redirectTo(route('customer.login')); // Pastikan route name benar
            }

            $request->session()->regenerate();
            return redirect()->intended(route('customer.dashboard')); // Pastikan route name benar
        }

        throw ValidationException::withMessages([
            'email' => __('Email atau password yang Anda masukkan salah.'),
        ])->redirectTo(route('customer.login'));
    }

    /**
     * Menangani proses logout customer.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout(); // Pastikan guard 'customer' digunakan

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Arahkan ke halaman login customer setelah logout
        return redirect()->route('customer.login')->with('success', 'Anda telah berhasil logout.');
    }
}
