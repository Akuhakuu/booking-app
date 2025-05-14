@extends('customer.layouts.master')

@section('page-title', 'Detail Booking ' . $booking->booking_code)

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
            border-radius: 5px;
        }

        .status-badge {
            font-size: 0.9em;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading mb-4">
        <h3>Detail Booking</h3>
        <p class="text-subtitle text-muted">Informasi lengkap untuk booking <span
                class="text-primary fw-bold">{{ $booking->booking_code }}</span>.</p>
    </div>

    <div class="page-content">
        @include('customer.partials.alerts') {{-- Sesuaikan path jika perlu --}}

        <section class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4 class="card-title">Informasi Booking</h4>
                    </div>
                    <div class="card-body">
                        {{-- ... (Kode Booking, Status Bayar, Status Sewa, Tgl Booking Dibuat - sama seperti sebelumnya) ... --}}
                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Kode Booking:</div>
                            <div class="col-sm-8 fw-bold">{{ $booking->booking_code }}</div>
                        </div>
                        {{-- Status Pembayaran dan Rental --}}
                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Status Pembayaran:</div>
                            <div class="col-sm-8">
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
                                    class="badge bg-light-{{ $paymentColor }} status-badge fs-6">{{ ucwords(str_replace('_', ' ', $paymentStatus)) }}</span>
                                @if (
                                    $booking->payment_status == 'pending' &&
                                        !in_array($booking->rental_status, ['cancelled_by_customer', 'cancelled_by_admin', 'cancelled_payment_issue']))
                                    <a href="{{ route('customer.payment.initiate', ['booking_hashid' => $booking->hashid]) }}"
                                        class="btn btn-sm btn-success ms-2">
                                        <i class="bi bi-credit-card"></i> Bayar Sekarang
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Status Penyewaan:</div>
                            <div class="col-sm-8">
                                @php
                                    $rentalStatus = $booking->rental_status ?? 'unknown';
                                    $rentalColor = 'secondary';
                                    $rentalStatusDisplay = ucwords(str_replace('_', ' ', $rentalStatus));
                                    $comparisonDate = $booking->return_date ?? $booking->end_date;

                                    if (
                                        $comparisonDate &&
                                        \Carbon\Carbon::now()->startOfDay()->gt($comparisonDate->startOfDay()) &&
                                        !in_array($rentalStatus, [
                                            'returned',
                                            'completed',
                                            'cancelled_by_customer',
                                            'cancelled_by_admin',
                                            'cancelled_payment_issue',
                                        ])
                                    ) {
                                        $rentalStatusDisplay = 'Telat Dikembalikan';
                                        $rentalColor = 'danger';
                                    } else {
                                        if (in_array($rentalStatus, ['pending_confirmation', 'pending_review'])) {
                                            $rentalColor = 'warning';
                                        } elseif (in_array($rentalStatus, ['confirmed', 'ready_to_pickup'])) {
                                            $rentalColor = 'info';
                                        } elseif (in_array($rentalStatus, ['picked_up', 'active'])) {
                                            $rentalColor = 'primary';
                                        } elseif (in_array($rentalStatus, ['returned', 'completed'])) {
                                            $rentalColor = 'success';
                                        } elseif (str_contains($rentalStatus, 'cancelled')) {
                                            $rentalColor = 'danger';
                                        }
                                    }
                                @endphp
                                <span
                                    class="badge bg-light-{{ $rentalColor }} status-badge fs-6">{{ $rentalStatusDisplay }}</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Tanggal Booking Dibuat:</div>
                            <div class="col-sm-8">{{ $booking->created_at->format('d M Y, H:i') }}</div>
                        </div>


                        {{-- Informasi Tanggal dan Waktu --}}
                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Tanggal Mulai Sewa:</div>
                            <div class="col-sm-8">{{ $booking->start_date->format('d M Y') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Waktu Mulai Sewa (Pengambilan):</div>
                            <div class="col-sm-8">
                                {{ $booking->start_time ? $booking->start_time->format('H:i') . ' WIB' : '-' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Ekspektasi Tanggal Pengembalian:</div>
                            <div class="col-sm-8">{{ $booking->end_date->format('d M Y') }}</div>
                        </div>

                        {{-- Informasi Pengembalian Aktual (jika sudah diisi admin) --}}
                        @if ($booking->return_date)
                            <hr class="my-3">
                            <h5 class="text-success">Informasi Pengembalian Aktual</h5>
                            <div class="row mb-2">
                                <div class="col-sm-4 detail-label text-success">Tanggal Dikembalikan:</div>
                                <div class="col-sm-8 fw-bold text-success">{{ $booking->return_date->format('d M Y') }}
                                </div>
                            </div>
                            @if ($booking->return_time)
                                <div class="row mb-2">
                                    <div class="col-sm-4 detail-label text-success">Waktu Dikembalikan:</div>
                                    <div class="col-sm-8 fw-bold text-success">
                                        {{ $booking->return_time->format('H:i') . ' WIB' }}</div>
                                </div>
                            @endif
                        @endif
                        <hr class="my-3">


                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Durasi Sewa (Ekspektasi):</div>
                            <div class="col-sm-8">{{ $booking->start_date->diffInDays($booking->end_date) }} Hari</div>
                        </div>
                        {{-- ... (Total Pembayaran, Catatan Customer, Catatan Admin - sama seperti sebelumnya) ... --}}
                        <div class="row mb-2">
                            <div class="col-sm-4 detail-label">Total Pembayaran:</div>
                            <div class="col-sm-8 text-primary fw-bold fs-5">
                                Rp{{ number_format($booking->total_price, 0, ',', '.') }}</div>
                        </div>
                        @if ($booking->notes)
                            <div class="row mb-2">
                                <div class="col-sm-4 detail-label">Catatan dari Anda:</div>
                                <div class="col-sm-8">{{ $booking->notes }}</div>
                            </div>
                        @endif
                        @if ($booking->admin_notes)
                            <div class="row mb-2">
                                <div class="col-sm-4 detail-label">Catatan dari Admin:</div>
                                <div class="col-sm-8 fst-italic bg-light p-2 rounded">{!! nl2br(e($booking->admin_notes)) !!}</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Daftar Item --}}
                {{-- ... (Sama seperti sebelumnya) ... --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4 class="card-title">Item yang Dibooking</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover item-list">
                                <thead>
                                    <tr>
                                        <th style="width:10%">Gambar</th>
                                        <th>Nama Item</th>
                                        <th>Jumlah</th>
                                        <th>Harga/Hari (saat booking)</th>
                                        <th>Subtotal Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rentalDays = $booking->start_date->diffInDays($booking->end_date); @endphp
                                    @forelse($booking->items as $item)
                                        @php
                                            $pivotData = $item->pivot;
                                            $quantityBooked = $pivotData->quantity;
                                            $priceWhenBooked = $pivotData->price_per_item;
                                            $rentalDurationDays = $booking->start_date->diffInDays($booking->end_date);
                                            if ($rentalDurationDays == 0) {
                                                $rentalDurationDays = 1;
                                            }
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
                                                <a href="{{ route('customer.catalog.show', ['item_hash' => $item->hashid]) }}"
                                                    class="text-dark text-decoration-none fw-bold">{{ $item->name }}</a><br>
                                                <small class="text-muted">{{ optional($item->brand)->name }} /
                                                    {{ optional($item->category)->name }}</small>
                                            </td>
                                            <td class="text-center">{{ $quantityBooked }}</td>
                                            <td class="text-end">Rp{{ number_format($priceWhenBooked, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp{{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Tidak ada item dalam booking
                                                ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Riwayat Pembayaran --}}
            {{-- ... (Sama seperti sebelumnya) ... --}}
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Riwayat Transaksi Pembayaran</h4>
                    </div>
                    <div class="card-body">
                        @if ($booking->payments->isEmpty())
                            <p class="text-muted text-center">Belum ada riwayat pembayaran.</p>
                            @if (
                                $booking->payment_status == 'pending' &&
                                    !in_array($booking->rental_status, ['cancelled_by_customer', 'cancelled_by_admin', 'cancelled_payment_issue']))
                                <div class="text-center mt-3">
                                    <a href="{{ route('customer.payment.initiate', ['booking_hashid' => $booking->hashid]) }}"
                                        class="btn btn-success">
                                        <i class="bi bi-credit-card"></i> Lakukan Pembayaran
                                    </a>
                                </div>
                            @endif
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($booking->payments()->orderBy('created_at', 'desc')->get() as $payment)
                                    <li class="list-group-item px-0">
                                        <div class="d-flex justify-content-between">
                                            <span>{{ $payment->transaction_time ? $payment->transaction_time->format('d M Y H:i') : $payment->created_at->format('d M Y H:i') }}</span>
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
                                                class="badge bg-light-{{ $pColor }}">{{ ucwords(str_replace('_', ' ', $pStatus)) }}</span>
                                        </div>
                                        <small class="text-muted">Metode: {{ $payment->payment_type ?? '-' }}</small><br>
                                        <small class="text-muted">ID Transaksi Midtrans:
                                            {{ $payment->midtrans_transaction_id ?? '-' }}</small><br>
                                        <small class="text-muted">Order ID Midtrans:
                                            {{ $payment->payment_gateway_order_id ?? '-' }}</small><br>
                                        <span class="fw-bold">Jumlah:
                                            Rp{{ number_format($payment->gross_amount, 0, ',', '.') }}</span>
                                        @if ($payment->notes)
                                            <br><small><i>Catatan Pembayaran: {{ $payment->notes }}</i></small>
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
