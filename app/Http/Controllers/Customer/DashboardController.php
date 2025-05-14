<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
// ... (use statements lain)
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\Item;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) { /* ... redirect ... */
        }
        /** @var \App\Models\Customer $customer */

        $activeBookingsCount = 0;
        $completedBookingsCount = 0;
        $recentBookings = collect();
        $errorMessage = null;

        try {
            // === PERBAIKI QUERY STATUS DI SINI ===
            $activeBookingsCount = $customer->bookings()
                ->where(function ($query) {
                    // Booking dianggap "aktif & proses" jika:
                    // 1. Pembayaran pending ATAU
                    // 2. Pembayaran sudah paid TAPI status rentalnya belum selesai/batal
                    $query->where('payment_status', 'pending')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('payment_status', 'paid')
                                ->whereNotIn('rental_status', ['returned', 'completed', 'cancelled_by_customer', 'cancelled_by_admin', 'cancelled_payment_issue']);
                        });
                })
                ->count();

            $completedBookingsCount = $customer->bookings()
                // Booking dianggap "selesai" jika status rentalnya sudah final dan pembayaran tidak gagal
                ->whereIn('rental_status', ['returned', 'completed'])
                ->whereNotIn('payment_status', ['failed', 'cancelled', 'expired', 'deny']) // Opsional, tergantung definisi "selesai" Anda
                ->count();
            // =====================================

            $recentBookings = $customer->bookings()
                ->with('items')
                ->latest()
                ->take(3)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching customer dashboard data for customer ID ' . $customer->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]); // Tambah trace
            $errorMessage = 'Gagal memuat data booking Anda saat ini. Silakan coba beberapa saat lagi atau hubungi support.';
        }

        $data = [
            'customer' => $customer,
            'activeBookingsCount' => $activeBookingsCount,
            'completedBookingsCount' => $completedBookingsCount,
            'recentBookings' => $recentBookings,
            'errorMessage' => $errorMessage,
        ];

        return view('customer.dashboard.index', $data);
    }
}
