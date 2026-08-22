<?php
require_once '../config/db.php';
$auth    = get_auth_user();
$user_id = (int)$auth['id'];
$role    = $auth['role'];

if ($role === 'admin' || $role === 'academician') {
    $sql = "SELECT sr.*, u1.full_name AS requesting_teacher,
               COALESCE(u2.full_name, 'Academician') AS target_teacher
            FROM swap_requests sr
            JOIN users u1 ON sr.requesting_teacher_id = u1.id
            LEFT JOIN users u2 ON sr.target_teacher_id = u2.id AND sr.target_teacher_id > 0
            ORDER BY sr.requested_at DESC";
} else {
    $sql = "SELECT sr.*, u1.full_name AS requesting_teacher,
               COALESCE(u2.full_name, 'Academician') AS target_teacher
            FROM swap_requests sr
            JOIN users u1 ON sr.requesting_teacher_id = u1.id
            LEFT JOIN users u2 ON sr.target_teacher_id = u2.id AND sr.target_teacher_id > 0
            WHERE sr.requesting_teacher_id = $user_id OR sr.target_teacher_id = $user_id
            ORDER BY sr.requested_at DESC";
}
$result = $conn->query($sql);
if (!$result) { respond(array('error' => 'Query failed: ' . $conn->error), 500); }
$rows = array();
while ($row = $result->fetch_assoc()) { $rows[] = $row; }
respond($rows);