@extends('admin.layouts.master') {{-- Sesuaikan dengan layout master admin Anda --}}

@section('page-title', 'Kelola Brands') {{-- Judul Halaman --}}

@section('breadcrumb')
    {{-- Breadcrumb --}}
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li> {{-- Sesuaikan route dashboard --}}
    <li class="breadcrumb-item active" aria-current="page">Kelola Brands</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Daftar Brands</h4>
                        <a href="{{ route('admin.brands.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-lg"></i> Tambah Brand
                        </a>
                    </div>
                    <div class="card-body">
                        {{-- Tampilkan Pesan Sukses/Error --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="brands-table" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Brand</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Data akan diisi oleh DataTables --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

{{-- ... (bagian atas view index sama) ... --}}

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- ... (DataTables JS) ... --}}

    <script>
        $(document).ready(function() {
            $('#brands-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.brands.data') }}',
                columns: [{
                        data: 'DT_RowIndex', // Gunakan key yang benar dari addIndexColumn()
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '15%'
                    } // action column
                ],
                order: [
                    [2, 'desc']
                ]
            });
        });

        // Fungsi delete menerima HASHID string
        function deleteBrand(brandHash) { // <-- Menerima hashid string
            Swal.fire({
                title: 'Yakin ingin menghapus brand ini?',
                text: "Tindakan ini tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Membuat URL lengkap di Javascript
                    var deleteUrl =
                        "{{ route('admin.brands.destroy', ['brand_hash' => ':hash']) }}"; // Template URL
                    deleteUrl = deleteUrl.replace(':hash', brandHash); // Ganti placeholder :hash

                    $.ajax({
                        url: deleteUrl, // <-- URL yang sudah diganti
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Terhapus!', response.message, 'success');
                            $('#brands-table').DataTable().ajax.reload(null, false); // Refresh tabel
                        },
                        error: function(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat menghapus brand.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            console.error("Delete Error:", xhr.responseText);
                            Swal.fire('Gagal!', errorMessage, 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
