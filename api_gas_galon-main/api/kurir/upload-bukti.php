<?php
/**
 * Kurir - Upload Bukti Pengiriman
 * Method: POST (multipart/form-data)
 * Endpoint: /api/kurir/upload-bukti.php
 * 
 * Body:
 * - id_pesanan: ID pesanan
 * - foto: File gambar (jpg/jpeg/png, max 5MB)
 * - catatan: Catatan opsional (misal: "Diterima oleh Ibu Siti")
 * 
 * Proses:
 * 1. Validasi pesanan milik kurir
 * 2. Validasi file foto
 * 3. Simpan foto ke folder uploads/bukti_pengiriman/
 * 4. Update database (bukti_pengiriman, waktu_upload_bukti)
 * 5. Auto update status jadi 'selesai'
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

// Hanya terima POST
if ($method !== 'POST') {
    sendResponse(false, "Method not allowed. Use POST");
}

try {
    // Validasi input
    if (!isset($_POST['id_pesanan']) || empty($_POST['id_pesanan'])) {
        sendResponse(false, "ID pesanan harus diisi");
    }
    
    $id_pesanan = (int)$_POST['id_pesanan'];
    $catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
    
    // Validasi file foto
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, "File foto harus diupload");
    }
    
    $foto = $_FILES['foto'];
    
    // Validasi ukuran file (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($foto['size'] > $max_size) {
        sendResponse(false, "Ukuran file terlalu besar. Maksimal 5MB");
    }
    
    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $foto['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        sendResponse(false, "Tipe file tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan");
    }
    
    // Cek apakah pesanan milik kurir ini dan statusnya 'dikirim'
    $stmt = $conn->prepare("
        SELECT id, status, bukti_pengiriman 
        FROM pesanan 
        WHERE id = ? AND id_kurir = ?
    ");
    $stmt->execute([$id_pesanan, $user['id']]);
    $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pesanan) {
        sendResponse(false, "Pesanan tidak ditemukan atau bukan pesanan Anda");
    }
    
    if ($pesanan['status'] !== 'dikirim') {
        sendResponse(false, "Upload bukti hanya untuk pesanan dengan status 'dikirim'");
    }
    
    // Hapus foto lama jika ada (upload ulang)
    if (!empty($pesanan['bukti_pengiriman'])) {
        $old_file = "../../uploads/bukti_pengiriman/" . $pesanan['bukti_pengiriman'];
        if (file_exists($old_file)) {
            unlink($old_file);
        }
    }
    
    // Generate nama file unik
    $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $filename = "pesanan_" . $id_pesanan . "_" . time() . "." . $extension;
    $upload_path = "../../uploads/bukti_pengiriman/" . $filename;
    
    // Pindahkan file ke folder uploads
    if (!move_uploaded_file($foto['tmp_name'], $upload_path)) {
        sendResponse(false, "Gagal mengupload file. Coba lagi");
    }
    
    // Update database
    $conn->beginTransaction();
    
    try {
        // Update pesanan: simpan nama file, waktu upload, dan ubah status jadi 'selesai'
        $stmt = $conn->prepare("
            UPDATE pesanan 
            SET bukti_pengiriman = ?, 
                waktu_upload_bukti = NOW(),
                status = 'selesai'
            WHERE id = ?
        ");
        $stmt->execute([$filename, $id_pesanan]);
        
        // Update pembayaran jadi sudah dibayar (BARU!)
        $stmt = $conn->prepare("
            UPDATE pembayaran 
            SET status = 'sudah_bayar', 
                tanggal_bayar = NOW() 
            WHERE id_pesanan = ?
        ");
        $stmt->execute([$id_pesanan]);
        
        $conn->commit();
        
        sendResponse(true, "Bukti pengiriman berhasil diupload. Pesanan selesai!", [
            'id_pesanan' => $id_pesanan,
            'bukti_pengiriman' => $filename,
            'status' => 'selesai',
            'catatan' => $catatan
        ]);
        
    } catch (PDOException $e) {
        $conn->rollBack();
        
        // Hapus file yang sudah diupload jika database gagal
        if (file_exists($upload_path)) {
            unlink($upload_path);
        }
        
        throw $e;
    }
    
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage());
} catch (Exception $e) {
    sendResponse(false, "Error: " . $e->getMessage());
}
?>