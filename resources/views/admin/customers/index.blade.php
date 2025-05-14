@extends('admin.layouts.master') {{-- Pastikan ini path ke layout master yang sudah benar --}}

@section('page-title', 'Kelola Customers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    {{-- Contoh Mazer menggunakan DataTable, bukan Kelola Customers, tapi sesuaikan saja --}}
    <li class="breadcrumb-item active" aria-current="page">Customers</li>
@endsection

@push('styles')
    {{-- Jika perlu CSS spesifik hanya untuk halaman ini --}}
    {{-- Contoh: <link rel="stylesheet" href="{{ asset('assets/admin/css/customer-table.css') }}"> --}}
@endpush

@section('content')
{{-- Mengikuti struktur section dari Mazer datatable.html --}}
<section class="section">
    <div class="card">
        <div class="card-header">
            {{-- Menggabungkan judul dari Mazer dan tombol tambah Anda --}}
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Daftar Customers</h5>
                <a href="{{ route('admin.customers.create') }}" class="btn btn-success">
                    <i class="bi bi-person-plus-fill"></i> Tambah Customer
                </a>
            </div>
        </div>
        <div class="card-body">
            @include('admin.partials.alerts') {{-- Tetap tampilkan alert jika ada --}}

            {{-- DIV .table-responsive penting untuk tabel horizontal scroll di layar kecil --}}
            <div class="table-responsive">
                {{-- Class utama: table dan table-striped. ID untuk inisialisasi JS. --}}
                {{-- dataTables.bootstrap5.css akan menangani styling integrasi BS5 --}}
                <table class="table table-striped" id="customers-table" style="width:100%">
                    <thead>
                        <tr>
                            {{-- Kolom header Anda --}}
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Status</th>
                            <th>Tgl Daftar</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data akan diisi oleh jQuery DataTables via AJAX --}}
                        {{-- Kosongkan saja bagian ini --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {{-- SweetAlert JS (sudah ada di layout?) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi jQuery DataTables
            $('#customers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.customers.data') }}', // Pastikan route ini benar
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone_number', name: 'phone_number' },
                    { data: 'status', name: 'status' }, // Sesuaikan rendering jika perlu (misal jadi badge)
                    { data: 'created_at', name: 'created_at' }, // Format tanggal mungkin perlu diatur di controller/resource
                    { data: 'action', name: 'action', orderable: false, searchable: false, width: '15%' }
                ],
                order: [[5, 'desc']], // Default order by created_at descending
                // Tambahan opsi DataTables jika perlu (misal bahasa)
                // language: {
                //     url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json', // Contoh Bahasa Indonesia
                // }
            });
        });

        // Fungsi delete dengan SweetAlert (Tidak perlu diubah)
        function deleteCustomer(deleteUrl) {
            Swal.fire({
                title: 'Yakin Hapus Customer?',
                text: "Data customer dan mungkin data terkait lainnya akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                // Penting untuk dark mode SweetAlert jika tema tidak otomatis terdeteksi
                customClass: {
                    // Cek apakah tema gelap aktif
                    popup: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'swal2-dark' : '',
                    // Anda mungkin perlu menambahkan CSS untuk .swal2-dark jika belum ada
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl, // URL dengan hashid
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Terhapus!', response.message, 'success');
                            // Reload tabel DataTables
                            $('#customers-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                             let errorMessage = 'Gagal menghapus customer.';
                             if (xhr.responseJSON && xhr.responseJSON.error) {
                                 errorMessage = xhr.responseJSON.error;
                             }
                             console.error("Delete Error:", xhr.responseJSON);
                            Swal.fire('Error!', errorMessage, 'error');
                        }
                    });
                }
            });
        }
    </script>

    {{-- CSS Kustom untuk SweetAlert Dark Mode (jika belum ada) --}}
    {{-- Bisa ditaruh di @push('styles') atau file CSS kustom --}}
    <style>
        .swal2-popup.swal2-dark {
            background-color: #283046; /* Sesuaikan dengan warna background Mazer dark */
            color: #b4b7bd; /* Sesuaikan dengan warna teks Mazer dark */
        }
        .swal2-title.swal2-dark {
             color: #fff; /* Sesuaikan */
        }
        /* ... styling lain untuk button, input, dll jika perlu ... */
    </style>
@endpush
