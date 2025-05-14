@extends('customer.layouts.master') {{-- Sesuaikan dengan layout customer Anda --}}

@section('page-title', 'Keranjang Belanja Saya')

@push('styles')
    {{-- CSS tambahan jika perlu --}}
    <style>
        /* Gaya untuk input quantity agar tidak terlalu lebar di tabel */
        #cart-table .quantity-input {
            max-width: 80px;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading mb-4">
        <h3>Keranjang Belanja Anda</h3>
        <p class="text-subtitle text-muted">Pilih item yang ingin Anda booking dan tentukan tanggal serta waktu sewa.</p>
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-12">
                {{-- Sertakan partial untuk menampilkan pesan sukses/error dari session --}}
                @include('customer.partials.alerts') {{-- GANTI PATH jika Anda punya partial alert khusus customer --}}

                @if (!$cartItems->isEmpty())
                    {{-- Form checkout membungkus tabel dan ringkasan --}}
                    <form action="{{ route('customer.booking.checkout') }}" method="POST" id="checkout-form">
                        @csrf
                        {{-- Pastikan TIDAK ADA @method directive lain di sini --}}

                        {{-- Card untuk Tabel Item Keranjang --}}
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="cart-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;"><input class="form-check-input" type="checkbox"
                                                        id="select-all-items" title="Pilih Semua"></th>
                                                <th style="width: 10%;">Gambar</th>
                                                <th>Nama Item</th>
                                                <th style="width: 15%;">Harga/Hari</th>
                                                <th style="width: 18%;">Jumlah</th>
                                                <th style="width: 15%;">Subtotal</th>
                                                <th style="width: 10%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cart-table-body">
                                            @foreach ($cartItems as $cartItem)
                                                {{-- Hanya tampilkan jika relasi item ada --}}
                                                @if ($cartItem->item)
                                                    @php
                                                        $item = $cartItem->item;
                                                        $itemHashid = $item->hashid; // Hashid dari Item (untuk link detail)
                                                        $cartItemHashid = $cartItem->hashid; // Hashid dari CartItem (untuk form update/delete)
                                                        $subtotalItem = $item->rental_price * $cartItem->quantity;
                                                    @endphp
                                                    {{-- Simpan harga dan hashid cart item di data attribute baris --}}
                                                    <tr data-item-price="{{ $item->rental_price }}"
                                                        data-cart-item-hashid="{{ $cartItemHashid }}">
                                                        <td>
                                                            {{-- Checkbox untuk memilih item, tidak checked by default --}}
                                                            <input type="checkbox" class="form-check-input item-checkbox"
                                                                name="selected_items[]" value="{{ $cartItemHashid }}"
                                                                data-subtotal="{{ $subtotalItem }}">
                                                        </td>
                                                        <td>
                                                            {{-- Link ke detail item --}}
                                                            <a
                                                                href="{{ route('customer.catalog.show', ['item_hash' => $itemHashid]) }}">
                                                                @if ($item->img && File::exists(public_path('assets/compiled/items/' . $item->img)))
                                                                    <img src="{{ asset('assets/compiled/items/' . $item->img) }}"
                                                                        alt="{{ $item->name }}" class="img-fluid rounded"
                                                                        style="width: 70px; height: 70px; object-fit: cover;">
                                                                @else
                                                                    <img src="{{ asset('assets/compiled/svg/no-image.svg') }}"
                                                                        alt="No image"
                                                                        class="img-fluid rounded bg-light p-2"
                                                                        style="width: 70px; height: 70px; object-fit: contain;">
                                                                @endif
                                                            </a>
                                                        </td>
                                                        <td>
                                                            {{-- Nama item (link ke detail) dan stok --}}
                                                            <a href="{{ route('customer.catalog.show', ['item_hash' => $itemHashid]) }}"
                                                                class="text-dark fw-bold text-decoration-none">
                                                                {{ $item->name }}
                                                            </a>
                                                            <br>
                                                            <small class="text-muted">Stok: {{ $item->stock }}</small>
                                                        </td>
                                                        <td>Rp{{ number_format($item->rental_price, 0, ',', '.') }}</td>
                                                        <td>
                                                            {{-- Input Quantity (bagian dari form checkout utama) --}}
                                                            <div
                                                                class="d-inline-flex align-items-center cart-update-form-container">
                                                                {{-- Nama input quantity dengan hashid cart item sebagai key --}}
                                                                <input type="number"
                                                                    name="quantities[{{ $cartItemHashid }}]"
                                                                    value="{{ $cartItem->quantity }}"
                                                                    class="form-control form-control-sm text-center me-2 quantity-input"
                                                                    min="1" max="{{ $item->stock }}"
                                                                    data-cartitemhash="{{ $cartItemHashid }}">
                                                            </div>
                                                        </td>
                                                        <td class="item-subtotal">
                                                            Rp{{ number_format($subtotalItem, 0, ',', '.') }}</td>
                                                        <td>
                                                            {{-- Form terpisah untuk Hapus Item --}}
                                                            <form action="{{ route('customer.cart.remove') }}"
                                                                method="POST" class="cart-remove-form d-inline">
                                                                @csrf
                                                                {{-- @method('DELETE') --}} {{-- Dihapus karena AJAX akan handle ini --}}
                                                                <input type="hidden" name="cart_item_hashid"
                                                                    value="{{ $cartItemHashid }}">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger btn-remove-cart"
                                                                    title="Hapus Item">
                                                                    <i class="bi bi-trash-fill"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @else
                                                    {{-- Baris jika item asli tidak ditemukan --}}
                                                    <tr>
                                                        <td></td>
                                                        <td colspan="5" class="text-center text-danger fst-italic">
                                                            Item ini (ID Cart: {{ $cartItem->id }}) sudah tidak tersedia
                                                            atau
                                                            datanya tidak lengkap.
                                                        </td>
                                                        <td>
                                                            {{-- Form hapus untuk item yang hilang --}}
                                                            <form action="{{ route('customer.cart.remove') }}"
                                                                method="POST" class="cart-remove-form d-inline">
                                                                @csrf
                                                                {{-- @method('DELETE') --}}
                                                                <input type="hidden" name="cart_item_hashid"
                                                                    value="{{ $cartItem->hashid }}">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-danger btn-remove-cart">Hapus</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endif {{-- End if $cartItem->item --}}
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan Booking & Tombol Checkout (masih di dalam form utama) --}}
                        <div class="row mt-4" id="checkout-summary-section">
                            <div class="col-md-8 offset-md-2">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h4 class="card-title mb-3">Detail Sewa & Ringkasan</h4>
                                        {{-- Input Tanggal, Durasi, Notes --}}
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="start_date" class="form-label">Tanggal Mulai Sewa <span
                                                        class="text-danger">*</span></label>
                                                <input type="date"
                                                    class="form-control @error('start_date') is-invalid @enderror"
                                                    id="start_date" name="start_date"
                                                    value="{{ old('start_date', now()->format('Y-m-d')) }}" required
                                                    min="{{ now()->format('Y-m-d') }}">
                                                @error('start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            {{-- ====================================================== --}}
                                            {{--            TAMBAHKAN INPUT START_TIME DI SINI          --}}
                                            {{-- ====================================================== --}}
                                            <div class="col-md-6">
                                                <label for="start_time" class="form-label">Waktu Pengambilan <span
                                                        class="text-danger">*</span></label>
                                                <input type="time"
                                                    class="form-control @error('start_time') is-invalid @enderror"
                                                    id="start_time" name="start_time"
                                                    value="{{ old('start_time', '09:00') }}" required>
                                                {{-- Default jam 9 pagi --}}
                                                @error('start_time')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6"> {{-- Rental Days dipindah ke sini agar sejajar dengan End Date Display --}}
                                                <label for="rental_days" class="form-label">Durasi Sewa (Hari) <span
                                                        class="text-danger">*</span></label>
                                                <input type="number"
                                                    class="form-control @error('rental_days') is-invalid @enderror"
                                                    id="rental_days" name="rental_days"
                                                    value="{{ old('rental_days', 1) }}" required min="1">
                                                @error('rental_days')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="end_date_display" class="form-label">Tanggal Selesai Sewa
                                                    (Estimasi)</label>
                                                <input type="text" class="form-control bg-light" id="end_date_display"
                                                    readonly disabled>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="notes_booking" class="form-label">Catatan Tambahan
                                                (Opsional)</label>
                                            <textarea name="notes_booking" id="notes_booking" class="form-control" rows="3"
                                                placeholder="Contoh: Ambil setelah jam makan siang.">{{ old('notes_booking') }}</textarea>
                                        </div>
                                        <hr>
                                        {{-- Total Booking (dihitung oleh JS) --}}
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4>Total Booking:</h4>
                                            <h4 class="text-primary fw-bold" id="total-booking-price">Rp0</h4>
                                        </div>
                                        {{-- Tombol Submit Form Checkout --}}
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-lg btn-primary" id="btn-checkout"
                                                disabled>
                                                <i class="bi bi-bag-check-fill me-2"></i> Lanjutkan ke Pembayaran
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    {{-- Pesan Keranjang Kosong --}}
                    <div class="alert alert-info text-center" id="empty-cart-message">
                        <h4 class="alert-heading"><i class="bi bi-cart-x-fill"></i> Keranjang Anda Kosong!</h4>
                        <p>Silakan tambahkan beberapa alat ke keranjang Anda terlebih dahulu.</p>
                        <a href="{{ route('customer.catalog.index') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-arrow-left-circle-fill me-2"></i> Kembali ke Katalog
                        </a>
                    </div>
                    {{-- Sembunyikan bagian summary jika keranjang kosong (opsional, JS akan handle) --}}
                    <div class="row mt-4" id="checkout-summary-section-empty" style="display:none;"></div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cartTableBody = document.getElementById('cart-table-body');
            const selectAllCheckbox = document.getElementById('select-all-items');
            let itemCheckboxes = cartTableBody ? Array.from(cartTableBody.querySelectorAll('.item-checkbox')) : [];
            let quantityInputs = cartTableBody ? Array.from(cartTableBody.querySelectorAll('.quantity-input')) : [];
            const totalBookingPriceEl = document.getElementById('total-booking-price');
            const btnCheckout = document.getElementById('btn-checkout');
            const startDateInput = document.getElementById('start_date');
            const startTimeInput = document.getElementById('start_time'); // <-- Ambil elemen start_time
            const rentalDaysInput = document.getElementById('rental_days');
            const endDateDisplay = document.getElementById('end_date_display');
            const checkoutSummarySection = document.getElementById('checkout-summary-section');
            const emptyCartMessageSection = document.getElementById('empty-cart-message');
            const SELECTED_ITEMS_STORAGE_KEY = 'selectedCartItems'; // Definisikan konstanta jika belum

            function formatCurrency(amount) {
                return 'Rp' + new Intl.NumberFormat('id-ID').format(amount);
            }

            function calculateTotal() {
                if (!cartTableBody || !totalBookingPriceEl || !btnCheckout || !rentalDaysInput) return;

                let currentTotal = 0;
                let allChecked = true;
                let hasCheckedItem = false;

                itemCheckboxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    if (!row) return;

                    const quantityInput = row.querySelector('.quantity-input');
                    const price = parseFloat(row.dataset.itemPrice);
                    const quantity = parseInt(quantityInput ? quantityInput.value : 0);

                    if (checkbox.checked) {
                        hasCheckedItem = true;
                        if (!isNaN(price) && !isNaN(quantity) && quantity > 0) {
                            currentTotal += price * quantity;
                        }
                        // Update subtotal in row, even if it's not directly used in total calculation here
                        const subtotalEl = row.querySelector('.item-subtotal');
                        if (subtotalEl && !isNaN(price) && !isNaN(quantity)) subtotalEl.textContent =
                            formatCurrency(price * quantity);

                    } else {
                        allChecked = false;
                        // Also update subtotal for unchecked items to reflect their quantity
                        const subtotalEl = row.querySelector('.item-subtotal');
                        if (subtotalEl && !isNaN(price) && !isNaN(quantity)) subtotalEl.textContent =
                            formatCurrency(price * quantity);
                    }
                });

                const rentalDays = parseInt(rentalDaysInput.value);
                if (!isNaN(rentalDays) && rentalDays > 0) {
                    currentTotal *= rentalDays;
                } else {
                    currentTotal = 0; // Jika durasi tidak valid, total jadi 0
                }

                totalBookingPriceEl.textContent = formatCurrency(currentTotal);
                btnCheckout.disabled = !hasCheckedItem || currentTotal <= 0;

                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked && itemCheckboxes.length > 0;
                    selectAllCheckbox.indeterminate = !allChecked && hasCheckedItem && itemCheckboxes.length > 0;
                }
            }

            function calculateEndDate() {
                if (!startDateInput || !rentalDaysInput || !endDateDisplay) return;

                const startDateValue = startDateInput.value;
                const rentalDaysValue = parseInt(rentalDaysInput.value);

                if (startDateValue && !isNaN(rentalDaysValue) && rentalDaysValue > 0) {
                    try {
                        const [year, month, day] = startDateValue.split('-').map(Number);
                        const startDateObj = new Date(year, month - 1, day);

                        if (isNaN(startDateObj.getTime())) {
                            endDateDisplay.value = 'Tanggal Mulai Tidak Valid';
                            return;
                        }

                        const endDateObj = new Date(startDateObj);
                        endDateObj.setDate(startDateObj.getDate() + rentalDaysValue);

                        const displayDay = String(endDateObj.getDate()).padStart(2, '0');
                        const displayMonth = String(endDateObj.getMonth() + 1).padStart(2, '0');
                        const displayYear = endDateObj.getFullYear();
                        endDateDisplay.value = `${displayDay}/${displayMonth}/${displayYear}`;

                    } catch (e) {
                        console.error("Error calculating end date:", e);
                        endDateDisplay.value = 'Error Kalkulasi';
                    }
                } else {
                    endDateDisplay.value = '';
                }
                calculateTotal(); // Hitung ulang total karena durasi mungkin mempengaruhi
            }

            function updateCartBadge() {
                const cartBadge = document.querySelector('.cart-badge-count');
                if (cartBadge && cartTableBody) {
                    const remainingItemElements = cartTableBody.querySelectorAll('tr');
                    const remainingItems = remainingItemElements.length;
                    cartBadge.textContent = remainingItems;
                    cartBadge.style.display = remainingItems > 0 ? 'inline-block' : 'none';
                }
            }

            function checkIfCartEmpty() {
                if (!cartTableBody || !checkoutSummarySection || !emptyCartMessageSection) return;

                const remainingItemElements = cartTableBody.querySelectorAll('tr');
                const tableElement = cartTableBody.closest('table');
                const tableCardElement = cartTableBody.closest('.card'); // Card pembungkus tabel

                if (remainingItemElements.length === 0) {
                    if (tableElement) tableElement.style.display = 'none';
                    if (tableCardElement) tableCardElement.style.display = 'none';
                    checkoutSummarySection.style.display = 'none';
                    emptyCartMessageSection.style.display = 'block';
                    if (selectAllCheckbox && selectAllCheckbox.closest('th')) {
                        selectAllCheckbox.closest('th').style.display = 'none';
                    }
                } else {
                    if (tableElement) tableElement.style.display = '';
                    if (tableCardElement) tableCardElement.style.display = '';
                    checkoutSummarySection.style.display = '';
                    emptyCartMessageSection.style.display = 'none';
                    if (selectAllCheckbox && selectAllCheckbox.closest('th')) {
                        selectAllCheckbox.closest('th').style.display = '';
                    }
                }
            }

            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Simpan status checkbox ke localStorage
                    let savedSelection = JSON.parse(localStorage.getItem(
                        SELECTED_ITEMS_STORAGE_KEY) || '[]');
                    if (this.checked) {
                        if (!savedSelection.includes(this.value)) {
                            savedSelection.push(this.value);
                        }
                    } else {
                        savedSelection = savedSelection.filter(hash => hash !== this.value);
                    }
                    localStorage.setItem(SELECTED_ITEMS_STORAGE_KEY, JSON.stringify(
                    savedSelection));
                    calculateTotal();
                });
            });

            // Restore selected items on page load
            const savedItems = JSON.parse(localStorage.getItem(SELECTED_ITEMS_STORAGE_KEY) || '[]');
            itemCheckboxes.forEach(checkbox => {
                if (savedItems.includes(checkbox.value)) {
                    checkbox.checked = true;
                }
            });


            let debounceTimer;
            quantityInputs.forEach(input => {
                input.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const currentInput = this;
                    const row = currentInput.closest('tr');

                    debounceTimer = setTimeout(() => {
                        const maxStock = parseInt(currentInput.getAttribute('max'));
                        let currentValue = parseInt(currentInput.value);
                        const cartItemHash = currentInput.dataset.cartitemhash;

                        if (isNaN(currentValue) || currentValue < 1) {
                            currentValue = 1;
                            currentInput.value = 1;
                        } else if (currentValue > maxStock) {
                            currentValue = maxStock;
                            currentInput.value = maxStock;
                            Swal.fire('Stok Tidak Cukup',
                                `Maksimal stok tersedia: ${maxStock}.`, 'warning');
                        }

                        // Update subtotal pada baris
                        const price = parseFloat(row.dataset.itemPrice);
                        const subtotalEl = row.querySelector('.item-subtotal');
                        if (subtotalEl && !isNaN(price)) {
                            subtotalEl.textContent = formatCurrency(price * currentValue);
                        }

                        calculateTotal(); // Hitung total keseluruhan jika item ini terpilih

                        const updateUrl = "{{ route('customer.cart.update') }}";
                        const formData = new FormData();
                        formData.append('_method', 'PUT');
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('cart_item_hashid', cartItemHash);
                        formData.append('quantity', currentValue);

                        fetch(updateUrl, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                    'Accept': 'application/json',
                                }
                            })
                            .then(response => response.ok ? response.json() : response
                            .json().then(err => {
                                throw err;
                            }))
                            .then(data => {
                                // console.log('Quantity updated:', data.message);
                                // Tidak perlu notifikasi berlebihan di sini
                            })
                            .catch(error => {
                                console.error("Update Quantity Error:", error);
                                Swal.fire('Error!', error.error ||
                                    'Gagal update jumlah item.', 'error');
                            });
                    }, 750);
                });
            });

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    let currentSelection = JSON.parse(localStorage.getItem(SELECTED_ITEMS_STORAGE_KEY) ||
                        '[]');
                    itemCheckboxes.forEach(checkbox => {
                        checkbox.checked = isChecked;
                        if (isChecked) {
                            if (!currentSelection.includes(checkbox.value)) {
                                currentSelection.push(checkbox.value);
                            }
                        } else {
                            currentSelection = currentSelection.filter(hash => hash !== checkbox
                                .value);
                        }
                    });
                    localStorage.setItem(SELECTED_ITEMS_STORAGE_KEY, JSON.stringify(currentSelection));
                    calculateTotal();
                });
            }

            if (startDateInput) startDateInput.addEventListener('change', calculateEndDate);
            if (rentalDaysInput) {
                rentalDaysInput.addEventListener('change', calculateEndDate);
                rentalDaysInput.addEventListener('input', calculateEndDate);
            }
            // Untuk start_time, tidak ada kalkulasi JS yang perlu diubah di sini,
            // nilainya akan dikirimkan bersama form.

            document.querySelectorAll('.btn-remove-cart').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const form = this.closest('form');
                    const cartItemRow = this.closest('tr');
                    const url = form.getAttribute('action');
                    const formData = new FormData(form);
                    const cartItemHashToRemove = form.querySelector(
                        'input[name="cart_item_hashid"]').value;

                    Swal.fire({
                        title: 'Yakin Hapus Item?',
                        text: "Item ini akan dihapus dari keranjang Anda.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-danger mx-1',
                            cancelButton: 'btn btn-secondary mx-1'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(url, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').getAttribute(
                                            'content'),
                                        'Accept': 'application/json',
                                    }
                                })
                                .then(response => response.ok ? response.json() : response
                                    .json().then(err => {
                                        throw err;
                                    }))
                                .then(data => {
                                    Swal.fire({
                                        title: 'Terhapus!',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    if (cartItemRow) cartItemRow.remove();

                                    // Re-initialize arrays setelah DOM berubah
                                    itemCheckboxes = cartTableBody ? Array.from(
                                        cartTableBody.querySelectorAll(
                                            '.item-checkbox')) : [];
                                    quantityInputs = cartTableBody ? Array.from(
                                        cartTableBody.querySelectorAll(
                                            '.quantity-input')) : [];

                                    let savedSelection = JSON.parse(localStorage
                                        .getItem(SELECTED_ITEMS_STORAGE_KEY) || '[]'
                                        );
                                    savedSelection = savedSelection.filter(hash =>
                                        hash !== cartItemHashToRemove);
                                    localStorage.setItem(SELECTED_ITEMS_STORAGE_KEY,
                                        JSON.stringify(savedSelection));

                                    calculateTotal();
                                    updateCartBadge();
                                    checkIfCartEmpty();
                                })
                                .catch(error => {
                                    let errorMessage = 'Gagal menghapus item.';
                                    if (error && error.error) {
                                        errorMessage = error.error;
                                    } else if (error && error.message) {
                                        errorMessage = error.message;
                                    }
                                    console.error("Delete Error:", error);
                                    Swal.fire('Error!', errorMessage, 'error');
                                });
                        }
                    });
                });
            });

            // Initial calculations
            calculateEndDate();
            // calculateTotal akan dipanggil oleh pemulihan item terpilih
            if (itemCheckboxes.length > 0) { // Pastikan ada item sebelum menghitung
                calculateTotal();
            }
            updateCartBadge();
            checkIfCartEmpty();

            // Hapus localStorage saat form di-submit (checkout berhasil)
            const checkoutForm = document.getElementById('checkout-form');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function() {
                    localStorage.removeItem(SELECTED_ITEMS_STORAGE_KEY);
                });
            }

        });
    </script>
@endpush
