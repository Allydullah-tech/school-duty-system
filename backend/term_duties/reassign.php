<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');

$data       = json_decode(file_get_contents('php://input'), true);
$id         = (int)($data['id'] ?? 0);
$teacher_id = (int)($data['teacher_id'] ?? 0);
$note       = $conn->real_escape_string($data['note'] ?? '');
$action     = $data['action'] ?? 'replace'; // 'replace' or 'add'

if (!$id || !$teacher_id) {
    respond(['error' => 'Week ID and teacher_id required'], 400);
}

$check = $conn->query("SELECT id, week_number, week_start, week_end, title, term_start FROM term_duties WHERE id=$id");
if (!$check || $check->num_rows === 0) {
    respond(['error' => 'Week not found'], 404);
}
$week = $check->fetch_assoc();

$tcheck = $conn->query("SELECT full_name FROM users WHERE id=$teacher_id AND role='teacher'");
if (!$tcheck || $tcheck->num_rows === 0) {
    respond(['error' => 'Teacher not found'], 404);
}

if ($action === 'add') {
    // Add another teacher to this week — insert a new row with same week details
    $wnum   = (int)$week['week_number'];
    $wstart = $conn->real_escape_string($week['week_start']);
    $wend   = $conn->real_escape_string($week['week_end']);
    $title  = $conn->real_escape_string($week['title']);
    $tstart = $conn->real_escape_string($week['term_start']);
    $cby    = (int)$auth['id'];
    $sql = "INSERT INTO term_duties
                (term_start, week_number, week_start, week_end, assigned_to, title, status, swapped, swap_note, created_by)
            VALUES
                ('$tstart', $wnum, '$wstart', '$wend', $teacher_id, '$title', 'pending', 1, '$note', $cby)";
    if ($conn->query($sql)) {
        respond(['message' => 'Additional teacher added to this week']);
    } else {
        respond(['error' => 'Failed: ' . $conn->error], 500);
    }
} else {
    // Replace existing assignment
    $sql = "UPDATE term_duties SET assigned_to=$teacher_id, swapped=1, swap_note='$note' WHERE id=$id";
    if ($conn->query($sql)) {
        respond(['message' => 'Week reassigned successfully']);
    } else {
        respond(['error' => 'Failed: ' . $conn->error], 500);
    }
}
