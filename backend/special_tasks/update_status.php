<?php
require_once '../config/db.php';
$auth = get_auth_user();
$d = json_decode(file_get_contents('php://input'), true);
$id = (int)($d['id'] ?? 0);
$status = $conn->real_escape_string($d['status'] ?? '');
if (!$id || !$status) respond(['error' => 'ID and status required'], 400);
if (!in_array($status, ['pending','in_progress','completed'])) respond(['error' => 'Invalid status'], 400);
if ($conn->query("UPDATE special_tasks SET status='$status' WHERE id=$id")) respond(['message' => 'Updated']);
else respond(['error' => 'Failed'], 500);
