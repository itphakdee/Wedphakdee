<?php
require_once __DIR__.'/admin_guard.php';
function n($c,$q){$r=$c->query($q);if(!$r)return 0;$x=$r->fetch_row();return (int)($x[0]??0);}
$total=n($conn,"SELECT COUNT(*) FROM users");$active=n($conn,"SELECT COUNT(*) FROM users WHERE status='active'");$admins=n($conn,"SELECT COUNT(*) FROM users WHERE role='admin'");
$mods=n($conn,"SELECT COUNT(DISTINCT module_key) FROM admin_permissions");
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ศูนย์ควบคุม Admin</title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="assets/admin.css"></head><body>
<div class="admin-header"><div><div class="brand">🛡️ ศูนย์ควบคุมผู้ดูแลระบบ</div><div class="sub">Wedphakdee · โรงพยาบาลภักดีชุมพล</div></div><b>👤 <?=ah($_SESSION['fullname']??'')?></b></div>
<div class="wrap">
<div class="title"><div><h1>Admin Control Center</h1><p>ควบคุมผู้ใช้ สิทธิ์ และทุกระบบ</p></div><a href="../../dashboard.php" class="btn btn-outline-secondary">← กลับหน้าหลัก</a></div>
<div class="stats"><div class="s blue"><span>ผู้ใช้ทั้งหมด</span><b><?=$total?></b><small>บัญชี</small></div><div class="s green"><span>ใช้งานอยู่</span><b><?=$active?></b><small>บัญชี</small></div><div class="s red"><span>Admin</span><b><?=$admins?></b><small>บัญชี</small></div><div class="s purple"><span>โมดูลระบบ</span><b><?=$mods?></b><small>โมดูล</small></div></div>
<div class="cards">
<a href="users.php"><b>👥 จัดการผู้ใช้งาน</b><span>แก้ Role / สถานะ / หัวหน้างาน</span></a>
<a href="permissions.php"><b>🔐 กำหนดสิทธิ์</b><span>กำหนดสิทธิ์รายบุคคลทุกระบบ</span></a>
<a href="modules.php"><b>🧩 โมดูลระบบ</b><span>ดูรายการระบบและสิทธิ์มาตรฐาน</span></a>
<a href="roles.php"><b>👑 Role</b><span>ภาพรวม admin / manager / user</span></a>
</div>
<div class="panel"><h2>ระบบที่ควบคุมได้</h2><div class="module-list">
<span>Dashboard</span><span>วันลา</span><span>หนังสือราชการ</span><span>ยานพาหนะ</span><span>แจ้งซ่อม</span><span>อาคาร</span><span>ห้องประชุม</span><span>บุคลากร</span><span>ผู้ใช้งาน</span><span>Admin</span>
</div></div>
</div></body></html>