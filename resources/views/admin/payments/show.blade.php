@extends('admin.layouts.master') {{-- Atau layout cetak khusus jika ada --}}

@section('page-title', 'Detail Pembayaran: ' . ($payment->payment_gateway_order_id ?? $payment->booking->booking_code))

@push('styles')
    <style>
        body {
            /* Untuk print, kadang perlu set background putih eksplisit */
            background-color: #fff !important;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        .text-center {
            text-align: center !important;
        }

        .text-end {
            text-align: right !important;
        }

        .fw-bold {
            font-weight: bold !important;
        }

        .mt-4 {
            margin-top: 1.5rem !important;
        }

        .mb-1 {
            margin-bottom: 0.25rem !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .text-primary {
            color: #0d6efd !important;
        }

        /* Sesuaikan warna primary Anda */
        .text-muted {
            color: #6c757d !important;
        }

        @media print {

            body,
            .page-content,
            .invoice-box {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
            }

            .no-print {
                display: none !important;
            }

            /* Pastikan sidebar dan elemen lain tidak ikut tercetak */
            #sidebar,
            header,
            footer,
            .page-heading,
            .breadcrumb-header,
            .card-header .btn {
                display: none !important;
            }

            #main,
            #main-content {
                padding: 0 !important;
                margin: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Laporan Pembayaran</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
@endsection

@section('content')
    <div class="page-heading no-print"> {{-- Sembunyikan page-heading saat print --}}
        <h3>Detail Transaksi Pembayaran</h3>
        <div class="d-flex justify-content-between align-items-center">
            <p class="text-subtitle text-muted">Order ID Midtrans: {{ $payment->payment_gateway_order_id ?? 'N/A' }}</p>
            <div>
                <button onclick="window.print()" class="btn btn-primary me-1"><i class="bi bi-printer-fill"></i>
                    Cetak</button>
                <a href="{{ route('admin.payments.edit', $payment->hashid) }}" class="btn btn-info"><i
                        class="bi bi-pencil-fill"></i> Edit Status</a>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="invoice-box">
                            <table>
                                <tr class="top">
                                    <td colspan="2">
                                        <table>
                                            <tr>
                                                <td class="title">
                                                    {{-- <img src="{{ asset('path/to/your/logo.png') }}" style="width: 100%; max-width: 150px" /> --}}
                                                    <h2 class="text-primary">INVOICE PEMBAYARAN</h2>
                                                </td>
                                                <td>
                                                    ID Transaksi:
                                                    {{ $payment->midtrans_transaction_id ?? $payment->payment_gateway_order_id }}<br />
                                                    Dibuat: {{ $payment->created_at->format('d M Y, H:i') }}<br />
                                                    Status: <span
                                                        class="fw-bold">{{ ucwords(str_replace('_', ' ', $payment->transaction_status)) }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr class="information">
                                    <td colspan="2">
                                        <table>
                                            <tr>
                                                <td>
                                                    <strong>Ditagihkan Kepada:</strong><br />
                                                    {{ optional(optional($payment->booking)->customer)->name ?? 'N/A' }}<br />
                                                    {{ optional(optional($payment->booking)->customer)->email ?? '' }}<br />
                                                    {{ optional(optional($payment->booking)->customer)->phone_number ?? '' }}<br />
                                                    {{ optional(optional($payment->booking)->customer)->address ?? '' }}
                                                </td>
                                                <td>
                                                    <strong>Rental Outdoor Anda</strong><br />
                                                    Alamat Perusahaan Anda<br />
                                                    Email Perusahaan Anda<br />
                                                    Telepon Perusahaan Anda
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr class="heading">
                                    <td>Detail Booking</td>
                                    <td class="text-end"></td>
                                </tr>
                                <tr class="details">
                                    <td>Kode Booking</td>
                                    <td class="text-end">{{ optional($payment->booking)->booking_code ?? 'N/A' }}</td>
                                </tr>
                                <tr class="details">
                                    <td>Tanggal Sewa</td>
                                    <td class="text-end">
                                        {{ optional($payment->booking)->start_date ? $payment->booking->start_date->format('d M Y') : '-' }}
                                        s/d
                                        {{ optional($payment->booking)->end_date ? $payment->booking->end_date->format('d M Y') : '-' }}
                                    </td>
                                </tr>
                                <tr class="details">
                                    <td>Durasi</td>
                                    <td class="text-end">
                                        @if (optional($payment->booking)->start_date && optional($payment->booking)->end_date)
                                            {{ $payment->booking->start_date->diffInDays($payment->booking->end_date) }}
                                            Hari
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>


                                <tr class="heading">
                                    <td>Item</td>
                                    <td class="text-end">Subtotal</td>
                                </tr>

                                @if (optional($payment->booking)->items)
                                    @php
                                        $bookingDays =
                                            optional($payment->booking)->start_date &&
                                            optional($payment->booking)->end_date
                                                ? $payment->booking->start_date->diffInDays($payment->booking->end_date)
                                                : 1;
                                        if ($bookingDays == 0) {
                                            $bookingDays = 1;
                                        }
                                    @endphp
                                    @foreach ($payment->booking->items as $item)
                                        <tr class="item">
                                            <td>{{ $item->name }} ({{ $item->pivot->quantity }} x
                                                Rp{{ number_format($item->pivot->price_per_item, 0, ',', '.') }} x
                                                {{ $bookingDays }} hari)</td>
                                            <td class="text-end">
                                                Rp{{ number_format($item->pivot->quantity * $item->pivot->price_per_item * $bookingDays, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif


                                <tr class="total">
                                    <td></td>
                                    <td class="text-end fw-bold">Total:
                                        Rp{{ number_format($payment->gross_amount, 0, ',', '.') }}</td>
                                </tr>

                                <tr class="heading">
                                    <td>Informasi Pembayaran</td>
                                    <td class="text-end"></td>
                                </tr>
                                <tr class="details">
                                    <td>Metode Pembayaran (Midtrans)</td>
                                    <td class="text-end">{{ $payment->payment_type ?? '-' }}</td>
                                </tr>
                                <tr class="details">
                                    <td>Waktu Transaksi (Midtrans)</td>
                                    <td class="text-end">
                                        {{ $payment->transaction_time ? $payment->transaction_time->format('d M Y, H:i:s') : '-' }}
                                    </td>
                                </tr>
                                @if ($payment->settlement_time)
                                    <tr class="details">
                                        <td>Waktu Settlement (Midtrans)</td>
                                        <td class="text-end">{{ $payment->settlement_time->format('d M Y, H:i:s') }}</td>
                                    </tr>
                                @endif
                                @if ($payment->notes)
                                    <tr class="heading">
                                        <td colspan="2">Catatan Admin untuk Pembayaran Ini</td>
                                    </tr>
                                    <tr class="details">
                                        <td colspan="2">{{ $payment->notes }}</td>
                                    </tr>
                                @endif
                            </table>
                            @if ($payment->midtrans_response_payload)
                                <div class="mt-4 no-print">
                                    <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                                        href="#midtransFullPayload" role="button" aria-expanded="false"
                                        aria-controls="midtransFullPayload">
                                        Lihat Full Payload Midtrans (Debug)
                                    </a>
                                    <div class="collapse mt-2" id="midtransFullPayload">
                                        <pre style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa; padding: 10px; border-radius: 4px;"><code>{{ json_encode($payment->midtrans_response_payload, JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
