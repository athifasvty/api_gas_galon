<?php
/**
 * Register Endpoint - FINAL VERSION
 * Method: POST
 * Endpoint: /api/auth/register.php
 */

require_once '../../config/database.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Only accept POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Method not allowed. Use POST");
}

// Get input data (support JSON dan form-data)
$data = getInputData();

// Validasi required fields
if (!isset($data->name) || empty($data->name)) {
    sendResponse(false, "Nama harus diisi");
}

if (!isset($data->username) || empty($data->username)) {
    sendResponse(false, "Username harus diisi");
}

if (!isset($data->password) || empty($data->password)) {
    sendResponse(false, "Password harus diisi");
}

$name = trim($data->name);
$username = trim($data->username);
$password = $data->password;
$phone = isset($data->phone) ? trim($data->phone) : '';
$address = isset($data->address) ? trim($data->address) : '';

// Validate name
if (strlen($name) < 3) {
    sendResponse(false, "Nama minimal 3 karakter");
}

// Validate username
if (strlen($username) < 3) {
    sendResponse(false, "Username minimal 3 karakter");
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    sendResponse(false, "Username hanya boleh mengandung huruf, angka, dan underscore");
}

// Validate password
if (strlen($password) < 6) {
    sendResponse(false, "Password minimal 6 karakter");
}

// Validate phone (optional)
if (!empty($phone)) {
    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        sendResponse(false, "Format nomor telepon tidak valid (10-15 digit)");
    }
}

try {
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        sendResponse(false, "Username sudah digunakan. Silakan pilih username lain");
    }

    // Check if phone already exists (if provided)
    if (!empty($phone)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
        $stmt->execute([$phone]);
        
        if ($stmt->fetch()) {
            sendResponse(false, "Nomor telepon sudah terdaftar");
        }
    }

    // Hash password
    $hashed_password = hashPassword($password);

    // Insert new user (default role: customer)
    $stmt = $conn->prepare("
        INSERT INTO users (name, username, password, role, phone, address, created_at) 
        VALUES (?, ?, ?, 'customer', ?, ?, NOW())
    ");
    
    $success = $stmt->execute([
        $name,
        $username,
        $hashed_password,
        $phone,
        $address
    ]);

    if (!$success) {
        sendResponse(false, "Registrasi gagal. Silakan coba lagi");
    }

    // Get the new user ID
    $user_id = $conn->lastInsertId();

    // Generate token
    $token = generateToken($user_id, $username, 'customer');

    // Prepare user data
    $userData = [
        "id" => (int)$user_id,
        "name" => $name,
        "username" => $username,
        "role" => "customer",
        "phone" => $phone,
        "address" => $address
    ];

    // Send success response
    sendResponse(true, "Registrasi berhasil. Selamat datang!", [
        "token" => $token,
        "user" => $userData
    ]);

} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage());
}
?>
