<?php
require_once '../config/db.php';
$auth = get_auth_user();

$result = $conn->query(
    "SELECT st.*, u.full_name AS teacher_name " .
    "FROM student_tasks st " .
    "JOIN users u ON st.assigned_by = u.id " .
    "WHERE st.status = 'active' " .
    "ORDER BY st.created_at DESC"
);
if (!$result) { respond(array('error' => 'Query failed: ' . $conn->error), 500); }
$tasks = array();
while ($row = $result->fetch_assoc()) { $tasks[] = $row; }
respond($tasks);
