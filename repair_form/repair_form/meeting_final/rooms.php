<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.manage');

if (!meeting_table_exists($conn,'meeting_rooms')) { header('Location: install.php'); exit; }

$rooms=$conn->query("
SELECT r.*,
  (SELECT COUNT(*) FROM meeting_bookings b WHERE b.room_id=r.id AND b.status IN ('pending','approved')) AS booking_count,
  (SELECT COUNT(*) FROM meeting_bookings b WHERE b.room_id=r.id AND b.meeting_date=CURDATE() AND b.status='approved' AND b.start_time<=CURTIME() AND b.end_time>CURTIME()) AS now_count
FROM meeting_rooms r
ORDER BY FIELD(r.status,'active','inactive'), r.id
");
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>จัดการห้องประชุม</title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="assets/meeting.css?v=20260831"></head>
<body class="meeting-body">
<header class="mh-topbar"><div class="mh-brand"><div class="mh-brand-mark">PDC</div><div><div class="mh-brand-title">จัดการห้องประชุม</div><div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล</div></div></div><a class="mh-btn mh-btn-light" href="index.php">← Dashboard</a></header>
<nav class="mh-nav"><div class="mh-nav-inner"><a href="index.php">Dashboard</a><a href="booking.php">จองห้องประชุม</a><a href="bookings.php">รายการจอง</a><a class="active" href="rooms.php">จัดการห้องประชุม</a></div></nav>
<main class="mh-wrap">
<section class="mh-page-title"><div><span class="mh-kicker">ROOM MASTER DATA</span><h1>ข้อมูลห้องประชุม</h1><p>บริหารชื่อห้อง ความจุ สถานที่ อุปกรณ์ สีประจำห้อง และสถานะเปิดใช้งาน</p></div><a class="mh-btn mh-btn-primary" href="room_add.php">＋ เพิ่มห้องประชุม</a></section>
<?php if(!empty($_GET['message'])):?><div class="mh-alert <?= (!empty($_GET['status'])&&$_GET['status']==='success')?'mh-alert-success':'mh-alert-danger' ?>"><strong><?= meeting_e($_GET['message']) ?></strong></div><?php endif;?>
<div class="mh-room-admin-grid">
<?php if($rooms && $rooms->num_rows): while($r=$rooms->fetch_assoc()):?>
<article class="mh-room-admin-card" style="--room-accent:<?= meeting_e($r['color']?:'#0f766e') ?>">
<div class="mh-room-admin-top"><span class="mh-room-admin-code"><?= meeting_e($r['room_code']) ?></span><span class="mh-status <?= $r['status']==='active'?'status-approved':'status-cancelled' ?>"><?= $r['status']==='active'?'เปิดใช้งาน':'ปิดใช้งาน' ?></span></div>
<div class="mh-room-admin-icon">▦</div><h2><?= meeting_e($r['room_name']) ?></h2>
<p><?= meeting_e($r['location']?:'ไม่ได้ระบุสถานที่') ?></p>
<div class="mh-room-admin-stats"><div><small>ความจุ</small><strong><?= (int)$r['capacity'] ?> คน</strong></div><div><small>รายการจอง</small><strong><?= (int)$r['booking_count'] ?></strong></div><div><small>ขณะนี้</small><strong><?= (int)$r['now_count']>0?'มีประชุม':'ว่าง' ?></strong></div></div>
<div class="mh-room-admin-equipment"><small>อุปกรณ์</small><span><?= meeting_e($r['equipment']?:'ไม่ได้ระบุ') ?></span></div>
<div class="mh-room-actions"><a href="room_edit.php?id=<?= (int)$r['id'] ?>">แก้ไขข้อมูล</a><a class="secondary" href="booking.php?room_id=<?= (int)$r['id'] ?>">จองห้อง</a></div>
</article>
<?php endwhile; else:?><div class="mh-empty">ยังไม่มีข้อมูลห้องประชุม</div><?php endif;?>
</div>
</main></body></html>
