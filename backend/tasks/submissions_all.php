<?php
require_once '../config/db.php';
$result = $conn->query("SELECT ts.*, st.title AS task_title, u1.full_name AS student_name, u2.full_name AS teacher_name FROM task_submissions ts JOIN student_tasks st ON ts.task_id = st.id JOIN users u1 ON ts.student_id = u1.id JOIN users u2 ON st.assigned_by = u2.id ORDER BY ts.submission_date DESC");
$submissions = [];
while ($row = $result->fetch_assoc()) $submissions[] = $row;
respond($submissions);
?>