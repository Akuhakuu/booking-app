@extends('customer.layouts.master') {{-- Sesuaikan layout customer --}}

@section('page-title', $item->name . ' - Detail Alat')

{{-- Breadcrumb (Contoh) --}}
{{-- @section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customer.catalog.index') }}">Katalog</a></li>
    @if ($item->category)
    <li class="breadcrumb-item"><a href="{{ route('customer.catalog.index', ['category' => $item->category->hashid]) }}">{{ $item->category->name }}</a></li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">{{ $item->name }}</li>
@endsection --}}

@section('content')
    <div class="page-heading mb-4">
        {{-- Breadcrumb Manual Sederhana --}}
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('customer.catalog.index') }}">Katalog</a></li>
                @if ($item->category)
                    <li class="breadcrumb-item"><a
                            href="{{ route('customer.catalog.index', ['category' => $item->category->hashid]) }}">{{ $item->category->name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $item->name }}</li>
            </ol>
        </nav>
        {{-- <h3>Detail Alat: {{ $item->name }}</h3> --}}
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            {{-- Kolom Gambar --}}
                            <div class="col-md-6 mb-4 mb-md-0 text-center">
                                @if ($item->img && File::exists(public_path('assets/compiled/items/' . $item->img)))
                                    <img src="{{ asset('assets/compiled/items/' . $item->img) }}" class="img-fluid rounded"
                                        alt="{{ $item->name }}" style="max-height: 450px; object-fit: contain;">
                                @else
                                    <img src="{{ asset('assets/compiled/svg/img-not-found.svg') }}" class="  "
                                        alt="No image available" style="width: 100%; height: 100%; object-fit: contain;">
                                @endif
                            </div>

                            {{-- Kolom Detail & Aksi --}}
                            <div class="col-md-6">
                                <h1 class="mb-3">{{ $item->name }}</h1>
                                <h3 class="text-primary mb-3">Rp{{ number_format($item->rental_price, 0, ',', '.') }}/Hari
                                </h3>

                                {{-- Rating (Contoh Statis) --}}
                                {{-- <div class="d-flex align-items-center mb-3">
                                    <div class="text-warning me-1">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-half"></i>
                                    </div> --}}
                                {{-- <span class="text-muted">(4.8 / 5.0 dari 556 ulasan)</span> Ganti dengan data dinamis jika ada --}}
                                {{-- </div> --}}

                                <p class="text-muted mb-4">{{ $item->description ?? 'Tidak ada deskripsi.' }}</p>

                                <h5>Spesifikasi:</h5>
                                <ul class="list-unstyled mb-4">
                                    <li><strong>Kategori:</strong> {{ optional($item->category)->name ?? '-' }}</li>
                                    <li><strong>Brand:</strong> {{ optional($item->brand)->name ?? '-' }}</li>
                                    <li><strong>Stok Tersedia:</strong> {{ $item->stock }} unit</li>
                                    {{-- Tambahkan spesifikasi lain dari deskripsi atau field baru jika ada --}}
                                    {{-- Contoh: <li><strong>Kapasitas:</strong> 60L</li> --}}
                                </ul>

                                {{-- Form untuk Add to Cart/Booking --}}
                                {{-- Ganti action ke route yang sesuai nanti --}}
                                <form action="{{ route('customer.cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item->hashid }}">
                                    {{-- Kirim hashid item --}}

                                    <div class="row align-items-center mb-4">
                                        <div class="col-auto">
                                            <label for="quantity" class="col-form-label">Jumlah:</label>
                                        </div>
                                        <div class="col-auto">
                                            <div class="input-group" style="width: 130px;">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="button-addon-minus" onclick="decreaseQty()">-</button>
                                                <input type="number" class="form-control text-center" id="quantity"
                                                    name="quantity" value="1" min="1" max="{{ $item->stock }}"
                                                    aria-label="Quantity">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="button-addon-plus" onclick="increaseQty()">+</button>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <span class="text-danger" id="stock-warning" style="display: none;">Stok tidak
                                                cukup!</span>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg px-5"
                                        {{ $item->stock <= 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-cart-plus-fill me-2"></i>
                                        {{ $item->stock > 0 ? 'Add to Cart' : 'Stok Habis' }}
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        const quantityInput = document.getElementById('quantity');
        const stock = {{ $item->stock }}; // Ambil stok dari PHP
        const stockWarning = document.getElementById('stock-warning');

        function decreaseQty() {
            let currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
                stockWarning.style.display = 'none';
            }
        }

        function increaseQty() {
            let currentValue = parseInt(quantityInput.value);
            if (currentValue < stock) {
                quantityInput.value = currentValue + 1;
                stockWarning.style.display = 'none';
            } else {
                stockWarning.style.display = 'inline'; // Tampilkan warning jika stok maks
            }
        }

        // Validasi saat input manual
        quantityInput.addEventListener('change', function() {
            let currentValue = parseInt(quantityInput.value);
            if (isNaN(currentValue) || currentValue < 1) {
                quantityInput.value = 1;
                stockWarning.style.display = 'none';
            } else if (currentValue > stock) {
                quantityInput.value = stock;
                stockWarning.style.display = 'inline';
            } else {
                stockWarning.style.display = 'none';
            }
        });

        // Pastikan nilai awal tidak melebihi stok
        if (parseInt(quantityInput.value) > stock) {
            quantityInput.value = stock > 0 ? stock : 1; // Jika stok 0, set ke 1 tapi tombol disabled
            if (stock > 0) stockWarning.style.display = 'inline';
        }
        if (stock <= 0) {
            quantityInput.value = 0;
            quantityInput.disabled = true;
            document.getElementById('button-addon-minus').disabled = true;
            document.getElementById('button-addon-plus').disabled = true;
        }
    </script>
@endpush
