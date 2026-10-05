<?php
/**
 * ระบบห้องประชุม โรงพยาบาลภักดีชุมพล
 */
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('ไม่พบการเชื่อมต่อฐานข้อมูลจาก ../../config.php');
}
$conn->set_charset('utf8mb4');

if (!function_exists('meeting_e')) {
    function meeting_e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('meeting_csrf_token')) {
    function meeting_csrf_token()
    {
        if (empty($_SESSION['meeting_csrf_token'])) {
            $_SESSION['meeting_csrf_token'] = bin2hex(random_bytes(24));
        }
        return $_SESSION['meeting_csrf_token'];
    }
}

if (!function_exists('meeting_verify_csrf')) {
    function meeting_verify_csrf($token)
    {
        $sessionToken = isset($_SESSION['meeting_csrf_token']) ? (string)$_SESSION['meeting_csrf_token'] : '';
        return $sessionToken !== '' && is_string($token) && hash_equals($sessionToken, $token);
    }
}

if (!function_exists('meeting_redirect')) {
    function meeting_redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('meeting_scalar')) {
    function meeting_scalar($conn, $sql)
    {
        $result = $conn->query($sql);
        if (!$result) {
            return 0;
        }
        $row = $result->fetch_row();
        return isset($row[0]) ? (int)$row[0] : 0;
    }
}

if (!function_exists('meeting_column_exists')) {
    function meeting_column_exists($conn, $table, $column)
    {
        $table = $conn->real_escape_string($table);
        $column = $conn->real_escape_string($column);
        $result = $conn->query("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '{$table}'
              AND COLUMN_NAME = '{$column}'
        ");
        if (!$result) {
            return false;
        }
        $row = $result->fetch_row();
        return !empty($row[0]);
    }
}

if (!function_exists('meeting_table_exists')) {
    function meeting_table_exists($conn, $table)
    {
        $table = $conn->real_escape_string($table);
        $result = $conn->query("
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '{$table}'
        ");
        if (!$result) {
            return false;
        }
        $row = $result->fetch_row();
        return !empty($row[0]);
    }
}

if (!function_exists('meeting_platforms')) {
    function meeting_platforms()
    {
        return array(
            'zoom' => array('label' => 'Zoom', 'icon' => '🎥', 'home' => 'https://zoom.us/'),
            'google_meet' => array('label' => 'Google Meet', 'icon' => '📹', 'home' => 'https://meet.google.com/'),
            'microsoft_teams' => array('label' => 'Microsoft Teams', 'icon' => '🟣', 'home' => 'https://teams.microsoft.com/'),
            'cisco_webex' => array('label' => 'Cisco Webex', 'icon' => '🔵', 'home' => 'https://www.webex.com/'),
            'line_meeting' => array('label' => 'LINE Meeting', 'icon' => '🟢', 'home' => 'https://line.me/'),
            'other' => array('label' => 'ลิงก์ประชุมอื่น', 'icon' => '🔗', 'home' => '')
        );
    }
}

if (!function_exists('meeting_platform_label')) {
    function meeting_platform_label($key)
    {
        $items = meeting_platforms();
        return isset($items[$key]) ? $items[$key]['label'] : 'ไม่ได้ระบุ';
    }
}

if (!function_exists('meeting_status_label')) {
    function meeting_status_label($status)
    {
        $map = array(
            'pending' => 'รออนุมัติ',
            'approved' => 'อนุมัติแล้ว',
            'rejected' => 'ไม่อนุมัติ',
            'cancelled' => 'ยกเลิกแล้ว',
            'completed' => 'เสร็จสิ้น'
        );
        return isset($map[$status]) ? $map[$status] : $status;
    }
}

if (!function_exists('meeting_status_class')) {
    function meeting_status_class($status)
    {
        $map = array(
            'pending' => 'status-pending',
            'approved' => 'status-approved',
            'rejected' => 'status-rejected',
            'cancelled' => 'status-cancelled',
            'completed' => 'status-completed'
        );
        return isset($map[$status]) ? $map[$status] : 'status-cancelled';
    }
}

if (!function_exists('meeting_is_valid_url')) {
    function meeting_is_valid_url($url)
    {
        if ($url === '') {
            return true;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, array('http', 'https'), true);
    }
}

if (!function_exists('meeting_format_thai_date')) {
    function meeting_format_thai_date($date)
    {
        if (!$date) {
            return '-';
        }
        $ts = strtotime($date);
        if (!$ts) {
            return meeting_e($date);
        }
        return date('d/m/', $ts) . (date('Y', $ts) + 543);
    }
}

if (!function_exists('meeting_schema_ready')) {
    function meeting_schema_ready($conn)
    {
        return meeting_table_exists($conn, 'meeting_rooms')
            && meeting_table_exists($conn, 'meeting_bookings')
            && meeting_column_exists($conn, 'meeting_bookings', 'user_id')
            && meeting_column_exists($conn, 'meeting_bookings', 'meeting_platform')
            && meeting_column_exists($conn, 'meeting_bookings', 'meeting_url')
            && meeting_column_exists($conn, 'meeting_bookings', 'approved_by')
            && meeting_column_exists($conn, 'meeting_bookings', 'approved_at');
    }
}
