<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add.php'); exit;
}
if (!personnel_verify_csrf($_POST['csrf_token'] ?? '')) {
    personnel_flash('error', 'คำขอไม่ถูกต้อง กรุณาลองใหม่');
    header('Location: add.php'); exit;
}
if (!personnel_schema_ready()) {
    header('Location: install.php'); exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$prefix = trim((string)($_POST['prefix'] ?? ''));
$firstName = trim((string)($_POST['first_name'] ?? ''));
$lastName = trim((string)($_POST['last_name'] ?? ''));
$firstNameEn = trim((string)($_POST['first_name_en'] ?? ''));
$nickname = trim((string)($_POST['nickname'] ?? ''));
$birthDate = trim((string)($_POST['birth_date'] ?? ''));
$gender = trim((string)($_POST['gender'] ?? ''));
$nationalId = personnel_clean_national_id($_POST['national_id'] ?? '');
$email = trim((string)($_POST['email'] ?? ''));
$employeeCode = trim((string)($_POST['employee_code'] ?? ''));
$positionName = trim((string)($_POST['position_name'] ?? ''));
$departmentId = ($_POST['department_id'] ?? '') !== '' ? (int)$_POST['department_id'] : null;
$department = trim((string)($_POST['department'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$roomLocation = trim((string)($_POST['room_location'] ?? ''));
$status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
$notes = trim((string)($_POST['notes'] ?? ''));
$fullname = trim($prefix . ' ' . $firstName . ' ' . $lastName);

$errors = [];
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) $errors[] = 'Username ต้องเป็นภาษาอังกฤษ/ตัวเลข 3-50 ตัวอักษร';
if ($prefix === '') $errors[] = 'กรุณาเลือกคำนำหน้า';
if ($firstName === '') $errors[] = 'กรุณากรอกชื่อ';
if ($lastName === '') $errors[] = 'กรุณากรอกนามสกุล';
if ($birthDate === '' || strtotime($birthDate) === false) $errors[] = 'กรุณาระบุวันเกิด';
if (!in_array($gender, personnel_gender_options(), true)) $errors[] = 'กรุณาเลือกเพศ';
if (!personnel_validate_national_id($nationalId)) $errors[] = 'เลขประจำตัวประชาชนต้องมี 13 หลัก';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';

if ($errors) {
    personnel_flash('error', implode(' / ', $errors));
    header('Location: add.php'); exit;
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username); $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) throw new Exception('Username นี้มีผู้ใช้งานแล้ว');
    $stmt->close();

    $stmt = $conn->prepare('SELECT id FROM personnel WHERE national_id = ? LIMIT 1');
    $stmt->bind_param('s', $nationalId); $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) throw new Exception('เลขประจำตัวประชาชนนี้มีอยู่ในระบบแล้ว');
    $stmt->close();

    $passwordHash = password_hash('123456', PASSWORD_DEFAULT);
    $userStatus = $status === 'active' ? 'active' : 'inactive';
    $role = 'user';
    $stmt = $conn->prepare('INSERT INTO users (fullname, username, password, email, department, department_id, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssiss', $fullname, $username, $passwordHash, $email, $department, $departmentId, $userStatus, $role);
    if (!$stmt->execute()) throw new Exception('สร้างบัญชีเข้าสู่ระบบไม่สำเร็จ: ' . $stmt->error);
    $userId = (int)$conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO personnel (user_id, employee_code, username, prefix, first_name, last_name, fullname, first_name_en, nickname, birth_date, gender, national_id, position_name, department, department_id, phone, email, room_location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssssssssssssisssss', $userId, $employeeCode, $username, $prefix, $firstName, $lastName, $fullname, $firstNameEn, $nickname, $birthDate, $gender, $nationalId, $positionName, $department, $departmentId, $phone, $email, $roomLocation, $status, $notes);
    if (!$stmt->execute()) throw new Exception('บันทึกข้อมูลบุคลากรไม่สำเร็จ: ' . $stmt->error);
    $personnelId = (int)$conn->insert_id;
    $stmt->close();

    $conn->commit();
    personnel_log_action($personnelId, 'create', 'สร้างบุคลากรและบัญชีผู้ใช้ Username: ' . $username);
    personnel_flash('success', 'เพิ่มบุคลากรเรียบร้อยแล้ว | Username: ' . $username . ' | รหัสผ่านเริ่มต้น: 123456');
    header('Location: detail.php?id=' . $personnelId); exit;
} catch (Throwable $e) {
    $conn->rollback();
    personnel_flash('error', $e->getMessage());
    header('Location: add.php'); exit;
}
