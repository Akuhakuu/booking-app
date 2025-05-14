@extends('admin.layouts.master') {{-- Sesuaikan path layout admin Anda --}}

@section('page-title', 'Kelola Users (Admin/Staff)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kelola Users</li>
@endsection

@section('content')
<div class="page-content">
    <section class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Daftar Users</h4>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Tambah User
                    </a>
                </div>
                <div class="card-body">
                    @include('admin.partials.alerts') {{-- Pastikan partial ini ada dan berfungsi --}}

                    <div class="table-responsive">
                        <table id="users-table" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Gender</th>
                                    <th>Status</th>
                                    <th>Tgl Dibuat</th>
                                    <th width="15%">Action</th>
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

@push('styles')
    {{-- Include DataTables CSS jika belum ada di master layout --}}
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> --}}
@endpush

@push('scripts')
    {{-- Include jQuery & DataTables JS jika belum ada di master layout --}}
    {{-- <script src="https://code.jquery.com/jquery-3.7.0.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> {{-- Untuk konfirmasi delete jika ingin AJAX --}}

    <script>
        $(document).ready(function() {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.users.data') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone_number', name: 'phone_number', orderable: false, searchable: false },
                    { data: 'gender', name: 'gender', orderable: false },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[6, 'desc']] // Default order by 'Tgl Dibuat' descending
            });
        });

        // Jika Anda ingin menggunakan AJAX delete dengan SweetAlert (mirip ItemController)
        function deleteUser(deleteUrl) {
            Swal.fire({
                title: 'Yakin Hapus User?',
                text: "Data user yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire('Terhapus!', response.message, 'success');
                            $('#users-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let errorMessage = 'Gagal menghapus user.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            Swal.fire('Error!', errorMessage, 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
