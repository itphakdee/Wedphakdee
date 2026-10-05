<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_permission('create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!maintenance_table_exists()) {
    maintenance_flash('error', 'ยังไม่ได้ติดตั้งฐานข้อมูลงานซ่อมบำรุง');
    header('Location: index.php');
    exit;
}

if (!maintenance_verify_csrf($_POST['csrf_token'] ?? '')) {
    maintenance_flash('error', 'ไม่สามารถตรวจสอบความปลอดภัยของแบบฟอร์มได้ กรุณาลองใหม่');
    header('Location: index.php#new-request');
    exit;
}

$senderName = trim($_POST['sender_name'] ?? '');
$department = trim($_POST['department'] ?? '');
$systemType = trim($_POST['system_type'] ?? '');
$technicianId = (int)($_POST['technician_id'] ?? 0);
$details = trim($_POST['details'] ?? '');
$location = trim($_POST['location'] ?? '');
$priority = trim($_POST['priority'] ?? 'normal');
$userId = (int)($_SESSION['user_id'] ?? 0);

$allowedSystems = ['ประปา', 'ไฟฟ้า', 'แอร์'];
$allowedPriorities = ['normal', 'urgent', 'emergency'];

if ($senderName === '' || $department === '' || !in_array($systemType, $allowedSystems, true) || $technicianId <= 0 || $details === '') {
    maintenance_flash('error', 'กรุณากรอกชื่อผู้ส่ง แผนก ระบบที่แจ้งซ่อม ช่าง และรายละเอียดให้ครบถ้วน');
    header('Location: index.php#new-request');
    exit;
}

if (!in_array($priority, $allowedPriorities, true)) {
    $priority = 'normal';
}

$technicianName = '';
$stmtTech = $conn->prepare("SELECT name FROM technicians WHERE id=? AND status='ใช้งาน' LIMIT 1");
if ($stmtTech) {
    $stmtTech->bind_param('i', $technicianId);
    $stmtTech->execute();
    $stmtTech->bind_result($technicianName);
    $stmtTech->fetch();
    $stmtTech->close();
}

if (trim((string)$technicianName) === '') {
    maintenance_flash('error', 'ไม่พบช่างที่เลือก หรือช่างไม่ได้อยู่ในสถานะใช้งาน');
    header('Location: index.php#new-request');
    exit;
}

$status = 'assigned';
$stmt = $conn->prepare("INSERT INTO maintenance_requests
    (user_id, sender_name, department, system_type, technician_id, technician_name, details, location, priority, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    maintenance_flash('error', 'ไม่สามารถเตรียมคำสั่งบันทึกข้อมูลได้: ' . $conn->error);
    header('Location: index.php#new-request');
    exit;
}

$stmt->bind_param('isssisssss', $userId, $senderName, $department, $systemType, $technicianId, $technicianName, $details, $location, $priority, $status);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();
    maintenance_flash('success', 'บันทึกงานซ่อมบำรุง #' . $newId . ' เรียบร้อยแล้ว และมอบหมายช่างแล้ว');
    header('Location: detail.php?id=' . $newId);
    exit;
}

$error = $stmt->error;
$stmt->close();
maintenance_flash('error', 'บันทึกข้อมูลไม่สำเร็จ: ' . $error);
header('Location: index.php#new-request');
exit;
