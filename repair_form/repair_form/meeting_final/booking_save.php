<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
require_permission('meeting.create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    meeting_redirect('booking.php');
}
if (!meeting_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
    meeting_redirect('booking.php?status=error&message=' . urlencode('คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง'));
}
if (!meeting_schema_ready($conn)) {
    meeting_redirect('install.php');
}

$roomId = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
$title = trim(isset($_POST['meeting_title']) ? $_POST['meeting_title'] : '');
$name = trim(isset($_POST['requester_name']) ? $_POST['requester_name'] : '');
$department = trim(isset($_POST['department']) ? $_POST['department'] : '');
$phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
$date = trim(isset($_POST['meeting_date']) ? $_POST['meeting_date'] : '');
$start = trim(isset($_POST['start_time']) ? $_POST['start_time'] : '');
$end = trim(isset($_POST['end_time']) ? $_POST['end_time'] : '');
$attendees = isset($_POST['attendees']) ? (int)$_POST['attendees'] : 0;
$detail = trim(isset($_POST['detail']) ? $_POST['detail'] : '');
$platform = trim(isset($_POST['meeting_platform']) ? $_POST['meeting_platform'] : '');
$url = trim(isset($_POST['meeting_url']) ? $_POST['meeting_url'] : '');
$userId = (int)$_SESSION['user_id'];

$errors = array();
if ($roomId <= 0) $errors[] = 'กรุณาเลือกห้องประชุม';
if ($title === '') $errors[] = 'กรุณากรอกหัวข้อการประชุม';
if ($name === '') $errors[] = 'กรุณากรอกชื่อผู้ขอใช้บริการ';
if ($date === '') $errors[] = 'กรุณาเลือกวันที่ประชุม';
if ($start === '' || $end === '') $errors[] = 'กรุณาระบุเวลาเริ่มและเวลาสิ้นสุด';
if ($attendees < 1) $errors[] = 'จำนวนผู้เข้าร่วมต้องไม่น้อยกว่า 1 คน';
if ($date !== '' && $date < date('Y-m-d')) $errors[] = 'ไม่สามารถจองวันที่ผ่านมาแล้วได้';
if ($start !== '' && $end !== '' && $start >= $end) $errors[] = 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม';

$platforms = meeting_platforms();
if ($platform !== '' && !isset($platforms[$platform])) {
    $errors[] = 'แพลตฟอร์มประชุมไม่ถูกต้อง';
}
if ($url !== '' && !meeting_is_valid_url($url)) {
    $errors[] = 'ลิงก์ประชุมต้องเป็น URL แบบ http:// หรือ https://';
}
if ($url !== '' && $platform === '') {
    $platform = 'other';
}

if ($errors) {
    meeting_redirect('booking.php?status=error&message=' . urlencode(implode(' · ', $errors)));
}

$stmt = $conn->prepare("SELECT room_name, capacity FROM meeting_rooms WHERE id=? AND status='active' LIMIT 1");
$stmt->bind_param('i', $roomId);
$stmt->execute();
$stmt->bind_result($roomName, $capacity);
if (!$stmt->fetch()) {
    $stmt->close();
    meeting_redirect('booking.php?status=error&message=' . urlencode('ไม่พบห้องประชุมหรือห้องถูกปิดใช้งาน'));
}
$stmt->close();

if ($attendees > (int)$capacity) {
    meeting_redirect('booking.php?status=error&message=' . urlencode('จำนวนผู้เข้าร่วมเกินความจุของ ' . $roomName . ' (' . (int)$capacity . ' คน)'));
}

$stmt = $conn->prepare("
    SELECT id
    FROM meeting_bookings
    WHERE room_id=?
      AND meeting_date=?
      AND status IN ('pending','approved')
      AND start_time < ?
      AND end_time > ?
    LIMIT 1
");
$stmt->bind_param('isss', $roomId, $date, $end, $start);
$stmt->execute();
$stmt->store_result();
$duplicate = $stmt->num_rows > 0;
$stmt->close();

if ($duplicate) {
    meeting_redirect('booking.php?room_id=' . $roomId . '&status=error&message=' . urlencode('ช่วงเวลานี้มีผู้จองห้องแล้ว กรุณาเลือกเวลาอื่น'));
}

$stmt = $conn->prepare("
    INSERT INTO meeting_bookings
    (user_id, room_id, meeting_title, requester_name, department, phone,
     meeting_date, start_time, end_time, attendees, detail,
     meeting_platform, meeting_url, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
");
if (!$stmt) {
    die('SQL ERROR: ' . meeting_e($conn->error));
}
$stmt->bind_param(
    'iisssssssisss',
    $userId, $roomId, $title, $name, $department, $phone,
    $date, $start, $end, $attendees, $detail, $platform, $url
);
if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    die('SQL ERROR: ' . meeting_e($err));
}
$newId = $stmt->insert_id;
$stmt->close();

meeting_redirect('booking_detail.php?id=' . (int)$newId . '&status=success&message=' . urlencode('บันทึกคำขอจองห้องเรียบร้อยแล้ว สถานะรออนุมัติ'));
