<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'student');
$student_id = (int)$auth['id'];
$result = $conn->query("
    SELECT ts.*, st.title AS task_title, st.subject,
           u.full_name AS teacher_name
    FROM task_submissions ts
    JOIN student_tasks st ON ts.task_id = st.id
    JOIN users u ON st.assigned_by = u.id
    WHERE ts.student_id = $student_id
    ORDER BY ts.submission_date DESC
");
if (!$result) respond(['error' => 'Query failed: ' . $conn->error], 500);
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
respond($rows);
