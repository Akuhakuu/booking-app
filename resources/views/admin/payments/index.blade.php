@extends('admin.layouts.master')

@section('page-title', 'Laporan Pembayaran Admin')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Laporan Pembayaran</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Laporan Semua Transaksi Pembayaran</h4>
                    </div>
                    <div class="card-body">
                        @include('admin.partials.alerts')
                        <div class="table-responsive">
                            <table id="admin-payments-table" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode Booking</th>
                                        <th>Customer</th>
                                        <th>Order ID Midtrans</th> {{-- payment_gateway_order_id --}}
                                        <th>ID Transaksi Midtrans</th> {{-- midtrans_transaction_id --}}
                                        <th>Jumlah</th>
                                        <th>Metode Bayar</th>
                                        <th>Waktu Transaksi</th>
                                        <th>Status Midtrans</th>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- DataTables JS --}}
    <script>
        $(document).ready(function() {
            $('#admin-payments-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.payments.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'booking_code',
                        name: 'booking.booking_code'
                    },
                    {
                        data: 'customer_name',
                        name: 'booking.customer.name'
                    },
                    {
                        data: 'payment_gateway_order_id',
                        name: 'payment_gateway_order_id'
                    }, // Ditambahkan
                    {
                        data: 'midtrans_transaction_id',
                        name: 'midtrans_transaction_id'
                    }, // Ditambahkan
                    {
                        data: 'gross_amount',
                        name: 'gross_amount'
                    }, // Menggunakan gross_amount
                    {
                        data: 'payment_type',
                        name: 'payment_type'
                    }, // Ditambahkan
                    {
                        data: 'transaction_time',
                        name: 'transaction_time'
                    }, // Menggunakan transaction_time
                    {
                        data: 'transaction_status',
                        name: 'transaction_status'
                    }, // Menggunakan transaction_status
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [7, 'desc']
                ] // Order by transaction_time descending
            });
        });
    </script>
@endpush
