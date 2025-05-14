@extends('customer.layouts.master') {{-- Sesuaikan layout --}}

{{-- Ambil nama booking code jika ada --}}
@section('page-title', 'Pembayaran Booking ' . ($booking->booking_code ?? ''))

@push('styles')
    {{-- CSS tambahan jika perlu --}}
@endpush

@section('content')
    <div class="page-heading mb-4">
        <h3>Pembayaran Booking</h3>
        <p class="text-subtitle text-muted">Selesaikan pembayaran untuk booking {{ $booking->booking_code ?? '' }}.</p>
    </div>

    <div class="page-content">
        <section class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                @include('admin.partials.alerts') {{-- Tampilkan pesan error/info --}}

                @if ($snapToken)
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-3">Total Pembayaran</h5>
                            <h2 class="text-primary fw-bold mb-4">
                                Rp{{ number_format($booking->total_price ?? 0, 0, ',', '.') }}</h2>
                            <p class="text-muted">Klik tombol di bawah untuk memilih metode pembayaran Anda melalui Midtrans.
                            </p>

                            {{-- Tombol untuk membuka Midtrans Snap --}}
                            <button id="pay-button" class="btn btn-lg btn-success mt-3 px-5">
                                <i class="bi bi-shield-check-fill me-2"></i> Bayar Sekarang
                            </button>

                            <p class="mt-4 text-muted small">Anda akan diarahkan ke halaman pembayaran aman Midtrans.</p>
                            <p class="mt-2"><a href="{{ route('customer.dashboard') }}">Kembali ke Dashboard</a></p>
                            {{-- Atau ke My Bookings --}}
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger text-center">
                        <h4 class="alert-heading">Error!</h4>
                        <p>Gagal memuat halaman pembayaran. Token pembayaran tidak valid atau tidak ditemukan.</p>
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-primary mt-2">Kembali ke Dashboard</a>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- Hanya load Snap JS jika Snap Token ada --}}
    @if ($snapToken)
        {{-- Script Midtrans Snap JS --}}
        <script
            src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script type="text/javascript">
            var payButton = document.getElementById('pay-button');
            payButton.addEventListener('click', function() {
                window.snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        console.log('Midtrans Payment Success:', result);
                        Swal.fire('Pembayaran Sukses!', 'Terima kasih, pembayaran Anda berhasil.',
                            'success').then(() => {
                            // === Redirect ke Detail Booking ===
                            window.location.href =
                                '{{ route('customer.bookings.show', ['booking_hashid' => $booking->hashid]) }}';
                        });
                    },
                    onPending: function(result) {
                        console.log('Midtrans Payment Pending:', result);
                        Swal.fire('Pembayaran Pending', 'Pembayaran Anda menunggu konfirmasi.', 'info')
                            .then(() => {
                                // === Redirect ke Detail Booking ===
                                window.location.href =
                                    '{{ route('customer.bookings.show', ['booking_hashid' => $booking->hashid]) }}';
                            });
                    },
                    onError: function(result) {
                        /* ... handle error ... */ },
                    onClose: function() {
                        console.log('Customer closed the popup without finishing the payment');
                        Swal.fire({
                            /* ... konfirmasi tutup ... */ }).then((result) => {
                            if (result.isConfirmed) {
                                // === Redirect ke Detail Booking ===
                                window.location.href =
                                    '{{ route('customer.bookings.show', ['booking_hashid' => $booking->hashid]) }}';
                            }
                        });
                    }
                });
            });
        </script>
        {{-- SweetAlert untuk callback Snap --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endif
@endpush
