<?php
require_once '../config/db.php';

$auth = get_auth_user();
$user_id = (int)$auth['id'];

$result = $conn->query("SELECT security_question FROM users WHERE id = $user_id");

if (!$result) {
    respond(['error' => 'Database error: ' . $conn->error], 500);
}

$user = $result->fetch_assoc();

respond([
    'has_security_question' => !empty($user['security_question'])
]);
?>