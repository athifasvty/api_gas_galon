<?php
/**
 * Login Endpoint
 * Method: POST
 * Endpoint: /api/auth/login.php
 * 
 * Body (JSON):
 * {
 *   "username": "admin",
 *   "password": "123456"
 * }
 */

require_once '../../config/database.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Only accept POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Method not allowed. Use POST");
}

// Get JSON input
$data = getJsonInput();

// Validate required fields
validateRequired($data, ['username', 'password']);

$username = trim($data->username);
$password = $data->password;

// Validate username format
if (strlen($username) < 3) {
    sendResponse(false, "Username minimal 3 karakter");
}

// Validate password
if (strlen($password) < 6) {
    sendResponse(false, "Password minimal 6 karakter");
}

try {
    // Check if user exists
    $stmt = $conn->prepare("
        SELECT 
            id, 
            name, 
            username, 
            password, 
            role, 
            phone, 
            address,
            created_at
        FROM users 
        WHERE username = ?
        LIMIT 1
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user found
    if (!$user) {
        sendResponse(false, "Username tidak ditemukan");
    }

    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        sendResponse(false, "Password salah");
    }

    // Generate token
    $token = generateToken($user['id'], $user['username'], $user['role']);

    // Optional: Log login activity
    // logActivity($conn, $user['id'], 'login', 'User logged in');

    // Prepare user data (remove password)
    $userData = [
        "id" => (int)$user['id'],
        "name" => $user['name'],
        "username" => $user['username'],
        "role" => $user['role'],
        "phone" => $user['phone'],
        "address" => $user['address'],
        "created_at" => $user['created_at']
    ];

    // Send success response
    sendResponse(true, "Login berhasil", [
        "token" => $token,
        "user" => $userData
    ]);

} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage());
}
?>