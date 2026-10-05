<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.view');
if (!meeting_schema_ready($conn)) { header('Location: install.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: bookings.php'); exit; }

$stmt = $conn->prepare("
SELECT b.*, r.room_name, r.room_code, r.capacity, r.location, r.equipment
FROM meeting_bookings b
INNER JOIN meeting_rooms r ON r.id=b.room_id
WHERE b.id=? LIMIT 1
");
$stmt->bind_param('i',$id); $stmt->execute(); $result=$stmt->get_result(); $b=$result->fetch_assoc(); $stmt->close();
if(!$b){ http_response_code(404); die('ไม่พบรายการจอง'); }

$userId=(int)$_SESSION['user_id'];
$isOwner=!empty($b['user_id']) && (int)$b['user_id']===$userId;
$canManage=has_permission('meeting.manage');
$isAdmin=is_admin_user();
$canApprove=has_permission('meeting.approve');
$canEdit=($isOwner||$canManage) && in_array($b['status'],array('pending','approved'),true);
?>
<!doctype html>
<html lang="th">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>รายละเอียดการจอง #<?= (int)$b['id'] ?></title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/meeting.css?v=20260831"></head>
<body class="meeting-body">
<header class="mh-topbar"><div class="mh-brand"><div class="mh-brand-mark">PDC</div><div><div class="mh-brand-title">รายละเอียดการจองห้องประชุม</div><div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล</div></div></div><a class="mh-btn mh-btn-light" href="bookings.php">← รายการจอง</a></header>
<main class="mh-wrap">
<section class="mh-page-title">
<div><span class="mh-kicker">MEETING BOOKING #<?= (int)$b['id'] ?></span><h1><?= meeting_e($b['meeting_title']) ?></h1><p><?= meeting_e($b['room_name']) ?> · <?= meeting_format_thai_date($b['meeting_date']) ?> · <?= substr($b['start_time'],0,5) ?> - <?= substr($b['end_time'],0,5) ?> น.</p></div>
<div class="mh-action-group"><?php if($canEdit):?><a class="mh-btn mh-btn-outline" href="booking_edit.php?id=<?= (int)$b['id'] ?>">แก้ไขข้อมูล</a><?php endif;?><?php if($b['meeting_url'] && $b['status']==='approved'):?><a class="mh-btn mh-btn-primary" href="meeting_link.php?id=<?= (int)$b['id'] ?>" target="_blank" rel="noopener">เข้าห้อง <?= meeting_e(meeting_platform_label($b['meeting_platform'])) ?> ↗</a><?php endif;?></div>
</section>

<?php if(!empty($_GET['message'])):?><div class="mh-alert <?= (!empty($_GET['status'])&&$_GET['status']==='success')?'mh-alert-success':'mh-alert-danger' ?>"><strong><?= meeting_e($_GET['message']) ?></strong></div><?php endif;?>

<div class="mh-detail-grid">
<section class="mh-panel">
<div class="mh-panel-head"><div><span class="mh-kicker">RESERVATION INFO</span><h2>ข้อมูลการจอง</h2></div><span class="mh-status <?= meeting_status_class($b['status']) ?>"><?= meeting_e(meeting_status_label($b['status'])) ?></span></div>
<div class="mh-info-grid">
<div><small>ห้องประชุม</small><strong><?= meeting_e($b['room_name']) ?></strong><span><?= meeting_e($b['room_code']) ?> · รองรับ <?= (int)$b['capacity'] ?> คน</span></div>
<div><small>สถานที่</small><strong><?= meeting_e($b['location']?:'-') ?></strong><span><?= meeting_e($b['equipment']?:'ไม่ได้ระบุอุปกรณ์') ?></span></div>
<div><small>วันที่</small><strong><?= meeting_format_thai_date($b['meeting_date']) ?></strong><span><?= substr($b['start_time'],0,5) ?> - <?= substr($b['end_time'],0,5) ?> น.</span></div>
<div><small>ผู้เข้าร่วม</small><strong><?= (int)$b['attendees'] ?> คน</strong><span>ความจุห้อง <?= (int)$b['capacity'] ?> คน</span></div>
<div><small>ผู้ขอใช้บริการ</small><strong><?= meeting_e($b['requester_name']) ?></strong><span><?= meeting_e($b['department']?:'-') ?> · <?= meeting_e($b['phone']?:'-') ?></span></div>
<div><small>สร้างรายการเมื่อ</small><strong><?= meeting_e($b['created_at']) ?></strong><span><?= $b['approved_at']?'อนุมัติเมื่อ '.meeting_e($b['approved_at']):'ยังไม่มีเวลาการอนุมัติ' ?></span></div>
</div>
<div class="mh-detail-text"><small>รายละเอียด / วาระการประชุม</small><p><?= nl2br(meeting_e($b['detail']?:'ไม่ได้ระบุรายละเอียดเพิ่มเติม')) ?></p></div>
</section>

<aside class="mh-panel">
<div class="mh-panel-head"><div><span class="mh-kicker">ONLINE MEETING</span><h2>ห้องประชุมออนไลน์</h2></div></div>
<?php if($b['meeting_url']):?>
<div class="mh-online-card">
<span class="mh-online-icon">🔗</span><small>แพลตฟอร์ม</small><strong><?= meeting_e(meeting_platform_label($b['meeting_platform'])) ?></strong>
<p>ลิงก์ถูกเก็บในระบบและจะเปิดในแท็บใหม่</p>
<?php if($b['status']==='approved'):?><a class="mh-btn mh-btn-primary" href="meeting_link.php?id=<?= (int)$b['id'] ?>" target="_blank" rel="noopener">เข้าสู่ห้องประชุม ↗</a><?php else:?><span class="mh-note">ลิงก์จะเปิดได้เมื่อรายการได้รับอนุมัติ</span><?php endif;?>
</div>
<?php else:?><div class="mh-empty mh-empty-compact">รายการนี้ไม่ได้บันทึกลิงก์ประชุมออนไลน์</div><?php endif;?>

<?php if($canApprove && $b['status']==='pending'):?>
<div class="mh-admin-actions">
<h3>การอนุมัติ</h3>
<form method="post" action="status_update.php" onsubmit="return confirm('ยืนยันอนุมัติรายการนี้?')"><input type="hidden" name="csrf_token" value="<?= meeting_e(meeting_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="status" value="approved"><button class="mh-btn mh-btn-primary" type="submit">✓ อนุมัติรายการ</button></form>
<form method="post" action="status_update.php" onsubmit="return confirm('ยืนยันไม่อนุมัติรายการนี้?')"><input type="hidden" name="csrf_token" value="<?= meeting_e(meeting_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="status" value="rejected"><button class="mh-btn mh-btn-danger" type="submit">✕ ไม่อนุมัติ</button></form>
</div>
<?php endif;?>

<?php if(($isOwner||$canManage) && in_array($b['status'],array('pending','approved'),true)):?>
<div class="mh-admin-actions">
<h3>ยกเลิกรายการ</h3>
<form method="post" action="status_update.php" onsubmit="return confirm('ยืนยันยกเลิกการจองนี้?')"><input type="hidden" name="csrf_token" value="<?= meeting_e(meeting_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="status" value="cancelled"><button class="mh-btn mh-btn-danger-outline" type="submit">ยกเลิกการจอง</button></form>
</div>
<?php endif;?>
</aside>
</div>
</main>
</body></html>
