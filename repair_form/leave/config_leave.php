<?php
require_once __DIR__ . '/../../config.php';

date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

function leave_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function leave_table_exists($name)
{
    global $conn;
    $safe = $conn->real_escape_string($name);
    $res = $conn->query("SHOW TABLES LIKE '{$safe}'");
    return $res && $res->num_rows > 0;
}

function leave_column_exists($table, $column)
{
    global $conn;
    $table = str_replace('`', '', $table);
    $safe = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$safe}'");
    return $res && $res->num_rows > 0;
}

function leave_current_user()
{
    static $cache = null;
    global $conn;

    if ($cache !== null) {
        return $cache;
    }

    $uid = (int)$_SESSION['user_id'];
    $hasPersonnel = leave_table_exists('personnel');

    if ($hasPersonnel) {
        $sql = "SELECT u.id, u.fullname, u.username, u.email, u.department, u.department_id,
                       u.status, u.role, u.supervisor_id,
                       p.employee_code, p.position_name,
                       COALESCE(NULLIF(p.department,''), u.department) AS profile_department,
                       COALESCE(p.department_id, u.department_id) AS profile_department_id,
                       p.phone
                FROM users u
                LEFT JOIN personnel p ON p.user_id = u.id
                WHERE u.id=? LIMIT 1";
    } else {
        $sql = "SELECT u.id, u.fullname, u.username, u.email, u.department, u.department_id,
                       u.status, u.role, u.supervisor_id,
                       '' AS employee_code, '' AS position_name,
                       u.department AS profile_department,
                       u.department_id AS profile_department_id,
                       '' AS phone
                FROM users u WHERE u.id=? LIMIT 1";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('ไม่สามารถอ่านข้อมูลผู้ใช้งานได้: ' . leave_e($conn->error));
    }
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    $cache = $result->fetch_assoc();
    $stmt->close();

    if (!$cache || $cache['status'] !== 'active') {
        session_destroy();
        header('Location: ../../login.php');
        exit;
    }

    return $cache;
}

function leave_is_admin()
{
    $u = leave_current_user();
    return isset($u['role']) && strtolower((string)$u['role']) === 'admin';
}

function leave_csrf_token()
{
    if (empty($_SESSION['leave_csrf_token'])) {
        $_SESSION['leave_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['leave_csrf_token'];
}

function leave_require_csrf()
{
    $posted = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
    $session = isset($_SESSION['leave_csrf_token']) ? (string)$_SESSION['leave_csrf_token'] : '';
    if ($session === '' || $posted === '' || !hash_equals($session, $posted)) {
        http_response_code(419);
        die('คำขอหมดอายุหรือ CSRF token ไม่ถูกต้อง กรุณาย้อนกลับและลองใหม่');
    }
}

function leave_flash($type, $message)
{
    $_SESSION['leave_flash'] = array('type' => $type, 'message' => $message);
}

function leave_flash_get()
{
    $flash = isset($_SESSION['leave_flash']) ? $_SESSION['leave_flash'] : null;
    unset($_SESSION['leave_flash']);
    return $flash;
}

function leave_redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function leave_fiscal_year($date = null)
{
    $ts = $date ? strtotime($date) : time();
    $y = (int)date('Y', $ts);
    $m = (int)date('n', $ts);
    $endYear = ($m >= 10) ? $y + 1 : $y;
    return $endYear + 543;
}

function leave_fiscal_bounds($fiscalYear)
{
    $be = (int)$fiscalYear;
    $endGregorian = $be - 543;
    $startGregorian = $endGregorian - 1;
    return array($startGregorian . '-10-01', $endGregorian . '-09-30');
}

function leave_date_thai($date, $withTime = false)
{
    if (!$date) return '-';
    $ts = strtotime($date);
    if (!$ts) return '-';
    $months = array('', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.');
    $text = date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . ((int)date('Y', $ts) + 543);
    if ($withTime) $text .= ' ' . date('H:i', $ts) . ' น.';
    return $text;
}

function leave_status_info($status)
{
    $map = array(
        'pending_handover' => array('รอเพื่อนร่วมงานรับมอบงาน', 'warning'),
        'pending_supervisor' => array('รอหัวหน้างานอนุมัติ', 'info'),
        'approved' => array('อนุมัติ', 'success'),
        'rejected' => array('ไม่อนุมัติ', 'danger'),
        'cancel_requested' => array('รอยืนยันยกเลิก', 'purple'),
        'cancelled' => array('ยกเลิกแล้ว', 'muted')
    );
    return isset($map[$status]) ? $map[$status] : array($status, 'muted');
}

function leave_handover_info($status)
{
    $map = array(
        'pending' => array('รอรับมอบงาน', 'warning'),
        'accepted' => array('รับมอบงานแล้ว', 'success'),
        'rejected' => array('ส่งคืนให้แก้ไข', 'danger')
    );
    return isset($map[$status]) ? $map[$status] : array($status, 'muted');
}

function leave_supervisor_info($status)
{
    $map = array(
        'pending' => array('รอเห็นชอบ', 'warning'),
        'approved' => array('เห็นชอบ', 'success'),
        'rejected' => array('ไม่เห็นชอบ', 'danger')
    );
    return isset($map[$status]) ? $map[$status] : array($status, 'muted');
}

function leave_generate_no($fiscalYear)
{
    global $conn;
    $prefix = 'LV' . (int)$fiscalYear . '-';
    $like = $prefix . '%';
    $stmt = $conn->prepare("SELECT leave_no FROM leave_applications WHERE leave_no LIKE ? ORDER BY id DESC LIMIT 1");
    $next = 1;
    if ($stmt) {
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $parts = explode('-', $row['leave_no']);
            $next = isset($parts[1]) ? ((int)$parts[1] + 1) : 1;
        }
        $stmt->close();
    }
    return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
}

function leave_day_count($start, $end)
{
    $s = strtotime($start);
    $e = strtotime($end);
    if (!$s || !$e || $e < $s) return 0;
    return (int)floor(($e - $s) / 86400) + 1;
}

/**
 * ตรวจสอบหน่วยงานจริงของผู้ใช้งานจากฐานข้อมูล
 * ลำดับความสำคัญ:
 * 1) personnel.department_id / personnel.department
 * 2) users.department_id / users.department
 * 3) เติมชื่อ/รหัสจากตาราง departments เมื่อมีข้อมูลเพียงด้านเดียว
 */
function leave_user_department_info($user)
{
    global $conn;

    $deptId = isset($user['profile_department_id']) ? (int)$user['profile_department_id'] : 0;
    $deptName = isset($user['profile_department']) ? trim((string)$user['profile_department']) : '';

    if ($deptId <= 0 && isset($user['department_id'])) {
        $deptId = (int)$user['department_id'];
    }
    if ($deptName === '' && isset($user['department'])) {
        $deptName = trim((string)$user['department']);
    }

    if (leave_table_exists('departments')) {
        if ($deptId > 0) {
            $stmt = $conn->prepare("SELECT id, department_name FROM departments WHERE id=? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $deptId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($row) {
                    $deptId = (int)$row['id'];
                    $deptName = trim((string)$row['department_name']);
                }
            }
        } elseif ($deptName !== '') {
            $stmt = $conn->prepare("SELECT id, department_name FROM departments WHERE TRIM(department_name)=TRIM(?) LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $deptName);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($row) {
                    $deptId = (int)$row['id'];
                    $deptName = trim((string)$row['department_name']);
                }
            }
        }
    }

    return array(
        'id' => $deptId,
        'name' => $deptName,
        'has_department' => ($deptId > 0 || $deptName !== '')
    );
}

/**
 * ดึงหัวหน้างานตาม "แผนกของผู้ใช้" เท่านั้น
 * ไม่ fallback ไปหา Admin หรือหัวหน้าของแผนกอื่น เพื่อป้องกันใบลาส่งผิดคน
 */
function leave_supervisor_for_user($user)
{
    global $conn;

    if (!leave_table_exists('leave_supervisors')) {
        return null;
    }

    $dept = leave_user_department_info($user);
    $deptId = (int)$dept['id'];
    $deptName = trim((string)$dept['name']);

    if (!$dept['has_department']) {
        return null;
    }

    // ให้ department_id เป็นตัวหลัก เพราะแม่นยำกว่าชื่อแผนก
    if ($deptId > 0) {
        $stmt = $conn->prepare(
            "SELECT u.id, u.fullname, u.username, u.department, u.department_id,
                    ls.department_id AS mapped_department_id,
                    ls.department_name AS mapped_department_name
             FROM leave_supervisors ls
             JOIN users u ON u.id = ls.supervisor_user_id
             WHERE ls.department_id = ?
               AND u.status = 'active'
             LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param('i', $deptId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row) {
                $row['resolved_department_id'] = $deptId;
                $row['resolved_department_name'] = $deptName;
                $row['matched_by'] = 'department_id';
                return $row;
            }
        }
    }

    // รองรับฐานข้อมูลเก่าที่กำหนดไว้ด้วยชื่อแผนก แต่ยังไม่มี department_id
    if ($deptName !== '') {
        $stmt = $conn->prepare(
            "SELECT u.id, u.fullname, u.username, u.department, u.department_id,
                    ls.department_id AS mapped_department_id,
                    ls.department_name AS mapped_department_name
             FROM leave_supervisors ls
             JOIN users u ON u.id = ls.supervisor_user_id
             WHERE TRIM(ls.department_name) = TRIM(?)
               AND u.status = 'active'
             LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param('s', $deptName);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row) {
                $row['resolved_department_id'] = $deptId;
                $row['resolved_department_name'] = $deptName;
                $row['matched_by'] = 'department_name';
                return $row;
            }
        }
    }

    return null;
}

function leave_upload_file($field, $subdir, $prefix)
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) throw new Exception('อัปโหลดไฟล์ไม่สำเร็จ');
    if ($_FILES[$field]['size'] > 10 * 1024 * 1024) throw new Exception('ไฟล์ต้องมีขนาดไม่เกิน 10 MB');

    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $allowed = array('pdf', 'jpg', 'jpeg', 'png');
    if (!in_array($ext, $allowed, true)) throw new Exception('รองรับเฉพาะ PDF, JPG, JPEG และ PNG');

    $dir = __DIR__ . '/uploads/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new Exception('ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้');

    $name = $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) throw new Exception('บันทึกไฟล์แนบไม่สำเร็จ');
    return 'uploads/' . $subdir . '/' . $name;
}

function leave_delete_file($relative)
{
    if (!$relative) return;
    $base = realpath(__DIR__ . '/uploads');
    $file = realpath(__DIR__ . '/' . $relative);
    if ($base && $file && strpos($file, $base) === 0 && is_file($file)) @unlink($file);
}

function leave_audit($applicationId, $action, $detail)
{
    global $conn;
    if (!leave_table_exists('leave_audit_logs')) return;
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $stmt = $conn->prepare("INSERT INTO leave_audit_logs(application_id, actor_user_id, action, detail, created_at) VALUES(?,?,?,?,NOW())");
    if ($stmt) {
        $stmt->bind_param('iiss', $applicationId, $uid, $action, $detail);
        $stmt->execute();
        $stmt->close();
    }
}

function leave_used_days($userId, $fiscalYear, $leaveTypeId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT COALESCE(SUM(leave_days),0) used_days FROM leave_applications WHERE user_id=? AND fiscal_year=? AND leave_type_id=? AND status='approved'");
    if (!$stmt) return 0;
    $stmt->bind_param('iii', $userId, $fiscalYear, $leaveTypeId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float)$row['used_days'];
}

function leave_quota($userId, $fiscalYear, $typeRow)
{
    global $conn;
    $typeId = (int)$typeRow['id'];
    $stmt = $conn->prepare("SELECT quota_days, carried_days, adjustment_days FROM leave_balances WHERE user_id=? AND fiscal_year=? AND leave_type_id=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('iii', $userId, $fiscalYear, $typeId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            return (float)$row['quota_days'] + (float)$row['carried_days'] + (float)$row['adjustment_days'];
        }
    }
    return $typeRow['default_quota_days'] === null ? null : (float)$typeRow['default_quota_days'];
}

function leave_can_view_application($row)
{
    $uid = (int)$_SESSION['user_id'];
    return leave_is_admin()
        || (int)$row['user_id'] === $uid
        || (int)$row['handover_user_id'] === $uid
        || (int)$row['supervisor_user_id'] === $uid;
}

function leave_can_edit_application($row)
{
    $uid = (int)$_SESSION['user_id'];
    if (leave_is_admin()) return !in_array($row['status'], array('cancelled'), true);
    if ((int)$row['user_id'] !== $uid) return false;
    return in_array($row['status'], array('pending_handover', 'pending_supervisor'), true);
}

function leave_get_application($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id=? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

function leave_format_days($days)
{
    $n = (float)$days;
    if (abs($n - round($n)) < 0.001) return number_format($n, 0);
    return number_format($n, 1);
}

leave_csrf_token();

// ฟังก์ชันเสริมสำหรับอ่านข้อมูลบุคลากรตาม user_id
if (!function_exists('leave_user_profile_by_id')) {
    function leave_user_profile_by_id($uid)
    {
        global $conn;
        $uid = (int)$uid;
        $hasPersonnel = leave_table_exists('personnel');
        if ($hasPersonnel) {
            $sql = "SELECT u.id,u.fullname,u.username,u.email,u.department,u.department_id,u.status,u.role,u.supervisor_id,
                           p.employee_code,p.position_name,COALESCE(NULLIF(p.department,''),u.department) profile_department,
                           COALESCE(p.department_id,u.department_id) profile_department_id,p.phone
                    FROM users u LEFT JOIN personnel p ON p.user_id=u.id WHERE u.id=? LIMIT 1";
        } else {
            $sql = "SELECT u.id,u.fullname,u.username,u.email,u.department,u.department_id,u.status,u.role,u.supervisor_id,
                           '' employee_code,'' position_name,u.department profile_department,u.department_id profile_department_id,'' phone
                    FROM users u WHERE u.id=? LIMIT 1";
        }
        $stmt=$conn->prepare($sql); if(!$stmt) return null; $stmt->bind_param('i',$uid); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close(); return $row;
    }
}
