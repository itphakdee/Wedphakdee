<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('edit');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:index.php');
    exit;
}
if (!personnel_verify_csrf($_POST['csrf_token'] ?? '')) {
    personnel_flash('error', 'คำขอไม่ถูกต้อง');
    header('Location:index.php');
    exit;
}
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location:index.php');
    exit;
}
$stmt = $conn->prepare('SELECT * FROM personnel WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$old) {
    http_response_code(404);
    die('ไม่พบข้อมูลบุคลากร');
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
if ($employeeCode === '') {
    $employeeCode = null;
}
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
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) $errors[] = 'Username ไม่ถูกต้อง';
if ($prefix === '' || $firstName === '' || $lastName === '') $errors[] = 'กรุณากรอกชื่อ-นามสกุลและคำนำหน้า';
if ($birthDate === '' || strtotime($birthDate) === false) $errors[] = 'กรุณาระบุวันเกิด';
if (!in_array($gender, personnel_gender_options(), true)) $errors[] = 'กรุณาเลือกเพศ';
if (!personnel_validate_national_id($nationalId)) $errors[] = 'เลขประจำตัวประชาชนต้องมี 13 หลัก';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'อีเมลไม่ถูกต้อง';
$requiredCareer = [[$workGroupId, 'work_group', 'กลุ่มงาน'], [$divisionId, 'division', 'ฝ่าย/แผนก'], [$positionId, 'position', 'ตำแหน่ง'], [$levelId, 'level', 'ระดับ'], [$currentStatusId, 'current_status', 'สถานะปัจจุบัน'], [$civilGroupId, 'civil_group', 'กลุ่มข้าราชการ'], [$civilTypeId, 'civil_type', 'ประเภทข้าราชการ'], [$personnelGroupId, 'personnel_group', 'กลุ่มบุคลากร']];
foreach ($requiredCareer as $r) {
    if ($r[0] <= 0 || !personnel_validate_career_option($r[0], $r[1])) $errors[] = 'กรุณาเลือก' . $r[2] . 'จากรายการ';
}
if ($departmentId <= 0 || $department === '') $errors[] = 'กรุณาเลือกหน่วยงาน';
if ($appointmentDate === '' || strtotime($appointmentDate) === false) $errors[] = 'กรุณาระบุวันที่บรรจุ';
if ($licenseIssueDate !== '' && strtotime($licenseIssueDate) === false) $errors[] = 'วันที่รับใบประกอบวิชาชีพไม่ถูกต้อง';
if (($_POST['salary'] ?? '') !== '' && $salary === null) $errors[] = 'เงินเดือนต้องเป็นตัวเลข';
if (($_POST['position_allowance'] ?? '') !== '' && $positionAllowance === null) $errors[] = 'เงินประจำตำแหน่งต้องเป็นตัวเลข';
if ($errors) {
    personnel_flash('error', implode(' / ', $errors));
    header('Location:edit.php?id=' . $id);
    exit;
}
try {
    $conn->begin_transaction();
    $stmt = $conn->prepare('SELECT id FROM personnel WHERE national_id=? AND id<>? LIMIT 1');
    $stmt->bind_param('si', $nationalId, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) throw new Exception('เลขประจำตัวประชาชนนี้มีอยู่ในระบบแล้ว');
    $stmt->close();
    $excludeUserId = (int)($old['user_id'] ?: 0);
    $stmt = $conn->prepare('SELECT id FROM users WHERE username=? AND id<>? LIMIT 1');
    $stmt->bind_param('si', $username, $excludeUserId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) throw new Exception('Username นี้มีผู้ใช้งานแล้ว');
    $stmt->close();
    $userId = (int)($old['user_id'] ?: 0);
    $userStatus = $status === 'active' ? 'active' : 'inactive';
    if ($userId > 0) {
        $stmt = $conn->prepare('UPDATE users SET fullname=?,username=?,email=?,department=?,department_id=?,status=? WHERE id=?');
        $stmt->bind_param('ssssisi', $fullname, $username, $email, $department, $departmentId, $userStatus, $userId);
        if (!$stmt->execute()) throw new Exception('อัปเดตบัญชีผู้ใช้ไม่สำเร็จ: ' . $stmt->error);
        $stmt->close();
    } else {
        $passwordHash = password_hash('123456', PASSWORD_DEFAULT);
        $role = 'user';
        $stmt = $conn->prepare('INSERT INTO users (fullname,username,password,email,department,department_id,status,role) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->bind_param('sssssiss', $fullname, $username, $passwordHash, $email, $department, $departmentId, $userStatus, $role);
        if (!$stmt->execute()) throw new Exception('สร้างบัญชีผู้ใช้ไม่สำเร็จ: ' . $stmt->error);
        $userId = (int)$conn->insert_id;
        $stmt->close();
    }
    $sql = 'UPDATE personnel SET user_id=?,employee_code=?,username=?,prefix=?,first_name=?,last_name=?,fullname=?,first_name_en=?,nickname=?,birth_date=?,gender=?,national_id=?,work_group_id=?,division_id=?,appointment_date=?,position_number=?,professional_license_no=?,license_issue_date=?,position_id=?,position_name=?,level_id=?,current_status_id=?,civil_service_group_id=?,civil_service_type_id=?,personnel_group_id=?,affiliation=?,salary=?,position_allowance=?,department=?,department_id=?,phone=?,email=?,room_location=?,status=?,notes=? WHERE id=?';
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('เตรียมคำสั่งแก้ไขไม่สำเร็จ: ' . $conn->error);
    $params = [$userId, $employeeCode, $username, $prefix, $firstName, $lastName, $fullname, $firstNameEn, $nickname, $birthDate, $gender, $nationalId, $workGroupId, $divisionId, $appointmentDate, $positionNumber, $professionalLicenseNo, $licenseIssueDate, $positionId, $positionName, $levelId, $currentStatusId, $civilGroupId, $civilTypeId, $personnelGroupId, $affiliation, $salary, $positionAllowance, $department, $departmentId, $phone, $email, $roomLocation, $status, $notes, $id];
    $types = 'i' . str_repeat('s', 11) . 'ii' . str_repeat('s', 4) . 'is' . str_repeat('i', 5) . 'sddsi' . str_repeat('s', 5) . 'i';
    personnel_bind_params($stmt, $types, $params);
    if (!$stmt->execute()) throw new Exception('อัปเดตข้อมูลบุคลากรไม่สำเร็จ: ' . $stmt->error);
    $stmt->close();
    $conn->commit();
    personnel_log_action($id, 'update', 'แก้ไขข้อมูลบุคลากรและข้อมูลอาชีพ Username: ' . $username);
    personnel_flash('success', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    header('Location:detail.php?id=' . $id);
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    personnel_flash('error', $e->getMessage());
    header('Location:edit.php?id=' . $id);
    exit;
}
