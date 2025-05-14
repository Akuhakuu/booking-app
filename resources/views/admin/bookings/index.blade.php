@extends('admin.layouts.master')

@section('page-title', 'Kelola Semua Booking')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kelola Booking</li>
@endsection

@push('styles')
    {{-- DataTables CSS jika belum ada di master layout --}}
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css"> --}}
    <style>
        #admin-bookings-table th,
        #admin-bookings-table td {
            font-size: 0.9rem;
            /* Perkecil font jika perlu */
            vertical-align: middle;
        }

        .btn-group .btn {
            margin-right: 0;
            /* Hapus margin jika menggunakan btn-group */
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Daftar Semua Booking</h4>
                    </div>
                    <div class="card-body">
                        @include('admin.partials.alerts') {{-- Pastikan path alert ini benar --}}
                        <div class="table-responsive">
                            <table id="admin-bookings-table" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode</th>
                                        <th>Customer</th>
                                        <th>Tgl Mulai</th>
                                        <th>Wkt Mulai</th>
                                        <th>Ekspektasi Kembali</th>
                                        <th>Aktual Kembali</th>
                                        <th>Durasi</th>
                                        <th>Total</th>
                                        <th>Status Bayar</th>
                                        <th>Status Sewa</th>
                                        <th>Diproses Oleh</th>
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
    {{-- DataTables JS & Bootstrap 5 adapter (jika belum ada di master) --}}
    {{-- <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script> --}}

    <script>
        $(document).ready(function() {
            $('#admin-bookings-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.bookings.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '3%'
                    },
                    {
                        data: 'booking_code',
                        name: 'booking_code',
                        width: '10%'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer.name'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'start_time',
                        name: 'start_time',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'actual_return_datetime',
                        name: 'return_date'
                    },
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
                        name: 'payment_status',
                        width: '8%'
                    },
                    {
                        data: 'rental_status',
                        name: 'rental_status',
                        width: '10%'
                    },
                    {
                        data: 'admin_handler',
                        name: 'user.name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '8%'
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_-_END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    zeroRecords: "Tidak ada data yang cocok",
                    paginate: {
                        first: "<<",
                        last: ">>",
                        next: ">",
                        previous: "<"
                    }
                }
            });
        });
    </script>
@endpush
