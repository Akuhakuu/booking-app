@extends('admin.layouts.master')

@section('page-title', 'Tambah Brand Baru')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.brands.index') }}">Kelola Brands</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tambah Brand</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Tambah Brand Baru</h4>
                    </div>
                    <div class="card-body">
                        {{-- Tampilkan error validasi global --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        {{-- Tampilkan Pesan Error dari redirect --}}
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.brands.store') }}" method="POST">
                            @csrf

                            <!-- Field Nama Brand -->
                            <div class="form-group mb-3"> {{-- Tambahkan margin bottom --}}
                                <label for="name" class="form-label">Nama Brand</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required autofocus>
                                {{-- Tampilkan error spesifik untuk field 'name' --}}
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary">Simpan Brand</button>
                            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Batal</a>
                        </form>

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

{{-- Tidak perlu script khusus untuk halaman create ini --}}
