<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');
$result = $conn->query("
    SELECT st.*, u1.full_name AS assigned_to_name, u2.full_name AS assigned_by_name
    FROM special_tasks st
    JOIN users u1 ON st.assigned_to = u1.id
    JOIN users u2 ON st.assigned_by = u2.id
    ORDER BY st.due_date ASC
");
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
respond($rows);
