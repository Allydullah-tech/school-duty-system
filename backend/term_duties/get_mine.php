<?php
require_once '../config/db.php';
$auth = get_auth_user();
$id   = (int)$auth['id'];

$result = $conn->query(
    "SELECT td.*, u.full_name AS teacher_name " .
    "FROM term_duties td " .
    "JOIN users u ON td.assigned_to = u.id " .
    "WHERE td.assigned_to = $id " .
    "ORDER BY td.week_number ASC"
);
if (!$result) { respond(array('error' => 'Query failed: ' . $conn->error), 500); }
$rows = array();
while ($row = $result->fetch_assoc()) {
    $row['swapped'] = ((int)$row['swapped'] === 1);
    $rows[] = $row;
}
respond($rows);