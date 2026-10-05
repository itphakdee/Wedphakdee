<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('create');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add.php');
    exit;
}
if (!personnel_verify_csrf($_POST['csrf_token'] ?? '')) {
    personnel_flash('error', 'คำขอไม่ถูกต้อง กรุณาลองใหม่');
    header('Location:add.php');
    exit;
}
if (!personnel_schema_ready()) {
    header('Location:install.php');
    exit;
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
$workGroupId = (int)($_POST['work_group_id'] ?? 0);
$divisionId = (int)($_POST['division_id'] ?? 0);
$departmentId = (int)($_POST['department_id'] ?? 0);
$appointmentDate = trim((string)($_POST['appointment_date'] ?? ''));
$positionNumber = trim((string)($_POST['position_number'] ?? ''));
$professionalLicenseNo = trim((string)($_POST['professional_license_no'] ?? ''));
$licenseIssueDate = trim((string)($_POST['license_issue_date'] ?? ''));
$positionId = (int)($_POST['position_id'] ?? 0);
$levelId = (int)($_POST['level_id'] ?? 0);
$currentStatusId = (int)($_POST['current_status_id'] ?? 0);
$civilGroupId = (int)($_POST['civil_service_group_id'] ?? 0);
$civilTypeId = (int)($_POST['civil_service_type_id'] ?? 0);
$personnelGroupId = (int)($_POST['personnel_group_id'] ?? 0);
$affiliation = trim((string)($_POST['affiliation'] ?? ''));
$salary = personnel_money_value($_POST['salary'] ?? '');
$positionAllowance = personnel_money_value($_POST['position_allowance'] ?? '');
$phone = trim((string)($_POST['phone'] ?? ''));
$roomLocation = trim((string)($_POST['room_location'] ?? ''));
$status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
$notes = trim((string)($_POST['notes'] ?? ''));
$fullname = trim($prefix . ' ' . $firstName . ' ' . $lastName);
$department = personnel_department_name($departmentId) ?: '';
$positionName = personnel_career_option_name($positionId, 'position') ?: '';

$errors = [];
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) $errors[] = 'Username ต้องเป็นภาษาอังกฤษ/ตัวเลข 3-50 ตัวอักษร';
if ($prefix === '') $errors[] = 'กรุณาเลือกคำนำหน้า';
if ($firstName === '') $errors[] = 'กรุณากรอกชื่อ';
if ($lastName === '') $errors[] = 'กรุณากรอกนามสกุล';
if ($birthDate === '' || strtotime($birthDate) === false) $errors[] = 'กรุณาระบุวันเกิด';
if (!in_array($gender, personnel_gender_options(), true)) $errors[] = 'กรุณาเลือกเพศ';
if (!personnel_validate_national_id($nationalId)) $errors[] = 'เลขประจำตัวประชาชนต้องมี 13 หลัก';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
$requiredCareer = [['work_group_id', $workGroupId, 'work_group', 'กลุ่มงาน'], ['division_id', $divisionId, 'division', 'ฝ่าย/แผนก'], ['position_id', $positionId, 'position', 'ตำแหน่ง'], ['level_id', $levelId, 'level', 'ระดับ'], ['current_status_id', $currentStatusId, 'current_status', 'สถานะปัจจุบัน'], ['civil_service_group_id', $civilGroupId, 'civil_group', 'กลุ่มข้าราชการ'], ['civil_service_type_id', $civilTypeId, 'civil_type', 'ประเภทข้าราชการ'], ['personnel_group_id', $personnelGroupId, 'personnel_group', 'กลุ่มบุคลากร']];
foreach ($requiredCareer as $r) {
    if ($r[1] <= 0 || !personnel_validate_career_option($r[1], $r[2])) $errors[] = 'กรุณาเลือก' . $r[3] . 'จากรายการ';
}
if ($departmentId <= 0 || $department === '') $errors[] = 'กรุณาเลือกหน่วยงาน';
if ($appointmentDate === '' || strtotime($appointmentDate) === false) $errors[] = 'กรุณาระบุวันที่บรรจุ';
if ($licenseIssueDate !== '' && strtotime($licenseIssueDate) === false) $errors[] = 'วันที่รับใบประกอบวิชาชีพไม่ถูกต้อง';
if (($_POST['salary'] ?? '') !== '' && $salary === null) $errors[] = 'เงินเดือนต้องเป็นตัวเลข';
if (($_POST['position_allowance'] ?? '') !== '' && $positionAllowance === null) $errors[] = 'เงินประจำตำแหน่งต้องเป็นตัวเลข';
if ($errors) {
    personnel_flash('error', implode(' / ', $errors));
    header('Location:add.php');
    exit;
}

try {
    $conn->begin_transaction();
    $stmt = $conn->prepare('SELECT id FROM users WHERE username=? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) throw new Exception('Username นี้มีผู้ใช้งานแล้ว');
    $stmt->close();
    $stmt = $conn->prepare('SELECT id FROM personnel WHERE national_id=? LIMIT 1');
    $stmt->bind_param('s', $nationalId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) throw new Exception('เลขประจำตัวประชาชนนี้มีอยู่ในระบบแล้ว');
    $stmt->close();
    $passwordHash = password_hash('123456', PASSWORD_DEFAULT);
    $userStatus = $status === 'active' ? 'active' : 'inactive';
    $role = 'user';
    $stmt = $conn->prepare('INSERT INTO users (fullname,username,password,email,department,department_id,status,role) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->bind_param('sssssiss', $fullname, $username, $passwordHash, $email, $department, $departmentId, $userStatus, $role);
    if (!$stmt->execute()) throw new Exception('สร้างบัญชีเข้าสู่ระบบไม่สำเร็จ: ' . $stmt->error);
    $userId = (int)$conn->insert_id;
    $stmt->close();

    $sql = 'INSERT INTO personnel (user_id,employee_code,username,prefix,first_name,last_name,fullname,first_name_en,nickname,birth_date,gender,national_id,work_group_id,division_id,appointment_date,position_number,professional_license_no,license_issue_date,position_id,position_name,level_id,current_status_id,civil_service_group_id,civil_service_type_id,personnel_group_id,affiliation,salary,position_allowance,department,department_id,phone,email,room_location,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('เตรียมคำสั่งบันทึกไม่สำเร็จ: ' . $conn->error);
    $params = [$userId, $employeeCode, $username, $prefix, $firstName, $lastName, $fullname, $firstNameEn, $nickname, $birthDate, $gender, $nationalId, $workGroupId, $divisionId, $appointmentDate, $positionNumber, $professionalLicenseNo, $licenseIssueDate, $positionId, $positionName, $levelId, $currentStatusId, $civilGroupId, $civilTypeId, $personnelGroupId, $affiliation, $salary, $positionAllowance, $department, $departmentId, $phone, $email, $roomLocation, $status, $notes];
    $types = 'i' . str_repeat('s', 11) . 'ii' . str_repeat('s', 4) . 'is' . str_repeat('i', 5) . 'sddsi' . str_repeat('s', 5);
    personnel_bind_params($stmt, $types, $params);
    if (!$stmt->execute()) throw new Exception('บันทึกข้อมูลบุคลากรไม่สำเร็จ: ' . $stmt->error);
    $personnelId = (int)$conn->insert_id;
    $stmt->close();
    $conn->commit();
    personnel_log_action($personnelId, 'create', 'สร้างบุคลากรและข้อมูลอาชีพ Username: ' . $username);
    personnel_flash('success', 'เพิ่มบุคลากรและข้อมูลอาชีพเรียบร้อยแล้ว | Username: ' . $username . ' | รหัสผ่านเริ่มต้น: 123456');
    header('Location:detail.php?id=' . $personnelId);
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    personnel_flash('error', $e->getMessage());
    header('Location:add.php');
    exit;
}
