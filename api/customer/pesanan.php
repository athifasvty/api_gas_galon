<?php
/**
 * Customer - Buat Pesanan & Lihat Pesanan Aktif
 * Method: GET, POST
 * Endpoint: /api/customer/pesanan.php
 * 
 * POST - Buat pesanan baru
 * Body (JSON):
 * {
 *   "items": [
 *     {"id_produk": 1, "jumlah": 2},
 *     {"id_produk": 3, "jumlah": 1}
 *   ],
 *   "metode_bayar": "cash" // atau "transfer"
 * }
 * 
 * GET - Lihat pesanan aktif (status: menunggu, diproses, dikirim)
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

// GET - Lihat pesanan aktif customer
if ($method === 'GET') {
    try {
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
                COUNT(dp.id) as jumlah_item
            FROM pesanan p
            LEFT JOIN users k ON p.id_kurir = k.id
            LEFT JOIN pembayaran pm ON p.id = pm.id_pesanan
            LEFT JOIN detail_pesanan dp ON p.id = dp.id_pesanan
            WHERE p.id_customer = ? 
            AND p.status IN ('menunggu', 'diproses', 'dikirim')
            GROUP BY p.id
            ORDER BY p.tanggal_pesan DESC
        ");
        $stmt->execute([$user['id']]);
        $pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format data
        foreach ($pesanan as &$item) {
            $item['id'] = (int)$item['id'];
            $item['total_harga'] = (float)$item['total_harga'];
            $item['jumlah_item'] = (int)$item['jumlah_item'];
            
            // Ambil detail items untuk setiap pesanan
            $stmt_detail = $conn->prepare("
                SELECT 
                    dp.id,
                    dp.id_produk,
                    dp.jumlah,
                    dp.subtotal,
                    pr.nama_produk,
                    pr.jenis,
                    pr.harga as harga_satuan,
                    dp.subtotal as subtotal
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
        
        sendResponse(true, "Pesanan aktif berhasil diambil", $pesanan);
        
    } catch (PDOException $e) {
        sendResponse(false, "Database error: " . $e->getMessage());
    }
}

// POST - Buat pesanan baru
if ($method === 'POST') {
    $data = getJsonInput();
    
    // Validasi input
    if (!isset($data->items) || !is_array($data->items) || empty($data->items)) {
        sendResponse(false, "Items pesanan harus diisi");
    }
    
    if (!isset($data->metode_bayar) || !in_array($data->metode_bayar, ['cash', 'transfer'])) {
        sendResponse(false, "Metode bayar harus 'cash' atau 'transfer'");
    }
    
    $conn->beginTransaction();
    
    try {
        $total_harga = 0;
        $items_detail = [];
        
        // Validasi setiap item dan hitung total
        foreach ($data->items as $item) {
            if (!isset($item->id_produk) || !isset($item->jumlah)) {
                $conn->rollBack();
                sendResponse(false, "Setiap item harus memiliki id_produk dan jumlah");
            }
            
            if ($item->jumlah <= 0) {
                $conn->rollBack();
                sendResponse(false, "Jumlah produk harus lebih dari 0");
            }
            
            // Cek produk dan stok
            $stmt = $conn->prepare("SELECT id, nama_produk, harga, stok FROM produk WHERE id = ?");
            $stmt->execute([$item->id_produk]);
            $produk = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$produk) {
                $conn->rollBack();
                sendResponse(false, "Produk dengan ID {$item->id_produk} tidak ditemukan");
            }
            
            if ($produk['stok'] < $item->jumlah) {
                $conn->rollBack();
                sendResponse(false, "Stok {$produk['nama_produk']} tidak mencukupi. Stok tersedia: {$produk['stok']}");
            }
            
            $subtotal = $produk['harga'] * $item->jumlah;
            $total_harga += $subtotal;
            
            $items_detail[] = [
                'id_produk' => $item->id_produk,
                'jumlah' => $item->jumlah,
                'harga' => $produk['harga']
            ];
        }
        
        // Insert pesanan
        $stmt = $conn->prepare("
            INSERT INTO pesanan (id_customer, tanggal_pesan, status, total_harga) 
            VALUES (?, NOW(), 'menunggu', ?)
        ");
        $stmt->execute([$user['id'], $total_harga]);
        $id_pesanan = $conn->lastInsertId();
        
        // Insert detail pesanan dan kurangi stok
        foreach ($items_detail as $detail) {
    // Insert detail
    $stmt = $conn->prepare("
        INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, subtotal) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $id_pesanan,
        $detail['id_produk'],
        $detail['jumlah'],
        $detail['harga'] * $detail['jumlah'] // Simpan subtotal, bukan harga satuan
    ]);
    
    // Kurangi stok
    $stmt = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
    $stmt->execute([$detail['jumlah'], $detail['id_produk']]);
}
        
        // Insert pembayaran
        $stmt = $conn->prepare("
            INSERT INTO pembayaran (id_pesanan, metode, status) 
            VALUES (?, ?, 'belum_bayar')
        ");
        $stmt->execute([$id_pesanan, $data->metode_bayar]);
        
        $conn->commit();
        
        sendResponse(true, "Pesanan berhasil dibuat", [
            "id_pesanan" => (int)$id_pesanan,
            "total_harga" => (float)$total_harga,
            "status" => "menunggu",
            "metode_bayar" => $data->metode_bayar
        ]);
        
    } catch (PDOException $e) {
        $conn->rollBack();
        sendResponse(false, "Database error: " . $e->getMessage());
    }
}

// Method tidak diizinkan
sendResponse(false, "Method not allowed. Use GET or POST");
?>