<?php
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Bangkok');
if (isset($conn) && $conn instanceof mysqli) {
    $conn->set_charset('utf8mb4');
}

$permissionHelper = __DIR__ . '/../admin/permissions_helper.php';
if (is_file($permissionHelper)) {
    require_once $permissionHelper;
}

function medical_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function medical_require_login()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }
}

function medical_table_exists($table)
{
    global $conn;
    $safe = $conn->real_escape_string((string)$table);
    $rs = $conn->query("SHOW TABLES LIKE '" . $safe . "'");
    return $rs && $rs->num_rows > 0;
}

function medical_column_exists($table, $column)
{
    global $conn;
    $table = str_replace('`', '', (string)$table);
    $column = $conn->real_escape_string((string)$column);
    $rs = $conn->query("SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'");
    return $rs && $rs->num_rows > 0;
}

function medical_current_user()
{
    static $cache = null;
    global $conn;
    if ($cache !== null) return $cache;

    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $cache = array(
        'id' => $uid, 'fullname' => '', 'username' => '', 'email' => '',
        'department' => '', 'department_id' => 0, 'role' => 'user', 'status' => 'active',
        'position_name' => '', 'phone' => ''
    );
    if ($uid <= 0) return $cache;

    $stmt = $conn->prepare("SELECT id,fullname,username,email,department,department_id,role,status FROM users WHERE id=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && ($row = $res->fetch_assoc())) {
            $cache = array_merge($cache, $row);
        }
        $stmt->close();
    }

    if (medical_table_exists('personnel') && medical_column_exists('personnel', 'user_id')) {
        $stmt = $conn->prepare("SELECT position_name,department,department_id,phone FROM personnel WHERE user_id=? AND (status='active' OR status IS NULL OR status='') ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && ($p = $res->fetch_assoc())) {
                if (!empty($p['position_name'])) $cache['position_name'] = $p['position_name'];
                if (!empty($p['department'])) $cache['department'] = $p['department'];
                if (!empty($p['department_id'])) $cache['department_id'] = (int)$p['department_id'];
                if (!empty($p['phone'])) $cache['phone'] = $p['phone'];
            }
            $stmt->close();
        }
    }

    if ((int)$cache['department_id'] > 0 && medical_table_exists('departments')) {
        $did = (int)$cache['department_id'];
        $stmt = $conn->prepare("SELECT department_name FROM departments WHERE id=? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $did);
            $stmt->execute();
            $stmt->bind_result($dn);
            if ($stmt->fetch() && trim((string)$dn) !== '') $cache['department'] = $dn;
            $stmt->close();
        }
    }

    return $cache;
}

function medical_is_admin()
{
    $u = medical_current_user();
    if (isset($u['role']) && $u['role'] === 'admin' && $u['status'] === 'active') return true;
    if (function_exists('is_admin_user') && is_admin_user()) return true;
    return false;
}

function medical_can($action)
{
    medical_require_login();
    $u = medical_current_user();
    $role = strtolower((string)$u['role']);
    if (medical_is_admin()) return true;
    if (in_array($action, array('view','create'), true)) return true;
    if (in_array($action, array('edit','manage'), true) && in_array($role, array('manager','technician'), true)) return true;
    if ($action === 'delete') return false;

    $key = 'medical.' . $action;
    if (function_exists('has_permission') && has_permission($key)) return true;
    if (function_exists('can_manage_action') && can_manage_action($key)) return true;
    return false;
}

function medical_require_permission($action)
{
    if (!medical_can($action)) {
        http_response_code(403);
        die('403 Forbidden: บัญชีนี้ไม่มีสิทธิ์ใช้งานส่วนศูนย์เครื่องมือแพทย์');
    }
}

function medical_can_view_all()
{
    $u = medical_current_user();
    return medical_is_admin() || in_array(strtolower((string)$u['role']), array('manager','technician'), true) || medical_can('manage');
}

function medical_csrf_token()
{
    if (empty($_SESSION['medical_csrf'])) {
        $_SESSION['medical_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['medical_csrf'];
}

function medical_verify_csrf($token)
{
    $stored = isset($_SESSION['medical_csrf']) ? $_SESSION['medical_csrf'] : '';
    return is_string($token) && $stored !== '' && hash_equals($stored, $token);
}

function medical_flash($type, $message)
{
    $_SESSION['medical_flash'] = array('type'=>(string)$type, 'message'=>(string)$message);
}

function medical_pull_flash()
{
    $f = isset($_SESSION['medical_flash']) ? $_SESSION['medical_flash'] : null;
    unset($_SESSION['medical_flash']);
    return $f;
}

function medical_status_meta($status)
{
    $map = array(
        'pending' => array('label'=>'รอตรวจสอบ','class'=>'pending'),
        'assigned' => array('label'=>'มอบหมายแล้ว','class'=>'assigned'),
        'in_progress' => array('label'=>'กำลังซ่อม','class'=>'progress'),
        'waiting_parts' => array('label'=>'รออะไหล่','class'=>'waiting'),
        'completed' => array('label'=>'เสร็จสิ้น','class'=>'completed'),
        'cancelled' => array('label'=>'ยกเลิก','class'=>'cancelled')
    );
    return isset($map[$status]) ? $map[$status] : array('label'=>$status ?: 'ไม่ระบุ','class'=>'neutral');
}

function medical_priority_meta($priority)
{
    $map = array(
        'normal'=>array('label'=>'ปกติ','class'=>'normal'),
        'urgent'=>array('label'=>'ด่วน','class'=>'urgent'),
        'emergency'=>array('label'=>'ด่วนมาก','class'=>'emergency')
    );
    return isset($map[$priority]) ? $map[$priority] : $map['normal'];
}

function medical_problem_meta($problem)
{
    $map = array(
        'malfunction'=>'เครื่องทำงานผิดปกติ',
        'not_power'=>'เปิดเครื่องไม่ติด',
        'alarm'=>'มีสัญญาณเตือน/Error',
        'broken'=>'ชำรุด/แตกหัก',
        'calibration'=>'ต้องการสอบเทียบ',
        'preventive'=>'บำรุงรักษาเชิงป้องกัน',
        'accessory'=>'อุปกรณ์ประกอบมีปัญหา',
        'other'=>'อื่น ๆ'
    );
    return isset($map[$problem]) ? $map[$problem] : ($problem ?: 'ไม่ระบุ');
}

function medical_thai_datetime($value)
{
    if (!$value) return '-';
    $ts = strtotime($value);
    if (!$ts) return medical_e($value);
    return date('d/m/', $ts) . ((int)date('Y', $ts)+543) . ' ' . date('H:i', $ts) . ' น.';
}

function medical_bind_params($stmt, $types, &$params)
{
    if ($types === '' || !$params) return true;
    $args = array($types);
    foreach ($params as $k => $v) {
        $args[] =& $params[$k];
    }
    return call_user_func_array(array($stmt, 'bind_param'), $args);
}

function medical_request_no($id)
{
    return 'MED' . ((int)date('Y') + 543) . '-' . str_pad((string)(int)$id, 5, '0', STR_PAD_LEFT);
}

function medical_get_request($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT r.*, e.display_name AS equipment_registry_name, t.fullname AS current_technician_name
                            FROM medical_repair_requests r
                            LEFT JOIN medical_equipment e ON e.id=r.equipment_id
                            LEFT JOIN medical_technicians t ON t.id=r.technician_id
                            WHERE r.id=? LIMIT 1");
    if (!$stmt) return null;
    $id = (int)$id;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function medical_can_view_request($row)
{
    if (!$row) return false;
    if (medical_can_view_all()) return true;
    $uid = (int)$_SESSION['user_id'];
    return (int)$row['user_id'] === $uid;
}

function medical_send_line($message, $event)
{
    $config = __DIR__ . '/../../lineapi/line_apps_script_config.php';
    if (!is_file($config)) return array('ok'=>false,'error'=>'ไม่พบไฟล์ตั้งค่า LINE Apps Script');
    require_once $config;
    if (!defined('LINE_APPS_SCRIPT_URL') || !LINE_APPS_SCRIPT_URL) return array('ok'=>false,'error'=>'ยังไม่ได้กำหนด LINE_APPS_SCRIPT_URL');
    if (!function_exists('curl_init')) return array('ok'=>false,'error'=>'PHP cURL ยังไม่ได้เปิดใช้งาน');

    $payload = json_encode(array(
        'secret'=>defined('LINE_APPS_SCRIPT_SHARED_SECRET') ? LINE_APPS_SCRIPT_SHARED_SECRET : '',
        'event'=>(string)$event,
        'message'=>(string)$message
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init(LINE_APPS_SCRIPT_URL);
    curl_setopt_array($ch, array(
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>array('Content-Type: application/json; charset=utf-8','Accept: application/json'),
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_CONNECTTIMEOUT=>defined('LINE_CURL_CONNECT_TIMEOUT') ? LINE_CURL_CONNECT_TIMEOUT : 15,
        CURLOPT_TIMEOUT=>defined('LINE_CURL_TIMEOUT') ? LINE_CURL_TIMEOUT : 35,
        CURLOPT_USERAGENT=>'PhakdeeChumphonMedicalRepair/1.0'
    ));
    $verify = defined('LINE_CURL_SSL_VERIFY') ? (bool)LINE_CURL_SSL_VERIFY : true;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verify);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verify ? 2 : 0);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $error !== '') return array('ok'=>false,'error'=>$error,'http'=>$http);
    $decoded = json_decode($response, true);
    $ok = is_array($decoded) && !empty($decoded['ok']);
    $err = '';
    if (!$ok && is_array($decoded) && !empty($decoded['error'])) $err = $decoded['error'];
    if (!$ok && $err === '' && is_array($decoded) && !empty($decoded['line_response'])) $err = $decoded['line_response'];
    return array('ok'=>$ok,'error'=>$err,'http'=>$http,'response'=>$decoded,'raw'=>$response);
}

function medical_log($requestId, $action, $oldStatus, $newStatus, $note)
{
    global $conn;
    if (!medical_table_exists('medical_status_logs')) return;
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $stmt = $conn->prepare("INSERT INTO medical_status_logs(request_id,user_id,action_key,old_status,new_status,note) VALUES(?,?,?,?,?,?)");
    if ($stmt) {
        $requestId = (int)$requestId;
        $stmt->bind_param('iissss', $requestId, $uid, $action, $oldStatus, $newStatus, $note);
        $stmt->execute();
        $stmt->close();
    }
}

function medical_schema_ready()
{
    $tables = array('medical_equipment','medical_technicians','medical_repair_requests','medical_status_logs','medical_feedback');
    foreach ($tables as $t) if (!medical_table_exists($t)) return false;
    return true;
}
