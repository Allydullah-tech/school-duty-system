<?php
require_once '../config/db.php';
$auth = get_auth_user();
$d    = json_decode(file_get_contents('php://input'), true);

$id     = (int)($d['id'] ?? 0);
$status = $conn->real_escape_string($d['status'] ?? '');

if (!$id || !$status) {
    respond(['error' => 'ID and status required'], 400);
}

$allowed = array('pending', 'ongoing', 'completed');
if (!in_array($status, $allowed)) {
    respond(['error' => 'Invalid status'], 400);
}

if ($conn->query("UPDATE duties SET status='$status' WHERE id=$id")) {
    respond(['message' => 'Status updated successfully']);
} else {
    respond(['error' => 'Failed to update: ' . $conn->error], 500);
}