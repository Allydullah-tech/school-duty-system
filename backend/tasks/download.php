<?php
require_once '../config/db.php';

// Allow token via Authorization header OR ?token= query param
// (browser link clicks/new tabs can't send custom headers)
if (isset($_GET['token']) && $_GET['token']) {
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $_GET['token'];
}

$auth = get_auth_user();
require_role($auth, 'teacher', 'admin');

$submission_id = (int)(isset($_GET['submission_id']) ? $_GET['submission_id'] : 0);
if (!$submission_id) {
    http_response_code(400);
    die('Submission ID required');
}

$teacher_id = (int)$auth['id'];

if ($auth['role'] === 'admin') {
    $sql = "SELECT ts.submission_file FROM task_submissions ts WHERE ts.id=$submission_id";
} else {
    $sql = "SELECT ts.submission_file
            FROM task_submissions ts
            JOIN student_tasks st ON ts.task_id = st.id
            WHERE ts.id=$submission_id AND st.assigned_by=$teacher_id";
}

$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    http_response_code(403);
    die('Access denied or submission not found');
}

$row = $result->fetch_assoc();
$file_name = $row['submission_file'];

if (!$file_name) {
    http_response_code(404);
    die('No file attached to this submission');
}

$file_path = __DIR__ . '/../uploads/submissions/' . $file_name;

if (!file_exists($file_path)) {
    http_response_code(404);
    die('File not found on server');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($file_path));
header('X-Content-Type-Options: nosniff');
readfile($file_path);
exit();