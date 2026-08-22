<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'teacher', 'admin');
$data        = json_decode(file_get_contents('php://input'), true);
$title       = $conn->real_escape_string($data['title'] ?? '');
$description = $conn->real_escape_string($data['description'] ?? '');
$subject     = $conn->real_escape_string($data['subject'] ?? '');
$due_date    = $conn->real_escape_string($data['due_date'] ?? '');
$assigned_by = (int)$auth['id'];
if (!$title) respond(['error' => 'Title is required'], 400);
$due_val = $due_date ? "'$due_date'" : "NULL";
$sql = "INSERT INTO student_tasks (title, description, subject, assigned_by, due_date, status)
        VALUES ('$title','$description','$subject',$assigned_by,$due_val,'active')";
if ($conn->query($sql)) respond(['message' => 'Task posted to all students'], 201);
else respond(['error' => 'Failed: '.$conn->error], 500);
