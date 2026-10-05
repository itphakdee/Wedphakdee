<?php
include("../../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../login.php");
    exit();
}

function dash_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function dash_scalar($conn, $sql, $field, $defaultValue)
{
    $result = $conn->query($sql);
    if ($result && ($row = $result->fetch_assoc()) && isset($row[$field])) {
        return $row[$field];
    }
    return $defaultValue;
}

function dash_rows($conn, $sql)
{
    $rows = array();
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function dash_status_class($status)
{
    if ($status === 'เสร็จสิ้น') return 'done';
    if ($status === 'กำลังดำเนินการ') return 'working';
    if ($status === 'รอรับงาน') return 'waiting';
    if ($status === 'ยกเลิก') return 'cancelled';
    return 'neutral';
}

function dash_priority_label($priority)
{
    if ($priority === 'emergency') return 'ด่วนมาก';
    if ($priority === 'urgent') return 'ด่วน';
    if ($priority === 'normal') return 'ปกติ';
    return 'ไม่ระบุ';
}

function dash_priority_class($priority)
{
    if ($priority === 'emergency') return 'emergency';
    if ($priority === 'urgent') return 'urgent';
    if ($priority === 'normal') return 'normal';
    return 'neutral';
}

$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
if ($selectedYear < 2020 || $selectedYear > 2100) {
    $selectedYear = (int)date('Y');
}

$currentUser = isset($_SESSION['fullname']) && trim($_SESSION['fullname']) !== ''
    ? trim($_SESSION['fullname'])
    : 'ผู้ใช้งาน';

$latestReceiveJoin = "
    LEFT JOIN repair_receive_jobs rr
      ON rr.repair_job_id = r.id
     AND rr.id = (
         SELECT MAX(rr2.id)
         FROM repair_receive_jobs rr2
         WHERE rr2.repair_job_id = r.id
     )
";

$totalRequests = (int)dash_scalar($conn, "SELECT COUNT(*) AS total FROM repair_jobs", 'total', 0);
$waiting = (int)dash_scalar($conn, "
    SELECT COUNT(*) AS total
    FROM repair_jobs r
    WHERE NOT EXISTS (
        SELECT 1 FROM repair_receive_jobs rr WHERE rr.repair_job_id = r.id
    )
", 'total', 0);

$receivedUnique = (int)dash_scalar($conn, "
    SELECT COUNT(DISTINCT repair_job_id) AS total
    FROM repair_receive_jobs
", 'total', 0);

$working = (int)dash_scalar($conn, "
    SELECT COUNT(*) AS total
    FROM repair_jobs r
    $latestReceiveJoin
    WHERE rr.id IS NOT NULL AND rr.status = 'กำลังดำเนินการ'
", 'total', 0);

$finish = (int)dash_scalar($conn, "
    SELECT COUNT(*) AS total
    FROM repair_jobs r
    $latestReceiveJoin
    WHERE rr.id IS NOT NULL AND rr.status = 'เสร็จสิ้น'
", 'total', 0);

$otherStatus = max(0, $receivedUnique - $working - $finish);

$urgentOpen = (int)dash_scalar($conn, "
    SELECT COUNT(*) AS total
    FROM repair_jobs r
    $latestReceiveJoin
    WHERE r.priority IN ('urgent','emergency')
      AND (rr.id IS NULL OR rr.status <> 'เสร็จสิ้น')
", 'total', 0);

$totalPrice = (float)dash_scalar($conn, "
    SELECT IFNULL(SUM(rr.total_price),0) AS total
    FROM repair_receive_jobs rr
    WHERE rr.id = (
        SELECT MAX(rr2.id)
        FROM repair_receive_jobs rr2
        WHERE rr2.repair_job_id = rr.repair_job_id
    )
", 'total', 0);

$thisMonthRequests = (int)dash_scalar($conn, "
    SELECT COUNT(*) AS total
    FROM repair_jobs
    WHERE YEAR(created_at)=YEAR(CURDATE())
      AND MONTH(created_at)=MONTH(CURDATE())
", 'total', 0);

$todayRequests = (int)dash_scalar($conn, "
    SELECT COUNT(*) AS total
    FROM repair_jobs
    WHERE DATE(created_at)=CURDATE()
", 'total', 0);

$completedRate = $totalRequests > 0 ? round(($finish / $totalRequests) * 100, 1) : 0;
$averageCost = $receivedUnique > 0 ? $totalPrice / $receivedUnique : 0;

$normalCount = (int)dash_scalar($conn, "SELECT COUNT(*) AS total FROM repair_jobs WHERE priority='normal'", 'total', 0);
$urgentCount = (int)dash_scalar($conn, "SELECT COUNT(*) AS total FROM repair_jobs WHERE priority='urgent'", 'total', 0);
$emergencyCount = (int)dash_scalar($conn, "SELECT COUNT(*) AS total FROM repair_jobs WHERE priority='emergency'", 'total', 0);
$priorityOther = max(0, $totalRequests - $normalCount - $urgentCount - $emergencyCount);

$monthLabels = array('ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.');
$monthJobs = array_fill(0, 12, 0);
$monthCosts = array_fill(0, 12, 0.0);

$monthlyJobRows = dash_rows($conn, "
    SELECT MONTH(created_at) AS m, COUNT(*) AS total
    FROM repair_jobs
    WHERE YEAR(created_at) = " . (int)$selectedYear . "
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
");
foreach ($monthlyJobRows as $row) {
    $index = (int)$row['m'] - 1;
    if ($index >= 0 && $index < 12) $monthJobs[$index] = (int)$row['total'];
}

$monthlyCostRows = dash_rows($conn, "
    SELECT MONTH(rr.created_at) AS m, IFNULL(SUM(rr.total_price),0) AS total
    FROM repair_receive_jobs rr
    WHERE YEAR(rr.created_at) = " . (int)$selectedYear . "
      AND rr.id = (
          SELECT MAX(rr2.id)
          FROM repair_receive_jobs rr2
          WHERE rr2.repair_job_id = rr.repair_job_id
      )
    GROUP BY MONTH(rr.created_at)
    ORDER BY MONTH(rr.created_at)
");
foreach ($monthlyCostRows as $row) {
    $index = (int)$row['m'] - 1;
    if ($index >= 0 && $index < 12) $monthCosts[$index] = (float)$row['total'];
}

$technicianRows = dash_rows($conn, "
    SELECT COALESCE(NULLIF(TRIM(rr.technician_name),''), 'ไม่ระบุช่าง') AS label, COUNT(*) AS total
    FROM repair_receive_jobs rr
    WHERE rr.id = (
        SELECT MAX(rr2.id)
        FROM repair_receive_jobs rr2
        WHERE rr2.repair_job_id = rr.repair_job_id
    )
    GROUP BY COALESCE(NULLIF(TRIM(rr.technician_name),''), 'ไม่ระบุช่าง')
    ORDER BY total DESC, label ASC
    LIMIT 7
");

$systemRows = dash_rows($conn, "
    SELECT COALESCE(NULLIF(TRIM(repair_system),''), 'ไม่ระบุระบบ') AS label, COUNT(*) AS total
    FROM repair_jobs
    GROUP BY COALESCE(NULLIF(TRIM(repair_system),''), 'ไม่ระบุระบบ')
    ORDER BY total DESC, label ASC
    LIMIT 7
");

$departmentRows = dash_rows($conn, "
    SELECT COALESCE(NULLIF(TRIM(department),''), 'ไม่ระบุแผนก') AS label, COUNT(*) AS total
    FROM repair_jobs
    GROUP BY COALESCE(NULLIF(TRIM(department),''), 'ไม่ระบุแผนก')
    ORDER BY total DESC, label ASC
    LIMIT 5
");

$recentRows = dash_rows($conn, "
    SELECT
        r.id,
        r.sender_name,
        r.department,
        r.repair_system,
        r.location,
        r.priority,
        r.created_at,
        rr.status,
        rr.technician_name,
        rr.total_price
    FROM repair_jobs r
    $latestReceiveJoin
    ORDER BY r.id DESC
    LIMIT 10
");

$technicianLabels = array();
$technicianData = array();
foreach ($technicianRows as $row) {
    $technicianLabels[] = $row['label'];
    $technicianData[] = (int)$row['total'];
}

$systemLabels = array();
$systemData = array();
foreach ($systemRows as $row) {
    $systemLabels[] = $row['label'];
    $systemData[] = (int)$row['total'];
}

$dashboardData = array(
    'status' => array(
        'labels' => array('รอรับงาน', 'กำลังดำเนินการ', 'เสร็จสิ้น', 'สถานะอื่น'),
        'data' => array($waiting, $working, $finish, $otherStatus)
    ),
    'priority' => array(
        'labels' => array('ปกติ', 'ด่วน', 'ด่วนมาก', 'ไม่ระบุ'),
        'data' => array($normalCount, $urgentCount, $emergencyCount, $priorityOther)
    ),
    'monthlyJobs' => array('labels' => $monthLabels, 'data' => $monthJobs),
    'monthlyCosts' => array('labels' => $monthLabels, 'data' => $monthCosts),
    'technicians' => array('labels' => $technicianLabels, 'data' => $technicianData),
    'systems' => array('labels' => $systemLabels, 'data' => $systemData)
);
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d5a55">
    <title>Dashboard งานซ่อมคอมพิวเตอร์ | โรงพยาบาลภักดีชุมพล</title>
    <link rel="stylesheet" href="../../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="dashboard.css?v=3.0.0">
</head>
<body class="pdc-dashboard-page">
    <?php
    $activePage = "computer";
    $basePath = "../../../";
    require __DIR__ . "/../../../components/sidebar.php";
    ?>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="dashboard-topbar__brand">
                <span class="dashboard-topbar__mark">PDC</span>
                <div>
                    <strong>ระบบบริหารงานซ่อมคอมพิวเตอร์</strong>
                    <small>โรงพยาบาลภักดีชุมพล · งานเทคโนโลยีสารสนเทศ</small>
                </div>
            </div>
            <div class="dashboard-topbar__meta">
                <span class="live-dot" aria-hidden="true"></span>
                <span>ข้อมูลล่าสุด <?= dash_e(date('d/m/Y H:i')) ?> น.</span>
            </div>
        </header>

        <div class="dashboard-shell">
            <section class="dashboard-hero">
                <div class="dashboard-hero__content">
                    <div class="dashboard-eyebrow">COMPUTER REPAIR MANAGEMENT DASHBOARD</div>
                    <h1>ภาพรวมงานแจ้งซ่อมคอมพิวเตอร์</h1>
                    <p>ติดตามปริมาณงาน สถานะ ความเร่งด่วน ภาระงานช่าง และค่าใช้จ่าย เพื่อสนับสนุนการบริหารงานซ่อมอย่างเป็นระบบ</p>
                    <div class="dashboard-hero__chips">
                        <span>ผู้ใช้งาน: <?= dash_e($currentUser) ?></span>
                        <span>เดือนนี้ <?= number_format($thisMonthRequests) ?> งาน</span>
                        <span>วันนี้ <?= number_format($todayRequests) ?> งาน</span>
                    </div>
                </div>
                <div class="dashboard-hero__actions">
                    <a href="../indexrepairlist.php" class="dash-btn dash-btn-light">← กลับหน้ารายการ</a>
                    <a href="../repair_form.php" class="dash-btn dash-btn-primary">＋ แจ้งซ่อมใหม่</a>
                    <button type="button" class="dash-btn dash-btn-ghost" onclick="window.print()">⎙ พิมพ์รายงาน</button>
                </div>
            </section>

            <section class="kpi-grid" aria-label="ตัวชี้วัดสำคัญ">
                <article class="kpi-card kpi-card--total">
                    <div class="kpi-card__icon">▦</div>
                    <div class="kpi-card__body">
                        <span class="kpi-label">รายการแจ้งซ่อมทั้งหมด</span>
                        <strong><?= number_format($totalRequests) ?></strong>
                        <small>รายการในระบบ</small>
                    </div>
                </article>

                <article class="kpi-card kpi-card--waiting">
                    <div class="kpi-card__icon">⌛</div>
                    <div class="kpi-card__body">
                        <span class="kpi-label">รอรับงาน</span>
                        <strong><?= number_format($waiting) ?></strong>
                        <small>ยังไม่ได้รับเข้าดำเนินการ</small>
                    </div>
                </article>

                <article class="kpi-card kpi-card--working">
                    <div class="kpi-card__icon">⚙</div>
                    <div class="kpi-card__body">
                        <span class="kpi-label">กำลังดำเนินการ</span>
                        <strong><?= number_format($working) ?></strong>
                        <small>งานที่อยู่ระหว่างซ่อม</small>
                    </div>
                </article>

                <article class="kpi-card kpi-card--done">
                    <div class="kpi-card__icon">✓</div>
                    <div class="kpi-card__body">
                        <span class="kpi-label">เสร็จสิ้น</span>
                        <strong><?= number_format($finish) ?></strong>
                        <small>อัตราสำเร็จ <?= number_format($completedRate, 1) ?>%</small>
                    </div>
                </article>

                <article class="kpi-card kpi-card--urgent">
                    <div class="kpi-card__icon">!</div>
                    <div class="kpi-card__body">
                        <span class="kpi-label">งานเร่งด่วนคงค้าง</span>
                        <strong><?= number_format($urgentOpen) ?></strong>
                        <small>ด่วนและด่วนมากที่ยังไม่เสร็จ</small>
                    </div>
                </article>

                <article class="kpi-card kpi-card--cost">
                    <div class="kpi-card__icon">฿</div>
                    <div class="kpi-card__body">
                        <span class="kpi-label">ค่าใช้จ่ายรวม</span>
                        <strong><?= number_format($totalPrice, 2) ?></strong>
                        <small>เฉลี่ย <?= number_format($averageCost, 2) ?> บาท/งานรับซ่อม</small>
                    </div>
                </article>
            </section>

            <section class="dashboard-summary-row">
                <article class="summary-panel summary-panel--progress">
                    <div class="summary-panel__top">
                        <div>
                            <span class="panel-kicker">SERVICE PERFORMANCE</span>
                            <h2>ความคืบหน้าภาพรวม</h2>
                        </div>
                        <strong><?= number_format($completedRate, 1) ?>%</strong>
                    </div>
                    <div class="progress-track" aria-label="อัตรางานเสร็จสิ้น <?= dash_e($completedRate) ?> เปอร์เซ็นต์">
                        <span style="width: <?= max(0, min(100, $completedRate)) ?>%"></span>
                    </div>
                    <div class="summary-stat-grid">
                        <div><span>รับงานแล้ว</span><strong><?= number_format($receivedUnique) ?></strong></div>
                        <div><span>รอรับงาน</span><strong><?= number_format($waiting) ?></strong></div>
                        <div><span>งานเดือนนี้</span><strong><?= number_format($thisMonthRequests) ?></strong></div>
                    </div>
                </article>

                <article class="summary-panel summary-panel--departments">
                    <div class="summary-panel__top compact">
                        <div>
                            <span class="panel-kicker">TOP REQUESTING UNITS</span>
                            <h2>แผนกที่แจ้งซ่อมสูงสุด</h2>
                        </div>
                    </div>
                    <div class="department-list">
                        <?php if (count($departmentRows) > 0): ?>
                            <?php $deptMax = max(1, (int)$departmentRows[0]['total']); ?>
                            <?php foreach ($departmentRows as $index => $row): ?>
                                <div class="department-row">
                                    <span class="department-rank"><?= (int)$index + 1 ?></span>
                                    <div class="department-info">
                                        <div class="department-name"><span><?= dash_e($row['label']) ?></span><strong><?= number_format((int)$row['total']) ?></strong></div>
                                        <div class="department-bar"><span style="width: <?= round(((int)$row['total'] / $deptMax) * 100, 1) ?>%"></span></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-mini">ยังไม่มีข้อมูลแผนก</div>
                        <?php endif; ?>
                    </div>
                </article>
            </section>

            <section class="chart-grid chart-grid--top">
                <article class="dash-panel">
                    <div class="dash-panel__header">
                        <div><span class="panel-kicker">WORK STATUS</span><h2>สถานะงานซ่อม</h2></div>
                        <span class="panel-note">ข้อมูลปัจจุบัน</span>
                    </div>
                    <div class="chart-wrap chart-wrap--donut"><canvas id="statusChart"></canvas></div>
                    <div class="chart-fallback" data-chart-fallback="statusChart">ไม่สามารถโหลดกราฟได้</div>
                </article>

                <article class="dash-panel">
                    <div class="dash-panel__header">
                        <div><span class="panel-kicker">PRIORITY</span><h2>ระดับความเร่งด่วน</h2></div>
                        <span class="panel-note">ทุกช่วงเวลา</span>
                    </div>
                    <div class="chart-wrap chart-wrap--donut"><canvas id="priorityChart"></canvas></div>
                    <div class="chart-fallback" data-chart-fallback="priorityChart">ไม่สามารถโหลดกราฟได้</div>
                </article>

                <article class="dash-panel">
                    <div class="dash-panel__header">
                        <div><span class="panel-kicker">REPAIR SYSTEM</span><h2>ระบบที่แจ้งซ่อมสูงสุด</h2></div>
                        <span class="panel-note">Top <?= count($systemRows) ?></span>
                    </div>
                    <div class="chart-wrap"><canvas id="systemChart"></canvas></div>
                    <div class="chart-fallback" data-chart-fallback="systemChart">ไม่สามารถโหลดกราฟได้</div>
                </article>
            </section>

            <section class="dash-panel trend-panel">
                <div class="dash-panel__header dash-panel__header--responsive">
                    <div><span class="panel-kicker">ANNUAL TREND</span><h2>แนวโน้มงานและค่าใช้จ่ายรายเดือน</h2></div>
                    <form method="get" class="year-filter">
                        <label for="year">ปี ค.ศ.</label>
                        <select id="year" name="year" onchange="this.form.submit()">
                            <?php for ($year = (int)date('Y') + 1; $year >= (int)date('Y') - 5; $year--): ?>
                                <option value="<?= $year ?>" <?= $year === $selectedYear ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
                <div class="chart-wrap chart-wrap--wide"><canvas id="trendChart"></canvas></div>
                <div class="chart-fallback" data-chart-fallback="trendChart">ไม่สามารถโหลดกราฟได้</div>
            </section>

            <section class="chart-grid chart-grid--bottom">
                <article class="dash-panel">
                    <div class="dash-panel__header">
                        <div><span class="panel-kicker">TECHNICIAN WORKLOAD</span><h2>ภาระงานของช่าง</h2></div>
                        <span class="panel-note">ตามงานล่าสุดของแต่ละรายการ</span>
                    </div>
                    <div class="chart-wrap chart-wrap--horizontal"><canvas id="technicianChart"></canvas></div>
                    <div class="chart-fallback" data-chart-fallback="technicianChart">ไม่สามารถโหลดกราฟได้</div>
                </article>

                <article class="dash-panel quick-actions-panel">
                    <div class="dash-panel__header">
                        <div><span class="panel-kicker">QUICK ACCESS</span><h2>เมนูใช้งานด่วน</h2></div>
                    </div>
                    <div class="quick-action-grid">
                        <a href="../indexrepairlist.php"><span>▤</span><strong>รายการแจ้งซ่อม</strong><small>ตรวจสอบงานที่รอรับ</small></a>
                        <a href="../savejob/receive_job_list.php"><span>✓</span><strong>งานที่รับซ่อม</strong><small>ติดตามรายละเอียดและสถานะ</small></a>
                        <a href="../my_jobs.php"><span>◉</span><strong>งานของฉัน</strong><small>ดูรายการของผู้ใช้งาน</small></a>
                        <a href="../repair_form.php"><span>＋</span><strong>แจ้งซ่อมใหม่</strong><small>สร้างรายการแจ้งซ่อม</small></a>
                    </div>
                </article>
            </section>

            <section class="dash-panel latest-panel">
                <div class="dash-panel__header dash-panel__header--responsive">
                    <div><span class="panel-kicker">LATEST REQUESTS</span><h2>10 รายการแจ้งซ่อมล่าสุด</h2></div>
                    <a class="text-link" href="../indexrepairlist.php">ดูรายการทั้งหมด →</a>
                </div>
                <div class="latest-table-wrap">
                    <table class="latest-table">
                        <thead>
                            <tr>
                                <th>เลขที่</th>
                                <th>ผู้แจ้ง / แผนก</th>
                                <th>ระบบ / สถานที่</th>
                                <th>ความเร่งด่วน</th>
                                <th>สถานะ</th>
                                <th>ช่างผู้รับผิดชอบ</th>
                                <th>ค่าใช้จ่าย</th>
                                <th>วันที่แจ้ง</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentRows) > 0): ?>
                                <?php foreach ($recentRows as $row): ?>
                                    <?php $status = $row['status'] ? $row['status'] : 'รอรับงาน'; ?>
                                    <tr>
                                        <td class="job-id">#<?= (int)$row['id'] ?></td>
                                        <td><strong><?= dash_e($row['sender_name']) ?></strong><small><?= dash_e($row['department']) ?></small></td>
                                        <td><strong><?= dash_e($row['repair_system'] ? $row['repair_system'] : 'ไม่ระบุระบบ') ?></strong><small><?= dash_e($row['location'] ? $row['location'] : 'ไม่ระบุสถานที่') ?></small></td>
                                        <td><span class="priority-badge <?= dash_priority_class($row['priority']) ?>"><?= dash_e(dash_priority_label($row['priority'])) ?></span></td>
                                        <td><span class="status-badge <?= dash_status_class($status) ?>"><i></i><?= dash_e($status) ?></span></td>
                                        <td><?= dash_e($row['technician_name'] ? $row['technician_name'] : 'ยังไม่มอบหมาย') ?></td>
                                        <td class="money"><?= number_format((float)$row['total_price'], 2) ?></td>
                                        <td><?= dash_e(date('d/m/Y H:i', strtotime($row['created_at']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8"><div class="table-empty">ยังไม่มีข้อมูลรายการแจ้งซ่อม</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="dashboard-footer">
                <span>โรงพยาบาลภักดีชุมพล · ระบบบริหารงานซ่อมคอมพิวเตอร์</span>
                <span>ข้อมูลสรุปจากฐานข้อมูลระบบแจ้งซ่อม</span>
            </footer>
        </div>
    </main>

    <script>
        window.PDCDashboardData = <?= json_encode($dashboardData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.PDCDashboardYear = <?= (int)$selectedYear ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="dashboard.js?v=3.0.0"></script>
    <script src="../../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
