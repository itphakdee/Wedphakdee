<?php
require_once __DIR__ . '/../../config.php';

/*
 * config_admin.php can be loaded from a component that is included inside a
 * function (for example the leave module layout). In that case PHP keeps
 * included variables in the caller scope, while require_once may skip
 * config.php because it was already loaded globally. Reuse/promote the global
 * connection explicitly so $conn is always available here and to permission
 * helper functions.
 */
if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}
if (isset($conn) && ($conn instanceof mysqli)) {
    $GLOBALS['conn'] = $conn;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('ไม่พบการเชื่อมต่อฐานข้อมูล');
}

$conn->set_charset('utf8mb4');

function ah($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/**
 * ตรวจสอบว่าเป็น Admin จริง
 * - ต้อง login
 * - role ต้องเป็น admin
 * - บัญชีต้อง active
 *
 * ตรวจจากฐานข้อมูลทุกครั้ง เพื่อป้องกันกรณี Admin ถูกระงับ
 * แต่ session เดิมยังค้างอยู่
 */
function admin_only()
{
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }

    $userId = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT role, status FROM users WHERE id=? LIMIT 1");

    if (!$stmt) {
        http_response_code(500);
        die('ไม่สามารถตรวจสอบสิทธิ์ผู้ดูแลระบบได้');
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($role, $status);

    if (!$stmt->fetch()) {
        $stmt->close();
        session_unset();
        session_destroy();
        header('Location: ../../login.php');
        exit;
    }

    $stmt->close();

    if ($status !== 'active' || $role !== 'admin') {
        http_response_code(403);
        die('403 Forbidden: สำหรับ Admin เท่านั้น');
    }

    // sync role/status กลับเข้า session
    $_SESSION['role'] = $role;
}
?>
