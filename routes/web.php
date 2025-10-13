<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\LaporanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes (Guest only - belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout (harus sudah login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (harus login sebagai admin)
Route::middleware(['admin.auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Produk Routes
    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [ProdukController::class, 'index'])->name('index');
        Route::get('/create', [ProdukController::class, 'create'])->name('create');
        Route::post('/', [ProdukController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ProdukController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProdukController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProdukController::class, 'destroy'])->name('destroy');
    });

    // Pesanan Routes
    Route::prefix('pesanan')->name('pesanan.')->group(function () {
        Route::get('/', [PesananController::class, 'index'])->name('index');
        Route::get('/{id}', [PesananController::class, 'show'])->name('show');
        Route::post('/{id}/assign-kurir', [PesananController::class, 'assignKurir'])->name('assign-kurir');
        Route::post('/{id}/update-status', [PesananController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}', [PesananController::class, 'destroy'])->name('destroy');
    });

    // Kurir Routes
    Route::prefix('kurir')->name('kurir.')->group(function () {
        Route::get('/', [KurirController::class, 'index'])->name('index');
        Route::get('/create', [KurirController::class, 'create'])->name('create');
        Route::post('/', [KurirController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [KurirController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KurirController::class, 'update'])->name('update');
        Route::delete('/{id}', [KurirController::class, 'destroy'])->name('destroy');
    });

    // Laporan Routes
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/overview', [LaporanController::class, 'overview'])->name('overview');
        Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
        Route::get('/transaksi', [LaporanController::class, 'transaksi'])->name('transaksi');
        Route::get('/kurir', [LaporanController::class, 'kurir'])->name('kurir');
    });
});