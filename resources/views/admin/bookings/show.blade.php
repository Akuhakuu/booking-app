@extends('admin.layouts.master')

@section('page-title', 'Detail Booking Admin: ' . $booking->booking_code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Kelola Booking</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail: {{ $booking->booking_code }}</li>
@endsection

@push('styles')
    <style>
        .detail-label {
            font-weight: 600;
            color: #555;
        }

        .item-list img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: .25rem;
            border: 1px solid #eee;
        }

        .status-badge {
            font-size: 0.85rem;
            padding: .4em .7em;
        }

        .admin-notes-display {
            white-space: pre-wrap;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            max-height: 200px;
            overflow-y: auto;
            font-size: 0.9rem;
        }

        .card-title {
            margin-bottom: 0;
        }

        .list-group-item {
            border-left: 0;
            border-right: 0;
        }

        .list-group-item:first-child {
            border-top: 0;
        }

        .list-group-item:last-child {
            border-bottom: 0;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <h3>Detail Booking: <span class="text-primary">{{ $booking->booking_code }}</span></h3>
            <div>
                <a href="{{ route('admin.bookings.editStatus', $booking->hashid) }}" class="btn btn-primary me-1">
                    <i class="bi bi-pencil-square"></i> Ubah Status
                </a>
                <a href="{{ route('admin.bookings.print', $booking->hashid) }}" class="btn btn-secondary" target="_blank">
                    <i class="bi bi-printer-fill"></i> Cetak Booking
                </a>
            </div>
        </div>
        <p class="text-subtitle text-muted mt-1 mb-3">Customer: {{ optional($booking->customer)->name ?? 'N/A' }}</p>
    </div>

    <div class="page-content">
        @include('admin.partials.alerts')

        <section class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h4 class="card-title">Informasi Booking</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Kode Booking:</div>
                            <div class="col-sm-8 col-md-9 fw-bold">{{ $booking->booking_code }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Customer:</div>
                            <div class="col-sm-8 col-md-9">{{ optional($booking->customer)->name }}
                                ({{ optional($booking->customer)->email ?? 'Email tidak tersedia' }})</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Status Pembayaran:</div>
                            <div class="col-sm-8 col-md-9">
                                @php
                                    $paymentStatus = $booking->payment_status ?? 'unknown';
                                    $paymentColor = 'secondary';
                                    if ($paymentStatus == 'pending') {
                                        $paymentColor = 'warning';
                                    } elseif ($paymentStatus == 'paid') {
                                        $paymentColor = 'success';
                                    } elseif (in_array($paymentStatus, ['failed', 'cancelled', 'expired', 'deny'])) {
                                        $paymentColor = 'danger';
                                    } elseif ($paymentStatus == 'challenge') {
                                        $paymentColor = 'info';
                                    }
                                @endphp
                                <span
                                    class="badge bg-light-{{ $paymentColor }} status-badge">{{ ucwords(str_replace('_', ' ', $paymentStatus)) }}</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Status Penyewaan:</div>
                            <div class="col-sm-8 col-md-9">
                                @php
                                    $rentalStatus = $booking->rental_status ?? 'unknown';
                                    $rColor = 'secondary';
                                    $rentalStatusDisplay = ucwords(str_replace('_', ' ', $rentalStatus));
                                    $comparisonDate = $booking->return_date ?? $booking->end_date;

                                    if (
                                        $comparisonDate &&
                                        \Carbon\Carbon::now()->startOfDay()->gt($comparisonDate->startOfDay()) &&
                                        !in_array($rentalStatus, [
                                            'returned',
                                            'completed',
                                            'completed_with_issue',
                                            'cancelled_by_customer',
                                            'cancelled_by_admin',
                                            'cancelled_payment_issue',
                                        ])
                                    ) {
                                        $rentalStatusDisplay = 'Telat Dikembalikan';
                                        $rColor = 'danger';
                                    } else {
                                        if (in_array($rentalStatus, ['pending_confirmation', 'pending_review'])) {
                                            $rColor = 'warning';
                                        } elseif (in_array($rentalStatus, ['confirmed', 'ready_to_pickup'])) {
                                            $rColor = 'info';
                                        } elseif (in_array($rentalStatus, ['picked_up', 'active'])) {
                                            $rColor = 'primary';
                                        } elseif (
                                            in_array($rentalStatus, ['returned', 'completed', 'completed_with_issue'])
                                        ) {
                                            $rColor = 'success';
                                        } elseif (str_contains($rentalStatus, 'cancelled')) {
                                            $rColor = 'danger';
                                        }
                                    }
                                @endphp
                                <span
                                    class="badge bg-light-{{ $rColor }} status-badge">{{ $rentalStatusDisplay }}</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Tanggal Booking Dibuat:</div>
                            <div class="col-sm-8 col-md-9">{{ $booking->created_at->format('d M Y, H:i') }} WIB</div>
                        </div>

                        <hr class="my-3">
                        <h5 class="mb-3">Detail Waktu Sewa</h5>
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Tgl Mulai Sewa:</div>
                            <div class="col-sm-8 col-md-9">{{ $booking->start_date->format('d M Y') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Waktu Mulai (Pengambilan):</div>
                            <div class="col-sm-8 col-md-9">
                                {{ $booking->start_time ? $booking->start_time->format('H:i') . ' WIB' : '-' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Ekspektasi Tgl Kembali:</div>
                            <div class="col-sm-8 col-md-9">{{ $booking->end_date->format('d M Y') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Durasi Sewa (Ekspektasi):</div>
                            <div class="col-sm-8 col-md-9">{{ $booking->start_date->diffInDays($booking->end_date) }} Hari
                            </div>
                        </div>

                        @if ($booking->return_date)
                            <hr class="my-3">
                            <h5 class="mb-3 text-success">Pengembalian Aktual</h5>
                            <div class="row mb-2">
                                <div class="col-sm-4 col-md-3 detail-label text-success">Tanggal Dikembalikan:</div>
                                <div class="col-sm-8 col-md-9 fw-bold text-success">
                                    {{ $booking->return_date->format('d M Y') }}</div>
                            </div>
                            @if ($booking->return_time)
                                <div class="row mb-2">
                                    <div class="col-sm-4 col-md-3 detail-label text-success">Waktu Dikembalikan:</div>
                                    <div class="col-sm-8 col-md-9 fw-bold text-success">
                                        {{ $booking->return_time->format('H:i') . ' WIB' }}</div>
                                </div>
                            @endif
                        @else
                            <div class="row mb-2 mt-3">
                                <div class="col-sm-4 col-md-3 detail-label">Pengembalian Aktual:</div>
                                <div class="col-sm-8 col-md-9 fst-italic text-muted">Belum ada data pengembalian</div>
                            </div>
                        @endif
                        <hr class="my-3">

                        <div class="row mb-2">
                            <div class="col-sm-4 col-md-3 detail-label">Total Harga Booking:</div>
                            <div class="col-sm-8 col-md-9 text-primary fw-bold fs-5">
                                Rp{{ number_format($booking->total_price, 0, ',', '.') }}</div>
                        </div>

                        @if ($booking->notes)
                            <div class="row mb-2">
                                <div class="col-sm-4 col-md-3 detail-label">Catatan Customer:</div>
                                <div class="col-sm-8 col-md-9 fst-italic">{{ $booking->notes }}</div>
                            </div>
                        @endif
                        @if ($booking->admin_notes)
                            <div class="row mb-2">
                                <div class="col-sm-4 col-md-3 detail-label">Catatan Admin:</div>
                                <div class="col-sm-8 col-md-9 admin-notes-display">{!! nl2br(e($booking->admin_notes)) !!}</div>
                            </div>
                        @endif
                        @if ($booking->user)
                            <div class="row mb-2">
                                <div class="col-sm-4 col-md-3 detail-label">Terakhir Diproses Oleh:</div>
                                <div class="col-sm-8 col-md-9">{{ $booking->user->name }} <small class="text-muted"> (ID:
                                        {{ $booking->user->id }})</small></div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h4 class="card-title">Item yang Dibooking ({{ $booking->items->count() }} item)</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover item-list">
                                <thead>
                                    <tr>
                                        <th style="width:10%">Gambar</th>
                                        <th>Nama Item</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Harga/Hari</th>
                                        <th class="text-end">Subtotal Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $rentalDurationDays = $booking->start_date->diffInDays($booking->end_date);
                                        if ($rentalDurationDays <= 0) {
                                            $rentalDurationDays = 1;
                                        }
                                    @endphp
                                    @forelse($booking->items as $item)
                                        @php
                                            $pivotData = $item->pivot;
                                            $quantityBooked = $pivotData->quantity;
                                            $priceWhenBooked = $pivotData->price_per_item;
                                            $itemSubtotal = $priceWhenBooked * $quantityBooked * $rentalDurationDays;
                                        @endphp
                                        <tr>
                                            <td>
                                                @if ($item->img && File::exists(public_path('assets/compiled/items/' . $item->img)))
                                                    <img src="{{ asset('assets/compiled/items/' . $item->img) }}"
                                                        alt="{{ $item->name }}">
                                                @else
                                                    <img src="{{ asset('assets/compiled/svg/no-image.svg') }}"
                                                        alt="No image" class="bg-light p-1">
                                                @endif
                                            </td>
                                            <td>
                                                @if (Route::has('admin.items.edit'))
                                                    <a href="{{ route('admin.items.edit', $item->hashid) }}"
                                                        target="_blank"
                                                        class="text-dark text-decoration-none fw-bold">{{ $item->name }}</a>
                                                @else
                                                    <span class="text-dark fw-bold">{{ $item->name }}</span>
                                                @endif
                                                <br><small class="text-muted">{{ optional($item->brand)->name }} /
                                                    {{ optional($item->category)->name }}</small>
                                            </td>
                                            <td class="text-center">{{ $quantityBooked }}</td>
                                            <td class="text-end">Rp{{ number_format($priceWhenBooked, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp{{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">Tidak ada item dalam
                                                booking ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h4 class="card-title">Riwayat Transaksi Pembayaran</h4>
                    </div>
                    <div class="card-body">
                        @if ($booking->payments->isEmpty())
                            <p class="text-muted text-center py-3">Belum ada riwayat pembayaran.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($booking->payments as $payment)
                                    <li class="list-group-item px-0 py-3">
                                        <div class="d-flex justify-content-between">
                                            <span
                                                class="fw-semibold">{{ $payment->transaction_time ? $payment->transaction_time->format('d M Y, H:i') : $payment->created_at->format('d M Y, H:i') }}</span>
                                            @php
                                                $pStatus = $payment->transaction_status ?? 'unknown';
                                                $pColor = 'secondary';
                                                if ($pStatus == 'pending') {
                                                    $pColor = 'warning';
                                                } elseif ($pStatus == 'settlement' || $pStatus == 'capture') {
                                                    $pColor = 'success';
                                                } elseif (in_array($pStatus, ['deny', 'cancel', 'expire', 'failure'])) {
                                                    $pColor = 'danger';
                                                }
                                            @endphp
                                            <span
                                                class="badge bg-light-{{ $pColor }} status-badge">{{ ucwords(str_replace('_', ' ', $pStatus)) }}</span>
                                        </div>
                                        <small class="text-muted d-block">Metode:
                                            {{ $payment->payment_type ?? '-' }}</small>
                                        @if ($payment->midtrans_transaction_id)
                                            <small class="text-muted d-block">ID Trans Midtrans:
                                                {{ $payment->midtrans_transaction_id }}</small>
                                        @endif
                                        @if ($payment->payment_gateway_order_id)
                                            <small class="text-muted d-block">Order ID Midtrans:
                                                {{ $payment->payment_gateway_order_id }}</small>
                                        @endif
                                        <span class="fw-bold d-block mt-1">Jumlah:
                                            Rp{{ number_format($payment->gross_amount, 0, ',', '.') }}</span>
                                        @if ($payment->notes)
                                            <small class="d-block fst-italic mt-1">Catatan Pembayaran:
                                                {{ $payment->notes }}</small>
                                        @endif
                                        @if (Route::has('admin.payments.show') || Route::has('admin.payments.edit'))
                                            @php
                                                $paymentDetailRoute = Route::has('admin.payments.show')
                                                    ? route('admin.payments.show', $payment->hashid)
                                                    : (Route::has('admin.payments.edit')
                                                        ? route('admin.payments.edit', $payment->hashid)
                                                        : null);
                                            @endphp
                                            @if ($paymentDetailRoute)
                                                <a href="{{ $paymentDetailRoute }}"
                                                    class="btn btn-sm btn-outline-secondary mt-2">
                                                    <i class="bi bi-receipt"></i> Detail Payment
                                                </a>
                                            @endif
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- Script tambahan jika diperlukan --}}
@endpush
