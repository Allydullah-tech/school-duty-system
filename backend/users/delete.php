<?php
require_once '../config/db.php';

$auth = get_auth_user();
require_role($auth, 'admin');

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

if (!$id) respond(['error' => 'User ID required'], 400);
if ($id === (int)$auth['id']) respond(['error' => 'Cannot delete yourself'], 400);

$check = $conn->query("SELECT role FROM users WHERE id=$id");
$userToDelete = $check->fetch_assoc();
if ($userToDelete && $userToDelete['role'] === 'admin') {
    respond(['error' => 'Cannot delete admin users'], 400);
}

if ($conn->query("DELETE FROM users WHERE id=$id")) {
    respond(['message' => 'User deleted permanently']);
} else {
    respond(['error' => 'Failed to delete user'], 500);
}