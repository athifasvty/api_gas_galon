<?php
/**
 * Customer - Riwayat Pesanan
 * Method: GET
 * Endpoint: /api/customer/riwayat.php
 * 
 * Query Parameters (optional):
 * - status: filter by status (selesai/dibatalkan)
 * - limit: jumlah data (default: 50)
 * 
 * Menampilkan pesanan dengan status: selesai, dibatalkan
 */

require_once '../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Verifikasi token - hanya customer
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
    $where = ["p.id_customer = ?"];
    $params = [$user['id']];
    
    // Filter status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        if (!in_array($_GET['status'], ['selesai', 'dibatalkan'])) {
            sendResponse(false, "Status harus 'selesai' atau 'dibatalkan'");
        }
        $where[] = "p.status = ?";
        $params[] = $_GET['status'];
    } else {
        // Default: tampilkan selesai dan dibatalkan
        $where[] = "p.status IN ('selesai', 'dibatalkan')";
    }
    
    // Limit
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    if ($limit > 100) $limit = 100; // Max 100
    
    $where_sql = implode(" AND ", $where);
    
    // Query riwayat
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.tanggal_pesan,
            p.status,
            p.total_harga,
            k.name as nama_kurir,
            k.phone as phone_kurir,
            pm.metode as metode_bayar,
            pm.status as status_bayar,
            pm.tanggal_bayar,
            COUNT(dp.id) as jumlah_item
        FROM pesanan p
        LEFT JOIN users k ON p.id_kurir = k.id
        LEFT JOIN pembayaran pm ON p.id = pm.id_pesanan
        LEFT JOIN detail_pesanan dp ON p.id = dp.id_pesanan
        WHERE $where_sql
        GROUP BY p.id
        ORDER BY p.tanggal_pesan DESC
        LIMIT ?
    ");
    
    $params[] = $limit;
    $stmt->execute($params);
    $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data dan ambil detail items
    foreach ($riwayat as &$item) {
        $item['id'] = (int)$item['id'];
        $item['total_harga'] = (float)$item['total_harga'];
        $item['jumlah_item'] = (int)$item['jumlah_item'];
        
        // Ambil detail items
        $stmt_detail = $conn->prepare("
            SELECT 
                dp.id,
                dp.id_produk,
                dp.jumlah,
                dp.harga as harga_satuan,
                pr.nama_produk,
                pr.jenis,
                (dp.jumlah * dp.harga) as subtotal
            FROM detail_pesanan dp
            JOIN produk pr ON dp.id_produk = pr.id
            WHERE dp.id_pesanan = ?
        ");
        $stmt_detail->execute([$item['id']]);
        $item['items'] = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
        
        // Format items
        foreach ($item['items'] as &$detail) {
            $detail['id'] = (int)$detail['id'];
            $detail['id_produk'] = (int)$detail['id_produk'];
            $detail['jumlah'] = (int)$detail['jumlah'];
            $detail['harga_satuan'] = (float)$detail['harga_satuan'];
            $detail['subtotal'] = (float)$detail['subtotal'];
        }
    }
    
    // Hitung statistik
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_pesanan,
            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as total_selesai,
            SUM(CASE WHEN status = 'dibatalkan' THEN 1 ELSE 0 END) as total_dibatalkan,
            SUM(CASE WHEN status = 'selesai' THEN total_harga ELSE 0 END) as total_belanja
        FROM pesanan
        WHERE id_customer = ? AND status IN ('selesai', 'dibatalkan')
    ");
    $stmt->execute([$user['id']]);
    $statistik = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format statistik
    $statistik['total_pesanan'] = (int)$statistik['total_pesanan'];
    $statistik['total_selesai'] = (int)$statistik['total_selesai'];
    $statistik['total_dibatalkan'] = (int)$statistik['total_dibatalkan'];
    $statistik['total_belanja'] = (float)$statistik['total_belanja'];
    
    sendResponse(true, "Riwayat pesanan berhasil diambil", [
        'riwayat' => $riwayat,
        'statistik' => $statistik
    ]);
    
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage());
}
?>