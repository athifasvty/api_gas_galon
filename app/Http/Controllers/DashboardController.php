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
        // Get data overview dari API
        $response = $this->apiService->getLaporan('overview');

        if (!$response['success']) {
            return view('dashboard.index')->with('error', $response['message']);
        }

        $data = $response['data'];

        return view('dashboard.index', [
            'pesanan_hari_ini' => $data['pesanan_hari_ini'] ?? 0,
            'pendapatan_hari_ini' => $data['pendapatan_hari_ini'] ?? 0,
            'pesanan_pending' => $data['pesanan_pending'] ?? 0,
            'produk_stok_menipis' => $data['produk_stok_menipis'] ?? 0,
            'produk_terlaris' => $data['produk_terlaris'] ?? [],
        ]);
    }
}