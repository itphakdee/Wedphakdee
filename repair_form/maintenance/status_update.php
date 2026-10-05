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

$oldRequest = null;
$stmtOld = $conn->prepare("SELECT sender_name, department, system_type, details, location, priority, status, technician_name FROM maintenance_requests WHERE id=? LIMIT 1");
if ($stmtOld) {
    $stmtOld->bind_param('i', $id);
    $stmtOld->execute();
    $oldRequest = $stmtOld->get_result()->fetch_assoc();
    $stmtOld->close();
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
    $statusMeta = maintenance_status_meta($status);
    $systemLabel = $oldRequest ? maintenance_system_meta($oldRequest['system_type'])['label'] : '-';
    $priorityLabel = $oldRequest ? maintenance_priority_meta($oldRequest['priority'])['label'] : '-';
    $sender = $oldRequest['sender_name'] ?? '-';
    $department = $oldRequest['department'] ?? '-';
    $location = trim((string)($oldRequest['location'] ?? ''));
    $responsible = trim((string)$technicianName) !== '' ? $technicianName : ($oldRequest['technician_name'] ?? '-');
    $message =
        "🛠️ อัปเดตสถานะงานซ่อมบำรุง\n" .
        "โรงพยาบาลภักดีชุมพล\n" .
        "━━━━━━━━━━━━━━━━━━\n" .
        "เลขที่งาน: #{$id}\n" .
        "ผู้แจ้ง: {$sender}\n" .
        "แผนก: {$department}\n" .
        "ระบบ: {$systemLabel}\n" .
        "สถานที่: " . ($location !== '' ? $location : '-') . "\n" .
        "ผู้รับผิดชอบ: {$responsible}\n" .
        "ความเร่งด่วน: {$priorityLabel}\n" .
        "สถานะใหม่: {$statusMeta['label']}\n" .
        "หมายเหตุ: " . ($note !== '' ? $note : '-') . "\n" .
        "เวลาอัปเดต: " . date('d/m/Y H:i') . " น.\n" .
        "━━━━━━━━━━━━━━━━━━";
    $lineResult = maintenance_send_line($message, 'maintenance_status_updated');
    maintenance_update_line_result($id, $lineResult);

    if ($status === 'completed') {
        $msg = 'บันทึกสถานะงาน #' . $id . ' เป็นเสร็จสิ้นแล้ว ผู้แจ้งงานสามารถให้ Feedback ได้';
    } else {
        $msg = 'บันทึกสถานะงาน #' . $id . ' เรียบร้อยแล้ว';
    }
    if (!empty($lineResult['ok'])) {
        $msg .= ' และส่งแจ้งเตือน LINE สำเร็จ';
        maintenance_flash('success', $msg);
    } else {
        $lineError = trim((string)($lineResult['error'] ?? ''));
        maintenance_flash('warning', $msg . ' แต่ส่ง LINE ไม่สำเร็จ' . ($lineError !== '' ? ': ' . $lineError : ''));
    }
} else {
    maintenance_flash('error', 'อัปเดตสถานะไม่สำเร็จ: ' . $stmt->error);
}
$stmt->close();
header('Location: detail.php?id=' . $id . '#manage');
exit;
