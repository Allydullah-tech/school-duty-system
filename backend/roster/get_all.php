<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');
$result = $conn->query("SELECT r.*, u.full_name AS teacher_name FROM roster_book r JOIN users u ON r.teacher_id = u.id ORDER BY r.submitted_at DESC");
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
respond($rows);
