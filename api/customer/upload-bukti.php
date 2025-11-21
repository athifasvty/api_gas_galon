<?php
/**
 * Customer - Upload Bukti Pembayaran QRIS
 * Method: POST (multipart/form-data)
 * Endpoint: /api/customer/upload-bukti.php
 * 
 * POST - Upload bukti pembayaran
 * Form Data:
 * - bukti: File image (jpg, jpeg, png)
 * - id_pesanan: ID pesanan (opsional, untuk naming file)
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

if ($method !== 'POST') {
    sendResponse(false, "Method not allowed. Use POST");
}

// Cek apakah ada file yang diupload
if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] === UPLOAD_ERR_NO_FILE) {
    sendResponse(false, "File bukti pembayaran harus diupload");
}

$file = $_FILES['bukti'];

// Validasi error upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    sendResponse(false, "Error saat upload file. Error code: " . $file['error']);
}

// Validasi ukuran file (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    sendResponse(false, "Ukuran file terlalu besar. Maksimal 5MB");
}

// Validasi tipe file
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    sendResponse(false, "Tipe file tidak diizinkan. Hanya JPG, JPEG, dan PNG");
}

// Validasi ekstensi file
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    sendResponse(false, "Ekstensi file tidak valid. Hanya jpg, jpeg, dan png");
}

try {
    // Generate unique filename
    $id_pesanan = isset($_POST['id_pesanan']) ? $_POST['id_pesanan'] : 'temp';
    $timestamp = time();
    $filename = "bukti_qris_{$id_pesanan}_{$timestamp}.{$fileExtension}";
    
    // Tentukan folder upload
    $uploadDir = '../../uploads/bukti_pembayaran/';
    
    // Buat folder jika belum ada
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        sendResponse(false, "Gagal menyimpan file");
    }
    
    // Return success dengan nama file
    sendResponse(true, "Bukti pembayaran berhasil diupload", [
        "filename" => $filename,
        "filepath" => "uploads/bukti_pembayaran/" . $filename,
        "size" => $file['size'],
        "type" => $mimeType
    ]);
    
} catch (Exception $e) {
    sendResponse(false, "Error: " . $e->getMessage());
}
?>