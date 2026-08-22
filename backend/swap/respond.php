<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'teacher');
$data = json_decode(file_get_contents('php://input'), true);
$request_id      = (int)($data['request_id'] ?? 0);
$status          = $conn->real_escape_string($data['status'] ?? '');
$response_comment = $conn->real_escape_string($data['response_comment'] ?? '');
if (!in_array($status, ['approved','rejected'])) respond(['error' => 'Invalid status'], 400);
$sql = "UPDATE swap_requests SET status='$status', responded_at=NOW(), response_comment='$response_comment'
        WHERE id=$request_id AND target_teacher_id=" . (int)$auth['id'];
if ($conn->query($sql)) respond(['message' => 'Swap request '.$status]);
else respond(['error' => 'Failed'], 500);
