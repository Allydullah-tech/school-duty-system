<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'teacher');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { $data = array(); }

$requesting_teacher_id = (int)$auth['id'];
$duty_date = $conn->real_escape_string(isset($data['duty_date']) ? $data['duty_date'] : '');
$reason    = $conn->real_escape_string(isset($data['reason'])    ? $data['reason']    : '');

if (!$duty_date) { respond(array('error' => 'duty_date is required'), 400); }

// Add duty_week_id column if missing
$col_check = $conn->query("SHOW COLUMNS FROM swap_requests LIKE 'duty_week_id'");
if ($col_check && $col_check->num_rows === 0) {
    $conn->query("ALTER TABLE swap_requests ADD COLUMN duty_week_id INT DEFAULT NULL");
}

// target_teacher_id must be NULL (not 0) to satisfy the foreign key constraint
// when the request is going to the academician rather than a specific teacher
$target_teacher_raw = isset($data['target_teacher_id']) ? (int)$data['target_teacher_id'] : 0;
$target_teacher_sql  = ($target_teacher_raw > 0) ? $target_teacher_raw : 'NULL';

$week_id_raw = isset($data['duty_week_id']) ? (int)$data['duty_week_id'] : 0;
$week_id_sql = ($week_id_raw > 0) ? $week_id_raw : 'NULL';

$sql = "INSERT INTO swap_requests (requesting_teacher_id, target_teacher_id, duty_date, duty_week_id, reason)
        VALUES ($requesting_teacher_id, $target_teacher_sql, '$duty_date', $week_id_sql, '$reason')";

if ($conn->query($sql)) {
    respond(array('message' => 'Swap request sent to academician successfully'));
} else {
    respond(array('error' => 'Database error: ' . $conn->error), 500);
}