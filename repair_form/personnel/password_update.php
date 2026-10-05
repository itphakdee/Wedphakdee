<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_login();
if (!personnel_is_admin()) {
    http_response_code(403);
    die('เฉพาะผู้ดูแลระบบเท่านั้น');
}
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
$stmt = $conn->prepare('SELECT id,user_id,username FROM personnel WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$person = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$person || empty($person['user_id'])) {
    personnel_flash('error', 'ไม่พบบัญชีผู้ใช้ที่เชื่อมกับบุคลากรนี้');
    header('Location:edit.php?id=' . $id);
    exit;
}
if (isset($_POST['reset_default'])) {
    $password = '123456';
} else {
    $password = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (strlen($password) < 6) {
        personnel_flash('error', 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
        header('Location:edit.php?id=' . $id);
        exit;
    }
    if ($password !== $confirm) {
        personnel_flash('error', 'รหัสผ่านยืนยันไม่ตรงกัน');
        header('Location:edit.php?id=' . $id);
        exit;
    }
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$uid = (int)$person['user_id'];
$stmt = $conn->prepare('UPDATE users SET password=? WHERE id=?');
$stmt->bind_param('si', $hash, $uid);
if (!$stmt->execute()) {
    personnel_flash('error', 'เปลี่ยนรหัสผ่านไม่สำเร็จ: ' . $stmt->error);
    $stmt->close();
    header('Location:edit.php?id=' . $id);
    exit;
}
$stmt->close();
personnel_log_action($id, 'password_change', isset($_POST['reset_default']) ? 'รีเซ็ตรหัสผ่านเป็นค่าเริ่มต้น' : 'ผู้ดูแลระบบเปลี่ยนรหัสผ่าน');
personnel_flash('success', isset($_POST['reset_default']) ? 'รีเซ็ตรหัสผ่านเป็น 123456 แล้ว' : 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
header('Location:edit.php?id=' . $id);
exit;
