<?php
require_once '../config/db.php';
$auth = get_auth_user();
$teacher_id = (int)$auth['id'];

$result = $conn->query("
    SELECT tp.*, u.full_name AS assigned_by_name
    FROM teacher_positions tp
    JOIN users u ON tp.assigned_by = u.id
    WHERE tp.teacher_id = $teacher_id AND tp.is_active = 1
    ORDER BY tp.assigned_date DESC
");

if (!$result) {
    respond(['error' => 'Query failed: ' . $conn->error], 500);
}

$positions = [];
while ($row = $result->fetch_assoc()) {
    $positions[] = $row;
}
respond($positions);
