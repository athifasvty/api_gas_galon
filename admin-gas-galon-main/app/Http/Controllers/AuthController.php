<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Tampilkan halaman login
     */
    public function showLoginForm()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->has('auth_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Process login
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ], [
            'username.required' => 'Username harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        // Call API login
        $response = $this->apiService->login(
            $request->username,
            $request->password
        );

        // Cek response
        if (!$response['success']) {
            return back()
                ->withInput()
                ->with('error', $response['message']);
        }

        // Cek apakah role adalah admin
        if ($response['data']['user']['role'] !== 'admin') {
            return back()
                ->withInput()
                ->with('error', 'Akses ditolak. Hanya admin yang bisa login di web ini.');
        }

        // Simpan data ke session
        session([
            'auth_token' => $response['data']['token'],
            'auth_user' => $response['data']['user'],
            'auth_role' => $response['data']['user']['role'],
            'auth_name' => $response['data']['user']['name'],
        ]);

        return redirect()->route('dashboard')->with('success', 'Login berhasil! Selamat datang, ' . $response['data']['user']['name']);
    }

    /**
     * Process logout
     */
    public function logout()
    {
        // Call API logout (optional, karena stateless)
        $this->apiService->logout();

        // Hapus session
        session()->flush();

        return redirect()->route('login')->with('success', 'Logout berhasil');
    }
}