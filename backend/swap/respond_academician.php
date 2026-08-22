<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');

$data            = json_decode(file_get_contents('php://input'), true);
$request_id      = (int)($data['request_id'] ?? 0);
$status          = $conn->real_escape_string($data['status'] ?? '');
$response_comment = $conn->real_escape_string($data['response_comment'] ?? '');
$reassign_to     = (int)($data['reassign_to'] ?? 0);

if (!$request_id) respond(['error' => 'request_id required'], 400);
if (!in_array($status, ['approved','rejected'])) respond(['error' => 'Invalid status'], 400);

// Get the swap request
$req = $conn->query("SELECT * FROM swap_requests WHERE id=$request_id");
if ($req->num_rows === 0) respond(['error' => 'Swap request not found'], 404);
$swap = $req->fetch_assoc();

// Update the swap request status
$conn->query("
    UPDATE swap_requests
    SET status='$status', responded_at=NOW(), response_comment='$response_comment'
    WHERE id=$request_id
");

// If approved AND we have a week ID AND a new teacher — reassign the term duty week
if ($status === 'approved' && $reassign_to && $swap['duty_week_id']) {
    $week_id    = (int)$swap['duty_week_id'];
    $note_esc   = $conn->real_escape_string("Transferred via swap request. " . ($data['response_comment'] ?? ''));
    $conn->query("
        UPDATE term_duties
        SET assigned_to=$reassign_to, swapped=1, swap_note='$note_esc'
        WHERE id=$week_id
    ");
}

respond(['message' => 'Swap request handled successfully', 'status' => $status]);
