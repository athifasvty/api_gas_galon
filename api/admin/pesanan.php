<?php
require_once '../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Verifikasi token
$user = verifyToken($conn);
if ($user['role'] !== 'admin') {
    sendResponse(false, "Akses ditolak");
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Lihat semua pesanan atau pesanan tertentu
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Detail pesanan tertentu dengan item (INCLUDE BUKTI!)
        $stmt = $conn->prepare("
            SELECT p.*, 
                   c.name as nama_customer, c.phone as phone_customer, c.address as alamat_customer,
                   k.name as nama_kurir, k.phone as phone_kurir,
                   pm.metode as metode_bayar, 
                   pm.status as status_bayar, 
                   pm.tanggal_bayar,
                   pm.bukti_pembayaran
            FROM pesanan p
            JOIN users c ON p.id_customer = c.id
            LEFT JOIN users k ON p.id_kurir = k.id
            LEFT JOIN pembayaran pm ON p.id = pm.id_pesanan
            WHERE p.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pesanan) {
            sendResponse(false, "Pesanan tidak ditemukan");
        }
        
        // Ambil detail items
        $stmt = $conn->prepare("
            SELECT dp.*, pr.nama_produk, pr.jenis, pr.harga as harga_satuan
            FROM detail_pesanan dp
            JOIN produk pr ON dp.id_produk = pr.id
            WHERE dp.id_pesanan = ?
        ");
        $stmt->execute([$_GET['id']]);
        $pesanan['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendResponse(true, "Detail pesanan", $pesanan);
    } else {
        // Semua pesanan dengan filter opsional (INCLUDE BUKTI!)
        $where = [];
        $params = [];
        
        if (isset($_GET['status'])) {
            $where[] = "p.status = ?";
            $params[] = $_GET['status'];
        }
        
        if (isset($_GET['tanggal_dari']) && isset($_GET['tanggal_sampai'])) {
            $where[] = "DATE(p.tanggal_pesan) BETWEEN ? AND ?";
            $params[] = $_GET['tanggal_dari'];
            $params[] = $_GET['tanggal_sampai'];
        }
        
        $where_sql = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
        
        $stmt = $conn->prepare("
            SELECT p.*, 
                   c.name as nama_customer, c.phone as phone_customer,
                   k.name as nama_kurir,
                   COUNT(dp.id) as jumlah_item
            FROM pesanan p
            JOIN users c ON p.id_customer = c.id
            LEFT JOIN users k ON p.id_kurir = k.id
            LEFT JOIN detail_pesanan dp ON p.id = dp.id_pesanan
            $where_sql
            GROUP BY p.id
            ORDER BY p.tanggal_pesan DESC
        ");
        $stmt->execute($params);
        $pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(true, "Data pesanan berhasil diambil", $pesanan);
    }
}

// PUT - Update pesanan (assign kurir, ubah status)
if ($method === 'PUT') {
    $data = getJsonInput();
    
    if (!isset($data->id)) {
        sendResponse(false, "ID pesanan harus diisi");
    }
    
    // Cek pesanan exists
    $stmt = $conn->prepare("SELECT * FROM pesanan WHERE id = ?");
    $stmt->execute([$data->id]);
    $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pesanan) {
        sendResponse(false, "Pesanan tidak ditemukan");
    }
    
    try {
        // Assign kurir
        if (isset($data->id_kurir)) {
            // Cek apakah user dengan id tersebut adalah kurir
            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$data->id_kurir]);
            $kurir = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$kurir || $kurir['role'] !== 'kurir') {
                sendResponse(false, "User bukan kurir");
            }
            
            // Update kurir dan ubah status ke diproses
            $stmt = $conn->prepare("UPDATE pesanan SET id_kurir = ?, status = 'diproses' WHERE id = ?");
            $success = $stmt->execute([$data->id_kurir, $data->id]);
            
            if ($success) {
                sendResponse(true, "Kurir berhasil di-assign");
            } else {
                sendResponse(false, "Gagal assign kurir");
            }
        }
        
        // Update status
        if (isset($data->status)) {
            // Validasi status
            $allowed_statuses = ['menunggu', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
            if (!in_array($data->status, $allowed_statuses)) {
                sendResponse(false, "Status tidak valid");
            }
            
            $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
            $success = $stmt->execute([$data->status, $data->id]);
            
            if ($success) {
                // Jika status selesai, update pembayaran
                if ($data->status === 'selesai') {
                    $stmt = $conn->prepare("UPDATE pembayaran SET status = 'sudah_bayar', tanggal_bayar = NOW() WHERE id_pesanan = ?");
                    $stmt->execute([$data->id]);
                }
                
                sendResponse(true, "Status pesanan berhasil diupdate");
            } else {
                sendResponse(false, "Gagal update status pesanan");
            }
        }
        
        sendResponse(false, "Tidak ada data yang diupdate");
        
    } catch (PDOException $e) {
        sendResponse(false, "Database error: " . $e->getMessage());
    }
}

// DELETE - Hapus pesanan (hanya untuk pesanan yang dibatalkan atau selesai)
if ($method === 'DELETE') {
    $data = getJsonInput();
    
    if (!isset($data->id)) {
        sendResponse(false, "ID pesanan harus diisi");
    }
    
    // Cek status pesanan dan ambil bukti_pengiriman + bukti_pembayaran
    $stmt = $conn->prepare("
        SELECT p.status, p.bukti_pengiriman, pm.bukti_pembayaran
        FROM pesanan p
        LEFT JOIN pembayaran pm ON p.id = pm.id_pesanan
        WHERE p.id = ?
    ");
    $stmt->execute([$data->id]);
    $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pesanan) {
        sendResponse(false, "Pesanan tidak ditemukan");
    }
    
    if (!in_array($pesanan['status'], ['dibatalkan', 'selesai'])) {
        sendResponse(false, "Hanya pesanan dengan status 'dibatalkan' atau 'selesai' yang bisa dihapus");
    }
    
    $conn->beginTransaction();
    try {
        // Hapus file bukti pengiriman jika ada
        if (!empty($pesanan['bukti_pengiriman'])) {
            $file_path = "../../uploads/bukti_pengiriman/" . $pesanan['bukti_pengiriman'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Hapus file bukti pembayaran jika ada
        if (!empty($pesanan['bukti_pembayaran'])) {
            $file_path = "../../uploads/bukti_pembayaran/" . $pesanan['bukti_pembayaran'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Hapus detail pesanan
        $stmt = $conn->prepare("DELETE FROM detail_pesanan WHERE id_pesanan = ?");
        $stmt->execute([$data->id]);
        
        // Hapus pembayaran
        $stmt = $conn->prepare("DELETE FROM pembayaran WHERE id_pesanan = ?");
        $stmt->execute([$data->id]);
        
        // Hapus pesanan
        $stmt = $conn->prepare("DELETE FROM pesanan WHERE id = ?");
        $stmt->execute([$data->id]);
        
        $conn->commit();
        sendResponse(true, "Pesanan berhasil dihapus");
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, "Gagal menghapus pesanan: " . $e->getMessage());
    }
}
?>