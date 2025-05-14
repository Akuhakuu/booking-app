@extends('customer.layouts.master') {{-- Sesuaikan dengan layout customer --}}

@section('page-title', 'Katalog Alat Outdoor')

@section('content')
    <div class="page-heading mb-4">
        {{-- <h3>Katalog Alat Outdoor</h3> --}}
        <p class="text-subtitle text-muted">Temukan alat yang Anda butuhkan untuk petualangan Anda.</p>
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-12">
                {{-- Search Bar --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form action="{{ route('customer.catalog.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control"
                                    placeholder="Cari nama alat, deskripsi, kategori, atau brand..." name="search"
                                    value="{{ $searchQuery ?? '' }}">
                                @if (isset($selectedCategory))
                                    {{-- Sertakan category jika sedang difilter --}}
                                    <input type="hidden" name="category" value="{{ $selectedCategory->hashid }}">
                                @endif
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Category Tabs --}}
                <div class="mb-4">
                    <ul class="nav nav-tabs">
                        {{-- Tab "Semua" --}}
                        <li class="nav-item">
                            <a class="nav-link {{ !$selectedCategory && !$searchQuery ? 'active' : '' }}"
                                href="{{ route('customer.catalog.index') }}">Semua Kategori</a>
                        </li>
                        {{-- Loop Kategori --}}
                        @foreach ($categories as $category)
                            <li class="nav-item">
                                <a class="nav-link {{ isset($selectedCategory) && $selectedCategory->id == $category->id ? 'active' : '' }}"
                                    href="{{ route('customer.catalog.index', ['category' => $category->hashid]) }}">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Pesan jika ada pencarian --}}
                @if ($searchQuery)
                    <div class="alert alert-info">
                        Menampilkan hasil pencarian untuk: <strong>"{{ $searchQuery }}"</strong>.
                        <a href="{{ route('customer.catalog.index', ['category' => $selectedCategory?->hashid]) }}"
                            class="float-end btn-sm btn-light">Reset Pencarian</a>
                    </div>
                @endif


                {{-- Item Grid --}}
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                    @forelse ($items as $item)
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 item-card">
                                <a href="{{ route('customer.catalog.show', $item->hashid) }}" class="stretched-link">
                                    @if ($item->img && File::exists(public_path('assets/compiled/items/' . $item->img)))
                                        <img src="{{ asset('assets/compiled/items/' . $item->img) }}"
                                            class="card-img-top item-card-img" alt="{{ $item->name }}">
                                    @else
                                        <img src="{{ asset('assets/compiled/svg/img-not-found.svg') }}"
                                            class="card-img-top item-card-img  bg-light" alt="No image available">
                                        {{-- Placeholder --}}
                                    @endif
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title mb-1">
                                        <a href="{{ route('customer.catalog.show', $item->hashid) }}"
                                            class="text-decoration-none text-dark stretched-link-sibling">{{ $item->name }}</a>
                                    </h5>
                                    <p class="card-text text-primary fw-bold mb-2">
                                        Rp{{ number_format($item->rental_price, 0, ',', '.') }}/Hari</p>
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Stok: {{ $item->stock }}</small>
                                        {{-- Bisa tambahkan badge kategori/brand jika perlu --}}
                                        {{-- <span class="badge bg-light-secondary">{{ optional($item->category)->name }}</span> --}}
                                    </div>
                                </div>
                                {{-- <div class="card-footer bg-white border-0 text-center">
                             <a href="{{ route('customer.catalog.show', $item->hashid) }}" class="btn btn-sm btn-outline-primary w-100">Lihat Detail</a>
                        </div> --}}
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                @if ($searchQuery)
                                    Tidak ada item yang cocok dengan pencarian Anda "{{ $searchQuery }}".
                                @elseif($selectedCategory)
                                    Belum ada item dalam kategori "{{ $selectedCategory->name }}".
                                @else
                                    Belum ada item yang tersedia saat ini.
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                {{-- <div class="mt-4 d-flex justify-content-center"> --}}
                {{-- Sertakan query string (search, category) saat paginasi --}}
                {{-- {{ $items->appends(request()->query())->links() }}
                </div> --}}

            </div>
        </section>
    </div>
@endsection

@push('styles')
    {{-- CSS tambahan jika perlu --}}
    <style>
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
        }

        .item-card-img {
            height: 200px;
            /* Atur tinggi gambar */
            object-fit: cover;
            /* Agar gambar tidak gepeng */
        }

        /* Pastikan link di dalam card body bisa diklik */
        .stretched-link-sibling {
            position: relative;
            z-index: 1;
        }
    </style>
@endpush
