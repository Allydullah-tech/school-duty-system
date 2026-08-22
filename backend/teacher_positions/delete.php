<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin');
$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
if (!$id) respond(['error' => 'ID required'], 400);
if ($conn->query("DELETE FROM teacher_positions WHERE id=$id")) {
    respond(['message' => 'Position removed']);
} else {
    respond(['error' => 'Failed: ' . $conn->error], 500);
}
