@extends('admin.layouts.master')

@section('page-title', 'Edit Item: ' . $item->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.items.index') }}">Kelola Items</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Item</li>
@endsection

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Edit Item: {{ $item->name }}</h4>
                    </div>
                    <div class="card-body">
                        {{-- === INCLUDE ALERT PARTIAL === --}}
                        @include('admin.partials.alerts')
                        {{-- === END INCLUDE ALERT PARTIAL === --}}

                        {{-- PENTING: enctype dan method PUT --}}
                        <form action="{{ route('admin.items.update', $item->hashid) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                {{-- Kolom Kiri --}}
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name" class="form-label">Nama Item <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $item->name) }}" required
                                            autofocus>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="category_id" class="form-label">Kategori <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('category_id') is-invalid @enderror"
                                            id="category_id" name="category_id" required>
                                            <option value="" disabled>Pilih Kategori...</option>
                                            @foreach ($categories as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ old('category_id', $item->category_id) == $id ? 'selected' : '' }}>
                                                    {{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="brand_id" class="form-label">Brand <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id"
                                            name="brand_id" required>
                                            <option value="" disabled>Pilih Brand...</option>
                                            @foreach ($brands as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ old('brand_id', $item->brand_id) == $id ? 'selected' : '' }}>
                                                    {{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('brand_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="rental_price" class="form-label">Harga Sewa (Rp / Hari) <span
                                                class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control @error('rental_price') is-invalid @enderror"
                                            id="rental_price" name="rental_price"
                                            value="{{ old('rental_price', $item->rental_price) }}" required min="0"
                                            step="1000">
                                        @error('rental_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="stock" class="form-label">Stok <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                            id="stock" name="stock" value="{{ old('stock', $item->stock) }}" required
                                            min="0">
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Kolom Kanan --}}
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label">Status <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status"
                                            name="status" required>
                                            <option value="" disabled>Pilih Status...</option>
                                            @foreach ($statuses as $key => $value)
                                                {{-- Menampilkan status saat ini meskipun 'rented' tapi tidak bisa dipilih jika bukan 'rented' --}}
                                                <option value="{{ $key }}"
                                                    {{ old('status', $item->status) == $key ? 'selected' : '' }}
                                                    {{ $key === 'rented' && $item->status !== 'rented' ? 'disabled' : '' }}>
                                                    {{ $value }}{{ $key === 'rented' ? ' (Otomatis)' : '' }}
                                                </option>
                                            @endforeach
                                            {{-- Jika status saat ini RENTED, tambahkan opsi ini secara eksplisit karena tidak ada di $statuses helper --}}
                                            @if ($item->status === 'rented' && !isset($statuses['rented']))
                                                <option value="rented" selected disabled>Rented (Otomatis)</option>
                                            @endif
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="description" class="form-label">Deskripsi</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                            rows="5">{{ old('description', $item->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="img" class="form-label">Ganti Gambar Item</label>
                                        <input class="form-control @error('img') is-invalid @enderror" type="file"
                                            id="img" name="img" accept="image/*">
                                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti gambar.
                                            Maks 2MB.</small>
                                        @error('img')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        {{-- Tampilkan Gambar Saat Ini & Preview --}}
                                        <div class="mt-3">
                                            <label>Gambar Saat Ini:</label><br>
                                            @if ($item->img && File::exists(public_path($targetPath . '/' . $item->img)))
                                                <img id="current-image" src="{{ asset($targetPath . '/' . $item->img) }}"
                                                    alt="Gambar Saat Ini" class="img-thumbnail"
                                                    style="max-height: 150px;">
                                            @else
                                                <span class="badge bg-light-secondary">Tidak ada gambar</span>
                                            @endif
                                            <img id="image-preview" src="#" alt="Preview Gambar Baru"
                                                class="mt-2 img-thumbnail"
                                                style="max-height: 150px; display: none; margin-left: 10px;" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Update Item</button>
                                <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Batal</a>
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
        // Script untuk image preview (sama seperti di create view)
        const imageInput = document.getElementById('img');
        const imagePreview = document.getElementById('image-preview');
        const currentImage = document.getElementById('current-image');

        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) { // Pastikan itu file gambar
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                        if (currentImage) currentImage.style.opacity = '0.5'; // Redupkan gambar lama
                    }
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.src = '#';
                    imagePreview.style.display = 'none';
                    if (currentImage) currentImage.style.opacity = '1'; // Kembalikan opacity gambar lama
                    if (file) { // Jika file dipilih tapi bukan gambar
                        alert('File yang dipilih bukan gambar!');
                        imageInput.value = ''; // Reset input file
                    }
                }
            });
        }
    </script>
@endpush
