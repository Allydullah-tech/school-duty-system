<?php
require_once '../config/db.php';

$auth = get_auth_user();
require_role($auth, 'admin');

$data = json_decode(file_get_contents('php://input'), true);

$security_question = $conn->real_escape_string($data['security_question'] ?? '');
$security_answer = $data['security_answer'] ?? '';

if (empty($security_question) || empty($security_answer)) {
    respond(['error' => 'Security question and answer are required'], 400);
}

$hashed_answer = password_hash(strtolower(trim($security_answer)), PASSWORD_DEFAULT);
$admin_id = (int)$auth['id'];

$sql = "UPDATE users SET security_question = '$security_question', security_answer = '$hashed_answer' WHERE id = $admin_id";

if ($conn->query($sql)) {
    respond(['message' => 'Security question saved successfully!']);
} else {
    respond(['error' => 'Failed to save security question: ' . $conn->error], 500);
}
?>