<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Item; // Untuk penyesuaian stok
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Untuk transaksi saat update
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View; // Untuk type hint
use Carbon\Carbon;

class PaymentController extends Controller
{
    // Status pembayaran yang bisa di-set oleh admin (ini adalah transaction_status dari Midtrans)
    private $editableTransactionStatuses = [
        'pending' => 'Pending',
        'settlement' => 'Settlement (Berhasil Dibayar)',
        'capture' => 'Capture (Berhasil Dibayar)',
        'failure' => 'Failure (Gagal)',
        'expire' => 'Expire (Kedaluwarsa)',
        'cancel' => 'Cancel (Dibatalkan)',
        'deny' => 'Deny (Ditolak Fraud)',
        'refund' => 'Refund (Dana Dikembalikan)',
        'partial_refund' => 'Partial Refund (Dana Dikembalikan Sebagian)',
        // Tambahkan status custom jika Anda punya proses verifikasi manual oleh admin
        // 'verified_manual' => 'Verified (Manual Admin)',
    ];

    /**
     * Menampilkan halaman daftar/laporan pembayaran.
     */
    public function index(): View
    {
        return view('admin.payments.index');
    }

    /**
     * Menyediakan data pembayaran untuk DataTables.
     */
    public function getData(Request $request)
    {
        $payments = Payment::with(['booking.customer', 'booking'])
            ->select('payments.*');

        return DataTables::of($payments)
            ->addIndexColumn()
            ->addColumn('booking_code', function ($payment) {
                return optional($payment->booking)->booking_code ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('customer_name', function ($payment) {
                return optional(optional($payment->booking)->customer)->name ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('payment_gateway_order_id_display', function ($payment) { // Kolom baru untuk display
                return $payment->payment_gateway_order_id ?? '-';
            })
            ->addColumn('midtrans_transaction_id_display', function ($payment) { // Kolom baru untuk display
                return $payment->midtrans_transaction_id ?? '-';
            })
            ->editColumn('gross_amount', function ($payment) {
                return 'Rp ' . number_format($payment->gross_amount, 0, ',', '.');
            })
            ->editColumn('payment_type', function ($payment) {
                return ucwords(str_replace('_', ' ', $payment->payment_type ?? '-'));
            })
            ->editColumn('transaction_time', function ($payment) {
                return $payment->transaction_time ? Carbon::parse($payment->transaction_time)->format('d M Y, H:i') : '-';
            })
            ->editColumn('transaction_status', function ($payment) {
                $status = $payment->transaction_status ?? 'unknown';
                $color = 'secondary';
                if ($status === 'pending') $color = 'warning';
                elseif ($status === 'settlement' || $status === 'capture') $color = 'success';
                elseif (in_array($status, ['failure', 'expire', 'cancel', 'deny'])) $color = 'danger';
                elseif (in_array($status, ['refund', 'partial_refund'])) $color = 'info';
                return '<span class="badge bg-light-' . $color . '">' . ucwords(str_replace('_', ' ', $status)) . '</span>';
            })
            ->addColumn('action', function ($payment) {
                $showUrl = route('admin.payments.show', $payment->hashid);
                $editUrl = route('admin.payments.edit', $payment->hashid);
                return '
                <a href="' . $showUrl . '" class="btn btn-sm btn-success me-1" title="Lihat Detail"><i class="bi bi-eye-fill"></i></a>
                <a href="' . $editUrl . '" class="btn btn-sm btn-info" title="Edit Status/Notes"><i class="bi bi-pencil-fill"></i></a>
                ';
            })
            ->rawColumns(['action', 'transaction_status', 'booking_code', 'customer_name'])
            ->make(true);
    }

    /**
     * Menampilkan detail spesifik pembayaran untuk Admin.
     * Menggunakan Route Model Binding {payment:hashid}.
     */
    public function show(Payment $payment): View
    {
        // Eager load relasi yang dibutuhkan untuk tampilan detail
        $payment->load(['booking.customer', 'booking.items', 'booking.items.brand', 'booking.items.category']);
        return view('admin.payments.show', compact('payment'));
    }


    /**
     * Menampilkan form untuk mengedit status/notes pembayaran.
     * Menggunakan Route Model Binding {payment:hashid}.
     */
    public function edit(Payment $payment): View
    {
        $payment->load(['booking.customer']); // Eager load customer via booking
        $statuses = $this->editableTransactionStatuses;
        return view('admin.payments.edit', compact('payment', 'statuses'));
    }

    /**
     * Mengupdate status atau notes pembayaran dari sisi Admin.
     * Juga akan mengupdate payment_status dan rental_status di Booking terkait,
     * serta menyesuaikan stok item jika pembayaran berubah menjadi 'paid' atau dari 'paid' ke status gagal/refund.
     */
    public function update(Request $request, Payment $payment)
    {
        $validatedData = $request->validate([
            'transaction_status' => ['required', 'string', Rule::in(array_keys($this->editableTransactionStatuses))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            $oldPaymentTransactionStatus = $payment->transaction_status; // Status Payment sebelum diubah
            $newPaymentTransactionStatus = $validatedData['transaction_status'];

            // Update record Payment
            $payment->transaction_status = $newPaymentTransactionStatus;
            $payment->notes = $validatedData['notes'];

            // Jika admin set 'settlement' atau 'capture' (pembayaran berhasil),
            // dan transaction_time belum ada, set ke waktu sekarang.
            // Ini berguna jika admin melakukan konfirmasi manual untuk pembayaran yang statusnya tidak otomatis update.
            if (in_array($newPaymentTransactionStatus, ['settlement', 'capture']) && is_null($payment->transaction_time)) {
                $payment->transaction_time = now();
                // Jika Anda punya kolom settlement_time terpisah di tabel payments dan ingin mengisinya:
                // $payment->settlement_time = now();
            }
            $payment->save();

            // Update status terkait di Booking jika ada booking yang terhubung
            if ($payment->booking) {
                $booking = $payment->booking;
                $oldBookingPaymentStatus = $booking->payment_status; // Status Booking sebelum diubah

                // Mapping status Midtrans ke status booking Anda
                if ($newPaymentTransactionStatus == 'settlement' || $newPaymentTransactionStatus == 'capture') {
                    $booking->payment_status = 'paid';
                    // Hanya ubah rental_status menjadi 'confirmed' jika sebelumnya belum diproses lebih lanjut atau dibatalkan
                    if (!in_array($booking->rental_status, ['picked_up', 'returned', 'completed', 'cancelled_by_customer', 'cancelled_by_admin', 'cancelled_payment_issue'])) {
                        $booking->rental_status = 'confirmed';
                    }
                    // Jika status booking sebelumnya BUKAN 'paid' dan SEKARANG menjadi 'paid' karena admin update
                    if ($oldBookingPaymentStatus !== 'paid' && $booking->payment_status === 'paid') {
                        $this->decreaseItemStockForBooking($booking); // Kurangi stok
                        Log::info("Admin Update: Stock decreased for Booking {$booking->booking_code} due to payment status change to PAID.");
                    }
                } elseif ($newPaymentTransactionStatus == 'pending') {
                    $booking->payment_status = 'pending';
                } elseif (in_array($newPaymentTransactionStatus, ['failure', 'expire', 'cancel', 'deny'])) {
                    $booking->payment_status = 'failed'; // Atau bisa 'expired', 'cancelled' tergantung kebutuhan
                    // Jika status rental belum final (selesai/dikembalikan/dibatalkan permanen)
                    if (!in_array($booking->rental_status, ['returned', 'completed', 'cancelled_by_customer', 'cancelled_by_admin'])) {
                        $booking->rental_status = 'cancelled_payment_issue';
                    }
                    // Jika status booking sebelumnya ADALAH 'paid' dan SEKARANG menjadi gagal/batal karena admin update
                    if ($oldBookingPaymentStatus === 'paid') {
                        $this->increaseItemStockForBooking($booking); // Kembalikan stok
                        Log::info("Admin Update: Stock increased for Booking {$booking->booking_code} due to payment status change from PAID to {$newPaymentTransactionStatus}.");
                    }
                } elseif ($newPaymentTransactionStatus == 'refund' || $newPaymentTransactionStatus == 'partial_refund') {
                    $booking->payment_status = 'refunded'; // Atau 'partially_refunded'
                    // Jika status booking sebelumnya ADALAH 'paid' dan SEKARANG di-refund
                    if ($oldBookingPaymentStatus === 'paid') {
                        $this->increaseItemStockForBooking($booking); // Kembalikan stok
                        Log::info("Admin Update: Stock increased for refunded Booking {$booking->booking_code}.");
                    }
                }
                // Tambahkan catatan admin di booking
                $booking->admin_notes = ($booking->admin_notes ? $booking->admin_notes . "\n" : "") . "Status pembayaran diubah oleh admin (Payment ID: {$payment->id}) ke '{$newPaymentTransactionStatus}' pada " . now()->format('d/m/Y H:i');
                $booking->save();
            }

            DB::commit();
            return redirect()->route('admin.payments.index')
                ->with('success', 'Status pembayaran dan booking berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin Payment Update Error: ' . $e->getMessage(), ['payment_id' => $payment->id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk mengurangi stok item saat booking dikonfirmasi PAID.
     *
     * @param Booking $booking
     * @return void
     */
    protected function decreaseItemStockForBooking(Booking $booking): void
    {
        try {
            $booking->loadMissing('items'); // Pastikan items sudah di-load
            foreach ($booking->items as $itemPivot) {
                $itemMaster = Item::find($itemPivot->id);
                if ($itemMaster) {
                    if ($itemMaster->stock >= $itemPivot->pivot->quantity) {
                        $itemMaster->decrement('stock', $itemPivot->pivot->quantity);
                        Log::info("ADMIN/WEBHOOK: Stock for item ID {$itemMaster->id} ('{$itemMaster->name}') decremented by {$itemPivot->pivot->quantity} for booking {$booking->booking_code}. New stock: {$itemMaster->stock}");
                    } else {
                        Log::error("ADMIN/WEBHOOK: CRITICAL - Stock for item ID {$itemMaster->id} ('{$itemMaster->name}') is insufficient ({$itemMaster->stock}) to decrement {$itemPivot->pivot->quantity} for booking {$booking->booking_code}. MANUAL INTERVENTION REQUIRED.");
                    }
                } else {
                    Log::error("ADMIN/WEBHOOK: Item master with ID {$itemPivot->id} not found during stock decrement for booking {$booking->booking_code}.");
                }
            }
        } catch (\Exception $e) {
            Log::error("ADMIN/WEBHOOK: Exception during stock decrement for booking {$booking->booking_code}. Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Helper method untuk menambah stok item (misal saat refund atau pembatalan setelah stok dikurangi).
     *
     * @param Booking $booking
     * @return void
     */
    protected function increaseItemStockForBooking(Booking $booking): void
    {
        try {
            $booking->loadMissing('items');
            foreach ($booking->items as $itemPivot) {
                $itemMaster = Item::find($itemPivot->id);
                if ($itemMaster) {
                    $itemMaster->increment('stock', $itemPivot->pivot->quantity);
                    Log::info("ADMIN/WEBHOOK: Stock for item ID {$itemMaster->id} ('{$itemMaster->name}') incremented by {$itemPivot->pivot->quantity} for booking {$booking->booking_code}. New stock: {$itemMaster->stock}");
                } else {
                    Log::error("ADMIN/WEBHOOK: Item master with ID {$itemPivot->id} not found during stock increment for booking {$booking->booking_code}.");
                }
            }
        } catch (\Exception $e) {
            Log::error("ADMIN/WEBHOOK: Exception during stock increment for booking {$booking->booking_code}. Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    // Method destroy() untuk Payment tidak disarankan dan sudah dikomentari sebelumnya.
}
