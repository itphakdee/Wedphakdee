<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../admin/permissions_helper.php";

$isComputerAdmin = is_admin_user();
$canEditComputer = $isComputerAdmin;
$canDeleteComputer = $isComputerAdmin;
$canReceiveComputer = $isComputerAdmin;
$showManagementColumn = $isComputerAdmin;

$sql = "
SELECT
    r.*,
    t.name AS technician_name
FROM repair_jobs r
LEFT JOIN technicians t
    ON r.technician_id = t.id
WHERE NOT EXISTS (
    SELECT 1
    FROM repair_receive_jobs rr
    WHERE rr.repair_job_id = r.id
)
ORDER BY r.id DESC
";

$result = $conn->query($sql);
$totalRows = $result ? $result->num_rows : 0;

function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ข้อมูลการแจ้งซ่อมคอมพิวเตอร์</title>

    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="repair_list.css?v=1.0.0">
</head>

<body class="repair-list-page">
    <?php
    $activePage = "computer";
    $basePath = "../../";
    require __DIR__ . "/../../components/sidebar.php";
    ?>

    <header class="repair-topbar">
        <div class="repair-topbar-inner">
            <div class="repair-topbar-title">
                <span class="repair-topbar-icon" aria-hidden="true">💻</span>
                <div>
                    <strong>ระบบแจ้งซ่อมคอมพิวเตอร์</strong>
                    <small>โรงพยาบาลภักดีชุมพล</small>
                </div>
            </div>
            <span class="repair-topbar-badge">งานบริการเทคโนโลยีสารสนเทศ</span>
        </div>
    </header>

    <main class="repair-main">
        <div class="repair-page-shell">
            <section class="repair-page-heading" aria-labelledby="pageTitle">
                <div>
                    <div class="repair-eyebrow">COMPUTER REPAIR SERVICE</div>
                    <h1 id="pageTitle">ข้อมูลการแจ้งซ่อมคอมพิวเตอร์</h1>
                    <p>ติดตามรายการแจ้งซ่อม ตรวจสอบความเร่งด่วน และบริหารจัดการงานซ่อมจากหน้าจอเดียว</p>
                </div>
                <div class="repair-summary-card">
                    <span class="repair-summary-label">รายการรอดำเนินการ</span>
                    <strong><?= (int)$totalRows ?></strong>
                    <span class="repair-summary-unit">รายการ</span>
                </div>
            </section>

            <section class="repair-card">
                <div class="repair-card-header">
                    <div>
                        <h2>รายการแจ้งซ่อมล่าสุด</h2>
                        <p>เรียงจากรายการใหม่ไปเก่า</p>
                    </div>

                    <div class="repair-toolbar" aria-label="เมนูจัดการงานซ่อม">
                        <a href="repair_form.php" class="repair-btn repair-btn-primary">
                            <span class="repair-btn-icon" aria-hidden="true">＋</span>
                            <span>เพิ่มข้อมูลแจ้งซ่อม</span>
                        </a>

                        <a href="savejob/receive_job_list.php" class="repair-btn repair-btn-secondary">
                            <span class="repair-btn-icon" aria-hidden="true">▤</span>
                            <span>รายละเอียดงานที่รับซ่อม</span>
                        </a>

                        <a href="Dashboard/dashboard.php" class="repair-btn repair-btn-outline">
                            <span class="repair-btn-icon" aria-hidden="true">▥</span>
                            <span>Dashboard สรุปงาน</span>
                        </a>

                        <a href="my_jobs.php" class="repair-btn repair-btn-outline">
                            <span class="repair-btn-icon" aria-hidden="true">✓</span>
                            <span>งานแจ้งซ่อมของฉัน</span>
                        </a>
                    </div>
                </div>

                <div class="repair-table-wrap">
                    <table class="repair-table">
                        <thead>
                            <tr>
                                <th class="col-id">ID</th>
                                <th>ชื่อผู้ส่ง</th>
                                <th>แผนก</th>
                                <th>ระบบที่ต้องการแจ้งซ่อม</th>
                                <th>สถานที่พบเจอปัญหา</th>
                                <th>ช่าง</th>
                                <th>รายละเอียด</th>
                                <th class="col-status">สถานะ</th>
                                <?php if ($showManagementColumn): ?>
                                    <th class="col-actions">จัดการ</th>
                                <?php endif; ?>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result && $totalRows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="repair-id">#<?= (int)$row['id'] ?></td>
                                        <td class="repair-person"><?= e($row['sender_name']) ?></td>
                                        <td><?= e($row['department']) ?></td>
                                        <td><?= e($row['repair_system']) ?: '<span class="repair-muted">ไม่ระบุ</span>' ?></td>
                                        <td><?= e($row['location']) ?: '<span class="repair-muted">ไม่ระบุ</span>' ?></td>
                                        <td><?= e($row['technician_name']) ?: '<span class="repair-muted">ยังไม่มอบหมาย</span>' ?></td>
                                        <td class="repair-details" title="<?= e($row['details']) ?>"><?= e($row['details']) ?: '<span class="repair-muted">ไม่มีรายละเอียด</span>' ?></td>
                                        <td>
                                            <?php
                                            switch ($row['priority']) {
                                                case 'normal':
                                                    echo '<span class="repair-status repair-status-normal"><span class="status-dot"></span>ปกติ</span>';
                                                    break;
                                                case 'urgent':
                                                    echo '<span class="repair-status repair-status-urgent"><span class="status-dot"></span>ด่วน</span>';
                                                    break;
                                                case 'emergency':
                                                    echo '<span class="repair-status repair-status-emergency"><span class="status-dot"></span>ด่วนมาก</span>';
                                                    break;
                                                default:
                                                    echo '<span class="repair-status repair-status-none"><span class="status-dot"></span>ไม่ระบุ</span>';
                                            }
                                            ?>
                                        </td>

                                        <?php if ($showManagementColumn): ?>
                                            <td>
                                                <div class="repair-actions">
                                                    <?php if ($canEditComputer): ?>
                                                        <a href="edit.php?id=<?= (int)$row['id'] ?>" class="repair-action repair-action-edit" title="แก้ไขรายการ">
                                                            <span aria-hidden="true">✎</span> แก้ไข
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($canDeleteComputer): ?>
                                                        <a href="delete.php?id=<?= (int)$row['id'] ?>" class="repair-action repair-action-delete"
                                                            onclick="return confirm('ยืนยันการลบรายการ #<?= (int)$row['id'] ?> ?')" title="ลบรายการ">
                                                            <span aria-hidden="true">×</span> ลบ
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($canReceiveComputer): ?>
                                                        <a href="receive_job.php?id=<?= (int)$row['id'] ?>" class="repair-action repair-action-receive" title="รับงานซ่อม">
                                                            <span aria-hidden="true">✓</span> รับงาน
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $showManagementColumn ? 9 : 8 ?>">
                                        <div class="repair-empty-state">
                                            <div class="repair-empty-icon" aria-hidden="true">✓</div>
                                            <strong>ไม่มีรายการแจ้งซ่อมที่รอดำเนินการ</strong>
                                            <span>เมื่อมีรายการใหม่ ระบบจะแสดงข้อมูลในตารางนี้</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="repair-card-footer">
                    <span>แสดงทั้งหมด <?= (int)$totalRows ?> รายการ</span>
                    <span class="repair-footer-note">ข้อมูลรายการที่ยังไม่ได้รับเข้าดำเนินการ</span>
                </div>
            </section>
        </div>
    </main>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php require __DIR__ . "/../../components/dialog.php"; ?>
    <script>
        <?php if (!empty($show_login_success)) { ?>
            showMessageDialog(
                <?php echo json_encode("สวัสดี " . $_SESSION["fullname"] . "\nยินดีต้อนรับเข้าสู่ระบบแจ้งซ่อมและบริหารงาน", JSON_UNESCAPED_UNICODE); ?>,
                <?php echo json_encode("✅ ยินดีต้อนรับ", JSON_UNESCAPED_UNICODE); ?>
            );
        <?php } ?>
    </script>
</body>

</html>
