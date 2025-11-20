<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class KurirController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Tampilkan list kurir
     */
    public function index()
    {
        try {
            $response = $this->apiService->getKurir();

            // Cek apakah response valid
            if (!$response || !is_array($response)) {
                return view('kurir.index', [
                    'error' => 'Tidak dapat terhubung ke API',
                    'kurir' => []
                ]);
            }

            if (!isset($response['success']) || !$response['success']) {
                return view('kurir.index', [
                    'error' => $response['message'] ?? 'Gagal mengambil data kurir',
                    'kurir' => []
                ]);
            }

            return view('kurir.index', [
                'kurir' => $response['data'] ?? []
            ]);

        } catch (\Exception $e) {
            return view('kurir.index', [
                'error' => 'Error: ' . $e->getMessage(),
                'kurir' => []
            ]);
        }
    }

    /**
     * Tampilkan form tambah kurir
     */
    public function create()
    {
        return view('kurir.create');
    }

    /**
     * Simpan kurir baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|alpha_dash',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:15',
        ], [
            'name.required' => 'Nama kurir harus diisi',
            'username.required' => 'Username harus diisi',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dan underscore',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        try {
            $response = $this->apiService->createKurir([
                'name' => $request->name,
                'username' => $request->username,
                'password' => $request->password,
                'phone' => $request->phone,
            ]);

            // Cek response
            if (!$response || !is_array($response)) {
                return back()->withInput()->with('error', 'Tidak dapat terhubung ke API');
            }

            if (!isset($response['success']) || !$response['success']) {
                return back()->withInput()->with('error', $response['message'] ?? 'Gagal menambahkan kurir');
            }

            return redirect()->route('kurir.index')->with('success', 'Kurir berhasil ditambahkan');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan form edit kurir
     */
    public function edit($id)
    {
        try {
            $response = $this->apiService->getKurir($id);

            if (!$response || !is_array($response)) {
                return redirect()->route('kurir.index')->with('error', 'Tidak dapat terhubung ke API');
            }

            if (!isset($response['success']) || !$response['success']) {
                return redirect()->route('kurir.index')->with('error', $response['message'] ?? 'Kurir tidak ditemukan');
            }

            return view('kurir.edit', [
                'kurir' => $response['data']
            ]);

        } catch (\Exception $e) {
            return redirect()->route('kurir.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Update kurir
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:6',
        ]);

        try {
            $data = [
                'id' => $id,
                'name' => $request->name,
                'phone' => $request->phone,
            ];

            // Hanya update password jika diisi
            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }

            $response = $this->apiService->updateKurir($data);

            if (!$response || !is_array($response)) {
                return back()->withInput()->with('error', 'Tidak dapat terhubung ke API');
            }

            if (!isset($response['success']) || !$response['success']) {
                return back()->withInput()->with('error', $response['message'] ?? 'Gagal mengupdate kurir');
            }

            return redirect()->route('kurir.index')->with('success', 'Data kurir berhasil diupdate');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Hapus kurir
     */
    public function destroy($id)
    {
        try {
            $response = $this->apiService->deleteKurir($id);

            if (!$response || !is_array($response)) {
                return back()->with('error', 'Tidak dapat terhubung ke API');
            }

            if (!isset($response['success']) || !$response['success']) {
                return back()->with('error', $response['message'] ?? 'Gagal menghapus kurir');
            }

            return redirect()->route('kurir.index')->with('success', 'Kurir berhasil dihapus');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}