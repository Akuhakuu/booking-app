@extends('admin.layouts.master')

@section('page-title', 'Edit Brand')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.brands.index') }}">Kelola Brands</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Brand</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Edit Brand: {{ $brand->name }}</h4>
                    </div>
                    {{-- ... (bagian atas view edit sama) ... --}}

                    <div class="card-body">
                        @include('admin.partials.alerts')

                        {{-- Action form menggunakan route name dan parameter 'brand_hash' --}}
                        <form action="{{ route('admin.brands.update', ['brand_hash' => $brand->hashid]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Field Nama Brand -->
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Nama Brand <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $brand->name) }}" required
                                    autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary">Update Brand</button>
                            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>

                    {{-- ... (bagian bawah view edit sama) ... --}}
                </div>
            </div>
        </section>
    </div>
@endsection

{{-- Tidak perlu script khusus untuk halaman edit ini --}}
