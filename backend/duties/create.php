<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');

$d = json_decode(file_get_contents('php://input'), true);
if (empty($d['title']) || empty($d['assigned_to']) || empty($d['duty_date'])) {
    respond(['error' => 'Title, assigned_to and duty_date are required'], 400);
}

$title       = $conn->real_escape_string($d['title']);
$description = $conn->real_escape_string($d['description'] ?? '');
$assigned_to = (int)$d['assigned_to'];
$assigned_by = (int)$auth['id'];
$duty_date   = $conn->real_escape_string($d['duty_date']);
$duty_time   = $conn->real_escape_string($d['duty_time'] ?? '');
$location    = $conn->real_escape_string($d['location'] ?? '');

$sql = "INSERT INTO duties (title, description, assigned_to, assigned_by, duty_date, duty_time, location)
        VALUES ('$title','$description',$assigned_to,$assigned_by,'$duty_date','$duty_time','$location')";

if ($conn->query($sql)) respond(['message' => 'Duty assigned successfully'], 201);
else respond(['error' => 'Failed to assign duty: '.$conn->error], 500);
