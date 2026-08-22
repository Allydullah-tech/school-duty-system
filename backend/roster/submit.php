<?php
require_once '../config/db.php';
$auth = get_auth_user();
require_role($auth, 'teacher');

$data = json_decode(file_get_contents('php://input'), true);

$teacher_id  = (int)$auth['id'];
$date        = $conn->real_escape_string($data['date'] ?? date('Y-m-d'));
$class_name  = $conn->real_escape_string($data['class_name'] ?? '');

// Registered
$reg_boys    = (int)($data['reg_boys'] ?? 0);
$reg_girls   = (int)($data['reg_girls'] ?? 0);
// Present
$pres_boys   = (int)($data['pres_boys'] ?? 0);
$pres_girls  = (int)($data['pres_girls'] ?? 0);
// Absent
$abs_boys    = (int)($data['abs_boys'] ?? 0);
$abs_girls   = (int)($data['abs_girls'] ?? 0);
// Permitted
$perm_boys   = (int)($data['perm_boys'] ?? 0);
$perm_girls  = (int)($data['perm_girls'] ?? 0);
// Sick
$sick_boys   = (int)($data['sick_boys'] ?? 0);
$sick_girls  = (int)($data['sick_girls'] ?? 0);

$remarks     = $conn->real_escape_string($data['remarks'] ?? '');

if (!$class_name) {
    respond(['error' => 'Class name is required'], 400);
}

// Auto-add new columns if old table schema
$cols_needed = array('class_name','reg_boys','reg_girls','pres_boys','pres_girls','abs_boys','abs_girls','perm_boys','perm_girls','sick_boys','sick_girls');
foreach ($cols_needed as $col) {
    $chk = $conn->query("SHOW COLUMNS FROM roster_book LIKE '$col'");
    if ($chk && $chk->num_rows === 0) {
        $type = ($col === 'class_name') ? "VARCHAR(100) DEFAULT ''" : "INT DEFAULT 0";
        $conn->query("ALTER TABLE roster_book ADD COLUMN $col $type");
    }
}

$check = $conn->query("SELECT id FROM roster_book WHERE teacher_id=$teacher_id AND date='$date' AND class_name='$class_name'");
if ($check && $check->num_rows > 0) {
    $sql = "UPDATE roster_book SET
                reg_boys=$reg_boys, reg_girls=$reg_girls,
                pres_boys=$pres_boys, pres_girls=$pres_girls,
                abs_boys=$abs_boys, abs_girls=$abs_girls,
                perm_boys=$perm_boys, perm_girls=$perm_girls,
                sick_boys=$sick_boys, sick_girls=$sick_girls,
                remarks='$remarks', submitted_at=NOW()
            WHERE teacher_id=$teacher_id AND date='$date' AND class_name='$class_name'";
} else {
    $sql = "INSERT INTO roster_book
                (teacher_id, date, class_name, reg_boys, reg_girls, pres_boys, pres_girls,
                 abs_boys, abs_girls, perm_boys, perm_girls, sick_boys, sick_girls, remarks)
            VALUES
                ($teacher_id, '$date', '$class_name', $reg_boys, $reg_girls,
                 $pres_boys, $pres_girls, $abs_boys, $abs_girls,
                 $perm_boys, $perm_girls, $sick_boys, $sick_girls, '$remarks')";
}

if ($conn->query($sql)) {
    respond(['message' => 'Roster submitted successfully']);
} else {
    respond(['error' => 'Failed: ' . $conn->error], 500);
}
