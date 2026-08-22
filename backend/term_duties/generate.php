<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'admin', 'academician');

$data       = json_decode(file_get_contents('php://input'), true);
$term_start = $conn->real_escape_string($data['term_start'] ?? '');
$title      = $conn->real_escape_string($data['title'] ?? 'Weekly Duty');
$weeks      = $data['weeks'] ?? [];

if (!$term_start || empty($weeks)) {
    respond(['error' => 'term_start and weeks are required'], 400);
}

// Delete existing schedule for this term start date
$conn->query("DELETE FROM term_duties WHERE term_start = '$term_start'");

$created_by = (int)$auth['id'];
$inserted   = 0;

foreach ($weeks as $w) {
    $week_number = (int)($w['week_number'] ?? 0);
    $week_start  = $conn->real_escape_string($w['week_start'] ?? '');
    $week_end    = $conn->real_escape_string($w['week_end'] ?? '');
    $assigned_to = (int)($w['assigned_to'] ?? 0);
    $w_title     = $conn->real_escape_string($w['title'] ?? $title);

    if (!$week_start || !$week_end || !$assigned_to) continue;

    $sql = "INSERT INTO term_duties
                (term_start, week_number, week_start, week_end, assigned_to, title, status, swapped, swap_note, created_by)
            VALUES
                ('$term_start', $week_number, '$week_start', '$week_end', $assigned_to, '$w_title', 'pending', 0, NULL, $created_by)";

    if ($conn->query($sql)) $inserted++;
}

respond([
    'message'    => 'Term schedule generated successfully',
    'weeks'      => $inserted,
    'term_start' => $term_start
], 201);