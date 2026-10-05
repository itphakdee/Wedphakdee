<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.manage');
if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: rooms.php'); exit; }
if (!meeting_verify_csrf(isset($_POST['csrf_token'])?$_POST['csrf_token']:'')) { die('CSRF validation failed'); }
$id=(int)$_POST['id']; $name=trim($_POST['room_name']); $code=trim($_POST['room_code']); $capacity=(int)$_POST['capacity']; $location=trim($_POST['location']); $equipment=trim($_POST['equipment']); $color=trim($_POST['color']); $status=$_POST['status']==='inactive'?'inactive':'active';
if($id<=0||$name===''||$capacity<1){ header('Location: rooms.php?status=error&message='.urlencode('ข้อมูลไม่ครบถ้วน')); exit; }
if(!preg_match('/^#[0-9a-fA-F]{6}$/',$color)) $color='#0f766e';
$stmt=$conn->prepare("UPDATE meeting_rooms SET room_name=?,room_code=?,capacity=?,location=?,equipment=?,color=?,status=?,updated_at=NOW() WHERE id=?");
$stmt->bind_param('ssissssi',$name,$code,$capacity,$location,$equipment,$color,$status,$id);
if(!$stmt->execute()){ $err=$stmt->error; $stmt->close(); header('Location: rooms.php?status=error&message='.urlencode('บันทึกไม่สำเร็จ: '.$err)); exit; }
$stmt->close(); header('Location: rooms.php?status=success&message='.urlencode('แก้ไขข้อมูลห้องประชุมเรียบร้อยแล้ว')); exit;
