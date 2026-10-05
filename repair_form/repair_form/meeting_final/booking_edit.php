<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.view');
if (!meeting_schema_ready($conn)) { header('Location: install.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt=$conn->prepare("SELECT * FROM meeting_bookings WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id); $stmt->execute(); $result=$stmt->get_result(); $b=$result->fetch_assoc(); $stmt->close();
if(!$b){ http_response_code(404); die('ไม่พบรายการจอง'); }

$userId=(int)$_SESSION['user_id'];
$isOwner=!empty($b['user_id']) && (int)$b['user_id']===$userId;
$canManage=has_permission('meeting.manage');
if(!($isOwner||$canManage) || !in_array($b['status'],array('pending','approved'),true)){
    http_response_code(403); die('ไม่มีสิทธิ์แก้ไขรายการนี้');
}
$rooms=$conn->query("SELECT id,room_name,room_code,capacity,location FROM meeting_rooms WHERE status='active' ORDER BY id");
$platforms=meeting_platforms();
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>แก้ไขการจอง #<?= (int)$b['id'] ?></title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="assets/meeting.css?v=20260831"></head>
<body class="meeting-body">
<header class="mh-topbar"><div class="mh-brand"><div class="mh-brand-mark">PDC</div><div><div class="mh-brand-title">แก้ไขการจองห้องประชุม</div><div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล</div></div></div><a class="mh-btn mh-btn-light" href="booking_detail.php?id=<?= (int)$b['id'] ?>">← รายละเอียด</a></header>
<main class="mh-wrap">
<section class="mh-page-title"><div><span class="mh-kicker">EDIT BOOKING #<?= (int)$b['id'] ?></span><h1>แก้ไขข้อมูลการจอง</h1><p>ระบบจะตรวจสอบความจุและเวลาซ้ำอีกครั้งก่อนบันทึก</p></div></section>
<?php if(!empty($_GET['message'])):?><div class="mh-alert mh-alert-danger"><strong><?= meeting_e($_GET['message']) ?></strong></div><?php endif;?>
<section class="mh-form-card">
<form method="post" action="booking_update.php" id="bookingForm">
<input type="hidden" name="csrf_token" value="<?= meeting_e(meeting_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
<div class="mh-form-section"><div class="mh-form-section-title"><span>01</span><div><h2>ข้อมูลการประชุม</h2><p>แก้ไขข้อมูลที่ต้องการ</p></div></div>
<div class="mh-form-grid">
<div class="mh-field mh-col-6"><label>ห้องประชุม <b>*</b></label><select name="room_id" id="room_id" required><?php while($r=$rooms->fetch_assoc()):?><option value="<?= (int)$r['id'] ?>" data-capacity="<?= (int)$r['capacity'] ?>" data-location="<?= meeting_e($r['location']) ?>" <?= (int)$b['room_id']===(int)$r['id']?'selected':'' ?>><?= meeting_e($r['room_name']) ?> · <?= (int)$r['capacity'] ?> คน</option><?php endwhile;?></select><small id="roomInfo"></small></div>
<div class="mh-field mh-col-6"><label>หัวข้อการประชุม <b>*</b></label><input name="meeting_title" value="<?= meeting_e($b['meeting_title']) ?>" required></div>
<div class="mh-field mh-col-4"><label>ชื่อผู้ขอ <b>*</b></label><input name="requester_name" value="<?= meeting_e($b['requester_name']) ?>" required></div>
<div class="mh-field mh-col-4"><label>หน่วยงาน</label><input name="department" value="<?= meeting_e($b['department']) ?>"></div>
<div class="mh-field mh-col-4"><label>โทรศัพท์</label><input name="phone" value="<?= meeting_e($b['phone']) ?>"></div>
<div class="mh-field mh-col-4"><label>ผู้เข้าร่วม <b>*</b></label><input id="attendees" type="number" name="attendees" min="1" value="<?= (int)$b['attendees'] ?>" required><small id="capacityMessage"></small></div>
<div class="mh-field mh-col-4"><label>วันที่ <b>*</b></label><input type="date" name="meeting_date" min="<?= date('Y-m-d') ?>" value="<?= meeting_e($b['meeting_date']) ?>" required></div>
<div class="mh-field mh-col-2"><label>เวลาเริ่ม <b>*</b></label><input type="time" name="start_time" value="<?= substr($b['start_time'],0,5) ?>" required></div>
<div class="mh-field mh-col-2"><label>สิ้นสุด <b>*</b></label><input type="time" name="end_time" value="<?= substr($b['end_time'],0,5) ?>" required></div>
<div class="mh-field mh-col-12"><label>รายละเอียด</label><textarea name="detail" rows="4"><?= meeting_e($b['detail']) ?></textarea></div>
</div></div>
<div class="mh-form-section"><div class="mh-form-section-title"><span>02</span><div><h2>ห้องประชุมออนไลน์</h2><p>แก้ไขแพลตฟอร์มและลิงก์ประชุม</p></div></div>
<div class="mh-form-grid">
<div class="mh-field mh-col-4"><label>แพลตฟอร์ม</label><select name="meeting_platform"><option value="">— ไม่ใช้ —</option><?php foreach($platforms as $k=>$p):?><option value="<?= meeting_e($k) ?>" <?= $b['meeting_platform']===$k?'selected':'' ?>><?= $p['icon'] ?> <?= meeting_e($p['label']) ?></option><?php endforeach;?></select></div>
<div class="mh-field mh-col-8"><label>ลิงก์ประชุม</label><input type="url" name="meeting_url" value="<?= meeting_e($b['meeting_url']) ?>" placeholder="https://..."></div>
</div></div>
<div class="mh-form-actions"><a class="mh-btn mh-btn-outline" href="booking_detail.php?id=<?= (int)$b['id'] ?>">ยกเลิก</a><button class="mh-btn mh-btn-primary" type="submit">บันทึกการแก้ไข</button></div>
</form>
</section></main><script src="assets/meeting.js?v=20260831"></script></body></html>
