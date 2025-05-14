<?php

// === NAMESPACE DIUBAH ===
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer; // Model Customer
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

// === NAMA CLASS DISESUAIKAN ===
class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:customer');
    }

    /**
     * Menampilkan form registrasi customer.
     */
    public function showRegistrationForm()
    {
        $genders = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
        // === PATH VIEW DIUBAH ===
        return view('customer.register', compact('genders'));
    }

    /**
     * Menangani request registrasi customer.
     */
    protected function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers', 'email')],
            'phone_number' => ['required', 'string', 'max:20'],
            'password' => ['required', Password::min(8), 'confirmed'],
            'address' => ['required', 'string'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
        ]);

        try {
            $customer = Customer::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone_number' => $validatedData['phone_number'],
                'password' => Hash::make($validatedData['password']),
                'address' => $validatedData['address'],
                'gender' => $validatedData['gender'],
                'status' => 'active',
            ]);

            // event(new Registered($customer)); // Uncomment jika perlu verifikasi email

            Auth::guard('customer')->login($customer);
            $request->session()->regenerate();

            return redirect()->route('customer.dashboard')->with('success', 'Registrasi berhasil! Selamat datang.'); // Pastikan route name benar

        } catch (\Exception $e) {
            Log::error('Customer Registration Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Registrasi gagal, terjadi kesalahan sistem.');
        }
    }
}
