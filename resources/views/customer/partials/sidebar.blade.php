{{-- Lokasi: resources/views/layouts/partials/sidebar.blade.php (atau di mana pun sidebar Anda berada) --}}
<div id="sidebar">
    <div class="sidebar-wrapper active">
        {{-- Bagian Header Sidebar --}}
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('customer.dashboard') }}">
                        {{-- Sesuaikan path logo jika perlu --}}
                        <img style="width: 90px; height: 90px;" src="{{ asset('assets/compiled/svg/logo.svg') }}"
                            alt="Logo" srcset="">
                    </a>
                </div>
                {{-- Tombol Theme Toggle (Light/Dark Mode) --}}
                <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                    {{-- Icon Light Mode --}}
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        aria-hidden="true" role="img" class="iconify iconify--system-uicons" width="20"
                        height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                        <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path
                                d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                                opacity=".3"></path>
                            <g transform="translate(-210 -1)">
                                <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                                <circle cx="220.5" cy="11.5" r="4"></circle>
                                <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                            </g>
                        </g>
                    </svg>
                    {{-- Switch Toggle --}}
                    <div class="form-check form-switch fs-6">
                        <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                        <label class="form-check-label"></label>
                    </div>
                    {{-- Icon Dark Mode --}}
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        aria-hidden="true" role="img" class="iconify iconify--mdi" width="20" height="20"
                        preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                        </path>
                    </svg>
                </div>
                {{-- Tombol Tutup Sidebar (Mobile View) --}}
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>

        {{-- Bagian Menu Sidebar --}}
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu Utama</li>

                {{-- Item Menu Dashboard Customer --}}
                <li class="sidebar-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('customer.dashboard') }}" class="sidebar-link">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- == ITEM MENU BARU: KATALOG == --}}
                <li class="sidebar-item {{ request()->routeIs('customer.catalog.*') ? 'active' : '' }}">
                    <a href="{{ route('customer.catalog.index') }}" class="sidebar-link">
                        <i class="bi bi-shop"></i> {{-- Icon toko/katalog --}}
                        <span>Katalog Alat</span>
                    </a>
                </li>
                {{-- == AKHIR ITEM MENU BARU == --}}

                {{-- Item Menu Booking Saya --}}
                <li class="sidebar-item {{ request()->routeIs('customer.cart.index') ? 'active' : '' }}">
                    <a href="{{ route('customer.cart.index') }}" class="sidebar-link">
                        <i class="bi bi-cart-fill"></i>
                        <span>Keranjang Saya</span>
                        @php
                            $cartCount = count(session()->get('cart', []));
                        @endphp
                        @if ($cartCount > 0)
                            <span class="badge bg-danger ms-auto">{{ $cartCount }}</span>
                        @endif
                    </a>
                </li>

                {{-- ... menu lain ... --}}
                <li class="sidebar-item {{ request()->routeIs('customer.bookings.*') ? 'active' : '' }}">
                    {{-- Link ke halaman daftar booking customer --}}
                    <a href="{{ route('customer.bookings.index') }}" class="sidebar-link">
                        <i class="bi bi-calendar-check-fill"></i> {{-- Icon berbeda --}}
                        <span>Booking Saya</span>
                    </a>
                </li>

                <li class="sidebar-title">Akun Saya</li>

                {{-- Item Menu Profil Saya --}}
                <li class="sidebar-item {{ request()->routeIs('customer.profile.*') ? 'active' : '' }}">
                    <a href="{{ route('customer.profile.edit') }}" class="sidebar-link"> {{-- <-- Arahkan ke route edit --}}
                        <i class="bi bi-person-circle"></i>
                        <span>Profil Saya</span>
                    </a>
                </li>

                {{-- Item Menu Log Out --}}
                <li class="sidebar-item">
                    {{-- Link ini hanya memicu fungsi JavaScript --}}
                    <a href="#" class="sidebar-link" onclick="confirmLogout(event);">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Log Out</span>
                    </a>
                    {{-- Form logout yang tersembunyi dengan ID yang BENAR --}}
                    <form id="customer-logout-form" action="{{ route('customer.logout') }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
        {{-- Akhir Bagian Menu Sidebar --}}

    </div>
</div>

{{-- Menambahkan script ke stack 'scripts' di layout utama --}}
{{-- Ini cara terbaik agar script hanya dimuat saat dibutuhkan dan terkumpul di akhir body --}}
@push('scripts')
    {{-- Memuat SweetAlert dari CDN (pastikan ada koneksi internet atau install via npm/yarn) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        /**
         * Fungsi untuk menampilkan konfirmasi SweetAlert sebelum logout.
         * @param {Event} event - Event klik pada link logout.
         */
        function confirmLogout(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: "Apakah Anda yakin ingin keluar dari sesi ini?",
                icon: 'question', // 'question' lebih cocok untuk konfirmasi
                showCancelButton: true,
                confirmButtonColor: '#435ebe', // Warna tema Mazer
                cancelButtonColor: '#dc3545', // Warna Bootstrap danger
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form dengan ID yang BENAR
                    document.getElementById('customer-logout-form').submit();
                }
            });
        }

        /**
         * Script untuk menyimpan dan memulihkan posisi scroll sidebar
         * menggunakan localStorage agar posisi tidak reset saat halaman refresh.
         */
        document.addEventListener('DOMContentLoaded', function() {
            // Pilih elemen wrapper sidebar yang bisa di-scroll
            const sidebarScroller = document.querySelector('#sidebar .sidebar-wrapper');

            // Pastikan elemen sidebar ditemukan
            if (sidebarScroller) {
                // Ambil posisi scroll tersimpan dari localStorage
                const scrollPosition = localStorage.getItem('sidebar-scroll-position');

                // Jika ada posisi tersimpan, pulihkan
                if (scrollPosition) {
                    // Parse ke integer karena localStorage menyimpan string
                    sidebarScroller.scrollTop = parseInt(scrollPosition, 10);
                }

                // Tambahkan event listener untuk menyimpan posisi saat di-scroll
                sidebarScroller.addEventListener('scroll', function() {
                    localStorage.setItem('sidebar-scroll-position', sidebarScroller.scrollTop);
                });
            }
        });
    </script>
@endpush
