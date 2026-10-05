<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_permission('delete');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0 || !maintenance_verify_csrf($_POST['csrf_token'] ?? '')) {
    maintenance_flash('error', 'คำขอลบรายการไม่ถูกต้อง');
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM maintenance_requests WHERE id=? LIMIT 1");
$stmt->bind_param('i', $id);
if ($stmt->execute() && $stmt->affected_rows > 0) maintenance_flash('success', 'ลบรายการ #' . $id . ' เรียบร้อยแล้ว');
else maintenance_flash('error', 'ไม่พบรายการที่ต้องการลบ หรือไม่สามารถลบได้');
$stmt->close();
header('Location: index.php');
exit;
