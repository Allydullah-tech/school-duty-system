<?php
require_once '../config/db.php';

$auth = get_auth_user();
require_role($auth, 'admin');

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

if (!$id) respond(['error' => 'User ID required'], 400);
if ($id === (int)$auth['id']) respond(['error' => 'Cannot modify yourself'], 400);

$result = $conn->query("SELECT is_active, role FROM users WHERE id=$id");
if ($result->num_rows === 0) respond(['error' => 'User not found'], 404);

$row = $result->fetch_assoc();
if ($row['role'] === 'admin') respond(['error' => 'Cannot modify admin users'], 400);

$new = $row['is_active'] ? 0 : 1;
if ($conn->query("UPDATE users SET is_active=$new WHERE id=$id")) {
    respond(['message' => $new ? 'User activated' : 'User deactivated']);
} else {
    respond(['error' => 'Failed to update user status'], 500);
}