@extends('customer.layouts.master') {{-- Sesuaikan dengan layout master customer Anda --}}

@section('page-title', 'Dashboard Customer')

@push('styles')
    {{-- CSS tambahan jika perlu --}}
    <style>
        .status-badge {
            font-size: 0.85rem;
            /* Sedikit lebih kecil agar pas */
            padding: .4em .7em;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <h3>Selamat Datang, {{ $customer->name ?? 'Customer' }}!</h3>
        <p class="text-subtitle text-muted">Ini adalah halaman ringkasan aktivitas rental Anda di Hawari Outdoor.</p>

        {{-- Tampilkan pesan error jika ada masalah saat load data booking dari controller --}}
        @if (isset($errorMessage) && $errorMessage)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errorMessage }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Tampilkan pesan sukses/error dari redirect session (jika ada dari proses lain) --}}
        @include('admin.partials.alerts') {{-- Ganti path jika ada partial alert khusus customer --}}
    </div>

    <div class="page-content">
        <section class="row">
            {{-- Kolom untuk Stat Cards --}}
            <div class="col-12 col-lg-8">
                <div class="row">
                    {{-- Card Booking Aktif/Pending Pembayaran/Pending Konfirmasi --}}
                    <div class="col-6 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon purple mb-2">
                                            <i class="iconly-boldUser"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Booking Aktif & Proses</h6>
                                        <h6 class="font-extrabold mb-0">{{ $activeBookingsCount ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Card Booking Selesai (Sudah Dikembalikan) --}}
                    <div class="col-6 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon green mb-2">
                                            <i class="bi bi-calendar-check-fill"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Booking Selesai</h6>
                                        <h6 class="font-extrabold mb-0">{{ $completedBookingsCount ?? 0 }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Booking Terbaru Anda --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>3 Booking Terakhir Anda</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kode Booking</th>
                                                <th>Tgl Mulai</th>
                                                <th>Tgl Kembali</th>
                                                <th>Status Bayar</th>
                                                <th>Status Sewa</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentBookings ?? [] as $booking)
                                                <tr>
                                                    <td class="text-bold-500">{{ $booking->booking_code ?? 'N/A' }}</td>
                                                    <td>{{ $booking->start_date ? $booking->start_date->format('d M Y') : '-' }}
                                                    </td>
                                                    <td>{{ $booking->end_date ? $booking->end_date->format('d M Y') : '-' }}
                                                    </td>
                                                    <td>
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
                                                            class="badge bg-light-{{ $paymentColor }} status-badge">{{ ucwords(str_replace('_', ' ', $paymentStatus)) }}</span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $rentalStatus = $booking->rental_status ?? 'unknown';
                                                            $rentalColor = 'secondary';
                                                            $isLate = false;

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
                                                                $rentalStatusDisplay = ucwords(
                                                                    str_replace('_', ' ', $rentalStatus),
                                                                );
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
                                                            class="badge bg-light-{{ $rentalColor }} status-badge">{{ $rentalStatusDisplay }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('customer.bookings.show', ['booking_hashid' => $booking->hashid]) }}"
                                                            class="btn btn-sm btn-outline-info"
                                                            title="Lihat Detail Booking">
                                                            <i class="bi bi-eye-fill"></i>
                                                        </a>
                                                        @if (
                                                            $booking->payment_status == 'pending' &&
                                                                !in_array($booking->rental_status, ['cancelled_by_customer', 'cancelled_by_admin', 'cancelled_payment_issue']))
                                                            <a href="{{ route('customer.payment.initiate', ['booking_hashid' => $booking->hashid]) }}"
                                                                class="btn btn-sm btn-success ms-1"
                                                                title="Lanjutkan Pembayaran">
                                                                <i class="bi bi-credit-card-fill"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">Anda belum memiliki riwayat
                                                        booking.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{-- Link ke semua booking (jika ada halamannya) --}}
                                @if (isset($recentBookings) && $recentBookings->isNotEmpty())
                                    <div class="text-center mt-3">
                                        <a href="{{ route('customer.bookings.index') }}">Lihat Semua Booking Saya</a>
                                        {{-- Pastikan route ini ada --}}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom untuk Quick Actions / Info Tambahan Customer --}}
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Aksi Cepat</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('customer.catalog.index') }}" class="btn btn-outline-primary"> <i
                                    class="bi bi-search me-2"></i> Cari & Sewa Alat</a>
                            <a href="{{ route('customer.bookings.index') }}" class="btn btn-outline-secondary"> <i
                                    class="bi bi-calendar-check me-2"></i> Booking Saya</a> {{-- Pastikan route ini ada --}}
                            <a href="{{ route('customer.profile.edit') }}" class="btn btn-outline-info"> <i
                                    class="bi bi-person-circle me-2"></i> Edit Profil</a> {{-- Pastikan route ini ada --}}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4>Butuh Bantuan?</h4>
                    </div>
                    <div class="card-body">
                        <p>Jika Anda mengalami kendala atau memiliki pertanyaan, jangan ragu menghubungi kami.</p>

                        {{-- Link untuk WhatsApp (lebih umum untuk web) --}}
                        <p>
                            <i class="bi bi-whatsapp me-2"></i>
                            <a href="https://wa.me/6285119478701" target="_blank">0851-1947-8701 (WhatsApp)</a>
                        </p>

                        <p>
                            <i class="bi bi-envelope-fill me-2"></i>
                            <a href="mailto:devkitaid@gmail.com">devkitaid@gmail.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- Script tambahan jika diperlukan untuk dashboard customer --}}
@endpush
