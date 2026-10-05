<?php
require_once __DIR__ . '/config_vehicle.php';
header('Content-Type: text/html; charset=utf-8');

$u = $currentVehicleUser;
$d = $currentVehicleDept;
$s = $currentVehicleSupervisor;

function dbg($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ตรวจสอบข้อมูลแผนกผู้ใช้</title>
<style>
body{font-family:Tahoma,Arial,sans-serif;background:#f4f8fa;margin:0;padding:24px;color:#153247}
.wrap{max-width:900px;margin:auto;background:#fff;border:1px solid #dce9ef;border-radius:18px;box-shadow:0 12px 35px rgba(31,73,99,.08);padding:24px}
h1{margin:0 0 8px;font-size:25px}.muted{color:#6f8796;margin-bottom:22px}
table{width:100%;border-collapse:collapse}th,td{padding:12px 14px;border-bottom:1px solid #e7eff3;text-align:left}th{width:300px;background:#f8fbfc}
.ok{color:#087b66;font-weight:700}.warn{color:#b46b00;font-weight:700}.btn{display:inline-block;margin-top:20px;padding:10px 15px;border-radius:10px;background:#0b6070;color:#fff;text-decoration:none}
code{background:#eef5f7;padding:2px 6px;border-radius:6px}
</style>
</head>
<body><div class="wrap">
<h1>ตรวจสอบการดึงแผนกและหัวหน้างาน</h1>
<div class="muted">หน้านี้แสดงเฉพาะข้อมูลของบัญชีที่กำลังเข้าสู่ระบบ เพื่อใช้ตรวจสอบระบบขอใช้รถ</div>
<table>
<tr><th>User ID</th><td><?= dbg(isset($u['id'])?$u['id']:'') ?></td></tr>
<tr><th>Username</th><td><?= dbg(isset($u['username'])?$u['username']:'') ?></td></tr>
<tr><th>ชื่อผู้ใช้ (users.fullname)</th><td><?= dbg(isset($u['fullname'])?$u['fullname']:'') ?></td></tr>
<tr><th>แผนกจาก users</th><td><?= dbg(isset($u['department'])?$u['department']:'') ?: '<span class="warn">ไม่มีข้อมูล</span>' ?></td></tr>
<tr><th>department_id จาก users</th><td><?= dbg(isset($u['department_id'])?$u['department_id']:'') ?: '<span class="warn">ไม่มีข้อมูล</span>' ?></td></tr>
<tr><th>แผนกจาก personnel</th><td><?= dbg(isset($u['personnel_department'])?$u['personnel_department']:'') ?: '<span class="warn">ไม่มีข้อมูล</span>' ?></td></tr>
<tr><th>department_id จาก personnel</th><td><?= dbg(isset($u['personnel_department_id'])?$u['personnel_department_id']:'') ?: '<span class="warn">ไม่มีข้อมูล</span>' ?></td></tr>
<tr><th>แผนกที่ระบบเลือกใช้</th><td><?= $d['name']!==''?'<span class="ok">'.dbg($d['name']).' (#'.dbg($d['id']).')</span>':'<span class="warn">ยังหาแผนกไม่ได้</span>' ?></td></tr>
<tr><th>หัวหน้างานที่ระบบเลือก</th><td><?= !empty($s['fullname'])?'<span class="ok">'.dbg($s['fullname']).' (User #'.dbg($s['id']).')</span>':'<span class="warn">ยังไม่พบหัวหน้างานของแผนก</span>' ?></td></tr>
</table>
<p class="muted" style="margin-top:18px">ลำดับตรวจสอบ: <code>personnel.user_id</code> → personnel username/email/fullname → <code>users.department</code> → <code>departments</code> → <code>leave_supervisors</code> → users ของหัวหน้า</p>
<a class="btn" href="index.php?page=add&type=general">← กลับหน้าขอใช้รถ</a>
</div></body></html>
