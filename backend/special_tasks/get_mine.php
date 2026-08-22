<?php
require_once '../config/db.php';
$auth = get_auth_user();
$id = (int)$auth['id'];
$result = $conn->query("
    SELECT st.*, u.full_name AS assigned_by_name
    FROM special_tasks st
    JOIN users u ON st.assigned_by = u.id
    WHERE st.assigned_to = $id
    ORDER BY st.due_date ASC
");
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
respond($rows);
