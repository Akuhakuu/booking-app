<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Item;     // Model Item
use App\Models\Customer; // Model Customer
use App\Models\Booking;  // Model Booking
use App\Models\Brand;    // Model Brand
use App\Models\Category; // Model Category
use App\Models\Payment;  // Model Payment

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard admin dengan data ringkasan.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Ambil data ringkasan (contoh)
        $totalItems = Item::count();
        $totalCustomers = Customer::count();
        $totalBrands = Brand::count();
        $totalCategories = Category::count();

        // Booking (contoh status, sesuaikan dengan status Anda)
        $pendingBookings = Booking::where('rental_status', 'pending_confirmation')->count();
        $activeBookings = Booking::where('rental_status', 'picked_up')->count(); // Misal booking yg sedang berjalan
        $completedBookings = Booking::where('rental_status', 'returned')->count();

        // Payment (contoh status)
        $verifiedPayments = Payment::where('transaction_status', 'settlement')->count();
        $pendingPayments = Payment::where('transaction_status', 'pending')->count();

        // Data terbaru (contoh)
        $recentBookings = Booking::with('customer', 'items') // Eager load relasi
            ->latest() // Urutkan dari terbaru
            ->take(5) // Ambil 5 terakhir
            ->get();

        // Kumpulkan semua data untuk dikirim ke view
        $data = [
            'totalItems' => $totalItems,
            'totalCustomers' => $totalCustomers,
            'totalBrands' => $totalBrands,
            'totalCategories' => $totalCategories,
            'pendingBookings' => $pendingBookings,
            'activeBookings' => $activeBookings,
            'completedBookings' => $completedBookings,
            'verifiedPayments' => $verifiedPayments,
            'pendingPayments' => $pendingPayments,
            'recentBookings' => $recentBookings,
        ];

        // Kembalikan view dashboard admin beserta datanya
        return view('admin.dashboard.index', $data);
    }
}
