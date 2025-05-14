<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Pastikan Auth di-import jika digunakan di closure

/*
|--------------------------------------------------------------------------
| Import Controllers
|--------------------------------------------------------------------------
*/
// == Admin Controllers ==
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\ItemController as AdminItemController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;

// == Customer Controllers ==
use App\Http\Controllers\Customer\LoginController as CustomerLoginController;
use App\Http\Controllers\Customer\RegisterController as CustomerRegisterController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\CatalogController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;

// == Webhook Controller ==
use App\Http\Controllers\Webhook\MidtransController as MidtransWebhookController;


/*
|--------------------------------------------------------------------------
| Rute Landing Page / Umum
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Jika sudah login sebagai customer, arahkan ke dashboard
    if (Auth::guard('customer')->check()) {
        return redirect()->route('customer.dashboard');
    }
    // Jika belum, arahkan ke halaman login customer
    return redirect()->route('customer.login');
})->name('landing');


/*
|==========================================================================
| ADMIN ROUTES
|==========================================================================
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () { // Guard 'web' untuk admin
        Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login'])->name('login.post');
    });
    Route::middleware('auth')->group(function () { // Guard 'web' untuk admin
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Brands (Decode Manual)
        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/', [AdminBrandController::class, 'index'])->name('index');
            Route::get('/data', [AdminBrandController::class, 'getData'])->name('data');
            Route::get('/create', [AdminBrandController::class, 'create'])->name('create');
            Route::post('/', [AdminBrandController::class, 'store'])->name('store');
            Route::get('/{brand_hash}/edit', [AdminBrandController::class, 'edit'])->name('edit')->where('brand_hash', '[a-zA-Z0-9]+');
            Route::put('/{brand_hash}', [AdminBrandController::class, 'update'])->name('update')->where('brand_hash', '[a-zA-Z0-9]+');
            Route::delete('/{brand_hash}', [AdminBrandController::class, 'destroy'])->name('destroy')->where('brand_hash', '[a-zA-Z0-9]+');
        });

        // Store Information Management
        Route::get('/store/edit', [AdminStoreController::class, 'edit'])->name('store.edit');
        Route::put('/store/update', [AdminStoreController::class, 'update'])->name('store.update');
        // Items (RMB :hashid)
        Route::prefix('items')->name('items.')->group(function () {
            Route::get('/', [AdminItemController::class, 'index'])->name('index');
            Route::get('/data', [AdminItemController::class, 'getData'])->name('data');
            Route::get('/create', [AdminItemController::class, 'create'])->name('create');
            Route::post('/', [AdminItemController::class, 'store'])->name('store');
            Route::get('/{item:hashid}/edit', [AdminItemController::class, 'edit'])->name('edit');
            Route::put('/{item:hashid}', [AdminItemController::class, 'update'])->name('update');
            Route::delete('/{item:hashid}', [AdminItemController::class, 'destroy'])->name('destroy');
        });

        // Categories (RMB :hashid)
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [AdminCategoryController::class, 'index'])->name('index');
            Route::get('/data', [AdminCategoryController::class, 'getData'])->name('data');
            Route::get('/create', [AdminCategoryController::class, 'create'])->name('create');
            Route::post('/', [AdminCategoryController::class, 'store'])->name('store');
            Route::get('/{category:hashid}/edit', [AdminCategoryController::class, 'edit'])->name('edit');
            Route::put('/{category:hashid}', [AdminCategoryController::class, 'update'])->name('update');
            Route::delete('/{category:hashid}', [AdminCategoryController::class, 'destroy'])->name('destroy');
        });

        // Customer Data Management by Admin (RMB :hashid)
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [AdminCustomerController::class, 'index'])->name('index');
            Route::get('/data', [AdminCustomerController::class, 'getData'])->name('data');
            Route::get('/create', [AdminCustomerController::class, 'create'])->name('create');
            Route::post('/', [AdminCustomerController::class, 'store'])->name('store');
            Route::get('/{customer:hashid}/edit', [AdminCustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer:hashid}', [AdminCustomerController::class, 'update'])->name('update');
            Route::delete('/{customer:hashid}', [AdminCustomerController::class, 'destroy'])->name('destroy');
        });

        // User Data Management by Admin (RMB :hashid)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/data', [AdminUserController::class, 'getData'])->name('data');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user:hashid}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user:hashid}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user:hashid}', [AdminUserController::class, 'destroy'])->name('destroy');
        });

        // routes/web.php -> di dalam grup admin.payments.
        // ...
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
            Route::get('/data', [AdminPaymentController::class, 'getData'])->name('data');
            Route::get('/{payment:hashid}/edit', [AdminPaymentController::class, 'edit'])->name('edit');
            Route::put('/{payment:hashid}', [AdminPaymentController::class, 'update'])->name('update');
            Route::get('/{payment:hashid}', [AdminPaymentController::class, 'show'])->name('show');
        });

        // Route::prefix('bookings')->name('bookings.')->group(function () {

        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [AdminBookingController::class, 'index'])->name('index');          // Daftar semua booking
            Route::get('/data', [AdminBookingController::class, 'getData'])->name('data');        // Data untuk DataTables
            Route::get('/{booking:hashid}', [AdminBookingController::class, 'show'])->name('show'); // Detail booking
            Route::get('/{booking:hashid}/edit-status', [AdminBookingController::class, 'editStatus'])->name('editStatus'); // Form edit status rental
            Route::put('/{booking:hashid}/update-status', [AdminBookingController::class, 'updateStatus'])->name('updateStatus'); // Proses update status rental
            Route::get('/{booking:hashid}/print', [AdminBookingController::class, 'printBooking'])->name('print');
        });
    });
});


/*
|==========================================================================
| CUSTOMER ROUTES
|==========================================================================
*/
Route::name('customer.')->group(function () {

    // --- Customer Authentication (Guest Only) ---
    Route::middleware('guest:customer')->group(function () { // Guard 'customer'
        Route::get('/login', [CustomerLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [CustomerLoginController::class, 'login'])->name('login.post');
        Route::get('/register', [CustomerRegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [CustomerRegisterController::class, 'register'])->name('register.post');
    });

    // --- Customer Authenticated Routes ---
    Route::middleware('auth:customer')->group(function () { // Guard 'customer'
        Route::post('/logout', [CustomerLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

        // === KATALOG & DETAIL ITEM (SEKARANG MEMBUTUHKAN LOGIN CUSTOMER) ===
        Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
        Route::get('/item/{item_hash}', [CatalogController::class, 'show'])
            ->name('catalog.show')
            ->where('item_hash', '[a-zA-Z0-9]+'); // Decode manual di controller
        // =================================================================

        // Cart Routes
        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add', [CartController::class, 'add'])->name('add');
            Route::put('/update', [CartController::class, 'update'])->name('update');
            Route::delete('/remove', [CartController::class, 'remove'])->name('remove');
        });

        // Booking & Payment Process Routes
        Route::post('/booking/checkout', [CustomerBookingController::class, 'processCheckout'])->name('booking.checkout');

        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/initiate/{booking_hashid}', [CustomerPaymentController::class, 'initiatePayment'])->name('initiate')->where('booking_hashid', '[a-zA-Z0-9]+');
            Route::get('/show/{booking_hashid}', [CustomerPaymentController::class, 'showPaymentPage'])->name('show')->where('booking_hashid', '[a-zA-Z0-9]+');
            Route::get('/finished/{booking_hashid}', [CustomerPaymentController::class, 'paymentFinished'])->name('finished')->where('booking_hashid', '[a-zA-Z0-9]+');
        });

        // === MY BOOKINGS ROUTES ===
        Route::prefix('my-bookings')->name('bookings.')->group(function () {
            // Halaman utama daftar booking (sekarang hanya menampilkan view kerangka DataTables)
            Route::get('/', [CustomerBookingController::class, 'myBookings'])->name('index');

            // === TAMBAHKAN ROUTE INI UNTUK ENDPOINT DATATABLES ===
            Route::get('/data', [CustomerBookingController::class, 'getMyBookingsData'])->name('data');
            // =======================================================

            // Route untuk menampilkan DETAIL booking
            Route::get('/{booking_hashid}', [CustomerBookingController::class, 'showMyBooking'])
                ->name('show') // Nama route lengkap: customer.bookings.show
                ->where('booking_hashid', '[a-zA-Z0-9]+');
        });

        // === PROFILE MANAGEMENT ROUTES ===
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/edit', [CustomerProfileController::class, 'edit'])->name('edit'); // Halaman edit profil
            Route::put('/update', [CustomerProfileController::class, 'update'])->name('update'); // Proses update profil
            // Anda bisa tambahkan route lain jika ada, misal untuk ubah foto profil
        });
    });
});


/*
|==========================================================================
| MIDTRANS WEBHOOK NOTIFICATION ROUTE
|==========================================================================
*/
//  Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handleNotification'])->name('midtrans.notification');
