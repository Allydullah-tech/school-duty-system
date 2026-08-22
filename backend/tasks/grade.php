<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'teacher', 'admin');
$data = json_decode(file_get_contents('php://input'), true);
$submission_id = (int)($data['submission_id'] ?? 0);
$grade = $conn->real_escape_string($data['grade'] ?? '');
$comment = $conn->real_escape_string($data['comment'] ?? '');
if (!$submission_id || !$grade) respond(['error' => 'submission_id and grade required'], 400);
$sql = "UPDATE task_submissions SET grade='$grade', grade_comment='$comment', graded_date=NOW() WHERE id=$submission_id";
if ($conn->query($sql)) respond(['message' => 'Graded successfully']);
else respond(['error' => 'Failed'], 500);
