<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');
$d = json_decode(file_get_contents('php://input'), true);
$title       = $conn->real_escape_string($d['title'] ?? '');
$description = $conn->real_escape_string($d['description'] ?? '');
$assigned_to = (int)($d['assigned_to'] ?? 0);
$assigned_by = (int)$auth['id'];
$due_date    = $conn->real_escape_string($d['due_date'] ?? '');
$priority    = $conn->real_escape_string($d['priority'] ?? 'medium');
if (!$title || !$assigned_to) respond(['error' => 'Title and assigned_to required'], 400);
$due_val = $due_date ? "'$due_date'" : "NULL";
$sql = "INSERT INTO special_tasks (title, description, assigned_to, assigned_by, due_date, priority)
        VALUES ('$title','$description',$assigned_to,$assigned_by,$due_val,'$priority')";
if ($conn->query($sql)) respond(['message' => 'Special task assigned successfully'], 201);
else respond(['error' => 'Failed: '.$conn->error], 500);
