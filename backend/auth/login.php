<?php
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['username']) || empty($data['password'])) {
    respond(['error' => 'Username/Registration and password required'], 400);
}

$username = $conn->real_escape_string($data['username']);
$password = $data['password'];
$login_type = $data['login_type'] ?? 'staff'; // admin, staff, student

// Check if login is by email or registration number
$result = $conn->query("SELECT * FROM users WHERE email='$username' OR reg_number='$username'");

if ($result->num_rows === 0) {
    respond(['error' => 'Invalid credentials'], 401);
}

$user = $result->fetch_assoc();

if ($user['is_active'] != 1) {
    respond(['error' => 'Account disabled. Please contact administrator'], 401);
}

if (!password_verify($password, $user['password'])) {
    respond(['error' => 'Invalid credentials'], 401);
}

// Role-based access control based on login type
if ($login_type === 'admin' && $user['role'] !== 'admin') {
    respond(['error' => 'Access denied. Admin privileges required.'], 403);
}

if ($login_type === 'staff' && !in_array($user['role'], ['academician', 'teacher'])) {
    respond(['error' => 'Access denied. Staff privileges required.'], 403);
}

if ($login_type === 'student' && $user['role'] !== 'student') {
    respond(['error' => 'Access denied. Student privileges required.'], 403);
}

$payload = [
    'id' => $user['id'],
    'full_name' => $user['full_name'],
    'role' => $user['role'],
    'email' => $user['email'],
    'reg_number' => $user['reg_number']
];
$token = generate_token($payload);

respond([
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'full_name' => $user['full_name'],
        'role' => $user['role'],
        'email' => $user['email'],
        'reg_number' => $user['reg_number']
    ]
]);