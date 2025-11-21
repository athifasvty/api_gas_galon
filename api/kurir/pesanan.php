<?php
/**
 * Kurir - Lihat Pesanan yang Di-assign
 * Method: GET
 * Endpoint: /api/kurir/pesanan.php
 * 
 * Query Parameters (optional):
 * - status: filter by status (diproses/dikirim/selesai)
 * - id: detail pesanan tertentu
 * 
 * Menampilkan pesanan yang di-assign ke kurir login
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
                p.ongkir,
                p.total_harga,
                p.bukti_pengiriman,
                p.waktu_upload_bukti,
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
        $pesanan['ongkir'] = (float)$pesanan['ongkir'];
        $pesanan['total_harga'] = (float)$pesanan['total_harga'];
        $pesanan['subtotal'] = $pesanan['total_harga'] - $pesanan['ongkir'];
        
        // Format bukti pengiriman (bisa null)
        $pesanan['bukti_pengiriman'] = $pesanan['bukti_pengiriman'] ?: null;
        $pesanan['waktu_upload_bukti'] = $pesanan['waktu_upload_bukti'] ?: null;
        
        // Generate full URL untuk foto jika ada
        if ($pesanan['bukti_pengiriman']) {
            // Gunakan IP yang sama dengan API
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $base_path = str_replace('/api/kurir/pesanan.php', '', $_SERVER['PHP_SELF']);
            $pesanan['bukti_url'] = $protocol . '://' . $host . $base_path . '/uploads/bukti_pengiriman/' . $pesanan['bukti_pengiriman'];
        } else {
            $pesanan['bukti_url'] = null;
        }
        
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
        $stmt_detail->execute([$_GET['id']]);
        $items = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
        
        // Format items
        $pesanan['items'] = [];
        foreach ($items as $detail) {
            $pesanan['items'][] = [
                'id' => (int)$detail['id'],
                'id_produk' => (int)$detail['id_produk'],
                'jumlah' => (int)$detail['jumlah'],
                'nama_produk' => $detail['nama_produk'],
                'jenis' => $detail['jenis'],
                'harga_satuan' => (float)$detail['harga_satuan'],
                'subtotal' => (float)$detail['subtotal']
            ];
        }
        
        sendResponse(true, "Detail pesanan", $pesanan);
    }
    
    // List semua pesanan yang di-assign ke kurir ini
    $where = ["p.id_kurir = ?"];
    $params = [$user['id']];
    
    // Filter status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        if (!in_array($_GET['status'], ['diproses', 'dikirim', 'selesai'])) {
            sendResponse(false, "Status harus 'diproses', 'dikirim', atau 'selesai'");
        }
        $where[] = "p.status = ?";
        $params[] = $_GET['status'];
    } else {
        // Default: tampilkan diproses dan dikirim saja
        $where[] = "p.status IN ('diproses', 'dikirim')";
    }
    
    $where_sql = implode(" AND ", $where);
    
    $query = "
        SELECT 
            p.id,
            p.tanggal_pesan,
            p.status,
            p.ongkir,
            p.total_harga,
            p.bukti_pengiriman,
            p.waktu_upload_bukti,
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
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $pesanan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data
    foreach ($pesanan_list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['ongkir'] = (float)$item['ongkir'];
        $item['total_harga'] = (float)$item['total_harga'];
        $item['subtotal'] = $item['total_harga'] - $item['ongkir'];
        $item['jumlah_item'] = (int)$item['jumlah_item'];
        
        // Format bukti pengiriman
        $item['bukti_pengiriman'] = $item['bukti_pengiriman'] ?: null;
        $item['waktu_upload_bukti'] = $item['waktu_upload_bukti'] ?: null;
        
        // Generate full URL untuk foto jika ada
        if ($item['bukti_pengiriman']) {
            $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF']));
            $item['bukti_url'] = $base_url . '/uploads/bukti_pengiriman/' . $item['bukti_pengiriman'];
        } else {
            $item['bukti_url'] = null;
        }
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