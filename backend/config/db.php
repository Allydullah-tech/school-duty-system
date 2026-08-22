<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_duty_system');
define('JWT_SECRET', 'school_system_secret_key_2024');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(array('error' => 'Database connection failed: ' . $conn->connect_error));
    exit();
}

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

function generate_token($payload) {
    $header = base64url_encode(json_encode(array('alg' => 'HS256', 'typ' => 'JWT')));
    $payload['exp'] = time() + 28800;
    $body = base64url_encode(json_encode($payload));
    $sig  = base64url_encode(hash_hmac('sha256', $header . '.' . $body, JWT_SECRET, true));
    return $header . '.' . $body . '.' . $sig;
}

function verify_token($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    $header = $parts[0];
    $body   = $parts[1];
    $sig    = $parts[2];
    $expected = base64url_encode(hash_hmac('sha256', $header . '.' . $body, JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return false;
    $payload = json_decode(base64url_decode($body), true);
    if (!$payload || $payload['exp'] < time()) return false;
    return $payload;
}

function get_auth_user() {
    $auth = '';
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization']))    $auth = $headers['Authorization'];
        elseif (isset($headers['authorization'])) $auth = $headers['authorization'];
    }
    if (!$auth && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!$auth || !preg_match('/^Bearer\s+(.+)$/i', $auth, $matches)) {
        respond(array('error' => 'Unauthorized - No valid token'), 401);
    }
    $token = $matches[1];
    $user  = verify_token($token);
    if (!$user) respond(array('error' => 'Invalid or expired token'), 401);
    return $user;
}

function require_role($user) {
    $roles = array_slice(func_get_args(), 1);
    if (!in_array($user['role'], $roles)) {
        respond(array('error' => 'Access denied - Insufficient permissions'), 403);
    }
}

function generate_random_password($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}

function generate_reg_number() {
    $year   = date('Y');
    $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    return $year . '/' . $random;
}
