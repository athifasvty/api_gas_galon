<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Tampilkan list pesanan
     */
    public function index(Request $request)
    {
        $filters = [];
        
        // Filter by status
        if ($request->has('status')) {
            $filters['status'] = $request->status;
        }

        // Filter by tanggal
        if ($request->has('tanggal_dari') && $request->has('tanggal_sampai')) {
            $filters['tanggal_dari'] = $request->tanggal_dari;
            $filters['tanggal_sampai'] = $request->tanggal_sampai;
        }

        $response = $this->apiService->getPesanan(null, $filters);

        if (!$response['success']) {
            return view('pesanan.index')->with('error', $response['message']);
        }

        return view('pesanan.index', [
            'pesanan' => $response['data'],
            'filter_status' => $request->status ?? '',
        ]);
    }

    /**
     * Tampilkan detail pesanan
     */
    public function show($id)
    {
        $response = $this->apiService->getPesanan($id);

        if (!$response['success']) {
            return redirect()->route('pesanan.index')->with('error', $response['message']);
        }

        // Get list kurir untuk assign
        $kurirResponse = $this->apiService->getKurir();

        return view('pesanan.detail', [
            'pesanan' => $response['data'],
            'kurir_list' => $kurirResponse['success'] ? $kurirResponse['data'] : [],
        ]);
    }

    /**
     * Assign kurir ke pesanan (manual atau auto)
     */
    public function assignKurir(Request $request, $id)
    {
        $id_kurir = null;

        // Jika ada id_kurir di request, gunakan itu
        if ($request->has('id_kurir') && !empty($request->id_kurir)) {
            $request->validate([
                'id_kurir' => 'required|integer',
            ]);
            $id_kurir = $request->id_kurir;
        } else {
            // Auto-assign: cari kurir yang free (tidak sedang mengirim)
            $id_kurir = $this->getAvailableKurir();
            
            if (!$id_kurir) {
                return back()->with('error', 'Tidak ada kurir yang tersedia saat ini');
            }
        }

        $response = $this->apiService->updatePesanan([
            'id' => $id,
            'id_kurir' => $id_kurir,
        ]);

        if (!$response['success']) {
            return back()->with('error', $response['message']);
        }

        return back()->with('success', 'Kurir berhasil di-assign');
    }

    /**
     * Get available kurir (yang tidak sedang mengirim)
     */
    private function getAvailableKurir()
    {
        // Get semua kurir
        $kurirResponse = $this->apiService->getKurir();
        
        if (!$kurirResponse['success'] || empty($kurirResponse['data'])) {
            return null;
        }

        $kurir_list = $kurirResponse['data'];

        // Get semua pesanan yang sedang diproses atau dikirim
        $pesananDiprosesResponse = $this->apiService->getPesanan(null, ['status' => 'diproses']);
        $pesananDikirimResponse = $this->apiService->getPesanan(null, ['status' => 'dikirim']);
        
        $pesanan_busy = array_merge(
            $pesananDiprosesResponse['success'] ? $pesananDiprosesResponse['data'] : [],
            $pesananDikirimResponse['success'] ? $pesananDikirimResponse['data'] : []
        );

        // Kumpulkan ID kurir yang sedang handle pesanan
        $kurir_busy = [];
        foreach ($pesanan_busy as $pesanan) {
            if (!empty($pesanan['id_kurir'])) {
                $kurir_busy[] = $pesanan['id_kurir'];
            }
        }

        // Cari kurir yang free
        foreach ($kurir_list as $kurir) {
            if (!in_array($kurir['id'], $kurir_busy)) {
                return $kurir['id'];
            }
        }

        return null;
    }

    /**
     * Update status pesanan
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,diproses,dikirim,selesai,dibatalkan',
        ]);

        $response = $this->apiService->updatePesanan([
            'id' => $id,
            'status' => $request->status,
        ]);

        if (!$response['success']) {
            return back()->with('error', $response['message']);
        }

        return back()->with('success', 'Status pesanan berhasil diupdate');
    }

    /**
     * Hapus pesanan
     */
    public function destroy($id)
    {
        $response = $this->apiService->deletePesanan($id);

        if (!$response['success']) {
            return back()->with('error', $response['message']);
        }

        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil dihapus');
    }
}