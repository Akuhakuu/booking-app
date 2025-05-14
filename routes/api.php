<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhook\MidtransController as MidtransWebhookController; // <-- Pastikan import

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// === MIDTRANS WEBHOOK NOTIFICATION ROUTE ===
// URL akan menjadi: https://yourdomain.com/api/midtrans/notification
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handleNotification'])->name('api.midtrans.notification'); // Beri nama berbeda untuk menghindari konflik jika ada nama sama di web.php
// =======================================