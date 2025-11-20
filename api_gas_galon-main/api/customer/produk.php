<?php
/**
 * Customer - Lihat Katalog Produk
 * Method: GET
 * Endpoint: /api/customer/produk.php
 * 
 * Query Parameters (optional):
 * - jenis: filter by jenis (elpiji/galon)
 * - stok_tersedia: true/false (default: true - hanya tampilkan stok > 0)
 */

require_once '../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Verifikasi token - hanya customer yang bisa akses
$user = verifyToken($conn);
if ($user['role'] !== 'customer') {
    sendResponse(false, "Akses ditolak. Hanya customer yang bisa mengakses");
}

$method = $_SERVER['REQUEST_METHOD'];

// Hanya terima GET
if ($method !== 'GET') {
    sendResponse(false, "Method not allowed. Use GET");
}

try {
    // Build query dengan filter
    $where = [];
    $params = [];
    
    // Filter jenis (elpiji/galon)
    if (isset($_GET['jenis']) && !empty($_GET['jenis'])) {
        if (!in_array($_GET['jenis'], ['elpiji', 'galon'])) {
            sendResponse(false, "Jenis harus 'elpiji' atau 'galon'");
        }
        $where[] = "jenis = ?";
        $params[] = $_GET['jenis'];
    }
    
    // Filter stok tersedia (default: true)
    $stok_tersedia = isset($_GET['stok_tersedia']) ? $_GET['stok_tersedia'] === 'true' : true;
    if ($stok_tersedia) {
        $where[] = "stok > 0";
    }
    
    $where_sql = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
    
    // Query produk
    $stmt = $conn->prepare("
        SELECT 
            id,
            nama_produk,
            jenis,
            harga,
            stok,
            CASE 
                WHEN stok = 0 THEN 'Habis'
                WHEN stok < 10 THEN 'Stok Terbatas'
                ELSE 'Tersedia'
            END as status_stok
        FROM produk 
        $where_sql
        ORDER BY jenis, nama_produk
    ");
    $stmt->execute($params);
    $produk = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format harga untuk response
    foreach ($produk as &$item) {
        $item['id'] = (int)$item['id'];
        $item['harga'] = (float)$item['harga'];
        $item['stok'] = (int)$item['stok'];
    }
    
    sendResponse(true, "Katalog produk berhasil diambil", $produk);
    
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage());
}
?>