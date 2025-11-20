<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL untuk PHP API backend
    | Development: http://localhost/api_gas_galon/api
    | Ngrok: https://your-ngrok-url.ngrok.io/api_gas_galon/api
    | Production: https://yourdomain.com/api
    |
    */
    'base_url' => env('API_BASE_URL', 'http://localhost/api_gas_galon/api'),

    /*
    |--------------------------------------------------------------------------
    | API Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout untuk request ke API (dalam detik)
    |
    */
    'timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | Daftar endpoint yang tersedia
    |
    */
    'endpoints' => [
        'auth' => [
            'login' => '/auth/login.php',
            'logout' => '/auth/logout.php',
        ],
        'admin' => [
            'produk' => '/admin/produk.php',
            'pesanan' => '/admin/pesanan.php',
            'kurir' => '/admin/kurir.php',
            'laporan' => '/admin/laporan.php',
        ],
    ],
];