<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');
$conn->set_charset('utf8mb4');

if (!(defined('VEHICLE_CRON_MODE') && VEHICLE_CRON_MODE) && !isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

function vehicle_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function vehicle_table_exists($table)
{
    global $conn;
    $safe = $conn->real_escape_string($table);
    $rs = $conn->query("SHOW TABLES LIKE '" . $safe . "'");
    return $rs && $rs->num_rows > 0;
}

function vehicle_column_exists($table, $column)
{
    global $conn;
    $safeTable = str_replace('`', '', $table);
    $safeCol = $conn->real_escape_string($column);
    $rs = $conn->query("SHOW COLUMNS FROM `" . $safeTable . "` LIKE '" . $safeCol . "'");
    return $rs && $rs->num_rows > 0;
}

function vehicle_load_user_profile($uid)
{
    global $conn;

    $uid = (int)$uid;
    if ($uid <= 0) {
        return array();
    }

    // 1) อ่านข้อมูลบัญชีจาก users ก่อนเสมอ
    // ไม่ JOIN fullname/email ข้ามตาราง เพราะ collation ของ users กับ personnel อาจต่างกัน
    // และทำให้ Query ล้มเหลวจนชื่อผู้ร้องขอ/แผนกเป็นค่าว่างได้
    $stmt = $conn->prepare("SELECT id,fullname,username,email,department,department_id,status,role,supervisor_id
                            FROM users
                            WHERE id=? LIMIT 1");
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res ? $res->fetch_assoc() : array();
    $stmt->close();

    if (!$user) {
        return array();
    }

    // ค่าเริ่มต้นกรณีไม่มีข้อมูล personnel
    $user['employee_code'] = '';
    $user['position_name'] = '';
    $user['personnel_department'] = '';
    $user['personnel_department_id'] = null;
    $user['phone'] = '';

    if (!vehicle_table_exists('personnel')) {
        return $user;
    }

    $person = array();

    // 2) วิธีหลัก: personnel.user_id = users.id
    if (vehicle_column_exists('personnel', 'user_id')) {
        $stmt = $conn->prepare("SELECT employee_code,position_name,department,department_id,phone
                                FROM personnel
                                WHERE user_id=? AND (status='active' OR status IS NULL OR status='')
                                ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $res = $stmt->get_result();
            $person = $res ? $res->fetch_assoc() : array();
            $stmt->close();
        }
    }

    // 3) Fallback สำหรับข้อมูลเก่าที่ personnel.user_id ยังไม่ได้ผูก
    // เทียบค่ากับ parameter ทีละฟิลด์ จึงไม่เกิด Illegal mix of collations ระหว่างสองตาราง
    if (!$person && !empty($user['username']) && vehicle_column_exists('personnel', 'username')) {
        $username = (string)$user['username'];
        $stmt = $conn->prepare("SELECT employee_code,position_name,department,department_id,phone
                                FROM personnel
                                WHERE username=? AND (status='active' OR status IS NULL OR status='')
                                ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();
            $person = $res ? $res->fetch_assoc() : array();
            $stmt->close();
        }
    }

    if (!$person && !empty($user['email']) && vehicle_column_exists('personnel', 'email')) {
        $email = (string)$user['email'];
        $stmt = $conn->prepare("SELECT employee_code,position_name,department,department_id,phone
                                FROM personnel
                                WHERE email=? AND (status='active' OR status IS NULL OR status='')
                                ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $person = $res ? $res->fetch_assoc() : array();
            $stmt->close();
        }
    }

    if (!$person && !empty($user['fullname']) && vehicle_column_exists('personnel', 'fullname')) {
        $fullname = (string)$user['fullname'];
        $stmt = $conn->prepare("SELECT employee_code,position_name,department,department_id,phone
                                FROM personnel
                                WHERE fullname=? AND (status='active' OR status IS NULL OR status='')
                                ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $fullname);
            $stmt->execute();
            $res = $stmt->get_result();
            $person = $res ? $res->fetch_assoc() : array();
            $stmt->close();
        }
    }

    if ($person) {
        $user['employee_code'] = isset($person['employee_code']) ? $person['employee_code'] : '';
        $user['position_name'] = isset($person['position_name']) ? $person['position_name'] : '';
        $user['personnel_department'] = isset($person['department']) ? $person['department'] : '';
        $user['personnel_department_id'] = isset($person['department_id']) ? $person['department_id'] : null;
        $user['phone'] = isset($person['phone']) ? $person['phone'] : '';
    }

    return $user;
}

function vehicle_current_user()
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $cached = vehicle_load_user_profile($uid);
    return $cached;
}

function vehicle_is_admin()
{
    $u = vehicle_current_user();
    return isset($u['role']) && $u['role'] === 'admin' && isset($u['status']) && $u['status'] === 'active';
}

function vehicle_department_for_user($user)
{
    global $conn;
    $deptId = 0;
    $deptName = '';

    if (isset($user['personnel_department_id']) && (int)$user['personnel_department_id'] > 0) {
        $deptId = (int)$user['personnel_department_id'];
    } elseif (isset($user['department_id']) && (int)$user['department_id'] > 0) {
        $deptId = (int)$user['department_id'];
    }

    if (isset($user['personnel_department']) && trim((string)$user['personnel_department']) !== '') {
        $deptName = trim((string)$user['personnel_department']);
    } elseif (isset($user['department'])) {
        $deptName = trim((string)$user['department']);
    }

    if ($deptId > 0 && vehicle_table_exists('departments')) {
        $stmt = $conn->prepare("SELECT department_name FROM departments WHERE id=? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $deptId);
            $stmt->execute();
            $stmt->bind_result($dbName);
            if ($stmt->fetch() && trim((string)$dbName) !== '') {
                $deptName = trim((string)$dbName);
            }
            $stmt->close();
        }
    }

    if ($deptId <= 0 && $deptName !== '' && vehicle_table_exists('departments')) {
        $stmt = $conn->prepare("SELECT id,department_name FROM departments WHERE department_name=? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $deptName);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $deptId = (int)$row['id'];
                $deptName = $row['department_name'];
            }
            $stmt->close();
        }
    }

    return array('id' => $deptId, 'name' => $deptName);
}

function vehicle_supervisor_for_user($user)
{
    global $conn;
    $dept = vehicle_department_for_user($user);
    $deptId = (int)$dept['id'];
    $deptName = trim((string)$dept['name']);

    if (vehicle_table_exists('leave_supervisors')) {
        if ($deptId > 0 && vehicle_column_exists('leave_supervisors', 'department_id')) {
            $stmt = $conn->prepare("SELECT u.id,u.fullname,u.username,u.role,u.status
                                    FROM leave_supervisors ls
                                    JOIN users u ON u.id=ls.supervisor_user_id
                                    WHERE ls.department_id=? AND u.status='active'
                                    ORDER BY ls.id DESC LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $deptId);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && ($row = $res->fetch_assoc())) {
                    $stmt->close();
                    $row['department_id'] = $deptId;
                    $row['department_name'] = $deptName;
                    return $row;
                }
                $stmt->close();
            }
        }

        if ($deptName !== '') {
            $stmt = $conn->prepare("SELECT u.id,u.fullname,u.username,u.role,u.status
                                    FROM leave_supervisors ls
                                    JOIN users u ON u.id=ls.supervisor_user_id
                                    WHERE ls.department_name=? AND u.status='active'
                                    ORDER BY ls.id DESC LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $deptName);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && ($row = $res->fetch_assoc())) {
                    $stmt->close();
                    $row['department_id'] = $deptId;
                    $row['department_name'] = $deptName;
                    return $row;
                }
                $stmt->close();
            }
        }
    }

    // Fallback: users.supervisor_id ของผู้ใช้งาน
    $supervisorId = isset($user['supervisor_id']) ? (int)$user['supervisor_id'] : 0;
    if ($supervisorId > 0) {
        $stmt = $conn->prepare("SELECT id,fullname,username,role,status FROM users WHERE id=? AND status='active' LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $supervisorId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $stmt->close();
                $row['department_id'] = $deptId;
                $row['department_name'] = $deptName;
                return $row;
            }
            $stmt->close();
        }
    }

    return array('id' => 0, 'fullname' => '', 'department_id' => $deptId, 'department_name' => $deptName);
}

function vehicle_status_meta($status)
{
    $map = array(
        'pending_supervisor' => array('รอหัวหน้างานรับรอง', 'amber'),
        'pending_admin' => array('รอเจ้าหน้าที่ดำเนินการ', 'blue'),
        'approved' => array('อนุมัติแล้ว', 'green'),
        'assigned' => array('จัดรถ/พนักงานขับแล้ว', 'teal'),
        'in_use' => array('กำลังใช้งาน', 'purple'),
        'completed' => array('เสร็จสิ้น', 'green'),
        'cancel_requested' => array('แจ้งขอยกเลิก', 'orange'),
        'cancelled' => array('ยกเลิกแล้ว', 'gray'),
        'rejected' => array('ไม่อนุมัติ', 'red')
    );
    return isset($map[$status]) ? $map[$status] : array($status, 'gray');
}

function vehicle_request_type_meta($type)
{
    if ($type === 'refer') {
        return array('ฉุกเฉิน (REFER)', 'refer');
    }
    return array('ประชุม/ภารกิจทั่วไป', 'general');
}

function vehicle_csrf_token()
{
    if (empty($_SESSION['vehicle_csrf_token'])) {
        if (function_exists('random_bytes')) {
            $_SESSION['vehicle_csrf_token'] = bin2hex(random_bytes(24));
        } else {
            $_SESSION['vehicle_csrf_token'] = sha1(uniqid(mt_rand(), true));
        }
    }
    return $_SESSION['vehicle_csrf_token'];
}

function vehicle_check_csrf()
{
    $posted = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
    $saved = isset($_SESSION['vehicle_csrf_token']) ? (string)$_SESSION['vehicle_csrf_token'] : '';
    if ($posted === '' || $saved === '' || !hash_equals($saved, $posted)) {
        http_response_code(419);
        die('คำขอหมดอายุหรือไม่ถูกต้อง กรุณากลับไปโหลดหน้าใหม่แล้วลองอีกครั้ง');
    }
}

function vehicle_redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function vehicle_flash($type, $message)
{
    $_SESSION['vehicle_flash'] = array('type' => $type, 'message' => $message);
}

function vehicle_take_flash()
{
    if (!empty($_SESSION['vehicle_flash'])) {
        $f = $_SESSION['vehicle_flash'];
        unset($_SESSION['vehicle_flash']);
        return $f;
    }
    return null;
}

function vehicle_request_access($row)
{
    if (vehicle_is_admin()) {
        return true;
    }
    $uid = (int)$_SESSION['user_id'];
    if (isset($row['user_id']) && (int)$row['user_id'] === $uid) {
        return true;
    }
    if (isset($row['supervisor_user_id']) && (int)$row['supervisor_user_id'] === $uid) {
        return true;
    }
    return false;
}

function vehicle_get_request($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT vr.*,
                                  vf.registration AS fleet_registration,
                                  vf.display_name AS fleet_name,
                                  vd.fullname AS driver_name,
                                  vd.phone AS driver_phone
                           FROM vehicle_requests vr
                           LEFT JOIN vehicle_fleet vf ON vf.id=vr.vehicle_id
                           LEFT JOIN vehicle_drivers vd ON vd.id=vr.driver_id
                           WHERE vr.id=? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function vehicle_send_line($message, $event)
{
    $config = __DIR__ . '/../lineapi/line_apps_script_config.php';
    if (!is_file($config)) {
        return array('ok' => false, 'error' => 'ไม่พบไฟล์ตั้งค่า LINE Apps Script');
    }
    require_once $config;

    if (!defined('LINE_APPS_SCRIPT_URL') || !LINE_APPS_SCRIPT_URL) {
        return array('ok' => false, 'error' => 'ยังไม่ได้กำหนด LINE_APPS_SCRIPT_URL');
    }
    if (!function_exists('curl_init')) {
        return array('ok' => false, 'error' => 'PHP cURL ยังไม่ได้เปิดใช้งาน');
    }

    $payload = json_encode(array(
        'secret' => defined('LINE_APPS_SCRIPT_SHARED_SECRET') ? LINE_APPS_SCRIPT_SHARED_SECRET : '',
        'event' => $event,
        'message' => $message
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init(LINE_APPS_SCRIPT_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, defined('LINE_CURL_CONNECT_TIMEOUT') ? LINE_CURL_CONNECT_TIMEOUT : 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, defined('LINE_CURL_TIMEOUT') ? LINE_CURL_TIMEOUT : 35);
    $verify = defined('LINE_CURL_SSL_VERIFY') ? LINE_CURL_SSL_VERIFY : true;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verify ? true : false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verify ? 2 : 0);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $error !== '') {
        return array('ok' => false, 'error' => $error, 'http' => $http);
    }

    $decoded = json_decode($response, true);
    $ok = is_array($decoded) && !empty($decoded['ok']);
    return array('ok' => $ok, 'http' => $http, 'response' => $decoded, 'raw' => $response,
                 'error' => (!$ok && is_array($decoded) && isset($decoded['error'])) ? $decoded['error'] : '');
}

function vehicle_format_thai_date($date)
{
    if (!$date || $date === '0000-00-00') {
        return '-';
    }
    $ts = strtotime($date);
    if (!$ts) {
        return vehicle_e($date);
    }
    $months = array(1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.');
    return date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . ((int)date('Y', $ts) + 543);
}

function vehicle_format_money($number)
{
    return number_format((float)$number, 2);
}

$currentVehicleUser = vehicle_current_user();
$currentVehicleDept = vehicle_department_for_user($currentVehicleUser);
$currentVehicleSupervisor = vehicle_supervisor_for_user($currentVehicleUser);
$currentVehicleIsAdmin = vehicle_is_admin();

function vehicle_bind_values($stmt, $types, $values)
{
    $params = array();
    $params[] = &$types;
    foreach ($values as $k => $v) {
        $values[$k] = $v;
        $params[] = &$values[$k];
    }
    return call_user_func_array(array($stmt, 'bind_param'), $params);
}

function vehicle_upload_document($field, $existing)
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]) || (int)$_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $existing;
    }
    $f = $_FILES[$field];
    if ((int)$f['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('อัปโหลดเอกสารไม่สำเร็จ (รหัส ' . (int)$f['error'] . ')');
    }
    if ((int)$f['size'] > 8 * 1024 * 1024) {
        throw new Exception('เอกสารมีขนาดเกิน 8 MB');
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $allowed = array('pdf','jpg','jpeg','png');
    if (!in_array($ext, $allowed, true)) {
        throw new Exception('อนุญาตเฉพาะไฟล์ PDF, JPG, JPEG, PNG');
    }
    $dir = __DIR__ . '/uploads/vehicle_documents';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new Exception('ไม่สามารถสร้างโฟลเดอร์เก็บเอกสารได้');
    }
    $name = 'vehicle_' . date('Ymd_His') . '_' . mt_rand(1000,9999) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        throw new Exception('ไม่สามารถบันทึกไฟล์เอกสารได้');
    }
    return 'uploads/vehicle_documents/' . $name;
}

function vehicle_add_log($requestId, $actionKey, $oldStatus, $newStatus, $note)
{
    global $conn;
    if (!vehicle_table_exists('vehicle_status_logs')) {
        return;
    }
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $stmt = $conn->prepare("INSERT INTO vehicle_status_logs(request_id,user_id,action_key,old_status,new_status,note) VALUES(?,?,?,?,?,?)");
    if ($stmt) {
        $stmt->bind_param('iissss', $requestId, $uid, $actionKey, $oldStatus, $newStatus, $note);
        $stmt->execute();
        $stmt->close();
    }
}

function vehicle_user_by_id($uid)
{
    return vehicle_load_user_profile((int)$uid);
}


/**
 * ==========================================================
 * พ.ร.บ. รถ / LINE Reminder
 * ==========================================================
 */
function vehicle_porobo_days_remaining($expiryDate)
{
    if (!$expiryDate || $expiryDate === '0000-00-00') {
        return null;
    }
    try {
        $tz = new DateTimeZone('Asia/Bangkok');
        $today = new DateTime('today', $tz);
        $expiry = new DateTime($expiryDate, $tz);
        $expiry->setTime(0, 0, 0);
        return (int)$today->diff($expiry)->format('%r%a');
    } catch (Exception $e) {
        return null;
    }
}

function vehicle_porobo_status_meta($days)
{
    if ($days === null) return array('ยังไม่กำหนดวันหมดอายุ', 'muted');
    if ($days < 0) return array('หมดอายุแล้ว ' . abs((int)$days) . ' วัน', 'danger');
    if ($days === 0) return array('ครบกำหนดวันนี้', 'danger');
    if ($days <= 7) return array('เหลือ ' . (int)$days . ' วัน', 'danger');
    if ($days <= 30) return array('เหลือ ' . (int)$days . ' วัน', 'warning');
    return array('เหลือ ' . (int)$days . ' วัน', 'success');
}

function vehicle_porobo_alert_key($days, $alertDays)
{
    if ($days === null) return '';
    $alertDays = (int)$alertDays;
    if ($alertDays <= 0) $alertDays = 30;
    if ($days < 0) return 'expired';
    if ($days === 0) return 'due_today';
    if ($days <= 1) return '1_day';
    if ($days <= 7) return '7_days';
    if ($days <= $alertDays) return 'advance_' . $alertDays;
    return '';
}

function vehicle_porobo_already_sent($vehicleId, $expiryDate, $alertKey)
{
    global $conn;
    if (!vehicle_table_exists('vehicle_porobo_line_logs')) return false;
    $stmt = $conn->prepare("SELECT id FROM vehicle_porobo_line_logs WHERE vehicle_id=? AND expiry_date=? AND alert_key=? AND send_status='success' LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('iss', $vehicleId, $expiryDate, $alertKey);
    $stmt->execute();
    $res = $stmt->get_result();
    $found = $res && $res->num_rows > 0;
    $stmt->close();
    return $found;
}

function vehicle_porobo_build_message($row, $days, $test = false)
{
    $meta = vehicle_porobo_status_meta($days);
    $title = $test ? '🧪 ทดสอบแจ้งเตือน พ.ร.บ. รถ' : '🚨 แจ้งเตือน พ.ร.บ. รถใกล้ครบกำหนด';
    $registration = isset($row['registration']) ? $row['registration'] : '-';
    $name = isset($row['display_name']) && trim($row['display_name']) !== '' ? $row['display_name'] : $registration;
    $provider = isset($row['porobo_provider']) && trim($row['porobo_provider']) !== '' ? $row['porobo_provider'] : '-';
    $policy = isset($row['porobo_policy_no']) && trim($row['porobo_policy_no']) !== '' ? $row['porobo_policy_no'] : '-';
    $expiry = isset($row['porobo_expiry_date']) ? $row['porobo_expiry_date'] : '';

    return $title . "\n"
        . "โรงพยาบาลภักดีชุมพล\n"
        . "━━━━━━━━━━━━━━━━\n"
        . "ทะเบียนรถ: " . $registration . "\n"
        . "รถ: " . $name . "\n"
        . "เลขที่กรมธรรม์/พ.ร.บ.: " . $policy . "\n"
        . "บริษัท/ผู้รับประกัน: " . $provider . "\n"
        . "วันหมดอายุ: " . vehicle_format_thai_date($expiry) . "\n"
        . "สถานะ: " . $meta[0] . "\n"
        . "━━━━━━━━━━━━━━━━\n"
        . "กรุณาดำเนินการต่ออายุ พ.ร.บ. ก่อนครบกำหนด";
}

function vehicle_porobo_log($row, $alertKey, $days, $eventName, $line)
{
    global $conn;
    if (!vehicle_table_exists('vehicle_porobo_line_logs')) return;

    $vehicleId = (int)$row['id'];
    $expiry = isset($row['porobo_expiry_date']) ? (string)$row['porobo_expiry_date'] : null;
    $status = !empty($line['ok']) ? 'success' : 'failed';
    $http = isset($line['http']) ? (int)$line['http'] : 0;
    $raw = isset($line['raw']) ? (string)$line['raw'] : '';
    $error = isset($line['error']) ? (string)$line['error'] : '';
    $createdBy = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    $stmt = $conn->prepare("INSERT INTO vehicle_porobo_line_logs(vehicle_id,expiry_date,alert_key,days_remaining,event_name,send_status,http_code,response_text,error_text,created_by,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,NOW())");
    if (!$stmt) return;
    $stmt->bind_param('issississi', $vehicleId, $expiry, $alertKey, $days, $eventName, $status, $http, $raw, $error, $createdBy);
    $stmt->execute();
    $stmt->close();
}

function vehicle_porobo_send_vehicle($row, $force = false, $test = false)
{
    $days = vehicle_porobo_days_remaining(isset($row['porobo_expiry_date']) ? $row['porobo_expiry_date'] : '');
    $alertDays = isset($row['porobo_alert_days']) ? (int)$row['porobo_alert_days'] : 30;
    $alertKey = vehicle_porobo_alert_key($days, $alertDays);

    if ($test) {
        $alertKey = 'test_' . date('Ymd_His');
    } elseif ($force) {
        $alertKey = 'manual_' . date('Ymd_His');
    } elseif ($alertKey === '') {
        return array('ok' => true, 'skipped' => true, 'reason' => 'ยังไม่ถึงช่วงแจ้งเตือน', 'days' => $days);
    } elseif (vehicle_porobo_already_sent((int)$row['id'], (string)$row['porobo_expiry_date'], $alertKey)) {
        return array('ok' => true, 'skipped' => true, 'reason' => 'เคยส่งแจ้งเตือนระดับนี้แล้ว', 'days' => $days);
    }

    $message = vehicle_porobo_build_message($row, $days, $test);
    $event = $test ? 'vehicle_porobo_test' : ($force ? 'vehicle_porobo_manual' : 'vehicle_porobo_due');
    $line = vehicle_send_line($message, $event);
    vehicle_porobo_log($row, $alertKey, $days === null ? 0 : $days, $event, $line);
    $line['days'] = $days;
    $line['alert_key'] = $alertKey;
    return $line;
}

function vehicle_porobo_scan_and_send($force = false, $onlyVehicleId = 0)
{
    global $conn;
    $summary = array('checked' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0, 'items' => array());

    if (!vehicle_table_exists('vehicle_fleet') || !vehicle_column_exists('vehicle_fleet', 'porobo_expiry_date')) {
        $summary['error'] = 'ยังไม่ได้ติดตั้งฟิลด์ พ.ร.บ. รถ';
        return $summary;
    }

    $where = "status<>'inactive' AND porobo_expiry_date IS NOT NULL AND porobo_expiry_date<>'0000-00-00'";
    if ((int)$onlyVehicleId > 0) $where .= ' AND id=' . (int)$onlyVehicleId;
    $rs = $conn->query("SELECT * FROM vehicle_fleet WHERE $where ORDER BY porobo_expiry_date, registration");

    while ($rs && ($row = $rs->fetch_assoc())) {
        $summary['checked']++;
        $result = vehicle_porobo_send_vehicle($row, $force, false);
        $summary['items'][] = array(
            'vehicle_id' => (int)$row['id'],
            'registration' => $row['registration'],
            'result' => $result
        );
        if (!empty($result['skipped'])) $summary['skipped']++;
        elseif (!empty($result['ok'])) $summary['sent']++;
        else $summary['failed']++;
    }
    return $summary;
}
