<?php
require_once '../config/db.php';

$auth = get_auth_user();
require_role($auth, 'admin');

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['full_name']) || empty($data['role'])) {
    respond(['error' => 'Full name and role are required'], 400);
}

$role = $data['role'];
if (!in_array($role, ['academician', 'teacher', 'student'])) {
    respond(['error' => 'Invalid role. Allowed: academician, teacher, student'], 400);
}

$full_name = $conn->real_escape_string($data['full_name']);
$created_by = (int)$auth['id'];
$generated_password = generate_random_password(10);
$hashed_password = password_hash($generated_password, PASSWORD_DEFAULT);

$email = null;
$reg_number = null;

if ($role === 'student') {
    // Use provided reg_number or generate one
    if (!empty($data['reg_number'])) {
        $reg_number = $conn->real_escape_string($data['reg_number']);
        // Check if reg_number already exists
        $check = $conn->query("SELECT id FROM users WHERE reg_number='$reg_number'");
        if ($check->num_rows > 0) {
            respond(['error' => 'Registration number already exists'], 409);
        }
    } else {
        $reg_number = generate_reg_number();
        $check = $conn->query("SELECT id FROM users WHERE reg_number='$reg_number'");
        while ($check->num_rows > 0) {
            $reg_number = generate_reg_number();
            $check = $conn->query("SELECT id FROM users WHERE reg_number='$reg_number'");
        }
    }
} else {
    // Use provided email or generate one
    if (!empty($data['email'])) {
        $email = $conn->real_escape_string($data['email']);
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(['error' => 'Invalid email format'], 400);
        }
        // Check if email already exists
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            respond(['error' => 'Email already exists'], 409);
        }
    } else {
        // Generate email from full name
        $name_parts = explode(' ', strtolower($full_name));
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : $first_name;
        $base_email = $first_name . '.' . $last_name . '@school.com';
        $email = $base_email;
        
        $counter = 1;
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        while ($check->num_rows > 0) {
            $email = $first_name . '.' . $last_name . $counter . '@school.com';
            $counter++;
            $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        }
    }
}

// Insert user
if ($role === 'student') {
    $sql = "INSERT INTO users (full_name, password, role, reg_number, created_by, is_active)
            VALUES ('$full_name', '$hashed_password', '$role', '$reg_number', $created_by, 1)";
} else {
    $sql = "INSERT INTO users (full_name, email, password, role, created_by, is_active)
            VALUES ('$full_name', '$email', '$hashed_password', '$role', $created_by, 1)";
}

if ($conn->query($sql)) {
    $new_user_id = $conn->insert_id;
    $response = [
        'message' => 'User created successfully',
        'user' => [
            'id' => $new_user_id,
            'full_name' => $full_name,
            'role' => $role,
            'generated_password' => $generated_password
        ]
    ];
    
    if ($role === 'student') {
        $response['user']['reg_number'] = $reg_number;
    } else {
        $response['user']['email'] = $email;
    }
    
    respond($response, 201);
} else {
    respond(['error' => 'Failed to create user: ' . $conn->error], 500);
}
?>