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
     * Assign kurir ke pesanan
     */
    public function assignKurir(Request $request, $id)
    {
        $request->validate([
            'id_kurir' => 'required|integer',
        ]);

        $response = $this->apiService->updatePesanan([
            'id' => $id,
            'id_kurir' => $request->id_kurir,
        ]);

        if (!$response['success']) {
            return back()->with('error', $response['message']);
        }

        return back()->with('success', 'Kurir berhasil di-assign');
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