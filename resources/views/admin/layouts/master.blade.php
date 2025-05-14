<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - Hawari_Outdoor')</title> {{-- Menggunakan yield untuk title --}}

    {{-- Favicon dari Template --}}
    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="shortcut icon"
        href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAAAiCAYAAADRcLDBAAAEs2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS41LjAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgZXhpZjpQaXhlbFhEaW1lbnNpb249IjMzIgogICBleGlmOlBpeGVsWURpbWVuc2lvbj0iMzQiCiAgIGV4aWY6Q29sb3JTcGFjZT0iMSIKICAgdGlmZjpJbWFnZVdpZHRoPSIzMyIKICAgdGlmZjpJbWFnZUxlbmd0aD0iMzQiCiAgIHRpZmY6UmVzb2x1dGlvblVuaXQ9IjIiCiAgIHRpZmY6WFJlc29sdXRpb249Ijk2LjAiCiAgIHRpZmY6WVJlc29sdXRpb249Ijk2LjAiCiAgIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiCiAgIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJzUkdCIElFQzYxOTY2LTIuMSIKICAgeG1wOk1vZGlmeURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiCiAgIHhtcDpNZXRhZGF0YURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiPgogICA8eG1wTU06SGlzdG9yeT4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGkKICAgICAgc3RFdnQ6YWN0aW9uPSJwcm9kdWNlZCIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWZmaW5pdHkgRGVzaWduZXIgMS4xMC4xIgogICAgICBzdEV2dDp3aGVuPSIyMDIyLTAzLTMxVDEwOjUwOjIzKzAyOjAwIi8+CiAgICA8L3JkZjpTZXE+CiAgIDwveG1wTU06SGlzdG9yeT4KICA8L3JkZjpEZXNjcmlwdGlvbj4KIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cjw/eHBhY2tldCBlbmQ9InIiPz5V57uAAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kc8rRFEUxz9maORHo1hYKC9hISNGTWwsRn4VFmOUX5uZZ36oeTOv954kW2WrKLHxa8FfwFZZK0WkZClrYoOe87ypmWTO7dzzud97z+nec8ETzaiaWd4NWtYyIiNhZWZ2TvE946WZSjqoj6mmPjE1HKWkfdxR5sSbgFOr9Ll/rXoxYapQVik8oOqGJTwqPL5i6Q5vCzeo6dii8KlwpyEXFL519LjLLw6nXP5y2IhGBsFTJ6ykijhexGra0ITl5bRqmWU1fx/nJTWJ7PSUxBbxJkwijBBGYYwhBgnRQ7/MIQIE6ZIVJfK7f/MnyUmuKrPOKgZLpEhj0SnqslRPSEyKnpCRYdXp/9++msneoFu9JgwVT7b91ga+LfjetO3PQ9v+PgLvI1xkC/m5A+h7F32zoLXug38dzi4LWnwHzjeg8UGPGbFfySvuSSbh9QRqZ6H+Gqrm3Z7l9zm+h+iafNUV7O5Bu5z3L/wAdthn7QIme0YAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAJTSURBVFiF7Zi9axRBGIefEw2IdxFBRQsLWUTBaywSK4ubdSGVIY1Y6HZql8ZKCGIqwX/AYLmCgVQKfiDn7jZeEQMWfsSAHAiKqPiB5mIgELWYOW5vzc3O7niHhT/YZvY37/swM/vOzJbIqVq9uQ04CYwCI8AhYAlYAB4Dc7HnrOSJWcoJcBS4ARzQ2F4BZ2LPmTeNuykHwEWgkQGAet9QfiMZjUSt3hwD7psGTWgs9pwH1hC1enMYeA7sKwDxBqjGnvNdZzKZjqmCAKh+U1kmEwi3IEBbIsugnY5avTkEtIAtFhBrQCX2nLVehqyRqFoCAAwBh3WGLAhbgCRIYYinwLolwLqKUwwi9pxV4KUlxKKKUwxC6ZElRCPLYAJxGfhSEOCz6m8HEXvOB2CyIMSk6m8HoXQTmMkJcA2YNTHm3congOvATo3tE3A29pxbpnFzQSiQPcB55IFmFNgFfEQeahaAGZMpsIJIAZWAHcDX2HN+2cT6r39GxmvC9aPNwH5gO1BOPFuBVWAZue0vA9+A12EgjPadnhCuH1WAE8ivYAQ4ohKaagV4gvxi5oG7YSA2vApsCOH60WngKrA3R9IsvQUuhIGY00K4flQG7gHH/mLytB4C42EgfrQb0mV7us8AAMeBS8mGNMR4nwHamtBB7B4QRNdaS0M8GxDEog7iyoAguvJ0QYSBuAOcAt71Kfl7wA8DcTvZ2KtOlJEr+ByyQtqqhTyHTIeB+ONeqi3brh+VgIN0fohUgWGggizZFTplu12yW8iy/YLOGWMpDMTPXnl+Az9vj2HERYqPAAAAAElFTkSuQmCC"
        type="image/png">

    {{-- CSS Bawaan Mazer + FontAwesome (jika masih perlu) --}}
    <link rel="stylesheet" href="{{ asset('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">
    {{-- Opsional, jika benar-benar dipakai --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/my-style.css') }}">

    {{-- Custom CSS kamu (jika ada yang spesifik tidak terkait toggle sidebar Mazer bawaan) --}}
    <style>
        /* CSS Kustom Tambahan bisa ditaruh di sini jika perlu */
        /* Contoh: Penyesuaian tampilan DataTables, dll. */
        .dataTables_wrapper .dt-buttons .btn {
            margin-left: 0.5em;
            /* Beri sedikit jarak antar tombol export */
        }
    </style>

    @stack('styles') {{-- Stack untuk CSS tambahan per halaman --}}

</head>


<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script> {{-- Inisialisasi Tema --}}
    <div id="app">
        {{-- SIDEBAR: Pastikan customer.partials.sidebar ada dan strukturnya benar --}}
        @include('admin.partials.sidebar')

        <div id="main">
            <header class="mb-3">
                {{-- Tombol Burger untuk toggle sidebar di mobile --}}
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                {{-- Judul Halaman Utama --}}
                <h3>@yield('page-title', 'Dashboard')</h3>
                {{-- Breadcrumb (Opsional, bisa ditaruh di sini atau dalam @yield('content')) --}}
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="page-content">
                {{-- Konten Dinamis per Halaman --}}
                @yield('content')
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        {{-- Copyright kamu --}}
                        <p>{{ date('Y') }} © Akuhaku</p>
                    </div>
                    <div class="float-end">
                        {{-- Credit Mazer (sesuai template) --}}
                        <p>Crafted with <span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span>
                            by <a href="https://devkita.com" target="_blank">Akuhaku</a></p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- JavaScript Libraries --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> {{-- Bootstrap JS jika diperlukan oleh komponen lain --}}

    {{-- JavaScript Mazer Core --}}
    <script src="{{ asset('assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script> {{-- Ini sudah termasuk handler untuk sidebar toggle --}}

    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script> {{-- Tambahkan print jika perlu --}}

    {{-- JavaScript Spesifik Halaman (Contoh: Dashboard Charts) --}}
    {{-- Jika halaman ini adalah dashboard, uncomment baris berikut --}}
    {{--
    <script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/dashboard.js') }}"></script>
    --}}

    {{-- Inisialisasi DataTables (Contoh) --}}
    <script>
        $(document).ready(function() {
            // Pastikan hanya inisialisasi jika tabel ada dan belum diinisialisasi
            if ($('#customer-table').length && !$.fn.DataTable.isDataTable('#customer-table')) {
                $('#customer-table').DataTable({
                    dom: 'Bfrtip', // Menampilkan tombol
                    buttons: [
                        // Konfigurasi tombol export
                        {
                            extend: 'copy',
                            className: 'btn btn-secondary btn-sm',
                            text: '<i class="fas fa-copy"></i> Copy'
                        },
                        {
                            extend: 'csv',
                            className: 'btn btn-info btn-sm',
                            text: '<i class="fas fa-file-csv"></i> CSV'
                        },
                        {
                            extend: 'excel',
                            className: 'btn btn-success btn-sm',
                            text: '<i class="fas fa-file-excel"></i> Excel'
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-danger btn-sm',
                            text: '<i class="fas fa-file-pdf"></i> PDF'
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-primary btn-sm',
                            text: '<i class="fas fa-print"></i> Print'
                        }
                    ],
                    language: { // Opsi Bahasa Indonesia
                        lengthMenu: "Tampilkan _MENU_ entri per halaman",
                        zeroRecords: "Tidak ada data yang ditemukan",
                        info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                        infoEmpty: "Tidak ada data yang tersedia",
                        infoFiltered: "(difilter dari _MAX_ total entri)",
                        search: "Cari:",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            previous: "Sebelumnya",
                            next: "Berikutnya"
                        }
                    },
                    // destroy: true // Biasanya tidak perlu jika pengecekan isDataTable sudah dilakukan
                });
            }

            // --- Hapus Script Toggle Sidebar Kustom ---
            // Mazer sudah menanganinya via app.js dan tombol .burger-btn / .sidebar-toggler
        });

        // --- Script Konfirmasi Hapus ---
        $('.delete-confirm').on('submit', function(event) {
            event.preventDefault(); // Mencegah form submit

            var form = this;
            var namaItem = $(form).data('nama') || 'item ini'; // Ambil nama, fallback jika tidak ada

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                html: `Anda akan menghapus: <br><b>${namaItem}</b><br>Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Lanjutkan submit jika dikonfirmasi
                }
            });
        });
        // --- Akhir Script Konfirmasi Hapus ---
    </script>

    {{-- <<< Tambahkan ini di sini >>> --}}
    {{-- Script untuk menampilkan SweetAlert dari Session --}}
    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Sukses',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
    {{-- Jika ada pesan error juga --}}
    @if (session('error'))
        <script>
            Swal.fire({
                title: 'Error',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
    {{-- <<< Akhir tambahan >>> --}}

    {{-- Stack untuk JS tambahan per halaman --}}
    @stack('scripts')
    {{-- @stack('lele') --}}


</body>

</html>
