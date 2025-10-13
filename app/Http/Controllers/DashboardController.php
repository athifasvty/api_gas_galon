<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Tampilkan dashboard
     */
    public function index()
    {
        try {
            // Get data overview dari API
            $response = $this->apiService->getLaporan('overview');

            // Cek apakah response valid
            if (!$response || !is_array($response)) {
                return view('dashboard.index', [
                    'error' => 'Tidak dapat terhubung ke API. Pastikan API sedang berjalan.',
                    'pesanan_hari_ini' => 0,
                    'pendapatan_hari_ini' => 0,
                    'pesanan_pending' => 0,
                    'produk_stok_menipis' => 0,
                    'produk_terlaris' => [],
                ]);
            }

            // Cek apakah request berhasil
            if (!isset($response['success']) || !$response['success']) {
                $errorMessage = $response['message'] ?? 'Terjadi kesalahan saat mengambil data';
                
                return view('dashboard.index', [
                    'error' => $errorMessage,
                    'pesanan_hari_ini' => 0,
                    'pendapatan_hari_ini' => 0,
                    'pesanan_pending' => 0,
                    'produk_stok_menipis' => 0,
                    'produk_terlaris' => [],
                ]);
            }

            // Data berhasil diambil
            $data = $response['data'] ?? [];

            return view('dashboard.index', [
                'pesanan_hari_ini' => $data['pesanan_hari_ini'] ?? 0,
                'pendapatan_hari_ini' => $data['pendapatan_hari_ini'] ?? 0,
                'pesanan_pending' => $data['pesanan_pending'] ?? 0,
                'produk_stok_menipis' => $data['produk_stok_menipis'] ?? 0,
                'produk_terlaris' => $data['produk_terlaris'] ?? [],
            ]);
            
        } catch (\Exception $e) {
            // Tangkap semua error
            return view('dashboard.index', [
                'error' => 'Error: ' . $e->getMessage(),
                'pesanan_hari_ini' => 0,
                'pendapatan_hari_ini' => 0,
                'pesanan_pending' => 0,
                'produk_stok_menipis' => 0,
                'produk_terlaris' => [],
            ]);
        }
    }
}