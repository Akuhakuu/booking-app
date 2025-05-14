<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Item;    // Digunakan untuk item_details Midtrans
use App\Models\Customer; // Digunakan untuk PHPDoc
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Midtrans\Config as MidtransConfig; // Alias untuk Midtrans Config
use Midtrans\Snap as MidtransSnap;     // Alias untuk Midtrans Snap
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;
use Carbon\Carbon;         // Untuk manipulasi tanggal
use Illuminate\Support\Str;  // Untuk Str::limit

class PaymentController extends Controller
{
    /**
     * Terapkan middleware auth:customer ke semua method controller ini.
     */
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * Menginisiasi proses pembayaran:
     * 1. Ambil detail booking.
     * 2. Generate Snap Token dari Midtrans dengan expiry 2 jam.
     * 3. Buat/Update record Payment awal.
     * 4. Redirect ke halaman yang akan menampilkan Midtrans Snap.
     *
     * @param  string $booking_hashid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiatePayment($booking_hashid)
    {
        $decodedBookingId = Hashids::decode($booking_hashid);
        if (empty($decodedBookingId)) {
            return redirect()->route('customer.dashboard')->with('error', 'Booking untuk pembayaran tidak valid.');
        }
        $bookingId = $decodedBookingId[0];
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        try {
            // Ambil booking yang akan dibayar, pastikan milik customer dan statusnya pending
            $booking = $customer->bookings()->with('items')->findOrFail($bookingId);

            if ($booking->payment_status !== 'pending') {
                return redirect()->route('customer.bookings.show', ['booking_hashid' => $booking->hashid])
                    ->with('info', 'Pembayaran untuk booking ini sudah diproses atau booking dibatalkan.');
            }

            // Siapkan detail item untuk Midtrans
            $itemDetailsMidtrans = [];
            // Hitung durasi dari tanggal booking
            $rentalDays = $booking->start_date->diffInDays($booking->end_date);
            if ($rentalDays == 0) $rentalDays = 1; // Pastikan minimal 1 hari untuk perhitungan

            foreach ($booking->items as $itemPivot) {
                $itemDetailsMidtrans[] = [
                    'id'       => $itemPivot->hashid, // Hashid item
                    'price'    => (int) $itemPivot->pivot->price_per_item, // Harga per hari (integer)
                    'quantity' => (int) ($itemPivot->pivot->quantity * $rentalDays), // Total unit dikali hari (integer)
                    'name'     => Str::limit($itemPivot->name . " ({$rentalDays} Hari)", 45)
                ];
            }

            // Midtrans memerlukan setidaknya satu item_details jika Anda mengirimkannya dan gross_amount > 0
            if (empty($itemDetailsMidtrans) && (int)$booking->total_price > 0) {
                Log::warning("No item details for Midtrans payment, but booking has total price.", ['booking_code' => $booking->booking_code]);
                throw new \Exception('Tidak ada detail item valid untuk dikirim ke gateway pembayaran.');
            }

            // Set konfigurasi Midtrans
            MidtransConfig::$serverKey = config('midtrans.server_key');
            MidtransConfig::$isProduction = config('midtrans.is_production');
            MidtransConfig::$isSanitized = config('midtrans.is_sanitized');
            MidtransConfig::$is3ds = config('midtrans.is_3ds');

            // Buat order_id yang unik untuk setiap upaya pembayaran Snap
            $midtransOrderId = $booking->booking_code . '-' . time();

            // Buat parameter transaksi untuk Midtrans Snap
            $midtransParams = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => (int) $booking->total_price,
                ],
                'customer_details' => [
                    'first_name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone_number,
                    // 'billing_address' => [ /* ... opsional ... */ ],
                ],
                // 'item_details' => $itemDetailsMidtrans, // Kirim jika tidak kosong
                'callbacks' => [
                    'finish' => route('customer.payment.finished', $booking->hashid) // URL redirect browser
                ],
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'), // Waktu server saat ini
                    'unit' => 'hour',    // Satuan: 'minute', 'hour', 'day'
                    'duration' => 2,     // Durasi batas waktu pembayaran
                ],
            ];
            // Hanya tambahkan item_details jika array-nya tidak kosong
            if (!empty($itemDetailsMidtrans)) {
                $midtransParams['item_details'] = $itemDetailsMidtrans;
            }

            // Dapatkan Snap Token dari Midtrans
            $snapToken = MidtransSnap::getSnapToken($midtransParams);

            if (!$snapToken) {
                throw new \Exception('Gagal mendapatkan token pembayaran dari Midtrans.');
            }

            // Buat atau update record Payment awal
            // Ini mencatat setiap upaya pembayaran yang diinisiasi
            Payment::updateOrCreate(
                [
                    'booking_id' => $booking->id,
                    'payment_gateway_order_id' => $midtransOrderId, // Gunakan order_id yang dikirim ke Midtrans
                ],
                [
                    'customer_id' => $customer->id,
                    'gross_amount' => $booking->total_price,
                    'transaction_status' => 'pending', // Status awal sebelum customer bayar
                    'snap_token' => $snapToken,       // Simpan snap token
                    'expiry_time' => Carbon::now()->addHours(2), // Simpan waktu kedaluwarsa di DB
                    'payment_type' => null,
                    'midtrans_transaction_id' => null,
                    'settlement_time' => null,
                    'midtrans_response_payload' => null, // Akan diisi oleh webhook
                ]
            );

            Log::info("Midtrans SnapToken generated for Booking {$booking->booking_code}. PG Order ID: {$midtransOrderId}. Expiry: 2 hours.");
            // Redirect ke halaman yang akan menampilkan Snap JS, kirim token via flash session
            return redirect()->route('customer.payment.show', ['booking_hashid' => $booking->hashid])
                ->with('snap_token', $snapToken);
        } catch (ModelNotFoundException $e) {
            Log::error("Booking not found during payment initiation.", ['booking_hashid' => $booking_hashid, 'customer_id' => $customer->id]);
            return redirect()->route('customer.dashboard')->with('error', 'Booking tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Error initiating payment: ' . $e->getMessage(), ['booking_hashid' => $booking_hashid, 'customer_id' => $customer->id, 'trace' => $e->getTraceAsString()]);
            // Arahkan ke detail booking agar bisa coba bayar lagi
            return redirect()->route('customer.bookings.show', ['booking_hashid' => $booking_hashid])
                ->with('error', 'Gagal memulai proses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman pembayaran Midtrans Snap.
     *
     * @param  string $booking_hashid
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showPaymentPage($booking_hashid)
    {
        $snapToken = session('snap_token');
        $booking = null;
        $decodedBookingId = Hashids::decode($booking_hashid);

        if (!empty($decodedBookingId)) {
            $bookingId = $decodedBookingId[0];
            $customer = Auth::guard('customer')->user();
            /** @var \App\Models\Customer $customer */
            $booking = $customer->bookings()->find($bookingId);
        }

        if (!$booking) {
            return redirect()->route('customer.dashboard')->with('error', 'Booking tidak ditemukan.');
        }

        // Jika token hilang dari session (misal user refresh halaman atau akses langsung)
        if (!$snapToken) {
            if ($booking->payment_status == 'pending') {
                // Coba ambil snap_token terakhir yang valid dari record payment
                $latestPaymentAttempt = $booking->payments()
                    ->whereNotNull('snap_token')
                    ->where('transaction_status', 'pending') // Hanya yg masih pending
                    ->latest()
                    ->first();

                if ($latestPaymentAttempt && $latestPaymentAttempt->snap_token && (!$latestPaymentAttempt->expiry_time || $latestPaymentAttempt->expiry_time > now())) {
                    $snapToken = $latestPaymentAttempt->snap_token;
                    Log::info("Retrieved snap token from DB for payment page: " . $booking->booking_code);
                } else {
                    // Jika tidak ada token valid atau sudah expired, arahkan untuk inisiasi ulang
                    Log::warning("Snap token missing or expired for pending payment. Redirecting to booking detail.", ['booking_code' => $booking->booking_code]);
                    return redirect()->route('customer.bookings.show', ['booking_hashid' => $booking->hashid])
                        ->with('warning', 'Sesi pembayaran tidak valid atau kedaluwarsa. Silakan klik tombol bayar lagi dari detail booking Anda.');
                }
            } else {
                // Jika status booking bukan lagi pending
                return redirect()->route('customer.bookings.show', ['booking_hashid' => $booking->hashid])
                    ->with('info', 'Status pembayaran untuk booking ini sudah tidak menunggu pembayaran.');
            }
        }

        // View: resources/views/customer/payment/show.blade.php
        return view('customer.payment.show', compact('snapToken', 'booking'));
    }


    /**
     * Halaman yang dituju setelah pembayaran via Midtrans (finish callback dari browser).
     * Ini HANYA redirect dari browser, update status final ada di Webhook.
     *
     * @param Request $request
     * @param string $booking_hashid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paymentFinished(Request $request, $booking_hashid)
    {
        Log::info('Midtrans Finish Callback Hit:', ['booking_hashid' => $booking_hashid, 'query_params' => $request->query()]);

        $status = $request->query('transaction_status');
        $message = 'Proses pembayaran Anda telah diarahkan kembali.';
        $messageType = 'info';

        if ($status === 'settlement' || $status === 'capture') {
            $message = 'Pembayaran Anda berhasil! Status booking akan segera diperbarui setelah konfirmasi dari server.';
            $messageType = 'success';
        } elseif ($status === 'pending') {
            $message = 'Pembayaran Anda sedang menunggu konfirmasi dari pihak penyedia layanan.';
            $messageType = 'info';
        } elseif (in_array($status, ['expire', 'cancel', 'deny', 'failure'])) {
            $message = 'Pembayaran Anda dibatalkan, gagal, atau telah kedaluwarsa. Silakan coba lagi atau hubungi support.';
            $messageType = 'error';
        }

        // Arahkan ke halaman detail booking untuk melihat status terbaru
        // Pastikan route 'customer.bookings.show' sudah ada
        return redirect()->route('customer.bookings.show', ['booking_hashid' => $booking_hashid])
            ->with($messageType, $message);
    }
}
