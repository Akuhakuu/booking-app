@extends('admin.layouts.master')

@section('page-title', 'Edit Pembayaran: ' . $payment->payment_gateway_order_id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Laporan Pembayaran</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Pembayaran</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Pembayaran (Order ID Midtrans: {{ $payment->payment_gateway_order_id }})
                        </h4>
                        <p>Booking Code: <a
                                href="{{-- route('admin.bookings.show', optional($payment->booking)->hashid) --}}">{{ optional($payment->booking)->booking_code ?? 'N/A' }}</a>
                        </p> {{-- Link ke detail booking admin jika ada --}}
                    </div>
                    <div class="card-body">
                        @include('admin.partials.alerts')

                        {{-- Detail Pembayaran (Readonly) --}}
                        <div class="mb-4 p-3 bg-light rounded border">
                            <h5>Detail dari Database</h5>
                            <div class="row">
                                <div class="col-md-6 mb-2"><strong>Customer:</strong>
                                    {{ optional(optional($payment->booking)->customer)->name ?? (optional($payment->customer)->name ?? 'N/A') }}
                                </div>
                                <div class="col-md-6 mb-2"><strong>Gross Amount:</strong>
                                    Rp{{ number_format($payment->gross_amount, 0, ',', '.') }}</div>
                                <div class="col-md-6 mb-2"><strong>Metode Bayar (Midtrans):</strong>
                                    {{ $payment->payment_type ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Waktu Transaksi (Midtrans):</strong>
                                    {{ $payment->transaction_time ? $payment->transaction_time->format('d M Y, H:i:s') : '-' }}
                                </div>
                                <div class="col-md-6 mb-2"><strong>ID Transaksi Midtrans:</strong>
                                    {{ $payment->midtrans_transaction_id ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Status Saat Ini (Midtrans):</strong> <span
                                        class="fw-bold">{{ ucwords(str_replace('_', ' ', $payment->transaction_status)) }}</span>
                                </div>
                            </div>
                            @if ($payment->midtrans_response_payload)
                                <div class="mt-2">
                                    <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                                        href="#midtransPayloadCollapse" role="button" aria-expanded="false"
                                        aria-controls="midtransPayloadCollapse">
                                        Lihat Full Payload Midtrans
                                    </a>
                                    <div class="collapse mt-2" id="midtransPayloadCollapse">
                                        <pre style="max-height: 300px; overflow-y: auto; background-color: #f8f9fa; padding: 10px; border-radius: 4px;"><code>{{ json_encode($payment->midtrans_response_payload, JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Form Edit Status dan Notes --}}
                        <form action="{{ route('admin.payments.update', $payment->hashid) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group mb-3">
                                <label for="transaction_status" class="form-label">Ubah Status Transaksi (Midtrans) <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('transaction_status') is-invalid @enderror"
                                    id="transaction_status" name="transaction_status" required>
                                    <option value="" disabled>Pilih Status...</option>
                                    @foreach ($statuses as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ old('transaction_status', $payment->transaction_status) == $key ? 'selected' : '' }}>
                                            {{ $value }}</option>
                                    @endforeach
                                    {{-- Jika status saat ini tidak ada di $statuses, tampilkan sebagai disabled --}}
                                    @if (!array_key_exists($payment->transaction_status, $statuses))
                                        <option value="{{ $payment->transaction_status }}" selected disabled>
                                            {{ ucwords(str_replace('_', ' ', $payment->transaction_status)) }} (Status Saat
                                            Ini)</option>
                                    @endif
                                </select>
                                <small class="form-text text-muted">Mengubah status di sini akan mempengaruhi status
                                    pembayaran di booking terkait.</small>
                                @error('transaction_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="notes" class="form-label">Catatan Admin untuk Payment Ini</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4">{{ old('notes', $payment->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Update Data Pembayaran</button>
                                <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
