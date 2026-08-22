<?php
require_once '../config/db.php';
// Public read — any authenticated user
$auth = get_auth_user();

$result = $conn->query("SELECT * FROM exam_timetable_config ORDER BY id DESC LIMIT 1");
if ($result->num_rows === 0) {
    respond(['weeks' => [], 'notes' => [], 'schoolName' => '', 'termName' => '']);
}
$row = $result->fetch_assoc();
$data = json_decode($row['config_json'], true);
respond($data ?: ['weeks'=>[],'notes'=>[]]);
