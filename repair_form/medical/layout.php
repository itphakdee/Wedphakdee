<?php
require_once __DIR__ . '/config_medical.php';

function medical_page_start($title, $subtitle, $active)
{
    global $conn;
    $u = medical_current_user();
    $flash = medical_pull_flash();
    ?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= medical_e($title) ?> | ศูนย์เครื่องมือแพทย์</title>
    <link rel="stylesheet" href="assets/medical.css?v=7">
</head>
<body>
<?php
    $activePage = 'medical';
    $basePath = '../../';
    require __DIR__ . '/../../components/sidebar.php';
?>
<div class="med-shell main-content">
    <header class="med-hero">
        <div>
            <div class="med-kicker">PHAKDEE CHUMPHON HOSPITAL · MEDICAL EQUIPMENT CENTER</div>
            <h1><?= medical_e($title) ?></h1>
            <p><?= medical_e($subtitle) ?></p>
        </div>
        <div class="med-user">
            <span class="med-user-icon">M</span>
            <div><strong><?= medical_e($u['fullname'] ?: 'ผู้ใช้งาน') ?></strong><span><?= medical_e($u['department'] ?: 'ไม่ระบุหน่วยงาน') ?></span></div>
        </div>
    </header>
    <nav class="med-nav">
        <a class="<?= $active==='dashboard'?'active':'' ?>" href="index.php">ภาพรวมและแจ้งซ่อม</a>
        <a class="<?= $active==='repairs'?'active':'' ?>" href="repairs.php">ทะเบียนงานแจ้งซ่อม</a>
        <?php if (medical_can('manage')): ?><a class="<?= $active==='resources'?'active':'' ?>" href="resources.php">ทะเบียนเครื่องมือ / ช่าง</a><?php endif; ?>
        <?php if (medical_is_admin()): ?><a class="<?= $active==='install'?'active':'' ?>" href="install.php">ติดตั้งฐานข้อมูล</a><?php endif; ?>
    </nav>
    <?php if ($flash): ?><div class="med-alert med-alert--<?= medical_e($flash['type']) ?>"><?= medical_e($flash['message']) ?></div><?php endif; ?>
    <main class="med-page">
<?php
}

function medical_page_end()
{
    ?>
    </main>
    <footer class="med-footer">ศูนย์เครื่องมือแพทย์ · โรงพยาบาลภักดีชุมพล</footer>
</div>
<script src="assets/medical.js?v=5"></script>
</body>
</html>
<?php
}
