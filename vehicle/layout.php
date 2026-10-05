<?php
require_once __DIR__ . '/config_vehicle.php';

function vehicle_page_start($title, $subtitle, $active)
{
    global $currentVehicleUser, $currentVehicleDept, $currentVehicleIsAdmin;
    $flash = vehicle_take_flash();
    $roleLabel = $currentVehicleIsAdmin ? 'ผู้ดูแลระบบ' : 'ผู้ใช้งาน';
    $approvalCount = 0;
    if (vehicle_column_exists('vehicle_requests', 'supervisor_user_id')) {
        $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($currentVehicleIsAdmin) {
            $ars = $GLOBALS['conn']->query("SELECT COUNT(*) c FROM vehicle_requests WHERE status='pending_supervisor'");
        } else {
            $ars = $GLOBALS['conn']->query("SELECT COUNT(*) c FROM vehicle_requests WHERE status='pending_supervisor' AND supervisor_user_id=" . $uid);
        }
        if ($ars && ($ar = $ars->fetch_assoc())) $approvalCount = (int)$ar['c'];
    }
    $deptLabel = $currentVehicleDept['name'] !== '' ? $currentVehicleDept['name'] : 'ยังไม่ระบุแผนก';
    $fullNameForInitial = isset($currentVehicleUser['fullname']) ? $currentVehicleUser['fullname'] : 'U';
    $initial = function_exists('mb_substr') ? mb_substr($fullNameForInitial, 0, 1, 'UTF-8') : substr($fullNameForInitial, 0, 1);
    ?><!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= vehicle_e($title) ?> | โรงพยาบาลภักดีชุมพล</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="assets/css/vehicle.css?v=20260922porobo1">
</head>
<body class="vehicle-body">
<div class="vehicle-shell">
<?php
    $activePage = 'vehicle';
    $basePath = '../';
    require __DIR__ . '/../components/sidebar.php';
?>
<main class="vehicle-main">
    <div class="vehicle-topbar">
        <div class="vehicle-topbar__brand">
            <div class="vehicle-brand-mark">PDC</div>
            <div class="vehicle-topbar__title"><strong>งานบริการยานพาหนะ</strong><span>โรงพยาบาลภักดีชุมพล</span></div>
        </div>
        <div class="vehicle-user-chip">
            <span class="vehicle-user-chip__avatar"><?= vehicle_e($initial) ?></span>
            <span><strong><?= vehicle_e(isset($currentVehicleUser['fullname']) ? $currentVehicleUser['fullname'] : 'ผู้ใช้งาน') ?></strong><br><small><?= vehicle_e($deptLabel . ' · ' . $roleLabel) ?></small></span>
        </div>
    </div>

    <section class="vehicle-hero">
        <div class="vehicle-hero__row">
            <div>
                <p class="vehicle-eyebrow">PHAKDEE CHUMPHON HOSPITAL · VEHICLE SERVICE</p>
                <h1><?= vehicle_e($title) ?></h1>
                <p><?= vehicle_e($subtitle) ?></p>
            </div>
            <div class="vehicle-hero__actions">
                <a class="vehicle-btn vehicle-btn--ghost" href="../dashboard.php">← หน้าหลัก</a>
                <a class="vehicle-btn vehicle-btn--primary" href="index.php?page=add">＋ ขอใช้รถ</a>
            </div>
        </div>
        <nav class="vehicle-nav">
            <a class="<?= $active==='dashboard'?'active':'' ?>" href="index.php?page=dashboard">ภาพรวม</a>
            <a class="<?= $active==='add'?'active':'' ?>" href="index.php?page=add">เพิ่มข้อมูลการใช้รถ</a>
            <a class="<?= $active==='list'?'active':'' ?>" href="index.php?page=list">ทะเบียนใช้รถ</a>
            <a class="<?= $active==='calendar'?'active':'' ?>" href="index.php?page=calendar">ปฏิทินยานพาหนะ</a>
            <?php if ($approvalCount > 0): ?><a class="<?= $active==='approvals'?'active':'' ?>" href="approvals.php">รอรับรอง (<?= $approvalCount ?>)</a><?php endif; ?>
            <?php if ($currentVehicleIsAdmin): ?><a class="<?= $active==='porobo'?'active':'' ?>" href="porobo.php">พ.ร.บ.รถ</a><?php endif; ?>
            <?php if ($currentVehicleIsAdmin): ?><a class="<?= $active==='resources'?'active':'' ?>" href="resources.php">จัดการรถ / พนักงานขับ</a><?php endif; ?>
        </nav>
    </section>

    <?php if ($flash): ?>
        <div class="vehicle-alert vehicle-alert--<?= vehicle_e($flash['type']) ?>"><?= vehicle_e($flash['message']) ?></div>
    <?php endif; ?>
<?php
}

function vehicle_page_end()
{
    ?>
</main>
</div>
<script src="assets/js/vehicle.js?v=20260922porobo1"></script>
<?php require __DIR__ . '/../components/dialog.php'; ?>
</body>
</html><?php
}
