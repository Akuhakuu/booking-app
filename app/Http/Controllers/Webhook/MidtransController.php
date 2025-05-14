<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Item;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification as MidtransNotification;
use Carbon\Carbon; // Untuk parsing tanggal dari Midtrans

class MidtransController extends Controller
{
    /**
     * Menangani notifikasi HTTP (webhook) dari Midtrans.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleNotification(Request $request)
    {
        $rawPayload = $request->getContent();
        Log::info('Midtrans Webhook - RAW Payload Received:', ['payload' => $rawPayload]);
        // $decodedPayloadForLog = json_decode($rawPayload, true); // Opsional untuk log tambahan
        // Log::info('Midtrans Webhook - Decoded Payload (Manual):', $decodedPayloadForLog);
        Log::info('Midtrans Webhook - Request Headers:', $request->headers->all());

        // 1. Set Konfigurasi Midtrans
        $serverKey = config('midtrans.server_key');
        $isProduction = config('midtrans.is_production');

        Log::info('Midtrans Webhook - Using Server Key: ' . substr($serverKey, 0, 10) . '... (masked)');
        Log::info('Midtrans Webhook - Is Production: ' . ($isProduction ? 'true' : 'false'));

        if (empty($serverKey)) {
            Log::error('Midtrans Webhook: Server Key is empty in config. Cannot proceed.');
            // Kirim 200 agar Midtrans tidak retry jika ini error konfigurasi permanen di sisi kita
            return response()->json(['status' => 'error', 'message' => 'Server key configuration missing on our end.'], 200);
        }

        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = $isProduction;

        // 2. Validasi dan Parse Notifikasi Midtrans
        $notification = null;
        try {
            Log::info('Midtrans Webhook: Attempting to instantiate MidtransNotification...');
            // Panggil tanpa argumen, library akan baca dari php://input dan validasi signature
            $notification = new MidtransNotification();
            Log::info('Midtrans Webhook: MidtransNotification instantiated and validated successfully.');
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook - Exception during MidtransNotification instantiation or signature verification: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(), // Untuk debugging lebih detail
            ]);
            // Jika signature tidak valid atau payload korup, Midtrans akan menganggap ini error.
            return response()->json(['status' => 'error', 'message' => 'Invalid notification data or signature: ' . $e->getMessage()], 400);
        }

        // 3. Ambil Data Penting dari Notifikasi (SETELAH VALID)
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? null;
        $paymentGatewayOrderId = $notification->order_id; // Ini adalah $booking->booking_code . '-' . time()
        $paymentType = $notification->payment_type ?? null;
        $transactionIdMidtrans = $notification->transaction_id ?? null;
        $transactionTime = $notification->transaction_time ?? null;
        $settlementTime = $notification->settlement_time ?? null;
        $grossAmountFromMidtrans = $notification->gross_amount ?? null;


        Log::info("Midtrans Webhook - Processing for Payment Gateway Order ID: {$paymentGatewayOrderId}, Status: {$transactionStatus}, Fraud: {$fraudStatus}");

        // 4. Cari Record Payment di Database Anda
        $payment = Payment::where('payment_gateway_order_id', $paymentGatewayOrderId)->latest()->first();

        if (!$payment) {
            Log::error("Midtrans Webhook: Payment record not found for payment_gateway_order_id: {$paymentGatewayOrderId}. Notification ignored, but sending 200 OK to Midtrans.");
            return response()->json(['status' => 'ok', 'message' => 'Payment record for order_id not found, notification data logged.'], 200);
        }

        // 5. Ambil Booking Terkait
        $booking = $payment->booking;
        if (!$booking) {
            Log::error("Midtrans Webhook: Associated booking not found for payment_id: {$payment->id}, PG Order ID: {$paymentGatewayOrderId}.");
            return response()->json(['status' => 'ok', 'message' => 'Associated booking not found, notification logged for payment update.'], 200);
        }

        // --- Mulai Transaksi Database untuk Update ---
        DB::beginTransaction();
        try {
            // 6. Update Record Payment dengan data dari notifikasi
            $payment->midtrans_transaction_id = $transactionIdMidtrans ?? $payment->midtrans_transaction_id;
            $payment->payment_type = $paymentType ?? $payment->payment_type;
            $payment->transaction_status = $transactionStatus; // Status terbaru dari Midtrans
            if ($transactionTime) {
                $payment->transaction_time = Carbon::parse($transactionTime)->setTimezone(config('app.timezone'));
            }
            // === KOMENTARI ATAU HAPUS BAGIAN INI ===
            // if (isset($notification->settlement_time)) {
            //     $payment->settlement_time = Carbon::parse($notification->settlement_time)->setTimezone(config('app.timezone'));
            // }
            // 
            // Simpan payload notifikasi sebagai array untuk audit/debug
            $payment->midtrans_response_payload = json_decode($request->getContent(), true);
            $payment->save();
            Log::info("Webhook: Payment record ID {$payment->id} updated. Status: {$payment->transaction_status}");


            // 7. Logika Update Status Booking dan Stok (Idempotency Check)
            // Hanya proses jika status pembayaran booking sebelumnya belum final.
            if (!in_array($booking->payment_status, ['paid', 'failed', 'cancelled', 'expired', 'refunded', 'challenge'])) {

                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    if (($fraudStatus ?? 'accept') == 'accept') {
                        $booking->payment_status = 'paid';
                        $booking->rental_status = 'confirmed'; // Siap untuk proses selanjutnya
                        $this->decreaseItemStock($booking); // Panggil method pengurangan stok
                        Log::info("Webhook: Booking {$booking->booking_code} status updated to PAID.");
                    } elseif ($fraudStatus == 'challenge') {
                        $booking->payment_status = 'challenge';
                        $booking->rental_status = 'pending_review'; // Admin perlu review
                        Log::info("Webhook: Booking {$booking->booking_code} payment is CHALLENGED.");
                    }
                } elseif ($transactionStatus == 'pending') {
                    $booking->payment_status = 'pending';
                    Log::info("Webhook: Booking {$booking->booking_code} payment is PENDING.");
                } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'])) {
                    $booking->payment_status = $transactionStatus; // Set sesuai status dari Midtrans
                    $booking->rental_status = 'cancelled_payment_issue';
                    Log::info("Webhook: Booking {$booking->booking_code} payment {$transactionStatus}.");
                }
                $booking->save();
                Log::info("Webhook: Booking record {$booking->booking_code} updated. Payment Status: {$booking->payment_status}, Rental Status: {$booking->rental_status}");
            } else {
                Log::info("Webhook: Booking {$booking->booking_code} already has a final payment status ({$booking->payment_status}). Notification for {$transactionStatus} (PG Order ID: {$paymentGatewayOrderId}) was for an existing payment record update, booking status not re-processed.");
            }

            DB::commit();
            Log::info("Midtrans Webhook - Successfully processed and DB committed for PG Order ID: {$paymentGatewayOrderId}");
            // 8. Kirim Response HTTP 200 OK ke Midtrans
            return response()->json(['status' => 'ok', 'message' => 'Notification processed successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Midtrans Webhook - DB Update Error for PG Order ID: {$paymentGatewayOrderId}. Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Tetap kirim 200 OK ke Midtrans agar tidak retry jika error ada di sisi kita,
            // tapi catat errornya agar bisa diinvestigasi manual.
            return response()->json(['status' => 'error', 'message' => 'Internal server error during DB update, notification logged.'], 200);
        }
    }

    /**
     * Helper method untuk mengurangi stok item.
     *
     * @param Booking $booking
     * @return void
     */
    protected function decreaseItemStock(Booking $booking): void
    {
        try {
            $booking->loadMissing('items'); // Pastikan items sudah di-load
            foreach ($booking->items as $itemPivot) {
                $itemMaster = Item::find($itemPivot->id); // Ambil model Item asli
                if ($itemMaster) {
                    // Kurangi stok hanya jika stok saat ini cukup
                    if ($itemMaster->stock >= $itemPivot->pivot->quantity) {
                        $itemMaster->decrement('stock', $itemPivot->pivot->quantity);
                        Log::info("Webhook: Stock for item ID {$itemMaster->id} ('{$itemMaster->name}') decremented by {$itemPivot->pivot->quantity} for booking {$booking->booking_code}. New stock: {$itemMaster->stock}");
                    } else {
                        // Ini kasus kritis, pembayaran berhasil tapi stok tiba-tiba tidak cukup
                        Log::error("Webhook: CRITICAL - Stock for item ID {$itemMaster->id} ('{$itemMaster->name}') is insufficient ({$itemMaster->stock}) to decrement {$itemPivot->pivot->quantity} for booking {$booking->booking_code}. Booking Payment was successful. MANUAL INTERVENTION REQUIRED.");
                        // TODO: Implementasikan notifikasi ke admin untuk kasus kritis ini.
                    }
                } else {
                    Log::error("Webhook: Item master with ID {$itemPivot->id} not found during stock decrement for booking {$booking->booking_code}.");
                }
            }
        } catch (\Exception $e) {
            Log::error("Webhook: Exception during stock decrement for booking {$booking->booking_code}. Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // TODO: Notifikasi admin tentang kegagalan pengurangan stok.
        }
    }
}
