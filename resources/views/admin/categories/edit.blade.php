@extends('admin.layouts.master')

@section('page-title', 'Edit Kategori: ' . $category->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Kelola Kategori</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Kategori</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12"> {{-- Buat form lebih sempit --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Form Edit Kategori: {{ $category->name }}</h4>
                    </div>
                    <div class="card-body">
                        @include('admin.partials.alerts')

                        <form action="{{ route('admin.categories.update', $category->hashid) }}" method="POST">
                            {{-- Action ke update dengan hashid --}}
                            @csrf
                            @method('PUT') {{-- Method PUT untuk update --}}

                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Nama Kategori <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $category->name) }}" required
                                    autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">Update Kategori</button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
