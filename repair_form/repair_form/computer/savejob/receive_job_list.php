<?php
include("../../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../login.php");
    exit();
}

$keyword = trim($_GET['keyword'] ?? '');
$status = trim($_GET['status'] ?? '');

$keywordSql = $conn->real_escape_string($keyword);
$statusSql = $conn->real_escape_string($status);

$sql = "SELECT
    rr.*,
    r.sender_name,
    r.department,
    r.repair_system,
    r.location,
    r.priority
FROM repair_receive_jobs rr
LEFT JOIN repair_jobs r
    ON rr.repair_job_id = r.id
WHERE 1";

if ($keyword !== '') {
    $sql .= " AND (
        r.sender_name LIKE '%{$keywordSql}%'
        OR rr.technician_name LIKE '%{$keywordSql}%'
        OR rr.device_name LIKE '%{$keywordSql}%'
    )";
}

if ($status !== '') {
    $sql .= " AND rr.status='{$statusSql}'";
}

$sql .= " ORDER BY rr.id DESC";

$result = $conn->query($sql);
$totalRows = $result ? $result->num_rows : 0;

function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatRepairDate($value)
{
    if (empty($value)) {
        return '-';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $value;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รายละเอียดงานรับซ่อมคอมพิวเตอร์</title>

    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link rel="stylesheet" href="../../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../repair_list.css?v=1.1.0">
</head>

<body class="repair-list-page receive-job-page">
    <?php
    $activePage = "computer";
    $basePath = "../../../";
    require __DIR__ . "/../../../components/sidebar.php";
    ?>

    <header class="repair-topbar">
        <div class="repair-topbar-inner">
            <div class="repair-topbar-title">
                <span class="repair-topbar-icon" aria-hidden="true">🧰</span>
                <div>
                    <strong>ระบบบริหารงานรับซ่อมคอมพิวเตอร์</strong>
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
                    <div class="repair-eyebrow">RECEIVED REPAIR JOBS</div>
                    <h1 id="pageTitle">รายละเอียดงานรับซ่อม</h1>
                    <p>ตรวจสอบงานที่รับเข้าดำเนินการ ติดตามสถานะ ความเร่งด่วน ค่าใช้จ่าย และเอกสารงานซ่อมในหน้าจอเดียว</p>
                </div>

                <div class="repair-summary-card">
                    <span class="repair-summary-label">รายการที่แสดง</span>
                    <strong><?= (int)$totalRows ?></strong>
                    <span class="repair-summary-unit">รายการ</span>
                </div>
            </section>

            <section class="repair-card receive-filter-card" aria-label="ค้นหาและกรองรายการ">
                <div class="receive-filter-heading">
                    <div>
                        <h2>ค้นหารายการงานรับซ่อม</h2>
                        <p>ค้นหาจากผู้แจ้ง ช่าง หรือชื่ออุปกรณ์ และกรองตามสถานะงาน</p>
                    </div>
                    <a href="../indexrepairlist.php" class="repair-btn repair-btn-outline receive-back-btn">
                        <span class="repair-btn-icon" aria-hidden="true">←</span>
                        <span>กลับหน้ารายการแจ้งซ่อม</span>
                    </a>
                </div>

                <form method="get" class="receive-filter-form">
                    <div class="receive-field receive-field-keyword">
                        <label for="keyword">คำค้นหา</label>
                        <div class="receive-input-wrap">
                            <span class="receive-input-icon" aria-hidden="true">⌕</span>
                            <input
                                id="keyword"
                                type="text"
                                name="keyword"
                                class="receive-control"
                                placeholder="ผู้แจ้ง / ช่าง / อุปกรณ์"
                                value="<?= e($keyword) ?>">
                        </div>
                    </div>

                    <div class="receive-field">
                        <label for="status">สถานะงาน</label>
                        <select id="status" name="status" class="receive-control receive-select">
                            <option value="" <?= $status === '' ? 'selected' : '' ?>>ทุกสถานะ</option>
                            <option value="กำลังดำเนินการ" <?= $status === 'กำลังดำเนินการ' ? 'selected' : '' ?>>กำลังดำเนินการ</option>
                            <option value="เสร็จสิ้น" <?= $status === 'เสร็จสิ้น' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                        </select>
                    </div>

                    <div class="receive-filter-actions">
                        <button type="submit" class="repair-btn repair-btn-secondary receive-search-btn">
                            <span class="repair-btn-icon" aria-hidden="true">⌕</span>
                            <span>ค้นหา</span>
                        </button>

                        <?php if ($keyword !== '' || $status !== ''): ?>
                            <a href="receive_job_list.php" class="repair-btn repair-btn-outline">
                                <span class="repair-btn-icon" aria-hidden="true">↺</span>
                                <span>ล้างตัวกรอง</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="repair-card receive-list-card">
                <div class="repair-card-header">
                    <div>
                        <h2>รายการงานที่รับเข้าดำเนินการ</h2>
                        <p>เรียงจากรายการที่รับงานล่าสุดไปเก่า</p>
                    </div>
                    <div class="receive-result-note">
                        <span class="receive-result-dot" aria-hidden="true"></span>
                        พบ <?= (int)$totalRows ?> รายการ
                    </div>
                </div>

                <div class="repair-table-wrap">
                    <table class="repair-table receive-table">
                        <thead>
                            <tr>
                                <th class="col-id">#</th>
                                <th>ผู้แจ้ง</th>
                                <th>ช่าง</th>
                                <th class="col-status">สถานะ</th>
                                <th class="col-status">ความเร่งด่วน</th>
                                <th>อุปกรณ์</th>
                                <th class="receive-money-col">รวมเงิน</th>
                                <th class="receive-date-col">วันที่รับงาน</th>
                                <th class="receive-action-col">จัดการ</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result && $totalRows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="repair-id">#<?= (int)$row['repair_job_id'] ?></td>
                                        <td class="repair-person"><?= e($row['sender_name']) ?: '<span class="repair-muted">ไม่ระบุ</span>' ?></td>
                                        <td><?= e($row['technician_name']) ?: '<span class="repair-muted">ไม่ระบุ</span>' ?></td>
                                        <td>
                                            <?php if (($row['status'] ?? '') === 'เสร็จสิ้น'): ?>
                                                <span class="repair-status repair-status-complete">
                                                    <span class="status-dot"></span>เสร็จสิ้น
                                                </span>
                                            <?php else: ?>
                                                <span class="repair-status repair-status-progress">
                                                    <span class="status-dot"></span>กำลังดำเนินการ
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            switch ($row['priority'] ?? '') {
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
                                        <td><?= e($row['device_name']) ?: '<span class="repair-muted">ไม่ระบุ</span>' ?></td>
                                        <td class="receive-money"><?= number_format((float)($row['total_price'] ?? 0), 2) ?></td>
                                        <td>
                                            <span class="receive-date"><?= e(formatRepairDate($row['created_at'] ?? '')) ?></span>
                                        </td>
                                        <td>
                                            <div class="repair-actions">
                                                <a href="receive_job_detail.php?id=<?= (int)$row['id'] ?>" class="repair-action repair-action-detail" title="ดูรายละเอียด">
                                                    <span aria-hidden="true">▤</span> รายละเอียด
                                                </a>
                                                <a href="print_receive_job.php?id=<?= (int)$row['id'] ?>" target="_blank" class="repair-action repair-action-print" title="พิมพ์เอกสาร">
                                                    <span aria-hidden="true">▣</span> พิมพ์
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="repair-empty-state">
                                            <div class="repair-empty-icon" aria-hidden="true">⌕</div>
                                            <strong>ไม่พบรายการงานรับซ่อม</strong>
                                            <span>ลองเปลี่ยนคำค้นหาหรือสถานะ แล้วค้นหาอีกครั้ง</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="repair-card-footer">
                    <span>แสดงทั้งหมด <?= (int)$totalRows ?> รายการ</span>
                    <span class="repair-footer-note">ข้อมูลรายการงานที่ถูกรับเข้าดำเนินการแล้ว</span>
                </div>
            </section>
        </div>
    </main>

    <script src="../../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
