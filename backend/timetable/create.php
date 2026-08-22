<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'academician', 'admin');
$data = json_decode(file_get_contents('php://input'), true);
$day        = $conn->real_escape_string($data['day'] ?? '');
$time_slot  = $conn->real_escape_string($data['time_slot'] ?? '');
$subject    = $conn->real_escape_string($data['subject'] ?? '');
$teacher_id = (int)($data['teacher_id'] ?? 0);
$class_name = $conn->real_escape_string($data['class_name'] ?? '');
$room       = $conn->real_escape_string($data['room'] ?? '');
$created_by = (int)$auth['id'];
foreach (['day','time_slot','subject','teacher_id','class_name'] as $f) {
    if (empty($data[$f])) respond(['error' => "Field '$f' is required"], 400);
}
$sql = "INSERT INTO timetable (day_of_week, time_slot, subject, teacher_id, class_name, room, created_by)
        VALUES ('$day','$time_slot','$subject',$teacher_id,'$class_name','$room',$created_by)";
if ($conn->query($sql)) respond(['message' => 'Timetable entry created'], 201);
else respond(['error' => 'Failed: '.$conn->error], 500);
