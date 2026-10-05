<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.manage');
if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: rooms.php'); exit; }
if (!meeting_verify_csrf(isset($_POST['csrf_token'])?$_POST['csrf_token']:'')) { die('CSRF validation failed'); }

$name=trim($_POST['room_name']); $code=trim($_POST['room_code']); $capacity=(int)$_POST['capacity'];
$location=trim($_POST['location']); $equipment=trim($_POST['equipment']); $color=trim($_POST['color']);
$status=isset($_POST['status'])&&$_POST['status']==='inactive'?'inactive':'active';
if($name===''||$capacity<1){ header('Location: room_add.php'); exit; }
if(!preg_match('/^#[0-9a-fA-F]{6}$/',$color)) $color='#0f766e';

$stmt=$conn->prepare("INSERT INTO meeting_rooms(room_name,room_code,capacity,location,equipment,color,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?,NOW(),NOW())");
$stmt->bind_param('ssissss',$name,$code,$capacity,$location,$equipment,$color,$status);
if(!$stmt->execute()){ $err=$stmt->error; $stmt->close(); header('Location: rooms.php?status=error&message='.urlencode('บันทึกไม่สำเร็จ: '.$err)); exit; }
$stmt->close(); header('Location: rooms.php?status=success&message='.urlencode('เพิ่มห้องประชุมเรียบร้อยแล้ว')); exit;
