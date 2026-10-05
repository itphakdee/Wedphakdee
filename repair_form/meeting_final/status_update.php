<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.view');

if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: bookings.php'); exit; }
if (!meeting_verify_csrf(isset($_POST['csrf_token'])?$_POST['csrf_token']:'')) { die('CSRF validation failed'); }

$id=isset($_POST['id'])?(int)$_POST['id']:0;
$status=trim(isset($_POST['status'])?$_POST['status']:'');
if($id<=0 || !in_array($status,array('approved','rejected','cancelled'),true)){ header('Location: bookings.php?status=error&message='.urlencode('ข้อมูลสถานะไม่ถูกต้อง')); exit; }

$stmt=$conn->prepare("SELECT user_id,status,room_id,meeting_date,start_time,end_time FROM meeting_bookings WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id); $stmt->execute(); $stmt->bind_result($ownerId,$currentStatus,$roomId,$date,$start,$end);
if(!$stmt->fetch()){ $stmt->close(); header('Location: bookings.php?status=error&message='.urlencode('ไม่พบรายการจอง')); exit; } $stmt->close();

$userId=(int)$_SESSION['user_id'];
$isOwner=!empty($ownerId)&&(int)$ownerId===$userId;
$isAdmin=is_admin_user();
$canApprove=has_permission('meeting.approve');
$canManage=has_permission('meeting.manage');

if(in_array($status,array('approved','rejected'),true)){
    if(!$canApprove){ http_response_code(403); die('403 Forbidden: ไม่มีสิทธิ์อนุมัติห้องประชุม'); }
    if($currentStatus!=='pending'){ header('Location: booking_detail.php?id='.$id.'&status=error&message='.urlencode('รายการนี้ไม่ได้อยู่ในสถานะรออนุมัติ')); exit; }

    if($status==='approved'){
        $stmt=$conn->prepare("SELECT id FROM meeting_bookings WHERE room_id=? AND meeting_date=? AND status='approved' AND id<>? AND start_time<? AND end_time>? LIMIT 1");
        $stmt->bind_param('isiss',$roomId,$date,$id,$end,$start); $stmt->execute(); $stmt->store_result(); $conflict=$stmt->num_rows>0; $stmt->close();
        if($conflict){ header('Location: booking_detail.php?id='.$id.'&status=error&message='.urlencode('ไม่สามารถอนุมัติได้ เนื่องจากมีรายการอนุมัติที่เวลาซ้ำกัน')); exit; }
    }

    $approver=!empty($_SESSION['fullname'])?$_SESSION['fullname']:(!empty($_SESSION['username'])?$_SESSION['username']:'Administrator');
    $stmt=$conn->prepare("UPDATE meeting_bookings SET status=?,approved_by=?,approved_at=NOW(),updated_at=NOW() WHERE id=?");
    $stmt->bind_param('ssi',$status,$approver,$id);
}else{
    if(!($isOwner||$canManage)){ http_response_code(403); die('403 Forbidden: ไม่มีสิทธิ์ยกเลิกรายการนี้'); }
    if(!in_array($currentStatus,array('pending','approved'),true)){ header('Location: booking_detail.php?id='.$id.'&status=error&message='.urlencode('ไม่สามารถยกเลิกรายการในสถานะนี้ได้')); exit; }
    $stmt=$conn->prepare("UPDATE meeting_bookings SET status='cancelled',updated_at=NOW() WHERE id=?");
    $stmt->bind_param('i',$id);
}
if(!$stmt->execute()){ $err=$stmt->error; $stmt->close(); die('SQL ERROR: '.meeting_e($err)); } $stmt->close();

header('Location: booking_detail.php?id='.$id.'&status=success&message='.urlencode('อัปเดตสถานะเป็น '.meeting_status_label($status).' เรียบร้อยแล้ว')); exit;
