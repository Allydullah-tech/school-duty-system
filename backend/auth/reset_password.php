<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'school_duty_system';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed'
    ]);
    exit();
}

function sendJson($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    sendJson(['error' => 'Invalid request'], 400);
}

$action = $data['action'] ?? '';

/* =====================================================
   STEP 1: CHECK EMAIL
===================================================== */
if ($action === 'check_email') {

    $email = trim($data['email'] ?? '');

    if (empty($email)) {
        sendJson(['error' => 'Email is required'], 400);
    }

    $stmt = $conn->prepare("
        SELECT id, security_question
        FROM users
        WHERE email = ?
        AND role = 'admin'
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJson([
            'error' => 'Admin account not found'
        ], 404);
    }

    $user = $result->fetch_assoc();

    if (empty($user['security_question'])) {
        sendJson([
            'error' => 'Security question not set'
        ], 400);
    }

    sendJson([
        'success' => true,
        'admin_id' => $user['id'],
        'security_question' => $user['security_question']
    ]);
}

/* =====================================================
   STEP 2: VERIFY ANSWER
===================================================== */
elseif ($action === 'verify_answer') {

    $admin_id = intval($data['admin_id'] ?? 0);
    $answer = strtolower(trim($data['answer'] ?? ''));

    if (!$admin_id) {
        sendJson(['error' => 'Invalid admin ID'], 400);
    }

    if (empty($answer)) {
        sendJson(['error' => 'Answer is required'], 400);
    }

    // Ensure columns exist
    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_token'");

    if ($check->num_rows === 0) {
        $conn->query("
            ALTER TABLE users
            ADD reset_token VARCHAR(255) NULL
        ");

        $conn->query("
            ALTER TABLE users
            ADD reset_expiry DATETIME NULL
        ");
    }

    $stmt = $conn->prepare("
        SELECT security_answer
        FROM users
        WHERE id = ?
        AND role = 'admin'
    ");

    $stmt->bind_param("i", $admin_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJson(['error' => 'Admin not found'], 404);
    }

    $user = $result->fetch_assoc();

    if (!password_verify($answer, $user['security_answer'])) {
        sendJson(['error' => 'Incorrect security answer'], 401);
    }

    // Generate token
    $token = trim(bin2hex(random_bytes(32)));

    // Expire after 1 hour
    $expiry = date('Y-m-d H:i:s', time() + 3600);

    $update = $conn->prepare("
        UPDATE users
        SET reset_token = ?, reset_expiry = ?
        WHERE id = ?
    ");

    $update->bind_param("ssi", $token, $expiry, $admin_id);

    if (!$update->execute()) {
        sendJson([
            'error' => 'Failed to save reset token'
        ], 500);
    }

    sendJson([
        'success' => true,
        'reset_token' => $token
    ]);
}

/* =====================================================
   STEP 3: RESET PASSWORD
===================================================== */
elseif ($action === 'reset_password') {

    $token = trim($data['reset_token'] ?? '');
    $new_password = trim($data['new_password'] ?? '');

    if (empty($token)) {
        sendJson(['error' => 'Reset token missing'], 400);
    }

    if (strlen($new_password) < 6) {
        sendJson([
            'error' => 'Password must be at least 6 characters'
        ], 400);
    }

    // DEBUG
    error_log("TOKEN RECEIVED: " . $token);

    $stmt = $conn->prepare("
        SELECT id, reset_token, reset_expiry
        FROM users
        WHERE reset_token = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJson([
            'error' => 'Invalid reset token'
        ], 401);
    }

    $user = $result->fetch_assoc();

    // Check expiry manually
    if (strtotime($user['reset_expiry']) < time()) {
        sendJson([
            'error' => 'Reset token expired'
        ], 401);
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    $update = $conn->prepare("
        UPDATE users
        SET password = ?,
            reset_token = NULL,
            reset_expiry = NULL
        WHERE id = ?
    ");

    $update->bind_param("si", $hashed, $user['id']);

    if (!$update->execute()) {
        sendJson([
            'error' => 'Failed to update password'
        ], 500);
    }

    sendJson([
        'success' => true,
        'message' => 'Password reset successful'
    ]);
}

/* =====================================================
   INVALID ACTION
===================================================== */
else {
    sendJson([
        'error' => 'Invalid action'
    ], 400);
}

$conn->close();
?>