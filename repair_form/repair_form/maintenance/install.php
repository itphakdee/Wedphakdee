<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_login();

$isAdmin = function_exists('is_admin_user') ? is_admin_user() : (($_SESSION['role'] ?? '') === 'admin');
if (!$isAdmin) {
    http_response_code(403);
    die('403 Forbidden: การติดตั้งฐานข้อมูลสำหรับผู้ดูแลระบบเท่านั้น');
}

$message = '';
$type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!maintenance_verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'โทเคนความปลอดภัยไม่ถูกต้อง กรุณาลองใหม่';
        $type = 'error';
    } else {
        $requestReady = $conn->query(maintenance_schema_sql());
        $feedbackReady = false;
        if ($requestReady) {
            $feedbackReady = $conn->query(maintenance_feedback_schema_sql());
        }

        if ($requestReady && $feedbackReady) {
            $message = 'ติดตั้ง/อัปเดตฐานข้อมูลงานซ่อมบำรุงและ Feedback เรียบร้อยแล้ว';
            $type = 'success';
        } else {
            $message = 'ติดตั้งไม่สำเร็จ: ' . $conn->error;
            $type = 'error';
        }
    }
}

$requestTableReady = maintenance_table_exists();
$feedbackTableReady = maintenance_feedback_table_exists();
$ready = $requestTableReady && $feedbackTableReady;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ติดตั้งฐานข้อมูลงานซ่อมบำรุง</title>
<link rel="stylesheet" href="assets/maintenance.css?v=<?= filemtime(__DIR__ . '/assets/maintenance.css') ?>">
</head>
<body>
<?php $activePage='maintenance'; $basePath='../../'; require __DIR__ . '/../../components/sidebar.php'; ?>
<main class="main-content maintenance-main">
    <section class="mt-hero">
        <div class="mt-hero-copy"><p class="mt-eyebrow">DATABASE SETUP</p><h1>ติดตั้งฐานข้อมูลงานซ่อมบำรุง</h1><p>สร้างตารางสำหรับรายการแจ้งซ่อม สถานะ ช่างผู้รับผิดชอบ และ Feedback ของผู้แจ้งงาน</p></div>
        <div class="mt-hero-actions"><a class="mt-btn mt-btn-outline" href="index.php">← กลับหน้าหลัก</a></div>
    </section>

    <?php if ($message): ?><div class="mt-flash <?= mh($type) ?>"><?= mh($message) ?></div><?php endif; ?>

    <section class="mt-card mt-setup">
        <div class="mt-empty-icon">DB</div>
        <h2><?= $ready ? 'ฐานข้อมูลพร้อมใช้งานแล้ว' : ($requestTableReady ? 'พร้อมอัปเดตระบบ Feedback' : 'พร้อมติดตั้งฐานข้อมูล') ?></h2>
        <p><?= $ready ? 'ตรวจพบทั้ง maintenance_requests และ maintenance_feedback สามารถใช้งานระบบได้ทันที' : ($requestTableReady ? 'ระบบจะเพิ่มตาราง maintenance_feedback สำหรับเก็บคะแนน 5 / 3 / 1 และความคิดเห็น โดยไม่ลบข้อมูลงานเดิม' : 'ระบบจะสร้าง maintenance_requests และ maintenance_feedback โดยไม่ลบหรือแก้ไขข้อมูลตารางอื่น') ?></p>
        <?php if (!$ready): ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= mh(maintenance_csrf_token()) ?>">
                <button class="mt-btn mt-btn-primary" type="submit">ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้</button>
            </form>
        <?php else: ?>
            <a class="mt-btn mt-btn-teal" href="index.php">เปิดระบบงานซ่อมบำรุง</a>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
