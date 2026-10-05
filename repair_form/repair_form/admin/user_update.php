<?php
require_once __DIR__ . '/admin_guard.php';

$id = (int)($_POST['id'] ?? 0);
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$department = trim($_POST['department'] ?? '');
$departmentId = ($_POST['department_id'] ?? '') !== ''
    ? (int)$_POST['department_id']
    : null;
$role = $_POST['role'] ?? 'user';
$status = $_POST['status'] ?? 'active';

if ($id <= 0 || $fullname === '') {
    die('ข้อมูลไม่ครบ');
}

if (!in_array($role, ['admin','technician','manager','user'], true)) {
    $role = 'user';
}

if (!in_array($status, ['active','inactive','banned'], true)) {
    $status = 'active';
}

// ไม่ให้ Admin คนปัจจุบันปิด/ลดสิทธิ์บัญชีตัวเอง
if ($id === (int)$_SESSION['user_id']) {
    $role = 'admin';
    $status = 'active';
}

$stmt = $conn->prepare("
    UPDATE users
    SET fullname=?,
        email=?,
        department=?,
        department_id=?,
        role=?,
        status=?
    WHERE id=?
");

if (!$stmt) {
    die('SQL ERROR: ' . ah($conn->error));
}

$stmt->bind_param(
    'sssissi',
    $fullname,
    $email,
    $department,
    $departmentId,
    $role,
    $status,
    $id
);

if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    die('<h3>SQL ERROR</h3><pre>'.ah($err).'</pre>');
}

$stmt->close();

header('Location: users.php?message='.urlencode('แก้ไขผู้ใช้งานเรียบร้อยแล้ว'));
exit;
?>
