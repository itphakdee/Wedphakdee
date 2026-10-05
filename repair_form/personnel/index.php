<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('view');

if (!personnel_schema_ready()) {
    if (personnel_is_admin()) { header('Location: install.php'); exit; }
    die('ระบบข้อมูลบุคลากรยังไม่ได้ติดตั้งฐานข้อมูล กรุณาติดต่อผู้ดูแลระบบ');
}

$q = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$departmentFilter = trim((string)($_GET['department'] ?? ''));

$where = ['1=1']; $types = ''; $params = [];
if ($q !== '') {
    $where[] = '(p.fullname LIKE ? OR p.username LIKE ? OR p.employee_code LIKE ? OR p.email LIKE ? OR p.position_name LIKE ?)';
    $like = '%' . $q . '%';
    for ($i=0;$i<5;$i++) { $types .= 's'; $params[] = $like; }
}
if (in_array($statusFilter, ['active','inactive'], true)) { $where[]='p.status=?'; $types.='s'; $params[]=$statusFilter; }
if ($departmentFilter !== '') { $where[]='p.department=?'; $types.='s'; $params[]=$departmentFilter; }

$sql = 'SELECT p.*, u.role AS user_role, u.status AS user_status FROM personnel p LEFT JOIN users u ON u.id=p.user_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.id DESC';
$stmt = $conn->prepare($sql);
if ($types !== '') { personnel_bind_params($stmt, $types, $params); }
$stmt->execute(); $result = $stmt->get_result();
$rows = []; while ($row = $result->fetch_assoc()) $rows[]=$row; $stmt->close();

$stats = ['total'=>0,'active'=>0,'inactive'=>0,'accounts'=>0];
$res = $conn->query("SELECT COUNT(*) total, SUM(status='active') active_count, SUM(status='inactive') inactive_count, SUM(user_id IS NOT NULL) account_count FROM personnel");
if ($res && ($s=$res->fetch_assoc())) { $stats=['total'=>(int)$s['total'],'active'=>(int)$s['active_count'],'inactive'=>(int)$s['inactive_count'],'accounts'=>(int)$s['account_count']]; }
$departments = personnel_departments();
$flash = personnel_pull_flash();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ข้อมูลบุคลากร</title><link rel="stylesheet" href="assets/personnel.css?v=2.0">
</head>
<body>
<div class="personnel-shell">
<section class="hero"><div class="hero-grid"><div><div class="eyebrow">HOSPITAL PERSONNEL DIRECTORY</div><h1>ข้อมูลบุคลากรโรงพยาบาล</h1><p>จัดเก็บข้อมูลเจ้าหน้าที่ เชื่อมบัญชีเข้าสู่ระบบ และบริหารสถานะการปฏิบัติงานจากศูนย์กลาง</p></div><div class="hero-actions"><?php if(personnel_can('create')):?><a class="btn btn-primary" href="add.php">＋ เพิ่มบุคลากร</a><?php endif;?><?php if(personnel_is_admin()):?><a class="btn btn-soft" href="career_options.php">⚙ ข้อมูลอาชีพ</a><?php endif;?><a class="btn btn-outline" href="../../dashboard.php">← Dashboard</a></div></div></section>

<?php if ($flash): ?><div class="alert <?=($flash['type']==='success'?'alert-success':'alert-error')?>"><?=ph($flash['message'])?></div><?php endif;?>

<div class="stats">
  <div class="stat-card"><span>บุคลากรทั้งหมด</span><strong><?=$stats['total']?></strong></div>
  <div class="stat-card"><span>กำลังปฏิบัติงาน</span><strong><?=$stats['active']?></strong></div>
  <div class="stat-card"><span>ไม่ปฏิบัติงาน</span><strong><?=$stats['inactive']?></strong></div>
  <div class="stat-card"><span>มีบัญชีเข้าสู่ระบบ</span><strong><?=$stats['accounts']?></strong></div>
</div>

<section class="card">
<div class="card-head"><div class="card-title"><div class="icon-box">👥</div><div><h2>ทะเบียนบุคลากร</h2><div class="card-sub">ค้นหา ตรวจสอบ และจัดการบัญชีบุคลากร</div></div></div><div class="muted">แสดง <?=count($rows)?> รายการ</div></div>
<form class="search-panel" method="get">
  <div class="field"><input name="q" value="<?=ph($q)?>" placeholder="ค้นหา ชื่อ / Username / รหัสบุคลากร / อีเมล / ตำแหน่ง"></div>
  <div class="field"><select name="department"><option value="">ทุกหน่วยงาน</option><?php foreach($departments as $d):?><option value="<?=ph($d['department_name'])?>" <?=$departmentFilter===$d['department_name']?'selected':''?>><?=ph($d['department_name'])?></option><?php endforeach;?></select></div>
  <div class="field"><select name="status"><option value="">ทุกสถานะ</option><option value="active" <?=$statusFilter==='active'?'selected':''?>>ปฏิบัติงาน</option><option value="inactive" <?=$statusFilter==='inactive'?'selected':''?>>ไม่ปฏิบัติงาน</option></select></div>
  <button class="btn btn-navy" type="submit">ค้นหา</button>
</form>
<div class="table-wrap">
<?php if(!$rows):?><div class="empty">ไม่พบข้อมูลบุคลากรตามเงื่อนไขที่ค้นหา</div><?php else:?>
<table class="data-table"><thead><tr><th>#</th><th>บุคลากร</th><th>Username</th><th>รหัสบุคลากร</th><th>ตำแหน่ง / หน่วยงาน</th><th>อีเมล</th><th>เลขบัตรประชาชน</th><th>สถานะ</th><th>จัดการ</th></tr></thead><tbody>
<?php foreach($rows as $row): $sm=personnel_status_meta($row['status']); ?>
<tr>
<td><?=intval($row['id'])?></td>
<td class="name-cell"><strong><?=ph($row['fullname'])?></strong><span><?=ph($row['first_name_en'] ?: ($row['nickname'] ? 'ชื่อเล่น: '.$row['nickname'] : ''))?></span></td>
<td><strong><?=ph($row['username'] ?: '-')?></strong><?php if($row['user_id']):?><div><span class="badge <?=ph($row['user_role']==='admin'?'admin':'user')?>"><?=ph($row['user_role'] ?: 'user')?></span></div><?php endif;?></td>
<td><?=ph($row['employee_code'] ?: '-')?></td>
<td><strong><?=ph($row['position_name'] ?: '-')?></strong><div class="muted"><?=ph($row['department'] ?: '-')?></div></td>
<td><?=ph($row['email'] ?: '-')?></td>
<td><?=ph(personnel_mask_national_id($row['national_id']))?></td>
<td><span class="badge <?=ph($sm['class'])?>"><?=ph($sm['label'])?></span></td>
<td><div style="display:flex;gap:6px;flex-wrap:wrap"><a class="btn btn-sm btn-soft" href="detail.php?id=<?=intval($row['id'])?>">รายละเอียด</a><?php if(personnel_can('edit')):?><a class="btn btn-sm btn-outline" href="edit.php?id=<?=intval($row['id'])?>">แก้ไข</a><?php endif;?></div></td>
</tr><?php endforeach;?>
</tbody></table><?php endif;?>
</div>
</section>
</div>
</body></html>
