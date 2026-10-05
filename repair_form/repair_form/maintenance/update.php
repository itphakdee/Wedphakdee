<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_permission('edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

if (!maintenance_verify_csrf($_POST['csrf_token'] ?? '')) {
    maintenance_flash('error', 'ไม่สามารถตรวจสอบความปลอดภัยของแบบฟอร์มได้');
    header('Location: edit.php?id=' . $id);
    exit;
}

$senderName = trim($_POST['sender_name'] ?? '');
$department = trim($_POST['department'] ?? '');
$systemType = trim($_POST['system_type'] ?? '');
$technicianId = (int)($_POST['technician_id'] ?? 0);
$details = trim($_POST['details'] ?? '');
$location = trim($_POST['location'] ?? '');
$priority = trim($_POST['priority'] ?? 'normal');

if ($senderName === '' || $department === '' || !in_array($systemType, ['ประปา','ไฟฟ้า','แอร์'], true) || $technicianId <= 0 || $details === '') {
    maintenance_flash('error', 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
    header('Location: edit.php?id=' . $id);
    exit;
}
if (!in_array($priority, ['normal','urgent','emergency'], true)) $priority='normal';

$technicianName = '';
$stmtTech = $conn->prepare("SELECT name FROM technicians WHERE id=? AND status='ใช้งาน' LIMIT 1");
$stmtTech->bind_param('i', $technicianId);
$stmtTech->execute();
$stmtTech->bind_result($technicianName);
$stmtTech->fetch();
$stmtTech->close();
if (!$technicianName) {
    maintenance_flash('error', 'ไม่พบช่างที่เลือก');
    header('Location: edit.php?id=' . $id);
    exit;
}

$stmt = $conn->prepare("UPDATE maintenance_requests SET sender_name=?, department=?, system_type=?, technician_id=?, technician_name=?, details=?, location=?, priority=?, status=IF(status='pending','assigned',status) WHERE id=?");
$stmt->bind_param('sssissssi', $senderName, $department, $systemType, $technicianId, $technicianName, $details, $location, $priority, $id);
if ($stmt->execute()) maintenance_flash('success', 'บันทึกการแก้ไขรายการ #' . $id . ' เรียบร้อยแล้ว');
else maintenance_flash('error', 'แก้ไขข้อมูลไม่สำเร็จ: ' . $stmt->error);
$stmt->close();
header('Location: detail.php?id=' . $id);
exit;
