<?php
require_once '../config/db.php';

$auth = get_auth_user();
require_role($auth, 'admin');

$result = $conn->query("SELECT id, full_name, email, role, reg_number, is_active, created_at FROM users ORDER BY created_at DESC");

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
respond($users);