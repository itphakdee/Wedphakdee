<?php
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');

$permissionHelper = __DIR__ . '/../admin/permissions_helper.php';
if (is_file($permissionHelper)) {
    require_once $permissionHelper;
}

function ph($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function personnel_require_login()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }
}

function personnel_is_admin()
{
    $role = strtolower((string)($_SESSION['role'] ?? ''));
    if ($role === 'admin') {
        return true;
    }
    if (function_exists('is_admin_user')) {
        return (bool)is_admin_user();
    }
    return false;
}

function personnel_can($action)
{
    if (empty($_SESSION['user_id'])) {
        return false;
    }

    if (personnel_is_admin()) {
        return true;
    }

    if ($action === 'view') {
        if (function_exists('has_permission')) {
            return has_permission('personnel.view');
        }
        return true;
    }

    $key = 'personnel.' . $action;
    if (function_exists('can_manage_action')) {
        return can_manage_action($key);
    }
    if (function_exists('has_permission')) {
        return has_permission($key);
    }

    return false;
}

function personnel_require_permission($action)
{
    personnel_require_login();
    if (!personnel_can($action)) {
        http_response_code(403);
        die('403 Forbidden: บัญชีนี้ไม่มีสิทธิ์ใช้งานส่วนข้อมูลบุคลากร');
    }
}

function personnel_csrf_token()
{
    if (empty($_SESSION['personnel_csrf'])) {
        $_SESSION['personnel_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['personnel_csrf'];
}

function personnel_verify_csrf($token)
{
    $sessionToken = $_SESSION['personnel_csrf'] ?? '';
    return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function personnel_flash($type, $message)
{
    $_SESSION['personnel_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function personnel_pull_flash()
{
    $flash = $_SESSION['personnel_flash'] ?? null;
    unset($_SESSION['personnel_flash']);
    return $flash;
}

function personnel_column_exists($table, $column)
{
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    $sql = "SHOW COLUMNS FROM `{$table}` LIKE '" . $conn->real_escape_string($column) . "'";
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0;
}

function personnel_table_exists($table)
{
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $result = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
    return $result && $result->num_rows > 0;
}

function personnel_index_exists($table, $index)
{
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $index = $conn->real_escape_string($index);
    $result = $conn->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
    return $result && $result->num_rows > 0;
}

function personnel_schema_ready()
{
    return personnel_table_exists('personnel')
        && personnel_column_exists('personnel', 'username')
        && personnel_column_exists('personnel', 'first_name')
        && personnel_column_exists('personnel', 'last_name')
        && personnel_column_exists('personnel', 'national_id')
        && personnel_column_exists('personnel', 'user_id')
        && personnel_table_exists('users');
}

function personnel_install_schema()
{
    global $conn;
    $messages = [];

    if (!personnel_table_exists('personnel')) {
        $sql = "CREATE TABLE `personnel` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `employee_code` varchar(50) DEFAULT NULL,
          `username` varchar(50) DEFAULT NULL,
          `prefix` varchar(30) DEFAULT NULL,
          `first_name` varchar(100) DEFAULT NULL,
          `last_name` varchar(100) DEFAULT NULL,
          `fullname` varchar(150) NOT NULL,
          `first_name_en` varchar(150) DEFAULT NULL,
          `nickname` varchar(100) DEFAULT NULL,
          `birth_date` date DEFAULT NULL,
          `gender` varchar(20) DEFAULT NULL,
          `national_id` varchar(13) DEFAULT NULL,
          `position_name` varchar(150) DEFAULT NULL,
          `department` varchar(150) DEFAULT NULL,
          `department_id` int(11) DEFAULT NULL,
          `phone` varchar(50) DEFAULT NULL,
          `email` varchar(150) DEFAULT NULL,
          `room_location` varchar(150) DEFAULT NULL,
          `photo` varchar(255) DEFAULT NULL,
          `status` enum('active','inactive') NOT NULL DEFAULT 'active',
          `notes` text,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$conn->query($sql)) {
            throw new Exception('สร้างตาราง personnel ไม่สำเร็จ: ' . $conn->error);
        }
        $messages[] = 'สร้างตาราง personnel แล้ว';
    }

    $columns = [
        'user_id' => "ALTER TABLE `personnel` ADD COLUMN `user_id` int(11) DEFAULT NULL AFTER `id`",
        'username' => "ALTER TABLE `personnel` ADD COLUMN `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `employee_code`",
        'first_name' => "ALTER TABLE `personnel` ADD COLUMN `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `prefix`",
        'last_name' => "ALTER TABLE `personnel` ADD COLUMN `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `first_name`",
        'first_name_en' => "ALTER TABLE `personnel` ADD COLUMN `first_name_en` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `fullname`",
        'nickname' => "ALTER TABLE `personnel` ADD COLUMN `nickname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `first_name_en`",
        'birth_date' => "ALTER TABLE `personnel` ADD COLUMN `birth_date` date DEFAULT NULL AFTER `nickname`",
        'gender' => "ALTER TABLE `personnel` ADD COLUMN `gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `birth_date`",
        'national_id' => "ALTER TABLE `personnel` ADD COLUMN `national_id` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `gender`",
    ];

    foreach ($columns as $name => $sql) {
        if (!personnel_column_exists('personnel', $name)) {
            if (!$conn->query($sql)) {
                throw new Exception('เพิ่มคอลัมน์ ' . $name . ' ไม่สำเร็จ: ' . $conn->error);
            }
            $messages[] = 'เพิ่มคอลัมน์ ' . $name . ' แล้ว';
        }
    }

    if (!personnel_index_exists('personnel', 'uq_personnel_username')) {
        if ($conn->query("ALTER TABLE `personnel` ADD UNIQUE KEY `uq_personnel_username` (`username`)")) {
            $messages[] = 'เพิ่ม Unique Username แล้ว';
        }
    }
    if (!personnel_index_exists('personnel', 'uq_personnel_national_id')) {
        if ($conn->query("ALTER TABLE `personnel` ADD UNIQUE KEY `uq_personnel_national_id` (`national_id`)")) {
            $messages[] = 'เพิ่ม Unique เลขบัตรประชาชนแล้ว';
        }
    }
    if (!personnel_index_exists('personnel', 'idx_personnel_user_id')) {
        if ($conn->query("ALTER TABLE `personnel` ADD KEY `idx_personnel_user_id` (`user_id`)")) {
            $messages[] = 'เพิ่ม Index user_id แล้ว';
        }
    }

    if (!personnel_table_exists('personnel_audit_logs')) {
        $sql = "CREATE TABLE `personnel_audit_logs` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `personnel_id` int(11) DEFAULT NULL,
          `actor_user_id` int(11) DEFAULT NULL,
          `action` varchar(50) NOT NULL,
          `detail` text DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_personnel_audit_personnel` (`personnel_id`),
          KEY `idx_personnel_audit_actor` (`actor_user_id`),
          KEY `idx_personnel_audit_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$conn->query($sql)) {
            throw new Exception('สร้างตาราง personnel_audit_logs ไม่สำเร็จ: ' . $conn->error);
        }
        $messages[] = 'สร้างตารางบันทึกประวัติการจัดการแล้ว';
    }

    return $messages;
}

function personnel_log_action($personnelId, $action, $detail = '')
{
    global $conn;
    if (!personnel_table_exists('personnel_audit_logs')) {
        return;
    }
    $actor = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $stmt = $conn->prepare('INSERT INTO personnel_audit_logs (personnel_id, actor_user_id, action, detail) VALUES (?, ?, ?, ?)');
    if ($stmt) {
        $pid = $personnelId ? (int)$personnelId : null;
        $stmt->bind_param('iiss', $pid, $actor, $action, $detail);
        $stmt->execute();
        $stmt->close();
    }
}

function personnel_bind_params($stmt, $types, &$params)
{
    if ($types === '' || !$params) {
        return true;
    }
    $args = [$types];
    foreach ($params as $key => $value) {
        $args[] = &$params[$key];
    }
    return call_user_func_array([$stmt, 'bind_param'], $args);
}

function personnel_clean_national_id($value)
{
    return preg_replace('/\D+/', '', (string)$value);
}

function personnel_validate_national_id($value)
{
    return preg_match('/^\d{13}$/', (string)$value) === 1;
}

function personnel_mask_national_id($value)
{
    $value = personnel_clean_national_id($value);
    if (strlen($value) !== 13) {
        return '-';
    }
    return substr($value, 0, 1) . '-' . substr($value, 1, 4) . '-XXXXX-' . substr($value, 10, 2) . '-' . substr($value, 12, 1);
}

function personnel_format_national_id($value)
{
    $value = personnel_clean_national_id($value);
    if (strlen($value) !== 13) {
        return $value ?: '-';
    }
    return substr($value, 0, 1) . '-' . substr($value, 1, 4) . '-' . substr($value, 5, 5) . '-' . substr($value, 10, 2) . '-' . substr($value, 12, 1);
}

function personnel_status_meta($status)
{
    if ($status === 'inactive') {
        return ['label' => 'พ้นสภาพ / ไม่ปฏิบัติงาน', 'class' => 'inactive'];
    }
    return ['label' => 'ปฏิบัติงาน', 'class' => 'active'];
}

function personnel_gender_options()
{
    return ['ชาย', 'หญิง', 'ไม่ระบุ'];
}

function personnel_prefix_options()
{
    return ['นาย', 'นาง', 'นางสาว', 'นพ.', 'พญ.', 'ทพ.', 'ทพญ.', 'ภก.', 'ภญ.', 'พว.', 'อื่นๆ'];
}

function personnel_departments()
{
    global $conn;
    $items = [];
    if (personnel_table_exists('departments')) {
        $result = $conn->query("SELECT id, department_name FROM departments WHERE status='ใช้งาน' ORDER BY department_name ASC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
    }
    return $items;
}

function personnel_user_account($userId)
{
    global $conn;
    if (!$userId) {
        return null;
    }
    $stmt = $conn->prepare('SELECT id, fullname, username, email, department, status, role, created_at FROM users WHERE id = ? LIMIT 1');
    $id = (int)$userId;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row;
}
