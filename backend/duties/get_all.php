<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');
$result = $conn->query("
    SELECT d.*, u1.full_name AS assigned_to_name, u2.full_name AS assigned_by_name
    FROM duties d
    JOIN users u1 ON d.assigned_to = u1.id
    JOIN users u2 ON d.assigned_by = u2.id
    ORDER BY d.duty_date DESC
");
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
respond($rows);
