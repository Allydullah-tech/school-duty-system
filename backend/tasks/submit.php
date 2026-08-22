<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/db.php';
header('Content-Type: application/json');

try {
    $auth = get_auth_user();
    require_role($auth, 'student');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(array('error' => 'Only POST method allowed'), 405);
    }

    $task_id         = (int)(isset($_POST['task_id']) ? $_POST['task_id'] : 0);
    $submission_text = isset($_POST['submission_text']) ? trim($_POST['submission_text']) : '';
    $student_id      = (int)$auth['id'];

    if (!$task_id) {
        respond(array('error' => 'Task ID required'), 400);
    }

    $chk = $conn->query("SELECT id FROM student_tasks WHERE id=$task_id AND status IN ('active')");
    if (!$chk || $chk->num_rows === 0) {
        respond(array('error' => 'Task not found or closed'), 404);
    }

    $dup = $conn->query("SELECT id FROM task_submissions WHERE task_id=$task_id AND student_id=$student_id");
    if ($dup && $dup->num_rows > 0) {
        respond(array('error' => 'You have already submitted this task'), 400);
    }

    $colCheck = $conn->query("SHOW COLUMNS FROM task_submissions LIKE 'submission_file'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE task_submissions ADD COLUMN submission_file VARCHAR(500) DEFAULT NULL");
    }

    $file_name = null;

    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/submissions/';

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $original_name = $_FILES['submission_file']['name'];
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_ext = array('pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'zip', 'xlsx', 'pptx');

        if (!in_array($file_ext, $allowed_ext)) {
            respond(array('error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed_ext)), 400);
        }

        if ($_FILES['submission_file']['size'] > 15 * 1024 * 1024) {
            respond(array('error' => 'File too large. Maximum size is 15MB'), 400);
        }

        $file_name = 'task_' . $task_id . '_student_' . $student_id . '_' . time() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['submission_file']['tmp_name'], $file_path)) {
            respond(array('error' => 'Failed to upload file. Check folder permissions.'), 500);
        }
    }

    if (!$submission_text && !$file_name) {
        respond(array('error' => 'Please write a submission or upload a file'), 400);
    }

    $submission_text_escaped = $conn->real_escape_string($submission_text);

    if ($file_name) {
        $file_name_escaped = $conn->real_escape_string($file_name);
        $sql = "INSERT INTO task_submissions (task_id, student_id, submission_text, submission_file)
                VALUES ($task_id, $student_id, '$submission_text_escaped', '$file_name_escaped')";
    } else {
        $sql = "INSERT INTO task_submissions (task_id, student_id, submission_text)
                VALUES ($task_id, $student_id, '$submission_text_escaped')";
    }

    if ($conn->query($sql)) {
        respond(array('message' => 'Task submitted successfully!', 'file_uploaded' => $file_name ? true : false));
    } else {
        respond(array('error' => 'Failed: ' . $conn->error), 500);
    }

} catch (Exception $e) {
    respond(array('error' => 'Server error: ' . $e->getMessage()), 500);
}