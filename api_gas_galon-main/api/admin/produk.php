<?php
require_once '../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Verifikasi token dan pastikan role admin
$user = verifyToken($conn);
if ($user['role'] !== 'admin') {
    sendResponse(false, "Akses ditolak. Hanya admin yang bisa mengakses");
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Lihat semua produk atau produk tertentu
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $produk = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produk) {
            sendResponse(true, "Produk ditemukan", $produk);
        } else {
            sendResponse(false, "Produk tidak ditemukan");
        }
    } else {
        $stmt = $conn->query("SELECT * FROM produk ORDER BY created_at DESC");
        $produk = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(true, "Data produk berhasil diambil", $produk);
    }
}

// POST - Tambah produk baru
if ($method === 'POST') {
    $data = getJsonInput();
    
    // Validasi required fields
    validateRequired($data, ['nama_produk', 'jenis', 'harga', 'stok']);
    
    // Validasi jenis
    if (!in_array($data->jenis, ['elpiji', 'galon'])) {
        sendResponse(false, "Jenis produk harus 'elpiji' atau 'galon'");
    }
    
    // Validasi harga dan stok
    if ($data->harga < 0) {
        sendResponse(false, "Harga tidak boleh negatif");
    }
    
    if ($data->stok < 0) {
        sendResponse(false, "Stok tidak boleh negatif");
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO produk (nama_produk, jenis, harga, stok) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([
            $data->nama_produk,
            $data->jenis,
            $data->harga,
            $data->stok
        ]);
        
        if ($success) {
            sendResponse(true, "Produk berhasil ditambahkan", ["id" => $conn->lastInsertId()]);
        } else {
            sendResponse(false, "Produk gagal ditambahkan");
        }
    } catch (PDOException $e) {
        sendResponse(false, "Database error: " . $e->getMessage());
    }
}

// PUT - Update produk
if ($method === 'PUT') {
    $data = getJsonInput();
    
    if (!isset($data->id)) {
        sendResponse(false, "ID produk harus diisi");
    }
    
    // Cek apakah produk exists
    $stmt = $conn->prepare("SELECT id FROM produk WHERE id = ?");
    $stmt->execute([$data->id]);
    if (!$stmt->fetch()) {
        sendResponse(false, "Produk tidak ditemukan");
    }
    
    $updates = [];
    $params = [];
    
    if (isset($data->nama_produk)) {
        $updates[] = "nama_produk = ?";
        $params[] = $data->nama_produk;
    }
    if (isset($data->jenis)) {
        if (!in_array($data->jenis, ['elpiji', 'galon'])) {
            sendResponse(false, "Jenis produk harus 'elpiji' atau 'galon'");
        }
        $updates[] = "jenis = ?";
        $params[] = $data->jenis;
    }
    if (isset($data->harga)) {
        if ($data->harga < 0) {
            sendResponse(false, "Harga tidak boleh negatif");
        }
        $updates[] = "harga = ?";
        $params[] = $data->harga;
    }
    if (isset($data->stok)) {
        if ($data->stok < 0) {
            sendResponse(false, "Stok tidak boleh negatif");
        }
        $updates[] = "stok = ?";
        $params[] = $data->stok;
    }
    
    if (empty($updates)) {
        sendResponse(false, "Tidak ada data yang diupdate");
    }
    
    $params[] = $data->id;
    $sql = "UPDATE produk SET " . implode(", ", $updates) . " WHERE id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        
        if ($success) {
            sendResponse(true, "Produk berhasil diupdate");
        } else {
            sendResponse(false, "Produk gagal diupdate");
        }
    } catch (PDOException $e) {
        sendResponse(false, "Database error: " . $e->getMessage());
    }
}

// DELETE - Hapus produk
if ($method === 'DELETE') {
    $data = getJsonInput();
    
    if (!isset($data->id)) {
        sendResponse(false, "ID produk harus diisi");
    }
    
    // Cek apakah produk masih ada di pesanan
    $stmt = $conn->prepare("SELECT COUNT(*) FROM detail_pesanan WHERE id_produk = ?");
    $stmt->execute([$data->id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        sendResponse(false, "Produk tidak dapat dihapus karena masih ada dalam pesanan");
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
        $success = $stmt->execute([$data->id]);
        
        if ($success) {
            sendResponse(true, "Produk berhasil dihapus");
        } else {
            sendResponse(false, "Produk gagal dihapus");
        }
    } catch (PDOException $e) {
        sendResponse(false, "Database error: " . $e->getMessage());
    }
}
?>