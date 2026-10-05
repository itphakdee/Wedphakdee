<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.manage');
$id=isset($_GET['id'])?(int)$_GET['id']:0;
$stmt=$conn->prepare("SELECT * FROM meeting_rooms WHERE id=? LIMIT 1"); $stmt->bind_param('i',$id); $stmt->execute(); $result=$stmt->get_result(); $r=$result->fetch_assoc(); $stmt->close();
if(!$r){ http_response_code(404); die('ไม่พบห้องประชุม'); }
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>แก้ไขห้องประชุม</title><link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="assets/meeting.css?v=20260831"></head>
<body class="meeting-body"><header class="mh-topbar"><div class="mh-brand"><div class="mh-brand-mark">PDC</div><div><div class="mh-brand-title">แก้ไขห้องประชุม</div><div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล</div></div></div><a class="mh-btn mh-btn-light" href="rooms.php">← กลับ</a></header>
<main class="mh-wrap"><section class="mh-page-title"><div><span class="mh-kicker">EDIT ROOM #<?= (int)$r['id'] ?></span><h1><?= meeting_e($r['room_name']) ?></h1><p>แก้ไขข้อมูลห้องโดยไม่กระทบประวัติการจองเดิม</p></div></section>
<section class="mh-form-card"><form method="post" action="room_update.php"><input type="hidden" name="csrf_token" value="<?= meeting_e(meeting_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<div class="mh-form-section"><div class="mh-form-section-title"><span>01</span><div><h2>ข้อมูลห้อง</h2><p>ข้อมูลหลักสำหรับการบริหารห้องประชุม</p></div></div><div class="mh-form-grid">
<div class="mh-field mh-col-6"><label>ชื่อห้องประชุม <b>*</b></label><input name="room_name" value="<?= meeting_e($r['room_name']) ?>" required></div>
<div class="mh-field mh-col-3"><label>รหัสห้อง</label><input name="room_code" value="<?= meeting_e($r['room_code']) ?>"></div>
<div class="mh-field mh-col-3"><label>ความจุ <b>*</b></label><input type="number" name="capacity" min="1" value="<?= (int)$r['capacity'] ?>" required></div>
<div class="mh-field mh-col-6"><label>สถานที่</label><input name="location" value="<?= meeting_e($r['location']) ?>"></div>
<div class="mh-field mh-col-3"><label>สีประจำห้อง</label><input class="mh-color-input" type="color" name="color" value="<?= meeting_e($r['color']?:'#0f766e') ?>"></div>
<div class="mh-field mh-col-3"><label>สถานะ</label><select name="status"><option value="active" <?= $r['status']==='active'?'selected':'' ?>>เปิดใช้งาน</option><option value="inactive" <?= $r['status']==='inactive'?'selected':'' ?>>ปิดใช้งาน</option></select></div>
<div class="mh-field mh-col-12"><label>อุปกรณ์ประจำห้อง</label><textarea name="equipment" rows="4"><?= meeting_e($r['equipment']) ?></textarea></div>
</div></div><div class="mh-form-actions"><a class="mh-btn mh-btn-outline" href="rooms.php">ยกเลิก</a><button class="mh-btn mh-btn-primary" type="submit">บันทึกการแก้ไข</button></div>
</form></section></main></body></html>
