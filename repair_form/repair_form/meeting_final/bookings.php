<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
require_permission('meeting.view');
if (!meeting_schema_ready($conn)) { header('Location: install.php'); exit; }

$isAdmin = is_admin_user();
$canManage = has_permission('meeting.manage');
$canCreate = has_permission('meeting.create');
$userId = (int)$_SESSION['user_id'];

$q = trim(isset($_GET['q']) ? $_GET['q'] : '');
$statusFilter = trim(isset($_GET['status_filter']) ? $_GET['status_filter'] : '');
$roomFilter = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$dateFilter = trim(isset($_GET['meeting_date']) ? $_GET['meeting_date'] : '');
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$perPage = 12;

$where = array('1=1');

if ($q !== '') {
    $qEsc = $conn->real_escape_string($q);
    $where[] = "(b.meeting_title LIKE '%{$qEsc}%' OR b.requester_name LIKE '%{$qEsc}%' OR b.department LIKE '%{$qEsc}%')";
}
if (in_array($statusFilter, array('pending','approved','rejected','cancelled','completed'), true)) {
    $statusEsc = $conn->real_escape_string($statusFilter);
    $where[] = "b.status='{$statusEsc}'";
}
if ($roomFilter > 0) {
    $where[] = "b.room_id=" . (int)$roomFilter;
}
if ($dateFilter !== '') {
    $dateEsc = $conn->real_escape_string($dateFilter);
    $where[] = "b.meeting_date='{$dateEsc}'";
}
$whereSql = implode(' AND ', $where);

$countResult = $conn->query("SELECT COUNT(*) FROM meeting_bookings b WHERE {$whereSql}");
$totalRows = 0;
if ($countResult) {
    $countRow = $countResult->fetch_row();
    $totalRows = isset($countRow[0]) ? (int)$countRow[0] : 0;
}
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$sql = "
SELECT b.*, r.room_name, r.room_code
FROM meeting_bookings b
INNER JOIN meeting_rooms r ON r.id=b.room_id
WHERE {$whereSql}
ORDER BY b.meeting_date DESC, b.start_time DESC
LIMIT {$perPage} OFFSET {$offset}";
$result = $conn->query($sql);

$rooms = $conn->query("SELECT id,room_name FROM meeting_rooms WHERE status='active' ORDER BY id");

function booking_query_url($pageNo) {
    $copy = $_GET;
    $copy['page'] = $pageNo;
    return '?' . http_build_query($copy);
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>รายการจองห้องประชุม</title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/meeting.css?v=20260831">
</head>
<body class="meeting-body">
<header class="mh-topbar">
<div class="mh-brand"><div class="mh-brand-mark">PDC</div><div><div class="mh-brand-title">ระบบจองห้องประชุม</div><div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล</div></div></div>
<a class="mh-btn mh-btn-light" href="index.php">← Dashboard</a>
</header>
<nav class="mh-nav"><div class="mh-nav-inner"><a href="index.php">Dashboard</a><?php if($canCreate):?><a href="booking.php">จองห้องประชุม</a><?php endif;?><a class="active" href="bookings.php">รายการจอง</a><?php if($canManage):?><a href="rooms.php">จัดการห้องประชุม</a><?php endif;?></div></nav>

<main class="mh-wrap">
<section class="mh-page-title">
<div><span class="mh-kicker">BOOKING MANAGEMENT</span><h1>รายการจองห้องประชุม</h1><p>ค้นหา ติดตาม อนุมัติ และเปิดลิงก์ประชุมออนไลน์จากรายการเดียว</p></div>
<?php if($canCreate):?><a class="mh-btn mh-btn-primary" href="booking.php">＋ จองห้องประชุม</a><?php endif;?>
</section>

<?php if (!empty($_GET['message'])): ?>
<div class="mh-alert <?= (isset($_GET['status']) && $_GET['status']==='success')?'mh-alert-success':'mh-alert-danger' ?>"><strong><?= meeting_e($_GET['message']) ?></strong></div>
<?php endif; ?>

<section class="mh-panel">
<form class="mh-filter-grid" method="get">
<div class="mh-field"><label>ค้นหา</label><input name="q" value="<?= meeting_e($q) ?>" placeholder="หัวข้อ / ผู้ขอ / หน่วยงาน"></div>
<div class="mh-field"><label>สถานะ</label><select name="status_filter"><option value="">ทุกสถานะ</option><?php foreach(array('pending'=>'รออนุมัติ','approved'=>'อนุมัติแล้ว','rejected'=>'ไม่อนุมัติ','cancelled'=>'ยกเลิกแล้ว','completed'=>'เสร็จสิ้น') as $k=>$v):?><option value="<?= $k ?>" <?= $statusFilter===$k?'selected':'' ?>><?= $v ?></option><?php endforeach;?></select></div>
<div class="mh-field"><label>ห้องประชุม</label><select name="room_id"><option value="0">ทุกห้อง</option><?php if($rooms): while($r=$rooms->fetch_assoc()):?><option value="<?= (int)$r['id'] ?>" <?= $roomFilter===(int)$r['id']?'selected':'' ?>><?= meeting_e($r['room_name']) ?></option><?php endwhile; endif;?></select></div>
<div class="mh-field"><label>วันที่</label><input type="date" name="meeting_date" value="<?= meeting_e($dateFilter) ?>"></div>
<div class="mh-filter-actions"><button class="mh-btn mh-btn-primary">ค้นหา</button><a class="mh-btn mh-btn-outline" href="bookings.php">ล้าง</a></div>
</form>
</section>

<section class="mh-panel">
<div class="mh-panel-head"><div><span class="mh-kicker">RESULTS</span><h2>พบ <?= number_format($totalRows) ?> รายการ</h2></div></div>
<div class="mh-table-wrap">
<table class="mh-table">
<thead><tr><th>#</th><th>วัน / เวลา</th><th>ห้อง</th><th>หัวข้อ / ผู้ขอ</th><th>ออนไลน์</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
<tbody>
<?php if($result && $result->num_rows): while($b=$result->fetch_assoc()): ?>
<tr>
<td><strong>#<?= (int)$b['id'] ?></strong></td>
<td><strong><?= meeting_format_thai_date($b['meeting_date']) ?></strong><small><?= substr($b['start_time'],0,5) ?> - <?= substr($b['end_time'],0,5) ?> น.</small></td>
<td><strong><?= meeting_e($b['room_name']) ?></strong><small><?= meeting_e($b['room_code']) ?></small></td>
<td><strong><?= meeting_e($b['meeting_title']) ?></strong><small><?= meeting_e($b['requester_name']) ?><?= $b['department']?' · '.meeting_e($b['department']):'' ?></small></td>
<td><?php if($b['meeting_url']):?><span class="mh-platform-pill"><?= meeting_e(meeting_platform_label($b['meeting_platform'])) ?></span><?php else:?><span class="mh-muted">—</span><?php endif;?></td>
<td><span class="mh-status <?= meeting_status_class($b['status']) ?>"><?= meeting_e(meeting_status_label($b['status'])) ?></span></td>
<td>
<div class="mh-action-group">
<a class="mh-mini-btn" href="booking_detail.php?id=<?= (int)$b['id'] ?>">รายละเอียด</a>
<?php if($b['meeting_url'] && $b['status']==='approved'):?><a class="mh-mini-btn mh-mini-green" href="meeting_link.php?id=<?= (int)$b['id'] ?>" target="_blank" rel="noopener">เข้าประชุม ↗</a><?php endif;?>
<?php $isOwner = !empty($b['user_id']) && (int)$b['user_id']===$userId; ?>
<?php if(($isOwner || $canManage) && in_array($b['status'],array('pending','approved'),true)):?><a class="mh-mini-btn" href="booking_edit.php?id=<?= (int)$b['id'] ?>">แก้ไข</a><?php endif;?>
</div>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="7"><div class="mh-empty mh-empty-compact">ไม่พบรายการตามเงื่อนไข</div></td></tr>
<?php endif;?>
</tbody>
</table>
</div>

<?php if($totalPages>1):?>
<nav class="mh-pagination">
<?php if($page>1):?><a href="<?= meeting_e(booking_query_url($page-1)) ?>">‹ ก่อนหน้า</a><?php endif;?>
<?php
$start=max(1,$page-2); $end=min($totalPages,$page+2);
for($i=$start;$i<=$end;$i++): ?>
<a class="<?= $i===$page?'active':'' ?>" href="<?= meeting_e(booking_query_url($i)) ?>"><?= $i ?></a>
<?php endfor;?>
<?php if($page<$totalPages):?><a href="<?= meeting_e(booking_query_url($page+1)) ?>">ถัดไป ›</a><?php endif;?>
</nav>
<?php endif;?>
</section>
</main>
</body>
</html>
