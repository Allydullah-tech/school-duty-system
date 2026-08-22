<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin');

$data = json_decode(file_get_contents('php://input'), true);
$user_id = (int)($data['user_id'] ?? 0);
if (!$user_id) respond(['error' => 'User ID required'], 400);

$result = $conn->query("SELECT full_name, role FROM users WHERE id=$user_id");
if ($result->num_rows === 0) respond(['error' => 'User not found'], 404);
$u = $result->fetch_assoc();
if ($u['role'] === 'admin') respond(['error' => 'Cannot reset admin password here'], 403);

$new_password = generate_random_password(10);
$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password='$hashed' WHERE id=$user_id");

respond(['user_name' => $u['full_name'], 'new_password' => $new_password]);
