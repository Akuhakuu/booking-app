@extends('admin.layouts.master')

@section('page-title', 'Ubah Status Booking: ' . $booking->booking_code)
{{-- ... breadcrumb sama ... --}}
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Kelola Booking</a></li>
    <li class="breadcrumb-item"><a
            href="{{ route('admin.bookings.show', $booking->hashid) }}">{{ $booking->booking_code }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ubah Status & Pengembalian</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Ubah Status & Detail Pengembalian Booking: <span
                                class="text-primary">{{ $booking->booking_code }}</span></h4>
                    </div>
                    <div class="card-body">
                        @include('admin.partials.alerts')

                        <div class="mb-4 p-3 border rounded bg-light">
                            <h5>Ringkasan Booking</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Customer:</strong> {{ optional($booking->customer)->name ?? 'N/A' }}</p>
                                    <p><strong>Tanggal Mulai:</strong> {{ $booking->start_date->format('d M Y') }}</p>
                                    <p><strong>Waktu Mulai:</strong>
                                        {{ $booking->start_time ? $booking->start_time->format('H:i') . ' WIB' : '-' }}</p>
                                    <p><strong>Ekspektasi Kembali:</strong> {{ $booking->end_date->format('d M Y') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status Pembayaran:</strong> <span
                                            class="fw-bold">{{ ucwords(str_replace('_', ' ', $booking->payment_status)) }}</span>
                                    </p>
                                    <p><strong>Status Penyewaan Saat Ini:</strong> <span
                                            class="fw-bold">{{ ucwords(str_replace('_', ' ', $booking->rental_status)) }}</span>
                                    </p>
                                    @if ($booking->return_date)
                                        <p class="text-success"><strong>Aktual Kembali:</strong>
                                            {{ $booking->return_date->format('d M Y') }}
                                            {{ $booking->return_time ? $booking->return_time->format('H:i') . ' WIB' : '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.bookings.updateStatus', $booking->hashid) }}" method="POST"
                            id="updateBookingStatusForm">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="rental_status" class="form-label">Status Penyewaan Baru <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('rental_status') is-invalid @enderror" id="rental_status"
                                    name="rental_status" required>
                                    <option value="">Pilih status baru...</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('rental_status', $booking->rental_status) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('rental_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Input untuk Return Date dan Return Time --}}
                            <div id="return-details-section"
                                style="display: {{ in_array(old('rental_status', $booking->rental_status), ['returned', 'completed', 'completed_with_issue']) ? 'block' : 'none' }};">
                                <hr>
                                <h5 class="mb-3">Detail Pengembalian Aktual</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="return_date" class="form-label">Tanggal Dikembalikan</label>
                                        <input type="date"
                                            class="form-control @error('return_date') is-invalid @enderror" id="return_date"
                                            name="return_date"
                                            value="{{ old('return_date', $booking->return_date ? $booking->return_date->format('Y-m-d') : '') }}"
                                            min="{{ $booking->start_date->format('Y-m-d') }}">
                                        @error('return_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="return_time" class="form-label">Waktu Dikembalikan</label>
                                        <input type="time"
                                            class="form-control @error('return_time') is-invalid @enderror" id="return_time"
                                            name="return_time"
                                            value="{{ old('return_time', $booking->return_time ? $booking->return_time->format('H:i') : '') }}">
                                        @error('return_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="mb-3">
                                <label for="admin_notes" class="form-label">Catatan Admin (Opsional)</label>
                                <textarea class="form-control @error('admin_notes') is-invalid @enderror" id="admin_notes" name="admin_notes"
                                    rows="4" placeholder="Tambahkan catatan terkait perubahan status atau pengembalian...">{{ old('admin_notes') }}</textarea>
                                @error('admin_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Catatan ini akan ditambahkan (append) ke catatan admin
                                    yang sudah ada (jika ada).</small>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('admin.bookings.show', $booking->hashid) }}"
                                    class="btn btn-secondary ms-2">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rentalStatusSelect = document.getElementById('rental_status');
            const returnDetailsSection = document.getElementById('return-details-section');
            const returnDateInput = document.getElementById('return_date');
            const returnTimeInput = document.getElementById('return_time');

            const statusesRequiringReturnDetails = ['returned', 'completed', 'completed_with_issue'];

            function toggleReturnDetails() {
                if (statusesRequiringReturnDetails.includes(rentalStatusSelect.value)) {
                    returnDetailsSection.style.display = 'block';
                    // Jika belum ada nilai, set default ke hari ini dan jam sekarang
                    if (!returnDateInput.value) {
                        const today = new Date();
                        const yyyy = today.getFullYear();
                        const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based
                        const dd = String(today.getDate()).padStart(2, '0');
                        returnDateInput.value = `${yyyy}-${mm}-${dd}`;
                    }
                    if (!returnTimeInput.value) {
                        const now = new Date();
                        const hh = String(now.getHours()).padStart(2, '0');
                        const min = String(now.getMinutes()).padStart(2, '0');
                        returnTimeInput.value = `${hh}:${min}`;
                    }
                } else {
                    returnDetailsSection.style.display = 'none';
                    // Opsional: clear input jika disembunyikan? Atau biarkan nilainya agar bisa di-undo
                    // returnDateInput.value = '';
                    // returnTimeInput.value = '';
                }
            }

            if (rentalStatusSelect) {
                rentalStatusSelect.addEventListener('change', toggleReturnDetails);
                // Panggil saat load untuk set kondisi awal
                toggleReturnDetails();
            }
        });
    </script>
@endpush
