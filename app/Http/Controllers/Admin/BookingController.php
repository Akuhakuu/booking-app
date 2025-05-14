<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Store; // <-- IMPORT MODEL STORE
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    // Status rental yang bisa di-set oleh admin
    private $editableRentalStatuses = [
        'confirmed'                 => 'Confirmed (Siap Diambil)',
        'picked_up'                 => 'Picked Up (Sedang Disewa)',
        'returned'                  => 'Returned (Sudah Dikembalikan)',
        'completed'                 => 'Completed (Selesai & OK)',
        'completed_with_issue'      => 'Completed with Issue (Selesai Bermasalah)',
        'cancelled_by_admin'        => 'Cancelled by Admin',
    ];

    /**
     * Menampilkan halaman daftar semua booking untuk Admin.
     */
    public function index(): View
    {
        return view('admin.bookings.index');
    }

    /**
     * Menyediakan data booking untuk DataTables Admin.
     */
    public function getData(Request $request)
    {
        $bookings = Booking::with(['customer:id,name,email', 'user:id,name'])
            ->select('bookings.*');

        return DataTables::of($bookings)
            ->addIndexColumn()
            ->addColumn('customer_name', fn($booking) => optional($booking->customer)->name ?? 'N/A')
            ->addColumn('admin_handler', fn($booking) => optional($booking->user)->name ?? 'N/A')
            ->editColumn('start_date', fn($booking) => $booking->start_date?->format('d M Y'))
            ->editColumn('start_time', function ($booking) {
                return $booking->start_time ? $booking->start_time->format('H:i') : '-';
            })
            ->editColumn('end_date', function ($booking) {
                return $booking->end_date?->format('d M Y');
            })
            ->addColumn('actual_return_datetime', function ($booking) {
                if ($booking->return_date) {
                    $display = $booking->return_date->format('d M Y');
                    if ($booking->return_time) {
                        $display .= ' ' . $booking->return_time->format('H:i');
                    }
                    return $display;
                }
                return '-';
            })
            ->addColumn('duration', function ($booking) {
                return $booking->start_date && $booking->end_date ? $booking->start_date->diffInDays($booking->end_date) . ' Hari' : '-';
            })
            ->editColumn('total_price', fn($booking) => 'Rp' . number_format($booking->total_price, 0, ',', '.'))
            ->editColumn('payment_status', function ($booking) {
                $status = $booking->payment_status ?? 'unknown';
                $color = 'secondary';
                if ($status == 'pending') $color = 'warning';
                elseif ($status == 'paid') $color = 'success';
                elseif (in_array($status, ['failed', 'cancelled', 'expired', 'deny'])) $color = 'danger';
                elseif ($status == 'challenge') $color = 'info';
                return '<span class="badge bg-light-' . $color . '">' . ucwords(str_replace('_', ' ', $status)) . '</span>';
            })
            ->editColumn('rental_status', function ($booking) {
                $status = $booking->rental_status ?? 'unknown';
                $color = 'secondary';
                $rentalStatusDisplay = ucwords(str_replace('_', ' ', $status));
                $comparisonDate = $booking->return_date ?? $booking->end_date;

                if (
                    $comparisonDate && Carbon::now()->startOfDay()->gt($comparisonDate->startOfDay()) &&
                    !in_array($status, ['returned', 'completed', 'completed_with_issue', 'cancelled_by_customer', 'cancelled_by_admin', 'cancelled_payment_issue'])
                ) {
                    $rentalStatusDisplay = 'Telat Dikembalikan';
                    $color = 'danger';
                    return '<span class="badge bg-light-' . $color . '">' . $rentalStatusDisplay . '</span>';
                }

                if (in_array($status, ['pending_confirmation', 'pending_review'])) $color = 'warning';
                elseif (in_array($status, ['confirmed', 'ready_to_pickup'])) $color = 'info';
                elseif (in_array($status, ['picked_up', 'active'])) $color = 'primary';
                elseif (in_array($status, ['returned', 'completed', 'completed_with_issue'])) $color = 'success';
                elseif (str_contains($status, 'cancelled')) $color = 'danger';
                return '<span class="badge bg-light-' . $color . '">' . $rentalStatusDisplay . '</span>';
            })
            ->addColumn('action', function ($booking) {
                $showUrl = route('admin.bookings.show', $booking->hashid);
                $editUrl = route('admin.bookings.editStatus', $booking->hashid);
                return '
                    <div class="btn-group" role="group">
                        <a href="' . $showUrl . '" class="btn btn-sm btn-outline-info" title="Lihat Detail"><i class="bi bi-eye-fill"></i></a>
                        <a href="' . $editUrl . '" class="btn btn-sm btn-info" title="Ubah Status & Pengembalian"><i class="bi bi-pencil-square"></i></a>
                    </div>
                ';
            })
            ->rawColumns(['payment_status', 'rental_status', 'action'])
            ->make(true);
    }

    /**
     * Menampilkan halaman detail booking untuk Admin.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['customer', 'user', 'items.brand', 'items.category', 'payments' => fn($q) => $q->orderBy('created_at', 'desc')]);
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Menampilkan form untuk mengedit status penyewaan dan detail pengembalian booking oleh Admin.
     */
    public function editStatus(Booking $booking): View
    {
        $statuses = $this->editableRentalStatuses;
        return view('admin.bookings.edit_status', compact('booking', 'statuses'));
    }

    /**
     * Mengupdate status penyewaan, detail pengembalian, dan user_id booking oleh Admin.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'rental_status' => ['required', 'string', Rule::in(array_keys($this->editableRentalStatuses))],
            'return_date'   => ['nullable', 'required_if:rental_status,returned,completed,completed_with_issue', 'date_format:Y-m-d', 'after_or_equal:' . $booking->start_date->format('Y-m-d')],
            'return_time'   => ['nullable', 'required_if:rental_status,returned,completed,completed_with_issue', 'date_format:H:i'],
            'admin_notes'   => ['nullable', 'string', 'max:2000'],
        ], [
            'return_date.required_if' => 'Tanggal kembali aktual wajib diisi jika status adalah Returned/Completed.',
            'return_time.required_if' => 'Waktu kembali aktual wajib diisi jika status adalah Returned/Completed.',
            'return_date.after_or_equal' => 'Tanggal kembali aktual tidak boleh sebelum tanggal mulai sewa.',
        ]);

        $newRentalStatus = $validated['rental_status'];
        $oldRentalStatus = $booking->rental_status;
        $adminUser = Auth::user();

        $hasChanged = $newRentalStatus !== $oldRentalStatus ||
            (isset($validated['return_date']) && $validated['return_date'] != $booking->return_date?->format('Y-m-d')) ||
            (isset($validated['return_time']) && $validated['return_time'] != $booking->return_time?->format('H:i')) ||
            (!empty($validated['admin_notes'])) ||
            ($adminUser && $booking->user_id !== $adminUser->id);

        if (!$hasChanged) {
            return redirect()->route('admin.bookings.show', $booking->hashid)
                ->with('info', 'Tidak ada perubahan yang dilakukan.');
        }

        DB::beginTransaction();
        try {
            $booking->rental_status = $newRentalStatus;

            if ($adminUser) {
                $booking->user_id = $adminUser->id;
            } else {
                Log::warning("Admin user not authenticated during booking update for booking ID: {$booking->id}");
            }

            if (in_array($newRentalStatus, ['returned', 'completed', 'completed_with_issue'])) {
                $booking->return_date = $validated['return_date'] ? Carbon::parse($validated['return_date']) : null;
                $booking->return_time = $validated['return_time'] ? Carbon::parse($validated['return_time'])->format('H:i:s') : null;
            }

            if (!empty($validated['admin_notes'])) {
                $adminName = $adminUser ? $adminUser->name : 'System';
                $newNoteEntry = "Note by {$adminName} (" . now()->format('d/m/Y H:i') . "): " . $validated['admin_notes'];
                $booking->admin_notes = $booking->admin_notes ? $booking->admin_notes . "\n\n" . $newNoteEntry : $newNoteEntry;
            }

            if (
                in_array($newRentalStatus, ['returned', 'completed', 'completed_with_issue']) &&
                !in_array($oldRentalStatus, ['returned', 'completed', 'completed_with_issue'])
            ) {
                $this->increaseItemStockForBooking($booking);
                Log::info("Admin Update: Stock increased for Booking {$booking->booking_code} from '{$oldRentalStatus}' to '{$newRentalStatus}'. Processed by Admin ID: " . ($adminUser ? $adminUser->id : 'N/A'));
            }

            $booking->save();
            DB::commit();

            return redirect()->route('admin.bookings.show', $booking->hashid)
                ->with('success', 'Status dan detail pengembalian booking berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Admin Booking Update Error: " . $e->getMessage(), [
                'booking_id' => $booking->id,
                'admin_id' => $adminUser ? $adminUser->id : null,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui booking: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk menambah stok item.
     */
    protected function increaseItemStockForBooking(Booking $booking): void
    {
        try {
            $booking->loadMissing('items');
            foreach ($booking->items as $itemPivot) {
                $itemMaster = Item::find($itemPivot->id);
                if ($itemMaster && isset($itemPivot->pivot->quantity)) {
                    $itemMaster->increment('stock', $itemPivot->pivot->quantity);
                    Log::info("ADMIN STOCK UPDATE: Stock for item ID {$itemMaster->id} ('{$itemMaster->name}') incremented by {$itemPivot->pivot->quantity} for booking {$booking->booking_code}. New stock: {$itemMaster->stock}");
                } else {
                    Log::warning("ADMIN STOCK UPDATE: Could not increment stock. Item master or pivot quantity not found for item ID {$itemPivot->id} in booking {$booking->booking_code}.");
                }
            }
        } catch (\Exception $e) {
            Log::error("ADMIN STOCK UPDATE: Exception during stock increment for booking {$booking->booking_code}. Error: " . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman detail booking yang diformat untuk dicetak.
     */
    public function printBooking(Booking $booking): View
    {
        $booking->load(['customer', 'user', 'items.brand', 'items.category']);

        // Ambil data toko. Asumsi ID toko adalah 1 atau gunakan StoreController::STORE_RECORD_ID jika public
        $storeDetails = Store::find(1); // Ganti '1' dengan konstanta jika ada

        // Jika $storeDetails null (belum ada data toko), berikan objek Store kosong agar view tidak error
        if (!$storeDetails) {
            $storeDetails = new Store([
                'name' => config('app.name', 'Nama Toko Anda'),
                'address' => 'Alamat Toko Belum Diatur',
                'phone' => '-',
                'email' => '-',
                // isi default lain jika perlu untuk placeholder
            ]);
        }

        return view('admin.bookings.print', compact('booking', 'storeDetails'));
    }
}
