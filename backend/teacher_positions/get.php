<?php
require_once '../config/db.php';
$auth = get_auth_user();

$result = $conn->query("
    SELECT tp.*, u.full_name AS teacher_name, a.full_name AS assigned_by_name
    FROM teacher_positions tp
    JOIN users u ON tp.teacher_id = u.id
    JOIN users a ON tp.assigned_by = a.id
    WHERE tp.is_active = 1
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
