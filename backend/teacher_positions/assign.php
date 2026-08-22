<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin');

$data                = json_decode(file_get_contents('php://input'), true);
$teacher_id          = (int)($data['teacher_id'] ?? 0);
$position_name       = $conn->real_escape_string($data['position_name'] ?? '');
$position_description= $conn->real_escape_string($data['position_description'] ?? '');
$assigned_by         = (int)$auth['id'];

if (!$teacher_id || !$position_name) {
    respond(['error' => 'Teacher and position name are required'], 400);
}

// Verify teacher exists
$chk = $conn->query("SELECT id FROM users WHERE id=$teacher_id AND role='teacher'");
if (!$chk || $chk->num_rows === 0) {
    respond(['error' => 'Teacher not found'], 404);
}

$sql = "INSERT INTO teacher_positions (teacher_id, position_name, position_description, assigned_by)
        VALUES ($teacher_id, '$position_name', '$position_description', $assigned_by)";

if ($conn->query($sql)) {
    respond(['message' => 'Position assigned successfully'], 201);
} else {
    respond(['error' => 'Failed: ' . $conn->error], 500);
}
