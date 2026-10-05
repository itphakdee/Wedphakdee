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

function mh($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function maintenance_initial($name)
{
    $name = trim((string)$name);
    if ($name === '') return 'U';
    return function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : substr($name, 0, 1);
}

function maintenance_require_login()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }
}

function maintenance_can($action)
{
    $action = trim((string)$action);
    if ($action === '' || empty($_SESSION['user_id'])) {
        return false;
    }

    $role = strtolower((string)($_SESSION['role'] ?? 'user'));

    // บุคลากรที่ล็อกอินทุกคนสามารถดูและแจ้งงานได้
    if (in_array($action, ['view', 'create'], true)) {
        return true;
    }

    // ช่าง/หัวหน้างานสามารถแก้ไขและจัดการสถานะได้โดยค่าเริ่มต้น
    if (in_array($action, ['edit', 'manage'], true) && in_array($role, ['admin', 'manager', 'technician'], true)) {
        return true;
    }

    // การลบจำกัดไว้ที่ Admin/Manager
    if ($action === 'delete' && in_array($role, ['admin', 'manager'], true)) {
        return true;
    }

    if (function_exists('is_admin_user') && is_admin_user()) {
        return true;
    }

    $permissionKey = 'maintenance.' . $action;

    if (in_array($action, ['edit', 'delete', 'manage'], true) && function_exists('can_manage_action')) {
        return can_manage_action($permissionKey);
    }

    if (function_exists('has_permission')) {
        return has_permission($permissionKey);
    }

    return false;
}

function maintenance_require_permission($action)
{
    maintenance_require_login();
    if (!maintenance_can($action)) {
        http_response_code(403);
        die('403 Forbidden: บัญชีนี้ไม่มีสิทธิ์ใช้งานส่วนงานซ่อมบำรุง');
    }
}

function maintenance_csrf_token()
{
    if (empty($_SESSION['maintenance_csrf'])) {
        $_SESSION['maintenance_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['maintenance_csrf'];
}

function maintenance_verify_csrf($token)
{
    $sessionToken = $_SESSION['maintenance_csrf'] ?? '';
    return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function maintenance_table_exists()
{
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE 'maintenance_requests'");
    return $result && $result->num_rows > 0;
}

function maintenance_feedback_table_exists()
{
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE 'maintenance_feedback'");
    return $result && $result->num_rows > 0;
}

function maintenance_feedback_schema_sql()
{
    return <<<SQL
CREATE TABLE IF NOT EXISTS `maintenance_feedback` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int unsigned NOT NULL,
  `user_id` int NOT NULL,
  `score` tinyint unsigned NOT NULL,
  `feedback_text` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_maintenance_feedback_request` (`request_id`),
  KEY `idx_maintenance_feedback_user` (`user_id`),
  KEY `idx_maintenance_feedback_score` (`score`),
  CONSTRAINT `fk_maintenance_feedback_request`
    FOREIGN KEY (`request_id`) REFERENCES `maintenance_requests` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
}

function maintenance_ensure_feedback_table()
{
    global $conn;
    if (maintenance_feedback_table_exists()) {
        return true;
    }
    return (bool)$conn->query(maintenance_feedback_schema_sql());
}

function maintenance_feedback_meta($score)
{
    $map = [
        5 => ['label' => 'ดีมาก', 'class' => 'excellent', 'symbol' => '★'],
        3 => ['label' => 'พอใช้', 'class' => 'fair', 'symbol' => '●'],
        1 => ['label' => 'ปรับปรุง', 'class' => 'improve', 'symbol' => '!'],
    ];
    $score = (int)$score;
    return $map[$score] ?? ['label' => 'ไม่ระบุ', 'class' => 'neutral', 'symbol' => '•'];
}

function maintenance_status_meta($status)
{
    $map = [
        'pending' => ['label' => 'รอรับงาน', 'class' => 'pending'],
        'assigned' => ['label' => 'มอบหมายแล้ว', 'class' => 'assigned'],
        'in_progress' => ['label' => 'กำลังดำเนินการ', 'class' => 'progress'],
        'completed' => ['label' => 'เสร็จสิ้น', 'class' => 'completed'],
        'cancelled' => ['label' => 'ยกเลิก', 'class' => 'cancelled'],
    ];
    return $map[$status] ?? ['label' => $status ?: 'ไม่ระบุ', 'class' => 'neutral'];
}

function maintenance_priority_meta($priority)
{
    $map = [
        'normal' => ['label' => 'ปกติ', 'class' => 'normal'],
        'urgent' => ['label' => 'ด่วน', 'class' => 'urgent'],
        'emergency' => ['label' => 'ด่วนมาก', 'class' => 'emergency'],
    ];
    return $map[$priority] ?? ['label' => 'ปกติ', 'class' => 'normal'];
}

function maintenance_system_meta($system)
{
    $map = [
        'ประปา' => ['icon' => 'W', 'label' => 'ประปา'],
        'ไฟฟ้า' => ['icon' => 'E', 'label' => 'ไฟฟ้า'],
        'แอร์' => ['icon' => 'A', 'label' => 'เครื่องปรับอากาศ'],
    ];
    return $map[$system] ?? ['icon' => 'M', 'label' => $system ?: 'ไม่ระบุ'];
}

function maintenance_format_datetime($dateTime)
{
    if (!$dateTime) {
        return '-';
    }
    $ts = strtotime($dateTime);
    if (!$ts) {
        return mh($dateTime);
    }
    return date('d/m/Y H:i', $ts) . ' น.';
}

function maintenance_flash($type, $message)
{
    $_SESSION['maintenance_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function maintenance_pull_flash()
{
    $flash = $_SESSION['maintenance_flash'] ?? null;
    unset($_SESSION['maintenance_flash']);
    return $flash;
}


function maintenance_column_exists($table, $column)
{
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
    if ($table === '' || $column === '') return false;
    $result = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn->real_escape_string($column) . "'");
    return $result && $result->num_rows > 0;
}

function maintenance_ensure_line_columns()
{
    global $conn;
    if (!maintenance_table_exists()) return false;

    $columns = [
        'line_notify_status' => "ALTER TABLE `maintenance_requests` ADD COLUMN `line_notify_status` varchar(20) DEFAULT NULL COMMENT 'success/failed/disabled' AFTER `technician_note`",
        'line_notify_error' => "ALTER TABLE `maintenance_requests` ADD COLUMN `line_notify_error` text DEFAULT NULL COMMENT 'รายละเอียดกรณีส่ง LINE ไม่สำเร็จ' AFTER `line_notify_status`",
        'line_notified_at' => "ALTER TABLE `maintenance_requests` ADD COLUMN `line_notified_at` datetime DEFAULT NULL COMMENT 'เวลาที่ส่ง LINE สำเร็จล่าสุด' AFTER `line_notify_error`",
    ];

    foreach ($columns as $column => $sql) {
        if (!maintenance_column_exists('maintenance_requests', $column)) {
            if (!$conn->query($sql)) return false;
        }
    }
    return true;
}

function maintenance_send_line($message, $event = 'maintenance_repair')
{
    $config = __DIR__ . '/../../lineapi/line_apps_script_config.php';
    $result = [
        'ok' => false,
        'error' => '',
        'http' => 0,
        'response' => null,
        'raw' => '',
    ];

    if (!is_file($config)) {
        $result['error'] = 'ไม่พบไฟล์ตั้งค่า LINE Apps Script';
        return $result;
    }
    require_once $config;

    if (!defined('LINE_APPS_SCRIPT_URL') || trim((string)LINE_APPS_SCRIPT_URL) === '') {
        $result['error'] = 'ยังไม่ได้กำหนด LINE_APPS_SCRIPT_URL';
        return $result;
    }
    if (!defined('LINE_APPS_SCRIPT_SHARED_SECRET') || trim((string)LINE_APPS_SCRIPT_SHARED_SECRET) === '') {
        $result['error'] = 'ยังไม่ได้กำหนด LINE_APPS_SCRIPT_SHARED_SECRET';
        return $result;
    }

    $payload = json_encode([
        'secret' => LINE_APPS_SCRIPT_SHARED_SECRET,
        'event' => (string)$event,
        'message' => (string)$message,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        $result['error'] = 'ไม่สามารถแปลงข้อมูล LINE เป็น JSON ได้';
        return $result;
    }

    $verify = defined('LINE_CURL_SSL_VERIFY') ? (bool)LINE_CURL_SSL_VERIFY : true;
    $connectTimeout = defined('LINE_CURL_CONNECT_TIMEOUT') ? (int)LINE_CURL_CONNECT_TIMEOUT : 15;
    $timeout = defined('LINE_CURL_TIMEOUT') ? (int)LINE_CURL_TIMEOUT : 35;

    if (function_exists('curl_init')) {
        $ch = curl_init(LINE_APPS_SCRIPT_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_USERAGENT => 'PhakdeeChumphonMaintenance/1.0',
            CURLOPT_SSL_VERIFYPEER => $verify,
            CURLOPT_SSL_VERIFYHOST => $verify ? 2 : 0,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlNo = curl_errno($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result['http'] = $http;
        if ($response === false || $curlNo !== 0) {
            $result['error'] = 'cURL #' . $curlNo . ': ' . ($curlError !== '' ? $curlError : 'Unknown error');
            return $result;
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json; charset=utf-8\r\nAccept: application/json\r\n",
                'content' => $payload,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => $verify,
                'verify_peer_name' => $verify,
            ],
        ]);
        $response = @file_get_contents(LINE_APPS_SCRIPT_URL, false, $context);
        if ($response === false) {
            $result['error'] = 'ติดต่อ Google Apps Script ไม่สำเร็จ และ PHP cURL ไม่พร้อมใช้งาน';
            return $result;
        }
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $headerLine) {
                if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $headerLine, $m)) {
                    $result['http'] = (int)$m[1];
                }
            }
        }
    }

    $result['raw'] = (string)$response;
    $decoded = json_decode((string)$response, true);
    $result['response'] = $decoded;

    if (!is_array($decoded)) {
        $result['error'] = 'Apps Script ตอบกลับไม่ใช่ JSON';
        return $result;
    }
    if (empty($decoded['ok'])) {
        $result['error'] = !empty($decoded['error']) ? (string)$decoded['error'] : 'Apps Script แจ้งว่าส่ง LINE ไม่สำเร็จ';
        if (isset($decoded['line_status'])) $result['error'] .= ' | LINE HTTP ' . (int)$decoded['line_status'];
        if (!empty($decoded['line_response'])) $result['error'] .= ' | ' . (string)$decoded['line_response'];
        return $result;
    }
    if (isset($decoded['line_status'])) {
        $lineStatus = (int)$decoded['line_status'];
        if ($lineStatus < 200 || $lineStatus >= 300) {
            $result['error'] = 'LINE Messaging API ตอบ HTTP ' . $lineStatus;
            return $result;
        }
    }

    $result['ok'] = true;
    return $result;
}

function maintenance_update_line_result($requestId, $lineResult)
{
    global $conn;
    if (!maintenance_column_exists('maintenance_requests', 'line_notify_status')) return;

    $status = !empty($lineResult['ok']) ? 'success' : 'failed';
    $error = !empty($lineResult['error']) ? (string)$lineResult['error'] : '';
    $notifiedAt = !empty($lineResult['ok']) ? date('Y-m-d H:i:s') : null;
    $requestId = (int)$requestId;

    $stmt = $conn->prepare("UPDATE maintenance_requests SET line_notify_status=?, line_notify_error=?, line_notified_at=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param('sssi', $status, $error, $notifiedAt, $requestId);
        $stmt->execute();
        $stmt->close();
    }
}

function maintenance_schema_sql()
{
    return <<<SQL
CREATE TABLE IF NOT EXISTS `maintenance_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `sender_name` varchar(150) NOT NULL,
  `department` varchar(150) NOT NULL,
  `system_type` enum('ประปา','ไฟฟ้า','แอร์') NOT NULL,
  `technician_id` int DEFAULT NULL,
  `technician_name` varchar(150) DEFAULT NULL,
  `details` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `priority` enum('normal','urgent','emergency') NOT NULL DEFAULT 'normal',
  `status` enum('pending','assigned','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `technician_note` text DEFAULT NULL,
  `line_notify_status` varchar(20) DEFAULT NULL,
  `line_notify_error` text DEFAULT NULL,
  `line_notified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_maintenance_status` (`status`),
  KEY `idx_maintenance_system` (`system_type`),
  KEY `idx_maintenance_technician` (`technician_id`),
  KEY `idx_maintenance_created_at` (`created_at`),
  KEY `idx_maintenance_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
}
