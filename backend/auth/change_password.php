<?php
require_once '../config/db.php';

$auth = get_auth_user();
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['current_password']) || empty($data['new_password'])) {
    respond(['error' => 'Current password and new password are required'], 400);
}

$new_password = $data['new_password'];
if (strlen($new_password) < 6) {
    respond(['error' => 'New password must be at least 6 characters'], 400);
}

$user_id = (int)$auth['id'];
$result = $conn->query("SELECT password FROM users WHERE id=$user_id");
$user = $result->fetch_assoc();

if (!password_verify($data['current_password'], $user['password'])) {
    respond(['error' => 'Current password is incorrect'], 401);
}

$hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password='$hashed_new' WHERE id=$user_id");

respond(['message' => 'Password changed successfully']);