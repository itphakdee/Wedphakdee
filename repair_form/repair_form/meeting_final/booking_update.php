<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.view');
if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: bookings.php'); exit; }
if (!meeting_verify_csrf(isset($_POST['csrf_token'])?$_POST['csrf_token']:'')) { die('CSRF validation failed'); }

$id=isset($_POST['id'])?(int)$_POST['id']:0;
$stmt=$conn->prepare("SELECT user_id,status FROM meeting_bookings WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id); $stmt->execute(); $stmt->bind_result($ownerId,$currentStatus);
if(!$stmt->fetch()){ $stmt->close(); header('Location: bookings.php'); exit; } $stmt->close();

$userId=(int)$_SESSION['user_id'];
$isOwner=!empty($ownerId) && (int)$ownerId===$userId;
$canManage=has_permission('meeting.manage');
if(!($isOwner||$canManage) || !in_array($currentStatus,array('pending','approved'),true)){ http_response_code(403); die('ไม่มีสิทธิ์แก้ไขรายการนี้'); }

$roomId=(int)$_POST['room_id']; $title=trim($_POST['meeting_title']); $name=trim($_POST['requester_name']);
$department=trim($_POST['department']); $phone=trim($_POST['phone']); $date=trim($_POST['meeting_date']);
$start=trim($_POST['start_time']); $end=trim($_POST['end_time']); $attendees=(int)$_POST['attendees'];
$detail=trim($_POST['detail']); $platform=trim($_POST['meeting_platform']); $url=trim($_POST['meeting_url']);

$errors=array();
if($roomId<=0||$title===''||$name===''||$date===''||$start===''||$end==='') $errors[]='กรุณากรอกข้อมูลที่จำเป็นให้ครบ';
if($attendees<1) $errors[]='จำนวนผู้เข้าร่วมไม่ถูกต้อง';
if($date<date('Y-m-d')) $errors[]='ไม่สามารถเลือกวันที่ผ่านมาแล้วได้';
if($start>=$end) $errors[]='เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม';
$platforms=meeting_platforms();
if($platform!==''&&!isset($platforms[$platform])) $errors[]='แพลตฟอร์มไม่ถูกต้อง';
if($url!==''&&!meeting_is_valid_url($url)) $errors[]='ลิงก์ประชุมไม่ถูกต้อง';
if($url!==''&&$platform==='') $platform='other';
if($errors){ header('Location: booking_edit.php?id='.$id.'&message='.urlencode(implode(' · ',$errors))); exit; }

$stmt=$conn->prepare("SELECT room_name,capacity FROM meeting_rooms WHERE id=? AND status='active' LIMIT 1");
$stmt->bind_param('i',$roomId); $stmt->execute(); $stmt->bind_result($roomName,$capacity);
if(!$stmt->fetch()){ $stmt->close(); header('Location: booking_edit.php?id='.$id.'&message='.urlencode('ไม่พบห้องประชุม')); exit; } $stmt->close();
if($attendees>(int)$capacity){ header('Location: booking_edit.php?id='.$id.'&message='.urlencode('จำนวนผู้เข้าร่วมเกินความจุห้อง')); exit; }

$stmt=$conn->prepare("SELECT id FROM meeting_bookings WHERE room_id=? AND meeting_date=? AND status IN ('pending','approved') AND id<>? AND start_time<? AND end_time>? LIMIT 1");
$stmt->bind_param('isiss',$roomId,$date,$id,$end,$start); $stmt->execute(); $stmt->store_result(); $dup=$stmt->num_rows>0; $stmt->close();
if($dup){ header('Location: booking_edit.php?id='.$id.'&message='.urlencode('ช่วงเวลานี้มีผู้จองห้องแล้ว')); exit; }

$stmt=$conn->prepare("UPDATE meeting_bookings SET room_id=?,meeting_title=?,requester_name=?,department=?,phone=?,meeting_date=?,start_time=?,end_time=?,attendees=?,detail=?,meeting_platform=?,meeting_url=?,updated_at=NOW() WHERE id=?");
$stmt->bind_param('isssssssisssi',$roomId,$title,$name,$department,$phone,$date,$start,$end,$attendees,$detail,$platform,$url,$id);
if(!$stmt->execute()){ $err=$stmt->error; $stmt->close(); die('SQL ERROR: '.meeting_e($err)); } $stmt->close();
header('Location: booking_detail.php?id='.$id.'&status=success&message='.urlencode('แก้ไขข้อมูลการจองเรียบร้อยแล้ว')); exit;
