<?php
require_once '../config/db.php';
$auth = get_auth_user();

$result = $conn->query("
    SELECT td.*, u.full_name AS teacher_name
    FROM term_duties td
    JOIN users u ON td.assigned_to = u.id
    ORDER BY td.term_start ASC, td.week_number ASC
");

if (!$result) {
    respond(['error' => 'Query failed: ' . $conn->error], 500);
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $row['swapped'] = (int)$row['swapped'] === 1 ? true : false;
    $rows[] = $row;
}
respond($rows);