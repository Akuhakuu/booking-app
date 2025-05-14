@extends('admin.layouts.master')

@section('page-title', 'Kelola Informasi Toko')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Informasi Toko</li>
@endsection

@push('styles')
    <style>
        .img-preview-container {
            margin-top: 15px;
            padding: 10px;
            border: 1px dashed #ccc;
            display: inline-block;
            /* Agar border pas dengan gambar */
        }

        .img-preview {
            max-width: 200px;
            max-height: 150px;
            display: block;
            /* Hapus space bawah gambar */
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Informasi Toko</h4>
                    </div>
                    <div class="card-body">
                        @include('admin.partials.alerts')

                        <form action="{{ route('admin.store.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT') {{-- Method spoofing untuk update --}}

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name" class="form-label">Nama Toko <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $store->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="tagline" class="form-label">Tagline/Slogan Toko</label>
                                        <input type="text" class="form-control @error('tagline') is-invalid @enderror"
                                            id="tagline" name="tagline" value="{{ old('tagline', $store->tagline) }}">
                                        @error('tagline')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="address" class="form-label">Alamat Toko</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $store->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">Nomor Telepon</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone', $store->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">Email Toko</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $store->email) }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="website" class="form-label">Website Toko</label>
                                        <input type="url" class="form-control @error('website') is-invalid @enderror"
                                            id="website" name="website" value="{{ old('website', $store->website) }}"
                                            placeholder="https://contoh.com">
                                        @error('website')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="operational_hours" class="form-label">Jam Operasional</label>
                                        <textarea class="form-control @error('operational_hours') is-invalid @enderror" id="operational_hours"
                                            name="operational_hours" rows="2" placeholder="Senin - Jumat: 09:00 - 17:00
Sabtu: 10:00 - 15:00">{{ old('operational_hours', $store->operational_hours) }}</textarea>
                                        @error('operational_hours')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="form-group mb-3">
                                <label for="logo" class="form-label">Logo Toko</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                    id="logo" name="logo" onchange="previewImage(event)">
                                <small class="form-text text-muted">Format: JPG, PNG, SVG. Maks: 2MB. Rekomendasi:
                                    200x80px.</small>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if ($store->logo_path)
                                    <div class="img-preview-container mt-2">
                                        <p class="mb-1">Logo Saat Ini:</p>
                                        <img src="{{ Storage::url($store->logo_path) }}" alt="Logo Toko"
                                            class="img-preview" id="currentLogo">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_logo"
                                                id="remove_logo">
                                            <label class="form-check-label" for="remove_logo">
                                                Hapus logo saat ini
                                            </label>
                                        </div>
                                    </div>
                                @endif
                                <div class="img-preview-container mt-2" id="newLogoPreviewContainer"
                                    style="display:none;">
                                    <p class="mb-1">Preview Logo Baru:</p>
                                    <img src="#" alt="Preview Logo Baru" class="img-preview" id="newLogoPreview">
                                </div>
                            </div>


                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Informasi Toko</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            const newLogoPreview = document.getElementById('newLogoPreview');
            const newLogoPreviewContainer = document.getElementById('newLogoPreviewContainer');

            reader.onload = function() {
                newLogoPreview.src = reader.result;
                newLogoPreviewContainer.style.display = 'inline-block';
            }
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            } else {
                newLogoPreview.src = '#';
                newLogoPreviewContainer.style.display = 'none';
            }
        }
    </script>
@endpush
