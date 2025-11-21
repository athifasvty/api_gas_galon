<?php
/**
 * Logout Endpoint
 * Method: POST
 * Endpoint: /api/auth/logout.php
 * 
 * Headers:
 * Authorization: Bearer {token}
 * 
 * Note: Karena kita menggunakan stateless token (tidak disimpan di database),
 * logout dilakukan di sisi client dengan menghapus token.
 * Endpoint ini hanya untuk logging purposes dan response ke client.
 */

require_once '../../config/database.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Only accept POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Method not allowed. Use POST");
}

try {
    // Verify token and get user data
    $user = verifyToken($conn);

    // Optional: Log logout activity
    // logActivity($conn, $user['id'], 'logout', 'User logged out');

    // Optional: Jika ingin implement blacklist token, tambahkan token ke table blacklist
    /*
    $token = getAuthToken();
    $stmt = $conn->prepare("INSERT INTO token_blacklist (token, user_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$token, $user['id']]);
    */

    // Send success response
    sendResponse(true, "Logout berhasil", [
        "message" => "Token telah dihapus. Silakan login kembali untuk mengakses aplikasi"
    ]);

} catch (Exception $e) {
    sendResponse(false, "Logout gagal: " . $e->getMessage());
}
?>