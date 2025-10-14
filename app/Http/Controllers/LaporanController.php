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
        try {
            $response = $this->apiService->getLaporan('overview');

            if (!$response || !is_array($response)) {
                return view('laporan.overview', [
                    'error' => 'Tidak dapat terhubung ke API',
                    'data' => []
                ]);
            }

            if (!isset($response['success']) || !$response['success']) {
                return view('laporan.overview', [
                    'error' => $response['message'] ?? 'Gagal mengambil data',
                    'data' => []
                ]);
            }

            return view('laporan.overview', [
                'data' => $response['data'] ?? []
            ]);

        } catch (\Exception $e) {
            return view('laporan.overview', [
                'error' => 'Error: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Laporan Stok
     */
    public function stok()
    {
        try {
            $response = $this->apiService->getLaporan('stok');

            if (!$response || !is_array($response)) {
                return view('laporan.stok', [
                    'error' => 'Tidak dapat terhubung ke API',
                    'produk' => []
                ]);
            }

            if (!isset($response['success']) || !$response['success']) {
                return view('laporan.stok', [
                    'error' => $response['message'] ?? 'Gagal mengambil data',
                    'produk' => []
                ]);
            }

            return view('laporan.stok', [
                'produk' => $response['data'] ?? []
            ]);

        } catch (\Exception $e) {
            return view('laporan.stok', [
                'error' => 'Error: ' . $e->getMessage(),
                'produk' => []
            ]);
        }
    }

    /**
     * Laporan Transaksi
     */
    public function transaksi(Request $request)
    {
        $tanggal_dari = $request->input('tanggal_dari', date('Y-m-01'));
        $tanggal_sampai = $request->input('tanggal_sampai', date('Y-m-d'));

        try {
            $response = $this->apiService->getLaporan('transaksi', [
                'tanggal_dari' => $tanggal_dari,
                'tanggal_sampai' => $tanggal_sampai,
            ]);

            if (!$response || !is_array($response)) {
                return view('laporan.transaksi', [
                    'error' => 'Tidak dapat terhubung ke API',
                    'data' => [
                        'detail' => [],
                        'summary' => [
                            'total_transaksi' => 0,
                            'total_pendapatan' => 0,
                            'transaksi_selesai' => 0,
                            'transaksi_dibatalkan' => 0
                        ]
                    ],
                    'tanggal_dari' => $tanggal_dari,
                    'tanggal_sampai' => $tanggal_sampai,
                ]);
            }

            if (!isset($response['success']) || !$response['success']) {
                return view('laporan.transaksi', [
                    'error' => $response['message'] ?? 'Gagal mengambil data',
                    'data' => [
                        'detail' => [],
                        'summary' => [
                            'total_transaksi' => 0,
                            'total_pendapatan' => 0,
                            'transaksi_selesai' => 0,
                            'transaksi_dibatalkan' => 0
                        ]
                    ],
                    'tanggal_dari' => $tanggal_dari,
                    'tanggal_sampai' => $tanggal_sampai,
                ]);
            }

            return view('laporan.transaksi', [
                'data' => $response['data'] ?? [
                    'detail' => [],
                    'summary' => []
                ],
                'tanggal_dari' => $tanggal_dari,
                'tanggal_sampai' => $tanggal_sampai,
            ]);

        } catch (\Exception $e) {
            return view('laporan.transaksi', [
                'error' => 'Error: ' . $e->getMessage(),
                'data' => [
                    'detail' => [],
                    'summary' => []
                ],
                'tanggal_dari' => $tanggal_dari,
                'tanggal_sampai' => $tanggal_sampai,
            ]);
        }
    }

    /**
     * Laporan Performa Kurir
     */
    public function kurir(Request $request)
    {
        $tanggal_dari = $request->input('tanggal_dari', date('Y-m-01'));
        $tanggal_sampai = $request->input('tanggal_sampai', date('Y-m-d'));

        try {
            $response = $this->apiService->getLaporan('kurir', [
                'tanggal_dari' => $tanggal_dari,
                'tanggal_sampai' => $tanggal_sampai,
            ]);

            // Debug: Uncomment line ini untuk lihat response structure
            // dd($response);

            if (!$response || !is_array($response)) {
                return view('laporan.kurir', [
                    'error' => 'Tidak dapat terhubung ke API',
                    'kurir' => [],
                    'tanggal_dari' => $tanggal_dari,
                    'tanggal_sampai' => $tanggal_sampai,
                ]);
            }

            if (!isset($response['success']) || !$response['success']) {
                return view('laporan.kurir', [
                    'error' => $response['message'] ?? 'Gagal mengambil data',
                    'kurir' => [],
                    'tanggal_dari' => $tanggal_dari,
                    'tanggal_sampai' => $tanggal_sampai,
                ]);
            }

            // Ambil data kurir dari response
            // Cek struktur response: bisa $response['data'] atau $response['data']['data']
            $kurirData = [];
            if (isset($response['data'])) {
                // Jika response['data'] adalah array dengan key 'data' lagi
                if (is_array($response['data']) && isset($response['data']['data'])) {
                    $kurirData = $response['data']['data'];
                } else {
                    // Jika response['data'] langsung array kurir
                    $kurirData = $response['data'];
                }
            }

            return view('laporan.kurir', [
                'kurir' => $kurirData,
                'tanggal_dari' => $tanggal_dari,
                'tanggal_sampai' => $tanggal_sampai,
            ]);

        } catch (\Exception $e) {
            return view('laporan.kurir', [
                'error' => 'Error: ' . $e->getMessage(),
                'kurir' => [],
                'tanggal_dari' => $tanggal_dari,
                'tanggal_sampai' => $tanggal_sampai,
            ]);
        }
    }
}