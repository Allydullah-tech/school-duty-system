<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'teacher', 'admin');
$teacher_id = (int)$auth['id'];
$sql = $auth['role'] === 'admin'
    ? "SELECT ts.*, st.title AS task_title, u.full_name AS student_name, u.reg_number
       FROM task_submissions ts
       JOIN student_tasks st ON ts.task_id = st.id
       JOIN users u ON ts.student_id = u.id
       ORDER BY ts.submission_date DESC"
    : "SELECT ts.*, st.title AS task_title, u.full_name AS student_name, u.reg_number
       FROM task_submissions ts
       JOIN student_tasks st ON ts.task_id = st.id
       JOIN users u ON ts.student_id = u.id
       WHERE st.assigned_by = $teacher_id
       ORDER BY ts.submission_date DESC";
$result = $conn->query($sql);
if (!$result) respond(['error' => 'Query failed: ' . $conn->error], 500);
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
respond($rows);
