<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'teacher', 'admin');
$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
if (!$id) respond(['error' => 'ID required'], 400);
$teacher_id = (int)$auth['id'];
// verify ownership unless admin
if ($auth['role'] !== 'admin') {
    $chk = $conn->query("SELECT id FROM student_tasks WHERE id=$id AND assigned_by=$teacher_id");
    if (!$chk || $chk->num_rows === 0) respond(['error' => 'Task not found or not yours'], 403);
}
if ($conn->query("DELETE FROM student_tasks WHERE id=$id")) respond(['message' => 'Task deleted']);
else respond(['error' => 'Failed: ' . $conn->error], 500);
