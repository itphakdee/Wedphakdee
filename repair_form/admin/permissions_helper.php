<?php
require_once __DIR__ . '/config_admin.php';

function is_admin_user()
{
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $uid = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT role, status FROM users WHERE id=? LIMIT 1");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $stmt->bind_result($role, $status);

    $ok = $stmt->fetch() && $status === 'active' && $role === 'admin';
    $stmt->close();

    return $ok;
}

/**
 * Admin ผ่านทุก permission โดยอัตโนมัติ
 */
function has_permission($key)
{
    global $conn;

    if (is_admin_user()) {
        return true;
    }

    if (!isset($_SESSION['user_id']) || trim((string)$key) === '') {
        return false;
    }

    $uid = (int)$_SESSION['user_id'];
    $permission = trim((string)$key);

    // บัญชีต้อง active ก่อน
    $stmt = $conn->prepare("
        SELECT id
        FROM users
        WHERE id=? AND status='active'
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        return false;
    }

    $stmt->close();

    $stmt = $conn->prepare("
        SELECT id
        FROM user_permissions
        WHERE user_id=? AND permission_key=?
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('is', $uid, $permission);
    $stmt->execute();
    $stmt->store_result();

    $allowed = $stmt->num_rows > 0;
    $stmt->close();

    return $allowed;
}

function can_manage_action($key)
{
    // ผู้ใช้ role=user ไม่ได้รับสิทธิ์จัดการรายการ แม้จะมี permission จากข้อมูลเก่า
    if (isset($_SESSION['role']) && strtolower((string)$_SESSION['role']) === 'user') {
        return false;
    }

    return has_permission($key);
}

function require_manage_action($key)
{
    if (!can_manage_action($key)) {
        http_response_code(403);
        die('403 Forbidden: บัญชีนี้ไม่มีสิทธิ์จัดการรายการ');
    }
}

function require_permission($key)
{
    if (!has_permission($key)) {
        http_response_code(403);
        die('403 Forbidden: ไม่มีสิทธิ์ใช้งานส่วนนี้');
    }
}
?>
