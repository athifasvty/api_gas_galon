<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService
{
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('api.base_url');
        $this->timeout = config('api.timeout');
    }

    /**
     * Get authorization token from session
     */
    private function getToken()
    {
        return session('auth_token');
    }

    /**
     * Prepare headers for API request
     */
    private function getHeaders($includeAuth = true)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($includeAuth) {
            $token = $this->getToken();
            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
        }

        return $headers;
    }

    /**
     * GET request
     */
    public function get($endpoint, $params = [], $includeAuth = true)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($includeAuth))
                ->timeout($this->timeout)
                ->get($this->baseUrl . $endpoint, $params);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('API GET Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi ke server: ' . $e->getMessage()
            ];
        }
    }

    /**
     * POST request
     */
    public function post($endpoint, $data = [], $includeAuth = true)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($includeAuth))
                ->timeout($this->timeout)
                ->post($this->baseUrl . $endpoint, $data);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('API POST Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi ke server: ' . $e->getMessage()
            ];
        }
    }

    /**
     * PUT request
     */
    public function put($endpoint, $data = [], $includeAuth = true)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($includeAuth))
                ->timeout($this->timeout)
                ->put($this->baseUrl . $endpoint, $data);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('API PUT Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi ke server: ' . $e->getMessage()
            ];
        }
    }

    /**
     * DELETE request
     */
    public function delete($endpoint, $data = [], $includeAuth = true)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($includeAuth))
                ->timeout($this->timeout)
                ->delete($this->baseUrl . $endpoint, $data);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('API DELETE Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi ke server: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle API response
     */
    private function handleResponse($response)
    {
        $statusCode = $response->status();
        $body = $response->json();

        // Log untuk debugging
        Log::info('API Response', [
            'status' => $statusCode,
            'body' => $body
        ]);

        // Jika response null, kembalikan error yang informatif
        if (is_null($body)) {
            Log::error('API returned null response', [
                'status' => $statusCode,
                'raw_body' => $response->body()
            ]);
            
            return [
                'success' => false,
                'message' => 'API backend tidak merespons dengan benar. Pastikan API backend sudah berjalan.'
            ];
        }

        // Return response body
        return $body;
    }

    /**
     * Shortcut methods untuk endpoint spesifik
     */

    // Auth
    public function login($username, $password)
    {
        return $this->post('/api/auth/login.php', [
            'username' => $username,
            'password' => $password
        ], false);
    }

    public function logout()
    {
        return $this->post('/api/auth/logout.php');
    }

    // Produk
    public function getProduk($id = null)
    {
        $endpoint = '/api/admin/produk.php';
        if ($id) {
            return $this->get($endpoint, ['id' => $id]);
        }
        return $this->get($endpoint);
    }

    public function createProduk($data)
    {
        return $this->post('/api/admin/produk.php', $data);
    }

    public function updateProduk($data)
    {
        return $this->put('/api/admin/produk.php', $data);
    }

    public function deleteProduk($id)
    {
        return $this->delete('/api/admin/produk.php', ['id' => $id]);
    }

    // Pesanan
    public function getPesanan($id = null, $filters = [])
    {
        $endpoint = '/api/admin/pesanan.php';
        if ($id) {
            return $this->get($endpoint, ['id' => $id]);
        }
        return $this->get($endpoint, $filters);
    }

    public function updatePesanan($data)
    {
        return $this->put('/api/admin/pesanan.php', $data);
    }

    public function deletePesanan($id)
    {
        return $this->delete('/api/admin/pesanan.php', ['id' => $id]);
    }

    // Kurir
    public function getKurir($id = null)
    {
        $endpoint = '/api/admin/kurir.php';
        if ($id) {
            return $this->get($endpoint, ['id' => $id]);
        }
        return $this->get($endpoint);
    }

    public function createKurir($data)
    {
        return $this->post('/api/admin/kurir.php', $data);
    }

    public function updateKurir($data)
    {
        return $this->put('/api/admin/kurir.php', $data);
    }

    public function deleteKurir($id)
    {
        return $this->delete('/api/admin/kurir.php', ['id' => $id]);
    }

    // Laporan
    public function getLaporan($type, $params = [])
    {
        return $this->get('/api/admin/laporan.php', array_merge(['type' => $type], $params));
    }
}