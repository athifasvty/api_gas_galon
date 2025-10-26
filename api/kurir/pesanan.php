<?php
/**
 * Kurir - Lihat Pesanan yang Di-assign
 * Method: GET
 * Endpoint: /api/kurir/pesanan.php
 * 
 * Query Parameters (optional):
 * - status: filter by status (diproses/dikirim)
 * - id: detail pesanan tertentu
 * 
 * Menampilkan pesanan yang di-assign ke kurir login
 * Status: diproses, dikirim (exclude: menunggu, selesai, dibatalkan)
 */

require_once '../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Verifikasi token - hanya kurir
$user = verifyToken($conn);
if ($user['role'] !== 'kurir') {
    sendResponse(false, "Akses ditolak. Hanya kurir yang bisa mengakses");
}

$method = $_SERVER['REQUEST_METHOD'];

// Hanya terima GET
if ($method !== 'GET') {
    sendResponse(false, "Method not allowed. Use GET");
}

try {
    // Detail pesanan tertentu
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("
            SELECT 
                p.id,
                p.tanggal_pesan,
                p.status,
                p.total_harga,
                c.name as nama_customer,
                c.phone as phone_customer,
                c.address as alamat_customer,
                pm.metode as metode_bayar,
                pm.status as status_bayar
            FROM pesanan p
            JOIN users c ON p.id_customer = c.id
            LEFT JOIN pembayaran pm ON p.id = pm.id_pesanan
            WHERE p.id = ? AND p.id_kurir = ?
        ");
        $stmt->execute([$_GET['id'], $user['id']]);
        $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pesanan) {
            sendResponse(false, "Pesanan tidak ditemukan atau bukan pesanan Anda");
        }
        
        // Format data
        $pesanan['id'] = (int)$pesanan['id'];
        $pesanan['total_harga'] = (float)$pesanan['total_harga'];
        
        // Ambil detail items
        $stmt = $conn->prepare("
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
        $stmt->execute([$_GET['id']]);
        $pesanan['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format items
        foreach ($pesanan['items'] as &$item) {
            $item['id'] = (int)$item['id'];
            $item['id_produk'] = (int)$item['id_produk'];
            $item['jumlah'] = (int)$item['jumlah'];
            $item['harga_satuan'] = (float)$item['harga_satuan'];
            $item['subtotal'] = (float)$item['subtotal'];
        }
        
        sendResponse(true, "Detail pesanan", $pesanan);
    }
    
    // List semua pesanan yang di-assign ke kurir ini
    $where = ["p.id_kurir = ?"];
    $params = [$user['id']];
    
    // Filter status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        if (!in_array($_GET['status'], ['diproses', 'dikirim'])) {
            sendResponse(false, "Status harus 'diproses' atau 'dikirim'");
        }
        $where[] = "p.status = ?";
        $params[] = $_GET['status'];
    } else {
        // Default: tampilkan diproses dan dikirim saja
        $where[] = "p.status IN ('diproses', 'dikirim')";
    }
    
    $where_sql = implode(" AND ", $where);
    
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.tanggal_pesan,
            p.status,
            p.total_harga,
            c.name as nama_customer,
            c.phone as phone_customer,
            c.address as alamat_customer,
            pm.metode as metode_bayar,
            pm.status as status_bayar,
            COUNT(dp.id) as jumlah_item
        FROM pesanan p
        JOIN users c ON p.id_customer = c.id
        LEFT JOIN pembayaran pm ON p.id = pm.id_pesanan
        LEFT JOIN detail_pesanan dp ON p.id = dp.id_pesanan
        WHERE $where_sql
        GROUP BY p.id
        ORDER BY p.tanggal_pesan ASC
    ");
    $stmt->execute($params);
    $pesanan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data
    foreach ($pesanan_list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['total_harga'] = (float)$item['total_harga'];
        $item['jumlah_item'] = (int)$item['jumlah_item'];
    }
    
    // Statistik kurir
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_pesanan,
            SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as total_diproses,
            SUM(CASE WHEN status = 'dikirim' THEN 1 ELSE 0 END) as total_dikirim,
            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as total_selesai_hari_ini
        FROM pesanan
        WHERE id_kurir = ? 
        AND (
            status IN ('diproses', 'dikirim') 
            OR (status = 'selesai' AND DATE(tanggal_pesan) = CURDATE())
        )
    ");
    $stmt->execute([$user['id']]);
    $statistik = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format statistik
    foreach ($statistik as $key => &$value) {
        $value = (int)$value;
    }
    
    sendResponse(true, "Pesanan berhasil diambil", [
        'pesanan' => $pesanan_list,
        'statistik' => $statistik
    ]);
    
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage());
}
?>