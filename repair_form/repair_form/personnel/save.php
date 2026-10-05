<?php
require_once __DIR__ . '/config_personnel.php';
require_login();

$employeeCode = trim($_POST['employee_code'] ?? '');
$prefix = trim($_POST['prefix'] ?? '');
$fullname = trim($_POST['fullname'] ?? '');
$position = trim($_POST['position_name'] ?? '');
$department = trim($_POST['department'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$location = trim($_POST['room_location'] ?? '');
$status = $_POST['status'] ?? 'active';
$notes = trim($_POST['notes'] ?? '');

if ($fullname === '') die('กรุณากรอกชื่อ - นามสกุล');

if (!in_array($status, ['active','inactive'], true)) $status='active';

$stmt = $conn->prepare("
INSERT INTO personnel
(employee_code,prefix,fullname,position_name,department,phone,email,room_location,status,notes)
VALUES (?,?,?,?,?,?,?,?,?,?)
");
if (!$stmt) die('SQL ERROR: '.h($conn->error));

$stmt->bind_param(
    'ssssssssss',
    $employeeCode,$prefix,$fullname,$position,$department,
    $phone,$email,$location,$status,$notes
);

if (!$stmt->execute()) {
    $err=$stmt->error;
    $stmt->close();
    die('<h3>SQL ERROR</h3><pre>'.h($err).'</pre>');
}
$stmt->close();

header('Location: index.php?message='.urlencode('เพิ่มข้อมูลบุคลากรเรียบร้อยแล้ว'));
exit;
?>
