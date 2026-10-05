<?php
require_once __DIR__ . '/config_leave.php';

function leave_page_start($title, $subtitle = '', $activeTab = '')
{
    // Sidebar/permission files are included from inside this function.
    // Bring the global MySQLi connection into this scope so config_admin.php
    // can reuse the connection that config_leave.php already opened.
    global $conn;

    $user = leave_current_user();
    $fy = leave_fiscal_year();
    $flash = leave_flash_get();
    ?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= leave_e($title) ?> | โรงพยาบาลภักดีชุมพล</title>
    <link rel="stylesheet" href="assets/leave.css?v=3">
</head>
<body>
<?php
    $activePage = 'leave';
    $basePath = '../../';
    require __DIR__ . '/../../components/sidebar.php';
?>
<div class="leave-shell main-content">
    <header class="leave-topbar">
        <div>
            <div class="leave-kicker">PHAKDEE CHUMPHON HOSPITAL · LEAVE MANAGEMENT</div>
            <h1><?= leave_e($title) ?></h1>
            <?php if ($subtitle !== ''): ?><p><?= leave_e($subtitle) ?></p><?php endif; ?>
        </div>
        <div class="leave-usercard">
            <div class="leave-usercard__avatar"><?= leave_e(function_exists('mb_substr') ? mb_substr($user['fullname'], 0, 1, 'UTF-8') : substr($user['fullname'], 0, 1)) ?></div>
            <div><strong><?= leave_e($user['fullname']) ?></strong><span><?= leave_e($user['profile_department'] ?: 'ไม่ระบุหน่วยงาน') ?> · ปีงบ <?= (int)$fy ?></span></div>
        </div>
    </header>

    <nav class="leave-nav" aria-label="เมนูระบบลา">
        <a class="<?= $activeTab==='dashboard'?'active':'' ?>" href="index.php">ภาพรวม</a>
        <a class="<?= $activeTab==='add'?'active':'' ?>" href="add.php">ยื่นใบลา</a>
        <a class="<?= $activeTab==='handover'?'active':'' ?>" href="handovers.php">งานที่รับมอบ</a>
        <a class="<?= $activeTab==='approval'?'active':'' ?>" href="approvals.php">อนุมัติใบลา</a>
        <a class="<?= $activeTab==='calendar'?'active':'' ?>" href="calendar.php">ปฏิทินการลา</a>
        <?php if (leave_is_admin()): ?>
            <a class="<?= $activeTab==='supervisors'?'active':'' ?>" href="supervisors.php">หัวหน้างาน</a>
            <a class="<?= $activeTab==='settings'?'active':'' ?>" href="settings.php">สิทธิ์วันลา</a>
        <?php endif; ?>
    </nav>

    <?php if ($flash): ?>
        <div class="leave-alert leave-alert--<?= leave_e($flash['type']) ?>"><?= leave_e($flash['message']) ?></div>
    <?php endif; ?>

    <main class="leave-page">
<?php
}

function leave_page_end()
{
    ?>
    </main>
    <footer class="leave-footer">ระบบบริหารการลางาน · โรงพยาบาลภักดีชุมพล</footer>
</div>
<script src="assets/leave.js?v=3"></script>
</body>
</html>
<?php
}
