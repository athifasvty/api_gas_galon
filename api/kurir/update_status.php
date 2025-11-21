<?php
/**
 * Kurir - Update Status Pesanan
 * Method: PUT
 * Endpoint: /api/kurir/update_status.php
 * 
 * Body (JSON):
 * {
 *   "id_pesanan": 1,
 *   "status": "dikirim" // atau "selesai"
 * }
 * 
 * Flow status yang diizinkan:
 * - diproses → dikirim
 * - dikirim → selesai (HANYA via upload bukti!)
 * 
 * Ketika status = selesai:
 * - Update pembayaran.status = 'sudah_bayar'
 * - Update pembayaran.tanggal_bayar = NOW()
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

// Hanya terima PUT
if ($method !== 'PUT') {
    sendResponse(false, "Method not allowed. Use PUT");
}

$data = getJsonInput();

// Validasi input
validateRequired($data, ['id_pesanan', 'status']);

$id_pesanan = $data->id_pesanan;
$status_baru = $data->status;

// VALIDASI STATUS
// Status 'selesai' HANYA bisa diset via upload bukti!
if ($status_baru === 'selesai') {
    sendResponse(false, "Untuk menyelesaikan pesanan, silakan upload bukti pengiriman terlebih dahulu");
}

// Validasi status yang diizinkan (hanya 'dikirim')
if (!in_array($status_baru, ['dikirim'])) {
    sendResponse(false, "Status harus 'dikirim'");
}

$conn->beginTransaction();

try {
    // Cek pesanan dan status saat ini
    $stmt = $conn->prepare("
        SELECT id, status, id_kurir, total_harga 
        FROM pesanan 
        WHERE id = ?
    ");
    $stmt->execute([$id_pesanan]);
    $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pesanan) {
        $conn->rollBack();
        sendResponse(false, "Pesanan tidak ditemukan");
    }
    
    // Cek apakah pesanan di-assign ke kurir ini
    if ($pesanan['id_kurir'] != $user['id']) {
        $conn->rollBack();
        sendResponse(false, "Pesanan ini bukan pesanan Anda");
    }
    
    $status_sekarang = $pesanan['status'];
    
    // Validasi flow status
    $allowed_transitions = [
        'diproses' => ['dikirim'],
        'dikirim' => [] // Dari dikirim TIDAK bisa ke selesai via endpoint ini!
    ];
    
    if (!isset($allowed_transitions[$status_sekarang])) {
        $conn->rollBack();
        sendResponse(false, "Status pesanan '{$status_sekarang}' tidak bisa diubah");
    }
    
    if (!in_array($status_baru, $allowed_transitions[$status_sekarang])) {
        $conn->rollBack();
        
        // Pesan khusus kalau coba selesaikan tanpa upload bukti
        if ($status_sekarang === 'dikirim' && $status_baru === 'selesai') {
            sendResponse(false, "Untuk menyelesaikan pesanan, silakan upload bukti pengiriman terlebih dahulu");
        }
        
        $allowed = empty($allowed_transitions[$status_sekarang]) 
            ? 'tidak ada (upload bukti dulu)' 
            : implode(', ', $allowed_transitions[$status_sekarang]);
        sendResponse(false, "Status '{$status_sekarang}' hanya bisa diubah ke: {$allowed}");
    }
    
    // Update status pesanan
    $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status_baru, $id_pesanan]);
    
    if (!$success) {
        $conn->rollBack();
        sendResponse(false, "Gagal update status pesanan");
    }
    
    $conn->commit();
    
    // Log aktivitas (optional)
    // logActivity($conn, $user['id'], 'update_status_pesanan', "Pesanan #{$id_pesanan} diubah ke {$status_baru}");
    
    sendResponse(true, "Status pesanan berhasil diupdate", [
        "id_pesanan" => (int)$id_pesanan,
        "status_lama" => $status_sekarang,
        "status_baru" => $status_baru,
        "timestamp" => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    sendResponse(false, "Database error: " . $e->getMessage());
}
?>