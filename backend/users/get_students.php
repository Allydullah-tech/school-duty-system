<?php
require_once '../config/db.php';

$auth = get_auth_user();
require_role($auth, 'admin', 'teacher');

$result = $conn->query("SELECT id, full_name, reg_number FROM users WHERE role='student' AND is_active=1 ORDER BY full_name");

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
respond($students);