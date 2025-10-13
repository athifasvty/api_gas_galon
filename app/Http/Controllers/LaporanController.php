<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Laporan Overview
     */
    public function overview()
    {
        $response = $this->apiService->getLaporan('overview');

        if (!$response['success']) {
            return view('laporan.overview')->with('error', $response['message']);
        }

        return view('laporan.overview', ['data' => $response['data']]);
    }

    /**
     * Laporan Stok
     */
    public function stok()
    {
        $response = $this->apiService->getLaporan('stok');

        if (!$response['success']) {
            return view('laporan.stok')->with('error', $response['message']);
        }

        return view('laporan.stok', ['produk' => $response['data']]);
    }

    /**
     * Laporan Transaksi
     */
    public function transaksi(Request $request)
    {
        $tanggal_dari = $request->input('tanggal_dari', date('Y-m-01'));
        $tanggal_sampai = $request->input('tanggal_sampai', date('Y-m-d'));

        $response = $this->apiService->getLaporan('transaksi', [
            'tanggal_dari' => $tanggal_dari,
            'tanggal_sampai' => $tanggal_sampai,
        ]);

        if (!$response['success']) {
            return view('laporan.transaksi')->with('error', $response['message']);
        }

        return view('laporan.transaksi', [
            'data' => $response['data'],
            'tanggal_dari' => $tanggal_dari,
            'tanggal_sampai' => $tanggal_sampai,
        ]);
    }

    /**
     * Laporan Performa Kurir
     */
    public function kurir(Request $request)
    {
        $tanggal_dari = $request->input('tanggal_dari', date('Y-m-01'));
        $tanggal_sampai = $request->input('tanggal_sampai', date('Y-m-d'));

        $response = $this->apiService->getLaporan('kurir', [
            'tanggal_dari' => $tanggal_dari,
            'tanggal_sampai' => $tanggal_sampai,
        ]);

        if (!$response['success']) {
            return view('laporan.kurir')->with('error', $response['message']);
        }

        return view('laporan.kurir', [
            'kurir' => $response['data'],
            'tanggal_dari' => $tanggal_dari,
            'tanggal_sampai' => $tanggal_sampai,
        ]);
    }
}