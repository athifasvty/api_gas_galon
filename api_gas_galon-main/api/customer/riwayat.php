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
    
    // Limit - LANGSUNG DIMASUKKAN KE QUERY (bukan parameter)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    if ($limit > 100) $limit = 100; // Max 100
    if ($limit < 1) $limit = 50; // Min 1
    
    $where_sql = implode(" AND ", $where);
    
    // Query riwayat - LIMIT langsung di query string, TAMBAH FIELD ONGKIR
    $query = "
        SELECT 
            p.id,
            p.tanggal_pesan,
            p.status,
            p.ongkir,
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
        LIMIT $limit
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params); // JANGAN tambahkan $limit di sini
    $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data dan ambil detail items
    foreach ($riwayat as &$item) {
        $item['id'] = (int)$item['id'];
        $item['ongkir'] = (float)$item['ongkir'];
        $item['total_harga'] = (float)$item['total_harga'];
        $item['jumlah_item'] = (int)$item['jumlah_item'];
        
        // Hitung subtotal (total_harga - ongkir)
        $item['subtotal'] = $item['total_harga'] - $item['ongkir'];
        
        // Ambil detail items
        $stmt_detail = $conn->prepare("
            SELECT 
                dp.id,
                dp.id_produk,
                dp.jumlah,
                dp.subtotal,
                pr.nama_produk,
                pr.jenis,
                pr.harga as harga_satuan
            FROM detail_pesanan dp
            JOIN produk pr ON dp.id_produk = pr.id
            WHERE dp.id_pesanan = ?
        ");
        $stmt_detail->execute([$item['id']]);
        $items = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
        
        // Format items
        $item['items'] = [];
        foreach ($items as $detail) {
            $item['items'][] = [
                'id' => (int)$detail['id'],
                'id_produk' => (int)$detail['id_produk'],
                'jumlah' => (int)$detail['jumlah'],
                'nama_produk' => $detail['nama_produk'],
                'jenis' => $detail['jenis'],
                'harga_satuan' => (float)$detail['harga_satuan'],
                'subtotal' => (float)$detail['subtotal']
            ];
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