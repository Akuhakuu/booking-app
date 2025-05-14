@extends('admin.layouts.master') {{-- Sesuaikan layout admin --}}

@section('page-title', 'Kelola Kategori')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kelola Kategori</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Daftar Kategori Item</h4>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-lg"></i> Tambah Kategori
                        </a>
                    </div>
                    <div class="card-body">
                        {{-- Include partial alert --}}
                        @include('admin.partials.alerts')

                        <div class="table-responsive">
                            <table id="categories-table" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Kategori</th>
                                        <th>Tgl Dibuat</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Data diisi oleh DataTables --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- SweetAlert JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- DataTables JS (jika belum ada di master) --}}
    {{-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}

    <script>
        $(document).ready(function() {
            $('#categories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.categories.data') }}', // Route untuk data JSON
                columns: [{
                        data: 'DT_RowIndex',
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
                    }
                ],
                order: [
                    [2, 'desc']
                ] // Default order by created_at descending
            });
        });

        // Fungsi delete dengan SweetAlert
        function deleteCategory(deleteUrl) {
            Swal.fire({
                title: 'Yakin Hapus Kategori?',
                text: "Kategori yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl, // URL dengan hashid dari controller
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}' // CSRF Token
                        },
                        success: function(response) {
                            Swal.fire('Terhapus!', response.message, 'success');
                            $('#categories-table').DataTable().ajax.reload(null,
                                false); // Refresh tabel
                        },
                        error: function(xhr) {
                            let errorMessage = 'Gagal menghapus kategori.';
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
@endpush
