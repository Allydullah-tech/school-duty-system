<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'teacher');
$teacher_id = (int)$auth['id'];
$result = $conn->query("
    SELECT st.*, 
           (SELECT COUNT(*) FROM task_submissions ts WHERE ts.task_id = st.id) AS submission_count
    FROM student_tasks st
    WHERE st.assigned_by = $teacher_id
    ORDER BY st.created_at DESC
");
if (!$result) respond(['error' => 'Query failed: ' . $conn->error], 500);
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
respond($rows);
