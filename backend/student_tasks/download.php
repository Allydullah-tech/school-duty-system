<?php
require_once '../config/db.php';

$auth = get_auth_user();
$task_id = (int)($_GET['task_id'] ?? 0);

if (!$task_id) {
    die('Task ID required');
}

// Check access - teacher who assigned or student who submitted
$user_id = (int)$auth['id'];
$role = $auth['role'];

if ($role === 'student') {
    $check = $conn->query("SELECT submission_file FROM student_tasks WHERE id=$task_id AND assigned_to=$user_id");
} else {
    $check = $conn->query("SELECT submission_file FROM student_tasks WHERE id=$task_id AND assigned_by=$user_id");
}

if ($check->num_rows === 0) {
    die('Access denied');
}

$task = $check->fetch_assoc();
$file_name = $task['submission_file'];

if (!$file_name) {
    die('No file attached to this submission');
}

$file_path = __DIR__ . '/../uploads/assignments/' . $file_name;

if (!file_exists($file_path)) {
    die('File not found');
}

// Serve file for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();
?>