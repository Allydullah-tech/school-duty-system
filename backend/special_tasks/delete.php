<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');
$d = json_decode(file_get_contents('php://input'), true);
$id = (int)($d['id'] ?? 0);
if (!$id) respond(['error' => 'ID required'], 400);
if ($conn->query("DELETE FROM special_tasks WHERE id=$id")) respond(['message' => 'Task deleted']);
else respond(['error' => 'Failed'], 500);
