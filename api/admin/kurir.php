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

// GET - Lihat semua kurir atau kurir tertentu
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT id, name, username, phone FROM users WHERE id = ? AND role = 'kurir'");
        $stmt->execute([$_GET['id']]);
        $kurir = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($kurir) {
            sendResponse(true, "Data kurir ditemukan", $kurir);
        } else {
            sendResponse(false, "Kurir tidak ditemukan");
        }
    } else {
        $stmt = $conn->query("SELECT id, name, username, phone FROM users WHERE role = 'kurir' ORDER BY name");
        $kurir = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(true, "Data kurir berhasil diambil", $kurir);
    }
}

// POST - Tambah kurir baru
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->name) || !isset($data->username) || !isset($data->password)) {
        sendResponse(false, "Data tidak lengkap");
    }
    
    // Cek username sudah ada atau belum
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$data->username]);
    if ($stmt->fetch()) {
        sendResponse(false, "Username sudah digunakan");
    }
    
    $stmt = $conn->prepare("INSERT INTO users (name, username, password, role, phone) VALUES (?, ?, ?, 'kurir', ?)");
    $success = $stmt->execute([
        $data->name,
        $data->username,
        $data->password,
        $data->phone ?? ''
    ]);
    
    if ($success) {
        sendResponse(true, "Kurir berhasil ditambahkan", ["id" => $conn->lastInsertId()]);
    } else {
        sendResponse(false, "Gagal menambahkan kurir");
    }
}

// PUT - Update data kurir
if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->id)) {
        sendResponse(false, "ID kurir harus diisi");
    }
    
    $updates = [];
    $params = [];
    
    if (isset($data->name)) {
        $updates[] = "name = ?";
        $params[] = $data->name;
    }
    if (isset($data->phone)) {
        $updates[] = "phone = ?";
        $params[] = $data->phone;
    }
    if (isset($data->password)) {
        $updates[] = "password = ?";
        $params[] = $data->password;
    }
    
    if (empty($updates)) {
        sendResponse(false, "Tidak ada data yang diupdate");
    }
    
    $params[] = $data->id;
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ? AND role = 'kurir'";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success) {
        sendResponse(true, "Data kurir berhasil diupdate");
    } else {
        sendResponse(false, "Gagal update data kurir");
    }
}

// DELETE - Hapus kurir
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->id)) {
        sendResponse(false, "ID kurir harus diisi");
    }
    
    // Cek apakah kurir masih memiliki pesanan aktif
    $stmt = $conn->prepare("SELECT COUNT(*) FROM pesanan WHERE id_kurir = ? AND status IN ('diproses', 'dikirim')");
    $stmt->execute([$data->id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        sendResponse(false, "Kurir masih memiliki pesanan aktif, tidak bisa dihapus");
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'kurir'");
    $success = $stmt->execute([$data->id]);
    
    if ($success) {
        sendResponse(true, "Kurir berhasil dihapus");
    } else {
        sendResponse(false, "Gagal menghapus kurir");
    }
}
?>