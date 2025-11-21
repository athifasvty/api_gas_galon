<?php
// Enable error reporting untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database Configuration Class
class Database {
    private $host = "localhost";
    private $db_name = "db_gas_galon";
    private $username = "root";
    private $password = "";
    public $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Database connection error: " . $e->getMessage()
            ]);
            exit();
        }
        
        return $this->conn;
    }
}

// ==================== HELPER FUNCTIONS ====================

/**
 * Send JSON response and exit
 */
function sendResponse($success, $message, $data = null) {
    $response = [
        "success" => $success,
        "message" => $message
    ];
    
    if ($data !== null) {
        $response["data"] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}

/**
 * Get JSON Input from Request Body
 * 👇 FUNCTION BARU INI YANG DITAMBAHKAN!
 */
function getJsonInput() {
    // Try to get raw input
    $input = @file_get_contents('php://input');
    
    // If empty, try alternative methods
    if (empty($input)) {
        // Try $_POST first
        if (!empty($_POST)) {
            return (object)$_POST;
        }
        
        // Try $_REQUEST
        if (!empty($_REQUEST)) {
            return (object)$_REQUEST;
        }
        
        // If all failed
        sendResponse(false, 'No input data received');
    }
    
    // Decode JSON
    $data = json_decode($input);
    
    // Check for JSON errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(false, 'Invalid JSON format: ' . json_last_error_msg());
    }
    
    // Check if data is null or empty
    if ($data === null) {
        sendResponse(false, 'Empty request body');
    }
    
    return $data;
}

/**
 * Get authorization token from request header
 */
function getAuthToken() {
    $headers = null;
    
    // Try apache_request_headers first
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    } else {
        // Fallback for nginx
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
    }
    
    // Check Authorization header
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        return $token;
    }
    
    // Check authorization (lowercase)
    if (isset($headers['authorization'])) {
        $token = str_replace('Bearer ', '', $headers['authorization']);
        return $token;
    }
    
    return null;
}

/**
 * Verify token and return user data
 */
function verifyToken($conn) {
    $token = getAuthToken();
    
    if (!$token) {
        sendResponse(false, "Token tidak ditemukan. Silakan login terlebih dahulu");
    }

    // Decode token
    // Format token: base64(user_id:username:role:timestamp)
    $decoded = base64_decode($token);
    $parts = explode(':', $decoded);
    
    if (count($parts) !== 4) {
        sendResponse(false, "Token tidak valid");
    }

    $user_id = $parts[0];
    $username = $parts[1];
    $role = $parts[2];
    $timestamp = $parts[3];
    
    // Optional: Check token expiry (24 hours)
    $current_time = time();
    $token_age = $current_time - $timestamp;
    
    // Token expired after 24 hours (86400 seconds)
    if ($token_age > 86400) {
        sendResponse(false, "Token sudah kadaluarsa. Silakan login kembali");
    }
    
    // Verify user exists in database
    try {
        $stmt = $conn->prepare("SELECT id, username, role, name, phone, address FROM users WHERE id = ? AND username = ? AND role = ?");
        $stmt->execute([$user_id, $username, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            sendResponse(false, "User tidak ditemukan atau token tidak valid");
        }

        return $user;
    } catch (PDOException $e) {
        sendResponse(false, "Error verifying token: " . $e->getMessage());
    }
}

/**
 * Generate authentication token
 */
function generateToken($user_id, $username, $role) {
    $timestamp = time();
    $token_data = "$user_id:$username:$role:$timestamp";
    return base64_encode($token_data);
}

/**
 * Validate required fields
 */
function validateRequired($data, $required_fields) {
    $missing = [];
    
    foreach ($required_fields as $field) {
        if (!isset($data->$field) || empty($data->$field)) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendResponse(false, "Field berikut harus diisi: " . implode(', ', $missing));
    }
    
    return true;
}

/**
 * Hash password (simple, gunakan password_hash di production)
 */
function hashPassword($password) {
    // Untuk development gunakan plain text
    // Untuk production gunakan: return password_hash($password, PASSWORD_DEFAULT);
    return $password;
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    // Untuk development gunakan plain comparison
    // Untuk production gunakan: return password_verify($password, $hash);
    return $password === $hash;
}

/**
 * Get input data - MULTI METHOD SUPPORT
 * 👇 FUNCTION INI SEKARANG MEMANGGIL getJsonInput()
 */
function getInputData() {
    return getJsonInput();
}

/**
 * Log activity (optional, untuk audit trail)
 */
function logActivity($conn, $user_id, $action, $description = '') {
    try {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $action, $description]);
    } catch (PDOException $e) {
        // Silent fail, logging shouldn't break the app
    }
}
?>