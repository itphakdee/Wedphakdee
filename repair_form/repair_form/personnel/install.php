<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_login();

if (!personnel_is_admin()) {
    http_response_code(403);
    die('เฉพาะผู้ดูแลระบบเท่านั้นที่ติดตั้ง/อัปเดตฐานข้อมูลได้');
}

$messages = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!personnel_verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    } else {
        try {
            $messages = personnel_install_schema();
            if (!$messages) {
                $messages[] = 'โครงสร้างฐานข้อมูลพร้อมใช้งานอยู่แล้ว';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
$ready = personnel_schema_ready();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ติดตั้งระบบบุคลากร</title>
<link rel="stylesheet" href="assets/personnel.css?v=1.0">
</head>
<body>
<div class="install-box">
  <div class="eyebrow">PERSONNEL DATABASE SETUP</div>
  <h1>ติดตั้ง / อัปเดตฐานข้อมูลบุคลากร</h1>
  <p class="muted">ระบบจะเพิ่มฟิลด์ข้อมูลบุคลากรและเชื่อมบัญชีเข้าสู่ระบบกับตาราง <span class="code-pill">users</span> โดยไม่ลบข้อมูลเดิม</p>
  <?php if ($error): ?><div class="alert alert-error"><?=ph($error)?></div><?php endif; ?>
  <?php foreach ($messages as $m): ?><div class="alert alert-success"><?=ph($m)?></div><?php endforeach; ?>
  <div class="alert <?=$ready?'alert-success':'alert-info'?>">
    สถานะฐานข้อมูล: <strong><?=$ready?'พร้อมใช้งาน':'ยังต้องติดตั้ง/อัปเดต'?></strong>
  </div>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?=ph(personnel_csrf_token())?>">
    <button class="btn btn-primary" type="submit">ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้</button>
    <?php if ($ready): ?><a class="btn btn-outline" href="index.php">เข้าสู่ระบบบุคลากร</a><?php endif; ?>
  </form>
</div>
</body>
</html>
