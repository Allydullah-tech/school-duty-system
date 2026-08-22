<?php
require_once '../config/db.php';
$day = isset($_GET['day']) ? $conn->real_escape_string($_GET['day']) : '';
$sql = "SELECT t.*, u.full_name AS teacher_name FROM timetable t JOIN users u ON t.teacher_id = u.id";
if ($day) $sql .= " WHERE t.day_of_week = '$day'";
$sql .= " ORDER BY FIELD(t.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.time_slot";
$result = $conn->query($sql);
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
respond($rows);
