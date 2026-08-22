<?php
require_once '../config/db.php';
$auth = get_auth_user();
$id   = (int)$auth['id'];

$result = $conn->query("
    SELECT d.*, u.full_name AS assigned_by_name
    FROM duties d
    JOIN users u ON d.assigned_by = u.id
    WHERE d.assigned_to = $id
    ORDER BY d.duty_date ASC
");

if (!$result) {
    respond(['error' => 'Query failed: ' . $conn->error], 500);
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
respond($rows);