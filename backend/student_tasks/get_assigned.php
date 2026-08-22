<?php
require_once '../config/db.php';

$auth = get_auth_user();
require_role($auth, 'admin', 'teacher');

$id = (int)$auth['id'];

$result = $conn->query("
    SELECT st.*, u.full_name AS student_name, u.reg_number
    FROM student_tasks st 
    JOIN users u ON st.assigned_to = u.id
    WHERE st.assigned_by = $id 
    ORDER BY st.due_date ASC
");

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
respond($rows);