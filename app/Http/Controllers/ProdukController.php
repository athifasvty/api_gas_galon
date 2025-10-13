<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Tampilkan list produk
     */
    public function index()
    {
        $response = $this->apiService->getProduk();

        if (!$response['success']) {
            return view('produk.index')->with('error', $response['message']);
        }

        return view('produk.index', [
            'produk' => $response['data']
        ]);
    }

    /**
     * Tampilkan form tambah produk
     */
    public function create()
    {
        return view('produk.create');
    }

    /**
     * Simpan produk baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_produk' => 'required|string|max:100',
            'jenis' => 'required|in:elpiji,galon',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ], [
            'nama_produk.required' => 'Nama produk harus diisi',
            'jenis.required' => 'Jenis produk harus dipilih',
            'jenis.in' => 'Jenis produk tidak valid',
            'harga.required' => 'Harga harus diisi',
            'harga.numeric' => 'Harga harus berupa angka',
            'stok.required' => 'Stok harus diisi',
            'stok.integer' => 'Stok harus berupa angka',
        ]);

        // Call API
        $response = $this->apiService->createProduk([
            'nama_produk' => $request->nama_produk,
            'jenis' => $request->jenis,
            'harga' => $request->harga,
            'stok' => $request->stok,
        ]);

        if (!$response['success']) {
            return back()->withInput()->with('error', $response['message']);
        }

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan');
    }

    /**
     * Tampilkan form edit produk
     */
    public function edit($id)
    {
        $response = $this->apiService->getProduk($id);

        if (!$response['success']) {
            return redirect()->route('produk.index')->with('error', $response['message']);
        }

        return view('produk.edit', [
            'produk' => $response['data']
        ]);
    }

    /**
     * Update produk
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'nama_produk' => 'required|string|max:100',
            'jenis' => 'required|in:elpiji,galon',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        // Call API
        $response = $this->apiService->updateProduk([
            'id' => $id,
            'nama_produk' => $request->nama_produk,
            'jenis' => $request->jenis,
            'harga' => $request->harga,
            'stok' => $request->stok,
        ]);

        if (!$response['success']) {
            return back()->withInput()->with('error', $response['message']);
        }

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diupdate');
    }

    /**
     * Hapus produk
     */
    public function destroy($id)
    {
        $response = $this->apiService->deleteProduk($id);

        if (!$response['success']) {
            return back()->with('error', $response['message']);
        }

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus');
    }
}