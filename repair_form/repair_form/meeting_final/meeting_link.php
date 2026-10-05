<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.view');

$id=isset($_GET['id'])?(int)$_GET['id']:0;
if($id<=0){ header('Location: bookings.php'); exit; }

$stmt=$conn->prepare("SELECT meeting_url,status FROM meeting_bookings WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id); $stmt->execute(); $stmt->bind_result($url,$status);
if(!$stmt->fetch()){ $stmt->close(); http_response_code(404); die('ไม่พบรายการประชุม'); } $stmt->close();

$url=trim((string)$url);
if($status!=='approved'){ http_response_code(403); die('ลิงก์ประชุมจะเปิดได้เมื่อรายการได้รับอนุมัติแล้ว'); }
if($url==='' || !meeting_is_valid_url($url)){ http_response_code(400); die('รายการนี้ไม่มีลิงก์ประชุมที่ถูกต้อง'); }

header('Location: '.$url);
exit;
