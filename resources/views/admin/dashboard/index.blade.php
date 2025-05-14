@extends('admin.layouts.master') {{-- Sesuaikan dengan layout master admin Anda --}}

@section('page-title', 'Admin Dashboard')

@section('breadcrumb')
    {{-- Breadcrumb tidak perlu link ke diri sendiri --}}
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            {{-- Kolom untuk Stat Cards --}}
            <div class="col-12 col-lg-9">
                <div class="row">
                    {{-- Card Total Items --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon purple mb-2">
                                            <i class="iconly-boldShow bi-box-seam"></i> {{-- Icon Items --}}
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Total Items</h6>
                                        <h6 class="font-extrabold mb-0">{{ $totalItems ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Card Total Customers --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon blue mb-2">
                                            <i class="iconly-boldProfile bi-people-fill"></i> {{-- Icon Customers --}}
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Total Customers</h6>
                                        <h6 class="font-extrabold mb-0">{{ $totalCustomers ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Card Pending Bookings --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon green mb-2">
                                            <i class="iconly-boldAdd-User bi-calendar-plus"></i> {{-- Icon Pending Bookings --}}
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Booking Pending</h6>
                                        <h6 class="font-extrabold mb-0">{{ $pendingBookings ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Card Active Bookings --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon orange mb-2"> <!-- Warna berbeda -->
                                            <i class="iconly-boldBookmark bi-calendar-check"></i> <!-- Icon berbeda -->
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Booking Aktif</h6>
                                        <h6 class="font-extrabold mb-0">{{ $activeBookings ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Card Total Brands & Categories (contoh gabungan atau pisah) --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon red mb-2">
                                            <i class="iconly-boldBookmark bi-tags-fill"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Brands</h6>
                                        <h6 class="font-extrabold mb-0">{{ $totalBrands ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon red mb-2">
                                            <i class="iconly-boldBookmark bi-bookmark-fill"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Categories</h6>
                                        <h6 class="font-extrabold mb-0">{{ $totalCategories ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon bg-success text-white mb-2">
                                            <i class="bi bi-calendar2-check-fill"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Booking Selesai</h6>
                                        <h6 class="font-extrabold mb-0">{{ $completedBookings ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tambahkan card lain jika perlu (Verified Payments, dll.) --}}

                </div>

                {{-- Tabel Recent Bookings (Contoh) --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>5 Booking Terbaru</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-lg">
                                        <thead>
                                            <tr>
                                                <th>Kode Booking</th>
                                                <th>Customer</th>
                                                <th>Tgl Mulai</th>
                                                <th>Tgl Selesai</th>
                                                <th>Status Bayar</th> {{-- Kolom Status Bayar --}}
                                                <th>Status Sewa</th> {{-- Kolom Status Sewa --}}
                                                <th>Item (Jumlah)</th>
                                                <th>Aksi</th> {{-- Tambah kolom aksi --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentBookings as $booking)
                                                <tr>
                                                    <td class="text-bold-500">
                                                        {{-- Link ke detail booking admin --}}
                                                        <a
                                                            href="{{ route('admin.bookings.show', $booking->hashid) }}">{{ $booking->booking_code ?? 'N/A' }}</a>
                                                    </td>
                                                    <td>{{ optional($booking->customer)->name ?? 'N/A' }}</td>
                                                    <td class="text-bold-500">
                                                        {{ $booking->start_date ? $booking->start_date->format('d M Y') : '-' }}
                                                    </td>
                                                    <td>{{ $booking->end_date ? $booking->end_date->format('d M Y') : '-' }}
                                                    </td>
                                                    <td>
                                                        {{-- Logika untuk Payment Status --}}
                                                        @php
                                                            $paymentStatus = $booking->payment_status ?? 'unknown';
                                                            $paymentColor = 'secondary';
                                                            if ($paymentStatus == 'pending') {
                                                                $paymentColor = 'warning';
                                                            } elseif ($paymentStatus == 'paid') {
                                                                $paymentColor = 'success';
                                                            } elseif (
                                                                in_array($paymentStatus, [
                                                                    'failed',
                                                                    'cancelled',
                                                                    'expired',
                                                                    'deny',
                                                                ])
                                                            ) {
                                                                $paymentColor = 'danger';
                                                            } elseif ($paymentStatus == 'challenge') {
                                                                $paymentColor = 'info';
                                                            }
                                                        @endphp
                                                        <span
                                                            class="badge bg-light-{{ $paymentColor }}">{{ ucwords(str_replace('_', ' ', $paymentStatus)) }}</span>
                                                    </td>
                                                    <td>
                                                        {{-- Logika untuk Rental Status --}}
                                                        @php
                                                            $rentalStatus = $booking->rental_status ?? 'unknown';
                                                            $rentalColor = 'secondary';
                                                            $isLate = false;
                                                            $rentalStatusDisplay = ucwords(
                                                                str_replace('_', ' ', $rentalStatus),
                                                            );

                                                            if (
                                                                Carbon\Carbon::now()
                                                                    ->startOfDay()
                                                                    ->gt($booking->end_date->startOfDay()) &&
                                                                !in_array($rentalStatus, [
                                                                    'returned',
                                                                    'completed',
                                                                    'cancelled_by_customer',
                                                                    'cancelled_by_admin',
                                                                    'cancelled_payment_issue',
                                                                ])
                                                            ) {
                                                                $isLate = true;
                                                                $rentalStatusDisplay = 'Telat Dikembalikan';
                                                                $rentalColor = 'danger';
                                                            } else {
                                                                if (
                                                                    in_array($rentalStatus, [
                                                                        'pending_confirmation',
                                                                        'pending_review',
                                                                    ])
                                                                ) {
                                                                    $rentalColor = 'warning';
                                                                } elseif (
                                                                    in_array($rentalStatus, [
                                                                        'confirmed',
                                                                        'ready_to_pickup',
                                                                    ])
                                                                ) {
                                                                    $rentalColor = 'info';
                                                                } elseif (
                                                                    in_array($rentalStatus, ['picked_up', 'active'])
                                                                ) {
                                                                    $rentalColor = 'primary';
                                                                } elseif (
                                                                    in_array($rentalStatus, ['returned', 'completed'])
                                                                ) {
                                                                    $rentalColor = 'success';
                                                                } elseif (str_contains($rentalStatus, 'cancelled')) {
                                                                    $rentalColor = 'danger';
                                                                }
                                                            }
                                                        @endphp
                                                        <span
                                                            class="badge bg-light-{{ $rentalColor }}">{{ $rentalStatusDisplay }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $booking->items->count() }} Item
                                                        ({{ $booking->items->sum('pivot.quantity') }} unit)
                                                    </td>
                                                    <td>
                                                        {{-- Tombol Aksi untuk Admin --}}
                                                        <a href="{{ route('admin.bookings.show', $booking->hashid) }}"
                                                            class="btn btn-sm btn-outline-info" title="Detail">
                                                            <i class="bi bi-eye-fill"></i>
                                                        </a>
                                                        <a href="{{ route('admin.bookings.editStatus', $booking->hashid) }}"
                                                            class="btn btn-sm btn-outline-primary ms-1"
                                                            title="Ubah Status Sewa">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        {{-- Tombol edit payment jika perlu --}}
                                                        {{-- @php
                                                    $latestPayment = $booking->payments()->latest()->first();
                                                @endphp
                                                @if ($latestPayment)
                                                    <a href="{{ route('admin.payments.edit', $latestPayment->hashid) }}" class="btn btn-sm btn-outline-warning ms-1" title="Edit Pembayaran">
                                                        <i class="bi bi-credit-card"></i>
                                                    </a>
                                                @endif --}}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">Belum ada data booking terbaru.
                                                    </td> {{-- colspan jadi 8 --}}
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom untuk Info Tambahan (jika ada) --}}
                <div class="col-12 col-lg-3">
                    <div class="card">
                        <div class="card-header">
                            <h4>Ringkasan Pembayaran</h4>
                        </div>
                        <div class="card-body">
                            {{-- Contoh menampilkan status pembayaran --}}
                            <div class="d-flex align-items-center mb-2">
                                <div class="stats-icon green me-3">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold mb-0">Verified</h6>
                                    <h6 class="font-extrabold mb-0">{{ $verifiedPayments ?? 0 }}</h6>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="stats-icon yellow me-3"> {{-- Kuning untuk pending --}}
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold mb-0">Pending</h6>
                                    <h6 class="font-extrabold mb-0">{{ $pendingPayments ?? 0 }}</h6>
                                </div>
                            </div>
                            {{-- Tambahkan info lain di sini --}}
                        </div>
                    </div>
                    {{-- Card lain jika perlu --}}
                </div>
        </section>
    </div>
@endsection

{{-- Jika perlu script JS khusus dashboard, push di sini --}}
{{-- @push('scripts')
<script>
    // Contoh script
</script>
@endpush --}}
