@extends('customer.layouts.master')

@section('page-title', 'Booking Saya')

@push('styles')
    <style>
        .status-badge {
            font-size: 0.9em;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading mb-4">
        <h3>Booking Saya</h3>
        <p class="text-subtitle text-muted">Riwayat dan status semua booking alat outdoor Anda.</p>
    </div>

    <div class="page-content">
        @include('customer.partials.alerts') {{-- Sesuaikan path jika perlu --}}

        <section class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Daftar Booking</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-lg" id="my-bookings-table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode Booking</th>
                                        <th>Tgl Mulai</th>
                                        <th>Waktu Mulai</th> {{-- Kolom Baru --}}
                                        <th>Tgl Pengembalian</th> {{-- Judul diubah --}}
                                        <th>Durasi</th>
                                        <th>Total</th>
                                        <th>Status Bayar</th>
                                        <th>Status Sewa</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#my-bookings-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('customer.bookings.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'booking_code',
                        name: 'booking_code'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'start_time',
                        name: 'start_time'
                    }, // Data untuk start_time
                    {
                        data: 'end_date',
                        name: 'end_date'
                    }, // Data untuk ekspektasi kembali
                    {
                        data: 'duration',
                        name: 'duration',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total_price',
                        name: 'total_price'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'rental_status_display',
                        name: 'rental_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '12%'
                    }
                ],
                order: [
                    [2, 'desc']
                ], // Default order by start_date descending
                language: {
                    /* ... Opsi Bahasa Indonesia ... */
                }
            });
        });
    </script>
@endpush
