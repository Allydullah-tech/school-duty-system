<?php
require_once '../config/db.php';

$auth = get_auth_user();
require_role($auth, 'admin', 'academician');

$result = $conn->query("SELECT id, full_name, email FROM users WHERE role='teacher' AND is_active=1 ORDER BY full_name");

$teachers = [];
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}
respond($teachers);