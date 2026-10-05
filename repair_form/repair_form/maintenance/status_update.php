<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_permission('manage');

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
    header('Location: detail.php?id=' . $id . '#manage');
    exit;
}

$status = trim($_POST['status'] ?? '');
$technicianId = (int)($_POST['technician_id'] ?? 0);
$note = trim($_POST['technician_note'] ?? '');
$allowedStatuses = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'];

if (!in_array($status, $allowedStatuses, true)) {
    maintenance_flash('error', 'สถานะงานไม่ถูกต้อง');
    header('Location: detail.php?id=' . $id . '#manage');
    exit;
}

$technicianName = null;
if ($technicianId > 0) {
    $stmtTech = $conn->prepare("SELECT name FROM technicians WHERE id=? AND status='ใช้งาน' LIMIT 1");
    $stmtTech->bind_param('i', $technicianId);
    $stmtTech->execute();
    $stmtTech->bind_result($technicianName);
    $stmtTech->fetch();
    $stmtTech->close();
    if (!$technicianName) {
        maintenance_flash('error', 'ไม่พบข้อมูลช่างที่เลือก');
        header('Location: detail.php?id=' . $id . '#manage');
        exit;
    }
}

if ($technicianId > 0 && $status === 'pending') {
    $status = 'assigned';
}

$completedAt = $status === 'completed' ? date('Y-m-d H:i:s') : null;
$stmt = $conn->prepare("UPDATE maintenance_requests
    SET status=?, technician_id=NULLIF(?,0), technician_name=?, technician_note=?, completed_at=?
    WHERE id=?");
$stmt->bind_param('sisssi', $status, $technicianId, $technicianName, $note, $completedAt, $id);

if ($stmt->execute()) {
    if ($status === 'completed') {
        maintenance_flash('success', 'บันทึกสถานะงาน #' . $id . ' เป็นเสร็จสิ้นแล้ว ผู้แจ้งงานสามารถให้ Feedback ได้');
    } else {
        maintenance_flash('success', 'บันทึกสถานะงาน #' . $id . ' เรียบร้อยแล้ว');
    }
} else {
    maintenance_flash('error', 'อัปเดตสถานะไม่สำเร็จ: ' . $stmt->error);
}
$stmt->close();
header('Location: detail.php?id=' . $id . '#manage');
exit;
