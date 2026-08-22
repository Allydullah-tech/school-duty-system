<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) respond(['error' => 'Invalid JSON'], 400);

$json = $conn->real_escape_string(json_encode($data));
$saved_by = (int)$auth['id'];

// Upsert — one config row per system (replace on save)
$check = $conn->query("SELECT id FROM exam_timetable_config ORDER BY id DESC LIMIT 1");
if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();
    $id  = (int)$row['id'];
    $conn->query("UPDATE exam_timetable_config SET config_json='$json', saved_by=$saved_by, saved_at=NOW() WHERE id=$id");
} else {
    $conn->query("INSERT INTO exam_timetable_config (config_json, saved_by) VALUES ('$json', $saved_by)");
}
respond(['message' => 'Timetable saved successfully']);
