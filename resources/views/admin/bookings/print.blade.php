<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Booking - {{ $booking->booking_code }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img.logo {
            max-height: 60px;
            /* Sesuaikan tinggi logo */
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 22px;
            color: #0056b3;
        }

        .header p {
            margin: 3px 0;
            font-size: 11px;
        }

        .booking-details,
        .customer-details,
        .item-details-section {
            /* Ganti nama class agar tidak konflik */
            margin-bottom: 20px;
        }

        .booking-details h2,
        .customer-details h2,
        .item-details-section h2 {
            font-size: 16px;
            border-bottom: 1px solid #0056b3;
            padding-bottom: 4px;
            margin-bottom: 10px;
            color: #0056b3;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 5px 10px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .detail-grid strong {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        td.text-end {
            text-align: right;
        }

        td.text-center {
            text-align: center;
        }

        .notes-section {
            margin-top: 15px;
            padding: 8px;
            background-color: #f9f9f9;
            border: 1px dashed #ccc;
            font-size: 11px;
            white-space: pre-wrap;
            /* Untuk menampilkan baris baru dari nl2br */
        }

        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 10px;
            color: #777;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 8mm;
                border: none;
                box-shadow: none;
            }

            .btn-print {
                display: none;
            }

            .footer {
                position: fixed;
                bottom: 5mm;
                left: 0;
                right: 0;
                text-align: center;
            }

            body,
            table,
            .detail-grid {
                font-size: 10pt !important;
            }

            th,
            td {
                padding: 5px !important;
            }

            .booking-details h2,
            .customer-details h2,
            .item-details-section h2 {
                font-size: 14pt !important;
            }

            .header h1 {
                font-size: 18pt !important;
            }
        }

        .btn-print-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-print:hover {
            background-color: #0056b3;
        }

        .badge {
            display: inline-block;
            padding: .3em .6em;
            font-size: .7em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .bg-danger {
            background-color: #dc3545 !important;
        }

        .bg-info {
            background-color: #17a2b8 !important;
        }

        .bg-primary {
            background-color: #007bff !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }

        /* Tambahkan jika belum ada */
    </style>
</head>

<body>
    <div class="btn-print-container">
        <button onclick="window.print()" class="btn-print">Cetak Bukti Booking</button>
    </div>

    <div class="container">
        <div class="header">
            @if ($storeDetails && $storeDetails->logo_path && Storage::disk('public')->exists($storeDetails->logo_path))
                <img src="{{ Storage::url($storeDetails->logo_path) }}" alt="{{ $storeDetails->name ?? 'Logo' }}"
                    class="logo">
            @endif
            <h1>{{ $storeDetails->name ?? 'Bukti Booking Penyewaan' }}</h1>
            @if ($storeDetails->tagline)
                <p>{{ $storeDetails->tagline }}</p>
            @endif
            @if ($storeDetails->address)
                <p>{{ $storeDetails->address }}</p>
            @endif
            <p>
                @if ($storeDetails->phone)
                    Telp: {{ $storeDetails->phone }}
                @endif
                @if ($storeDetails->phone && $storeDetails->email)
                    |
                @endif
                @if ($storeDetails->email)
                    Email: {{ $storeDetails->email }}
                @endif
            </p>
            @if ($storeDetails->website)
                <p>Website: <a href="{{ $storeDetails->website }}" target="_blank"
                        style="color: #0056b3; text-decoration: none;">{{ $storeDetails->website }}</a></p>
            @endif
            @if ($storeDetails->operational_hours)
                <p style="font-size: 10px;">Jam Operasional: {!! nl2br(e($storeDetails->operational_hours)) !!}</p>
            @endif
        </div>

        <div class="booking-details">
            <h2>Detail Booking</h2>
            <div class="detail-grid">
                <strong>Kode Booking:</strong> <span
                    style="font-weight: bold; color: #0056b3;">{{ $booking->booking_code }}</span>
                <strong>Tanggal Booking:</strong> <span>{{ $booking->created_at->format('d M Y, H:i') }} WIB</span>
                <strong>Status Pembayaran:</strong>
                <span>
                    @php
                        $paymentStatus = $booking->payment_status ?? 'unknown';
                        $paymentColorClass = 'bg-secondary';
                        if ($paymentStatus == 'pending') {
                            $paymentColorClass = 'bg-warning';
                        } elseif ($paymentStatus == 'paid') {
                            $paymentColorClass = 'bg-success';
                        } elseif (in_array($paymentStatus, ['failed', 'cancelled', 'expired', 'deny'])) {
                            $paymentColorClass = 'bg-danger';
                        } elseif ($paymentStatus == 'challenge') {
                            $paymentColorClass = 'bg-info';
                        }
                    @endphp
                    <span
                        class="badge {{ $paymentColorClass }}">{{ ucwords(str_replace('_', ' ', $paymentStatus)) }}</span>
                </span>
                <strong>Status Penyewaan:</strong>
                <span>
                    @php
                        $rentalStatus = $booking->rental_status ?? 'unknown';
                        $rColorClass = 'bg-secondary';
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
                            $rColorClass = 'bg-danger';
                        } else {
                            if (in_array($rentalStatus, ['pending_confirmation', 'pending_review'])) {
                                $rColorClass = 'bg-warning';
                            } elseif (in_array($rentalStatus, ['confirmed', 'ready_to_pickup'])) {
                                $rColorClass = 'bg-info';
                            } elseif (in_array($rentalStatus, ['picked_up', 'active'])) {
                                $rColorClass = 'bg-primary';
                            } elseif (in_array($rentalStatus, ['returned', 'completed', 'completed_with_issue'])) {
                                $rColorClass = 'bg-success';
                            } elseif (str_contains($rentalStatus, 'cancelled')) {
                                $rColorClass = 'bg-danger';
                            }
                        }
                    @endphp
                    <span class="badge {{ $rColorClass }}">{{ $rentalStatusDisplay }}</span>
                </span>
            </div>
        </div>

        <div class="customer-details">
            <h2>Informasi Customer</h2>
            <div class="detail-grid">
                <strong>Nama Customer:</strong> <span>{{ optional($booking->customer)->name ?? 'N/A' }}</span>
                <strong>Email:</strong> <span>{{ optional($booking->customer)->email ?? 'N/A' }}</span>
                <strong>No. Telepon:</strong> <span>{{ optional($booking->customer)->phone_number ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="item-details-section"> {{-- Nama class diubah --}}
            <h2>Detail Waktu & Durasi Sewa</h2>
            <div class="detail-grid">
                <strong>Tanggal Mulai Sewa:</strong> <span>{{ $booking->start_date->format('d M Y') }}</span>
                <strong>Waktu Pengambilan:</strong>
                <span>{{ $booking->start_time ? $booking->start_time->format('H:i') . ' WIB' : '-' }}</span>
                <strong>Ekspektasi Tgl. Kembali:</strong> <span>{{ $booking->end_date->format('d M Y') }}</span>
                <strong>Durasi Sewa:</strong> <span>{{ $booking->start_date->diffInDays($booking->end_date) }}
                    Hari</span>
            </div>
            @if ($booking->return_date)
                <h3 style="font-size: 14px; margin-top: 15px; margin-bottom: 5px; color: green;">Pengembalian Aktual:
                </h3>
                <div class="detail-grid">
                    <strong>Tanggal Dikembalikan:</strong> <span
                        style="font-weight:bold; color:green;">{{ $booking->return_date->format('d M Y') }}</span>
                    @if ($booking->return_time)
                        <strong>Waktu Dikembalikan:</strong> <span
                            style="font-weight:bold; color:green;">{{ $booking->return_time->format('H:i') . ' WIB' }}</span>
                    @endif
                </div>
            @endif
        </div>


        <div class="item-details-section"> {{-- Nama class diubah --}}
            <h2>Item yang Dibooking</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 40%;">Nama Item</th>
                        <th class="text-center" style="width: 15%;">Jumlah</th>
                        <th class="text-end" style="width: 20%;">Harga/Hari</th>
                        <th class="text-end" style="width: 20%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $rentalDurationDays = $booking->start_date->diffInDays($booking->end_date);
                        if ($rentalDurationDays <= 0) {
                            $rentalDurationDays = 1;
                        }
                    @endphp
                    @forelse($booking->items as $index => $item)
                        @php
                            $pivotData = $item->pivot;
                            $quantityBooked = $pivotData->quantity;
                            $priceWhenBooked = $pivotData->price_per_item;
                            $itemSubtotal = $priceWhenBooked * $quantityBooked * $rentalDurationDays;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $item->name }}
                                <small style="display: block; color: #666;">{{ optional($item->brand)->name }} /
                                    {{ optional($item->category)->name }}</small>
                            </td>
                            <td class="text-center">{{ $quantityBooked }}</td>
                            <td class="text-end">Rp{{ number_format($priceWhenBooked, 0, ',', '.') }}</td>
                            <td class="text-end">Rp{{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada item dalam booking ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($booking->items->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3" style="border: none; border-bottom: 1px solid #ddd;"></td>
                            {{-- Kolom kosong untuk alignment --}}
                            <td
                                style="text-align: right; font-weight: bold; border-top: 1px solid #aaa; border-bottom: 1px solid #ddd;">
                                Total Biaya Sewa:</td>
                            <td
                                style="text-align: right; font-weight: bold; border-top: 1px solid #aaa; border-bottom: 1px solid #ddd; font-size: 1.05em;">
                                Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if ($booking->notes)
            <div class="notes-section">
                <strong>Catatan dari Customer:</strong><br>
                {{ $booking->notes }}
            </div>
        @endif

        @if ($booking->admin_notes)
            <div class="notes-section" style="margin-top: 10px; background-color: #eef7ff;">
                <strong>Catatan dari Admin:</strong><br>
                {!! nl2br(e($booking->admin_notes)) !!}
            </div>
        @endif

        <div class="footer">
            <p>Terima kasih telah melakukan penyewaan di {{ $storeDetails->name ?? 'Tempat Kami' }}.</p>
            <p>Dokumen ini dicetak pada: {{ now()->format('d M Y, H:i') }} WIB
                @if ($booking->user)
                    | Diproses oleh: {{ $booking->user->name }}
                @endif
            </p>
        </div>
    </div>
</body>

</html>
