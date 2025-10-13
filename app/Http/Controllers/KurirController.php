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
        $response = $this->apiService->getKurir();

        if (!$response['success']) {
            return view('kurir.index')->with('error', $response['message']);
        }

        return view('kurir.index', [
            'kurir' => $response['data']
        ]);
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

        $response = $this->apiService->createKurir([
            'name' => $request->name,
            'username' => $request->username,
            'password' => $request->password,
            'phone' => $request->phone,
        ]);

        if (!$response['success']) {
            return back()->withInput()->with('error', $response['message']);
        }

        return redirect()->route('kurir.index')->with('success', 'Kurir berhasil ditambahkan');
    }

    /**
     * Tampilkan form edit kurir
     */
    public function edit($id)
    {
        $response = $this->apiService->getKurir($id);

        if (!$response['success']) {
            return redirect()->route('kurir.index')->with('error', $response['message']);
        }

        return view('kurir.edit', [
            'kurir' => $response['data']
        ]);
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

        if (!$response['success']) {
            return back()->withInput()->with('error', $response['message']);
        }

        return redirect()->route('kurir.index')->with('success', 'Data kurir berhasil diupdate');
    }

    /**
     * Hapus kurir
     */
    public function destroy($id)
    {
        $response = $this->apiService->deleteKurir($id);

        if (!$response['success']) {
            return back()->with('error', $response['message']);
        }

        return redirect()->route('kurir.index')->with('success', 'Kurir berhasil dihapus');
    }
}