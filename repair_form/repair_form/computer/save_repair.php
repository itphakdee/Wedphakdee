<?php
/**
 * บันทึกงานแจ้งซ่อมคอมพิวเตอร์ + แจ้งเตือน LINE
 * PHP -> Google Apps Script -> LINE Messaging API
 *
 * ตำแหน่งไฟล์:
 * Wedphakdee/repair_form/computer/save_repair.php
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lineapi/line_apps_script_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: repair_form.php');
    exit;
}

function repair_redirect_error($message)
{
    $_SESSION['repair_form_error'] = (string)$message;
    header('Location: repair_form.php?error=1');
    exit;
}

function repair_clean_text($value, $maxLength)
{
    $value = trim((string)$value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }
    return substr($value, 0, $maxLength);
}

/**
 * ส่ง JSON ไปยัง Google Apps Script
 * คืนค่า array ที่มี success, status, http_code, response, error
 */
function repair_send_line_apps_script($payload)
{
    $result = array(
        'success' => false,
        'status' => 'error',
        'http_code' => 0,
        'response' => '',
        'error' => ''
    );

    if (!defined('LINE_APPS_SCRIPT_URL') || trim((string)LINE_APPS_SCRIPT_URL) === '') {
        $result['status'] = 'disabled';
        $result['error'] = 'ยังไม่ได้กำหนด LINE_APPS_SCRIPT_URL';
        return $result;
    }

    if (!defined('LINE_APPS_SCRIPT_SHARED_SECRET') || trim((string)LINE_APPS_SCRIPT_SHARED_SECRET) === '') {
        $result['status'] = 'disabled';
        $result['error'] = 'ยังไม่ได้กำหนด LINE_APPS_SCRIPT_SHARED_SECRET';
        return $result;
    }

    $payload['secret'] = LINE_APPS_SCRIPT_SHARED_SECRET;

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        $result['error'] = 'ไม่สามารถแปลงข้อมูลเป็น JSON ได้';
        return $result;
    }

    // ใช้ cURL เป็นหลัก
    if (function_exists('curl_init')) {
        $ch = curl_init(LINE_APPS_SCRIPT_URL);

        $options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json'
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => defined('LINE_CURL_CONNECT_TIMEOUT') ? LINE_CURL_CONNECT_TIMEOUT : 15,
            CURLOPT_TIMEOUT => defined('LINE_CURL_TIMEOUT') ? LINE_CURL_TIMEOUT : 35,
            CURLOPT_USERAGENT => 'PhakdeeChumphonRepair/1.0'
        );

        $sslVerify = defined('LINE_CURL_SSL_VERIFY') ? (bool)LINE_CURL_SSL_VERIFY : true;
        if ($sslVerify) {
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            $options[CURLOPT_SSL_VERIFYHOST] = 2;
        } else {
            // สำหรับ AppServ localhost ที่ยังไม่มี CA bundle
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        $result['http_code'] = $httpCode;
        $result['response'] = $response === false ? '' : (string)$response;

        if ($response === false || $curlErrno !== 0) {
            $result['error'] = 'cURL #' . $curlErrno . ': ' . ($curlError !== '' ? $curlError : 'Unknown cURL error');
            if ($effectiveUrl !== '') {
                $result['error'] .= ' | URL: ' . $effectiveUrl;
            }
            return $result;
        }
    } else {
        // Fallback กรณีไม่มี cURL และ allow_url_fopen เปิดอยู่
        $sslVerify = defined('LINE_CURL_SSL_VERIFY') ? (bool)LINE_CURL_SSL_VERIFY : true;
        $contextOptions = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type: application/json; charset=utf-8\r\nAccept: application/json\r\n",
                'content' => $json,
                'timeout' => defined('LINE_CURL_TIMEOUT') ? LINE_CURL_TIMEOUT : 35,
                'ignore_errors' => true
            ),
            'ssl' => array(
                'verify_peer' => $sslVerify,
                'verify_peer_name' => $sslVerify
            )
        );

        $context = stream_context_create($contextOptions);
        $response = @file_get_contents(LINE_APPS_SCRIPT_URL, false, $context);

        $httpCode = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $headerLine) {
                if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $headerLine, $m)) {
                    $httpCode = (int)$m[1];
                }
            }
        }

        $result['http_code'] = $httpCode;
        $result['response'] = $response === false ? '' : (string)$response;

        if ($response === false) {
            $result['error'] = 'ติดต่อ Google Apps Script ไม่สำเร็จ และ PHP cURL ไม่พร้อมใช้งาน';
            return $result;
        }
    }

    $decoded = json_decode($result['response'], true);

    if (!is_array($decoded)) {
        $result['error'] = 'Apps Script ตอบกลับมาไม่ใช่ JSON: ' . substr(strip_tags($result['response']), 0, 300);
        return $result;
    }

    if (empty($decoded['ok'])) {
        $result['error'] = isset($decoded['error'])
            ? (string)$decoded['error']
            : 'Apps Script แจ้งว่าส่ง LINE ไม่สำเร็จ';

        if (isset($decoded['line_status'])) {
            $result['error'] .= ' | LINE HTTP ' . (int)$decoded['line_status'];
        }
        if (!empty($decoded['line_response'])) {
            $result['error'] .= ' | ' . (string)$decoded['line_response'];
        }
        return $result;
    }

    // ต้องมี line_status 2xx จาก LINE Messaging API
    if (isset($decoded['line_status'])) {
        $lineStatus = (int)$decoded['line_status'];
        if ($lineStatus < 200 || $lineStatus >= 300) {
            $result['error'] = 'LINE Messaging API ตอบ HTTP ' . $lineStatus;
            return $result;
        }
    }

    $result['success'] = true;
    $result['status'] = 'success';
    return $result;
}

// ------------------------------
// ตรวจ CSRF
// ------------------------------
$csrfToken = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
$sessionToken = isset($_SESSION['repair_csrf_token']) ? (string)$_SESSION['repair_csrf_token'] : '';

if (
    $sessionToken === '' ||
    $csrfToken === '' ||
    !function_exists('hash_equals') ||
    !hash_equals($sessionToken, $csrfToken)
) {
    repair_redirect_error('คำขอหมดอายุหรือ CSRF Token ไม่ถูกต้อง กรุณาเปิดหน้าแจ้งซ่อมใหม่');
}

// ------------------------------
// รับข้อมูลฟอร์ม
// ------------------------------
$userId = (int)$_SESSION['user_id'];
$senderName = repair_clean_text(isset($_POST['sender_name']) ? $_POST['sender_name'] : '', 150);
$department = repair_clean_text(isset($_POST['department']) ? $_POST['department'] : '', 100);
$repairSystem = repair_clean_text(isset($_POST['repair_system']) ? $_POST['repair_system'] : '', 150);
$location = repair_clean_text(isset($_POST['location']) ? $_POST['location'] : '', 150);
$technicianId = isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0;
$details = repair_clean_text(isset($_POST['details']) ? $_POST['details'] : '', 2000);
$priority = repair_clean_text(isset($_POST['priority']) ? $_POST['priority'] : 'normal', 20);

$allowedPriority = array('normal', 'urgent', 'emergency');
if (!in_array($priority, $allowedPriority, true)) {
    $priority = 'normal';
}

if (
    $senderName === '' ||
    $department === '' ||
    $repairSystem === '' ||
    $location === '' ||
    $technicianId <= 0 ||
    $details === ''
) {
    repair_redirect_error('กรุณากรอกข้อมูลที่จำเป็นให้ครบทุกช่อง');
}

// ------------------------------
// ตรวจสอบช่าง
// ------------------------------
$technicianName = '';
$techStmt = $conn->prepare("SELECT name FROM technicians WHERE id = ? AND status = 'ใช้งาน' LIMIT 1");
if (!$techStmt) {
    repair_redirect_error('ไม่สามารถตรวจสอบข้อมูลช่างได้: ' . $conn->error);
}

$techStmt->bind_param('i', $technicianId);
if (!$techStmt->execute()) {
    $techStmt->close();
    repair_redirect_error('ไม่สามารถตรวจสอบข้อมูลช่างได้');
}

$techStmt->bind_result($technicianName);
$hasTechnician = $techStmt->fetch();
$techStmt->close();

if (!$hasTechnician || trim((string)$technicianName) === '') {
    repair_redirect_error('ช่างที่เลือกไม่พร้อมใช้งาน กรุณาเลือกรายชื่อใหม่');
}

// ------------------------------
// บันทึกฐานข้อมูลก่อน
// ------------------------------
$stmt = $conn->prepare(
    'INSERT INTO repair_jobs (user_id, sender_name, department, repair_system, location, technician_id, details, priority)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);

if (!$stmt) {
    repair_redirect_error('ไม่สามารถเตรียมคำสั่งบันทึกข้อมูลได้: ' . $conn->error);
}

$stmt->bind_param(
    'issssiss',
    $userId,
    $senderName,
    $department,
    $repairSystem,
    $location,
    $technicianId,
    $details,
    $priority
);

if (!$stmt->execute()) {
    $dbError = $stmt->error;
    $stmt->close();
    repair_redirect_error('บันทึกข้อมูลไม่สำเร็จ: ' . $dbError);
}

$jobId = (int)$stmt->insert_id;
$stmt->close();

$priorityMap = array(
    'normal' => 'ปกติ',
    'urgent' => 'ด่วน',
    'emergency' => 'ด่วนมาก'
);
$priorityText = isset($priorityMap[$priority]) ? $priorityMap[$priority] : 'ปกติ';

$createdAt = date('d/m/Y H:i') . ' น.';

$messageText =
    "🔧 แจ้งซ่อมใหม่ | โรงพยาบาลภักดีชุมพล\n" .
    "━━━━━━━━━━━━━━━━━━\n" .
    "เลขที่งาน: #" . $jobId . "\n" .
    "ผู้แจ้ง: " . $senderName . "\n" .
    "แผนก: " . $department . "\n" .
    "ระบบ: " . $repairSystem . "\n" .
    "สถานที่: " . $location . "\n" .
    "ผู้รับผิดชอบ: " . $technicianName . "\n" .
    "ความเร่งด่วน: " . $priorityText . "\n" .
    "รายละเอียด: " . $details . "\n" .
    "เวลารับแจ้ง: " . $createdAt . "\n" .
    "━━━━━━━━━━━━━━━━━━\n" .
    "ระบบแจ้งซ่อมและบริหารงานภายใน";

$lineResult = repair_send_line_apps_script(array(
    'event' => 'new_repair',
    'message' => $messageText,
    'job' => array(
        'id' => $jobId,
        'sender_name' => $senderName,
        'department' => $department,
        'repair_system' => $repairSystem,
        'location' => $location,
        'technician_id' => $technicianId,
        'technician_name' => $technicianName,
        'priority' => $priority,
        'priority_text' => $priorityText,
        'details' => $details,
        'created_at' => $createdAt
    )
));

// เก็บผลไว้ใน Session เพื่อแสดงบนหน้า repair_form.php
$_SESSION['repair_line_result'] = array(
    'success' => !empty($lineResult['success']),
    'status' => isset($lineResult['status']) ? $lineResult['status'] : 'error',
    'http_code' => isset($lineResult['http_code']) ? (int)$lineResult['http_code'] : 0,
    'error' => isset($lineResult['error']) ? (string)$lineResult['error'] : '',
    'response' => isset($lineResult['response']) ? substr((string)$lineResult['response'], 0, 1000) : ''
);

// Log เฉพาะกรณีผิดพลาด (ไม่เก็บ Token)
if (empty($lineResult['success'])) {
    $logLine = date('Y-m-d H:i:s') .
        ' | job=' . $jobId .
        ' | http=' . (isset($lineResult['http_code']) ? (int)$lineResult['http_code'] : 0) .
        ' | error=' . str_replace(array("\r", "\n"), ' ', isset($lineResult['error']) ? (string)$lineResult['error'] : '') .
        PHP_EOL;
    @file_put_contents(__DIR__ . '/../../lineapi/line_error.log', $logLine, FILE_APPEND);
}

// เปลี่ยน CSRF token หลังบันทึกสำเร็จ
if (function_exists('random_bytes')) {
    $_SESSION['repair_csrf_token'] = bin2hex(random_bytes(32));
} else {
    $_SESSION['repair_csrf_token'] = sha1(uniqid(mt_rand(), true));
}

$lineState = !empty($lineResult['success'])
    ? 'success'
    : (isset($lineResult['status']) ? $lineResult['status'] : 'error');

header(
    'Location: repair_form.php?saved=1&job_id=' . $jobId .
    '&line=' . rawurlencode($lineState)
);
exit;
