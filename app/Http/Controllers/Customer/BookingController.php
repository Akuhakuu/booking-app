<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Item;
use App\Models\CartItem;
use App\Models\Customer; // Untuk PHPDoc
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    public function processCheckout(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        $validatedCheckout = $request->validate([
            'selected_items'    => 'required|array|min:1',
            'selected_items.*'  => 'required|string',
            'quantities'        => 'required|array',
            'quantities.*'      => 'required|integer|min:1',
            'start_date'        => 'required|date|after_or_equal:today',
            'start_time'        => 'required|date_format:H:i', // Validasi untuk start_time
            'rental_days'       => 'required|integer|min:1',
            'notes_booking'     => 'nullable|string|max:1000',
        ], [
            'selected_items.required' => 'Anda harus memilih setidaknya satu item untuk di-booking.',
            'start_date.after_or_equal' => 'Tanggal mulai sewa tidak boleh tanggal yang sudah lewat.',
            'start_time.required' => 'Waktu mulai sewa (pengambilan) harus diisi.',
            'start_time.date_format' => 'Format waktu mulai sewa tidak valid (contoh: 09:30).',
            'rental_days.min' => 'Durasi sewa minimal 1 hari.',
            'quantities.*.min' => 'Jumlah item minimal 1.',
        ]);

        $selectedCartItemHashes = $validatedCheckout['selected_items'];
        $inputQuantities = $validatedCheckout['quantities'];
        $rentalDays = (int) $validatedCheckout['rental_days'];

        $itemsForBookingPivot = [];
        $finalTotalPrice = 0;

        DB::beginTransaction();
        try {
            // ... (Logika validasi item, stok, harga tetap sama)
            foreach ($selectedCartItemHashes as $cartItemHash) {
                $decodedCartItemId = Hashids::decode($cartItemHash);
                if (empty($decodedCartItemId)) {
                    throw new \Exception("Format ID keranjang tidak valid: {$cartItemHash}.");
                }
                $cartItemId = $decodedCartItemId[0];

                $quantityForItem = $inputQuantities[$cartItemHash] ?? null;
                if (is_null($quantityForItem) || !is_numeric($quantityForItem) || $quantityForItem < 1) {
                    throw new \Exception("Jumlah item keranjang tidak valid.");
                }
                $quantityForItem = (int) $quantityForItem;

                $cartItem = $customer->cartItems()->with('item')->find($cartItemId);

                if (!$cartItem || !$cartItem->item) {
                    throw new \Exception("Item keranjang (ID:{$cartItemId}) tidak ditemukan/item asli dihapus.");
                }
                $itemMaster = $cartItem->item;

                if ($itemMaster->status !== 'available') {
                    throw new \Exception("Item '{$itemMaster->name}' tidak tersedia.");
                }
                if ($quantityForItem > $itemMaster->stock) {
                    throw new \Exception("Stok '{$itemMaster->name}' tidak cukup (diminta: {$quantityForItem}, tersedia: {$itemMaster->stock}).");
                }
                if ($quantityForItem > CartItem::MAX_QUANTITY) {
                    throw new \Exception("Jumlah maks '{$itemMaster->name}' adalah " . CartItem::MAX_QUANTITY . ".");
                }

                $pricePerDay = $itemMaster->rental_price;
                $subTotal = $pricePerDay * $quantityForItem * $rentalDays;
                $finalTotalPrice += $subTotal;

                $itemsForBookingPivot[$itemMaster->id] = ['quantity' => $quantityForItem, 'price_per_item' => $pricePerDay];
            }

            if (empty($itemsForBookingPivot)) {
                DB::rollBack();
                return redirect()->route('customer.cart.index')->with('error', 'Tidak ada item valid yang dipilih untuk diproses booking.');
            }

            $startDate = Carbon::parse($validatedCheckout['start_date'])->startOfDay(); // Ambil tanggal saja
            // end_date adalah tanggal ekspektasi pengembalian
            // Jika sewa 1 hari (rental_days = 1), mulai tgl 10, kembali tgl 11.
            $endDate = $startDate->copy()->addDays($rentalDays)->startOfDay();

            $booking = Booking::create([
                'booking_code'   => 'RENT-' . strtoupper(Str::random(8)) . '-' . substr(time(), -4),
                'customer_id'    => $customer->id,
                'start_date'     => $startDate,
                'start_time'     => $validatedCheckout['start_time'], // Simpan start_time
                'end_date'       => $endDate, // Ekspektasi tanggal kembali
                // return_date dan return_time akan NULL by default (diisi Admin nanti)
                'total_price'    => $finalTotalPrice,
                'payment_status' => 'pending',
                'rental_status'  => 'pending_confirmation',
                'notes'          => $validatedCheckout['notes_booking'],
            ]);

            $booking->items()->attach($itemsForBookingPivot);

            $cartItemIdsToDelete = [];
            foreach ($selectedCartItemHashes as $hash) {
                $decoded = Hashids::decode($hash);
                if (!empty($decoded)) $cartItemIdsToDelete[] = $decoded[0];
            }
            if (!empty($cartItemIdsToDelete)) {
                $customer->cartItems()->whereIn('id', $cartItemIdsToDelete)->delete();
            }

            DB::commit();

            Log::info("Booking {$booking->booking_code} created by customer ID: {$customer->id}. Redirecting to initiate payment.");
            return redirect()->route('customer.payment.initiate', ['booking_hashid' => $booking->hashid]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Booking Checkout Process Error: " . $e->getMessage(), [
                'customer_id' => $customer->id,
                'request_data' => $request->except(['password', '_token', '_method', 'password_confirmation'])
            ]);
            return redirect()->route('customer.cart.index')->with('error', 'Checkout Gagal: ' . $e->getMessage());
        }
    }

    public function myBookings(): View
    {
        return view('customer.bookings.index');
    }

    public function getMyBookingsData(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        $bookingsQuery = $customer->bookings()
            ->select([
                'id',
                'booking_code',
                'start_date',
                'start_time', // Tambahkan start_time
                'end_date', // Ini adalah ekspektasi tanggal kembali
                // 'return_date', 'return_time', // Biarkan ini untuk halaman detail dulu
                'total_price',
                'payment_status',
                'rental_status',
                'created_at'
            ])
            ->withCount('items');

        return DataTables::of($bookingsQuery)
            ->addIndexColumn()
            ->editColumn('start_date', function ($booking) {
                return $booking->start_date ? $booking->start_date->format('d M Y') : '-';
            })
            ->editColumn('start_time', function ($booking) { // Format start_time
                return $booking->start_time ? $booking->start_time->format('H:i') . ' WIB' : '-';
            })
            ->editColumn('end_date', function ($booking) { // Ini "Ekspektasi Kembali"
                return $booking->end_date ? $booking->end_date->format('d M Y') : '-';
            })
            ->addColumn('duration', function ($booking) {
                if ($booking->start_date && $booking->end_date) {
                    return $booking->start_date->diffInDays($booking->end_date) . ' Hari';
                }
                return '-';
            })
            ->editColumn('total_price', function ($booking) {
                return 'Rp' . number_format($booking->total_price, 0, ',', '.');
            })
            ->editColumn('payment_status', function ($booking) {
                // ... (logika badge status pembayaran tetap sama)
                $status = $booking->payment_status ?? 'unknown';
                $color = 'secondary';
                if ($status == 'pending') $color = 'warning';
                elseif ($status == 'paid') $color = 'success';
                elseif (in_array($status, ['failed', 'cancelled', 'expired', 'deny'])) $color = 'danger';
                elseif ($status == 'challenge') $color = 'info';
                return '<span class="badge bg-light-' . $color . ' status-badge">' . ucwords(str_replace('_', ' ', $status)) . '</span>';
            })
            ->addColumn('rental_status_display', function ($booking) {
                // ... (logika badge status rental, bisa disesuaikan jika perlu membandingkan dengan return_date)
                $rentalStatus = $booking->rental_status;
                $rentalColor = 'secondary';
                $rentalStatusDisplay = ucwords(str_replace('_', ' ', $rentalStatus));
                // Untuk cek telat, gunakan end_date (ekspektasi) karena return_date belum tentu ada
                if (
                    $booking->end_date && // Pastikan end_date ada
                    Carbon::now()->startOfDay()->gt($booking->end_date->startOfDay()) &&
                    !in_array($rentalStatus, ['returned', 'completed', 'cancelled_by_customer', 'cancelled_by_admin', 'cancelled_payment_issue'])
                ) {
                    $rentalStatusDisplay = 'Telat Dikembalikan'; // Berdasarkan ekspektasi
                    $rentalColor = 'danger';
                } else {
                    if (in_array($rentalStatus, ['pending_confirmation', 'pending_review'])) $rentalColor = 'warning';
                    elseif (in_array($rentalStatus, ['confirmed', 'ready_to_pickup'])) $rentalColor = 'info';
                    elseif (in_array($rentalStatus, ['picked_up', 'active'])) $rentalColor = 'primary';
                    elseif (in_array($rentalStatus, ['returned', 'completed'])) $rentalColor = 'success';
                    elseif (str_contains($rentalStatus, 'cancelled')) $rentalColor = 'danger';
                }
                return '<span class="badge bg-light-' . $rentalColor . ' status-badge">' . $rentalStatusDisplay . '</span>';
            })
            ->addColumn('action', function ($booking) {
                // ... (logika tombol aksi tetap sama)
                $bookingHash = $booking->hashid;
                if (empty($bookingHash)) {
                    Log::error("HashID kosong untuk Booking ID: {$booking->id} di getMyBookingsData");
                    return '<span class="text-danger">Error ID</span>';
                }
                $detailUrl = route('customer.bookings.show', ['booking_hashid' => $bookingHash]);
                $payUrlHtml = '';
                if ($booking->payment_status == 'pending' && !in_array($booking->rental_status, ['cancelled_by_customer', 'cancelled_by_admin', 'cancelled_payment_issue'])) {
                    $payUrl = route('customer.payment.initiate', ['booking_hashid' => $bookingHash]);
                    $payUrlHtml = '<a href="' . $payUrl . '" class="btn btn-sm btn-success ms-1" title="Bayar Sekarang"><i class="bi bi-credit-card-fill"></i></a>';
                }
                return '<a href="' . $detailUrl . '" class="btn btn-sm btn-outline-info" title="Lihat Detail"><i class="bi bi-eye-fill"></i></a>' . $payUrlHtml;
            })
            ->rawColumns(['payment_status', 'rental_status_display', 'action'])
            ->make(true);
    }

    public function showMyBooking($booking_hashid): View|\Illuminate\Http\RedirectResponse
    {
        $decodedBookingId = Hashids::decode($booking_hashid);
        if (empty($decodedBookingId)) {
            return redirect()->route('customer.dashboard')->with('error', 'Format ID Booking tidak valid.');
        }
        $bookingId = $decodedBookingId[0];
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        try {
            $booking = $customer->bookings()
                ->with([
                    'items',
                    'items.brand',
                    'items.category',
                    'payments' => fn($q) => $q->orderBy('created_at', 'desc')
                ])
                ->findOrFail($bookingId);
            // Data start_time, end_date, return_date, return_time akan otomatis ter-load

            return view('customer.bookings.show', compact('booking'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Booking yang Anda cari tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error("Error showing customer booking detail: " . $e->getMessage(), ['booking_id' => $bookingId, 'customer_id' => $customer->id]);
            return redirect()->route('customer.dashboard')
                ->with('error', 'Terjadi kesalahan saat menampilkan detail booking.');
        }
    }
}
