<?php
require_once '../config/db.php';

$auth = get_auth_user();
$task_id = (int)($_GET['task_id'] ?? 0);

if (!$task_id) {
    respond(['error' => 'Task ID required'], 400);
}

// Check if user is the student or the teacher who assigned it
$user_id = (int)$auth['id'];
$role = $auth['role'];

if ($role === 'student') {
    $check = $conn->query("SELECT id FROM student_tasks WHERE id=$task_id AND assigned_to=$user_id");
} else {
    $check = $conn->query("SELECT id FROM student_tasks WHERE id=$task_id AND assigned_by=$user_id");
}

if ($check->num_rows === 0) {
    respond(['error' => 'Access denied'], 403);
}

$result = $conn->query("SELECT submission_text, submission_file, submission_date, status, grade, grade_comment FROM student_tasks WHERE id=$task_id");
$submission = $result->fetch_assoc();

if ($submission['submission_file']) {
    $submission['file_url'] = '/school-duty-system/backend/uploads/assignments/' . $submission['submission_file'];
    $submission['file_exists'] = file_exists(__DIR__ . '/../uploads/assignments/' . $submission['submission_file']);
}

respond($submission);
?>