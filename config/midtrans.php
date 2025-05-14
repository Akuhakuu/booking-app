<?php
// config/midtrans.php

return [
    // Baca nilai dari variabel MIDTRANS_MERCHANT_ID di file .env
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),

    // Baca nilai dari variabel MIDTRANS_CLIENT_KEY di file .env
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    // Baca nilai dari variabel MIDTRANS_SERVER_KEY di file .env
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    // Baca nilai dari MIDTRANS_IS_PRODUCTION, default ke false jika tidak ada
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Baca nilai dari MIDTRANS_IS_SANITIZED, default ke true jika tidak ada
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    // Baca nilai dari MIDTRANS_IS_3DS, default ke true jika tidak ada
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];
