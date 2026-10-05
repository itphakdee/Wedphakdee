<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_login();

$schemaReady = maintenance_table_exists();
$flash = maintenance_pull_flash();

$departments = [];
$deptResult = $conn->query("SELECT department_name FROM departments WHERE status='ใช้งาน' ORDER BY department_name");
if ($deptResult) {
    while ($row = $deptResult->fetch_assoc()) {
        $departments[] = $row['department_name'];
    }
}

$technicians = [];
$techResult = $conn->query("SELECT id, name, department FROM technicians WHERE status='ใช้งาน' ORDER BY department, name");
if ($techResult) {
    while ($row = $techResult->fetch_assoc()) {
        $technicians[] = $row;
    }
}

$currentDepartment = '';
if (!empty($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmtUser = $conn->prepare("SELECT department FROM users WHERE id=? LIMIT 1");
    if ($stmtUser) {
        $stmtUser->bind_param('i', $uid);
        $stmtUser->execute();
        $stmtUser->bind_result($currentDepartment);
        $stmtUser->fetch();
        $stmtUser->close();
    }
}

$stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];
$rows = [];
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$systemFilter = trim($_GET['system'] ?? '');

if ($schemaReady) {
    $statsResult = $conn->query("SELECT
        COUNT(*) AS total,
        SUM(status IN ('pending','assigned')) AS pending,
        SUM(status='in_progress') AS in_progress,
        SUM(status='completed') AS completed
        FROM maintenance_requests");
    if ($statsResult && ($s = $statsResult->fetch_assoc())) {
        $stats = [
            'total' => (int)$s['total'],
            'pending' => (int)$s['pending'],
            'in_progress' => (int)$s['in_progress'],
            'completed' => (int)$s['completed'],
        ];
    }

    $sql = "SELECT * FROM maintenance_requests
            WHERE (?='' OR sender_name LIKE CONCAT('%',?,'%') OR department LIKE CONCAT('%',?,'%') OR details LIKE CONCAT('%',?,'%') OR COALESCE(technician_name,'') LIKE CONCAT('%',?,'%'))
              AND (?='' OR status=?)
              AND (?='' OR system_type=?)
            ORDER BY id DESC
            LIMIT 100";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            'sssssssss',
            $search, $search, $search, $search, $search,
            $statusFilter, $statusFilter,
            $systemFilter, $systemFilter
        );
        $stmt->execute();
        $result = $stmt->get_result();
        while ($result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }
        $stmt->close();
    }
}

$canCreate = maintenance_can('create') || (function_exists('is_admin_user') && is_admin_user());
$canEdit = maintenance_can('edit');
$canDelete = maintenance_can('delete');
$canManage = maintenance_can('manage');
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>งานซ่อมบำรุง | โรงพยาบาลภักดีชุมพล</title>
    <link rel="stylesheet" href="assets/maintenance.css?v=<?= filemtime(__DIR__ . '/assets/maintenance.css') ?>">
</head>
<body>
<?php
$activePage = 'maintenance';
$basePath = '../../';
require __DIR__ . '/../../components/sidebar.php';
?>
<main class="main-content maintenance-main">
    <div class="mt-page-top">
        <div class="mt-breadcrumb">
            <a href="../home_repair.php">ระบบแจ้งซ่อม</a>
            <span>›</span>
            <span>งานซ่อมบำรุง</span>
        </div>
        <div class="mt-user-chip">
            <span class="mt-user-avatar"><?= mh(maintenance_initial($_SESSION['fullname'] ?? 'U')) ?></span>
            <span><?= mh($_SESSION['fullname'] ?? 'ผู้ใช้งาน') ?></span>
        </div>
    </div>

    <section class="mt-hero">
        <div class="mt-hero-copy">
            <p class="mt-eyebrow">FACILITY MAINTENANCE</p>
            <h1>ระบบงานซ่อมบำรุง</h1>
            <p>รับแจ้งงานประปา ไฟฟ้า และเครื่องปรับอากาศ พร้อมมอบหมายช่างและติดตามสถานะงานอย่างเป็นระบบ</p>
        </div>
        <div class="mt-hero-actions">
            <a class="mt-btn mt-btn-outline" href="../home_repair.php">← กลับเมนูแจ้งซ่อม</a>
            <?php if ($schemaReady && $canCreate): ?>
                <a class="mt-btn mt-btn-teal" href="#new-request">＋ แจ้งซ่อมใหม่</a>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($flash): ?>
        <div class="mt-flash <?= mh($flash['type']) ?>"><?= mh($flash['message']) ?></div>
    <?php endif; ?>

    <?php if (!$schemaReady): ?>
        <section class="mt-card mt-setup">
            <div class="mt-empty-icon">DB</div>
            <h2>ยังไม่ได้ติดตั้งฐานข้อมูลงานซ่อมบำรุง</h2>
            <p>ติดตั้งตาราง <code>maintenance_requests</code> ก่อนเริ่มใช้งาน ระบบมีตัวติดตั้งให้เรียบร้อยแล้ว</p>
            <a class="mt-btn mt-btn-primary" href="install.php">ติดตั้งฐานข้อมูล</a>
        </section>
    <?php else: ?>
        <section class="mt-stats" aria-label="สรุปสถานะงานซ่อมบำรุง">
            <div class="mt-stat">
                <div class="mt-stat-icon">Σ</div>
                <div><div class="mt-stat-value"><?= (int)$stats['total'] ?></div><div class="mt-stat-label">งานทั้งหมด</div></div>
            </div>
            <div class="mt-stat pending">
                <div class="mt-stat-icon">!</div>
                <div><div class="mt-stat-value"><?= (int)$stats['pending'] ?></div><div class="mt-stat-label">รอรับ / มอบหมาย</div></div>
            </div>
            <div class="mt-stat progress">
                <div class="mt-stat-icon">↻</div>
                <div><div class="mt-stat-value"><?= (int)$stats['in_progress'] ?></div><div class="mt-stat-label">กำลังดำเนินการ</div></div>
            </div>
            <div class="mt-stat completed">
                <div class="mt-stat-icon">✓</div>
                <div><div class="mt-stat-value"><?= (int)$stats['completed'] ?></div><div class="mt-stat-label">เสร็จสิ้น</div></div>
            </div>
        </section>

        <div class="mt-grid">
            <section class="mt-card" id="new-request">
                <div class="mt-card-head">
                    <div class="mt-card-title-wrap">
                        <div class="mt-section-badge">01</div>
                        <div>
                            <h2>แบบฟอร์มแจ้งซ่อมบำรุง</h2>
                            <p>กรอกข้อมูลให้ครบถ้วนเพื่อส่งงานไปยังช่างที่รับผิดชอบ</p>
                        </div>
                    </div>
                </div>
                <div class="mt-card-body">
                    <?php if (!$canCreate): ?>
                        <div class="mt-flash info">บัญชีนี้สามารถดูรายการได้ แต่ยังไม่มีสิทธิ์เพิ่มงานซ่อมบำรุง</div>
                    <?php else: ?>
                    <form method="post" action="save.php" id="maintenanceForm" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= mh(maintenance_csrf_token()) ?>">
                        <div class="mt-form-grid">
                            <div class="mt-field full">
                                <label class="mt-label" for="sender_name">ชื่อผู้ส่ง <span class="mt-required">*</span></label>
                                <input class="mt-control" id="sender_name" name="sender_name" maxlength="150" required
                                       value="<?= mh($_SESSION['fullname'] ?? '') ?>" placeholder="ชื่อ - นามสกุล ผู้แจ้งซ่อม">
                            </div>

                            <div class="mt-field full">
                                <label class="mt-label" for="department">แผนก <span class="mt-required">*</span></label>
                                <select class="mt-control" id="department" name="department" required>
                                    <option value="">-- เลือกแผนก --</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= mh($department) ?>" <?= $currentDepartment === $department ? 'selected' : '' ?>><?= mh($department) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mt-field full">
                                <span class="mt-label">ระบบที่ต้องการแจ้งซ่อม <span class="mt-required">*</span></span>
                                <div class="mt-system-options">
                                    <?php foreach (['ประปา' => 'W', 'ไฟฟ้า' => 'E', 'แอร์' => 'A'] as $system => $letter): ?>
                                        <label>
                                            <input class="mt-system-radio" type="radio" name="system_type" value="<?= mh($system) ?>" required>
                                            <span class="mt-system-card"><strong><?= $letter ?></strong><span><?= mh($system) ?></span></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mt-field full">
                                <label class="mt-label" for="technician_id">เลือกช่าง <span class="mt-required">*</span></label>
                                <select class="mt-control" id="technician_id" name="technician_id" required>
                                    <option value="">-- เลือกช่างผู้รับผิดชอบ --</option>
                                    <?php foreach ($technicians as $tech): ?>
                                        <option value="<?= (int)$tech['id'] ?>" data-specialty="<?= mh(trim((string)$tech['department'])) ?>">
                                            <?= mh(trim((string)$tech['name'])) ?><?= $tech['department'] ? ' — ' . mh($tech['department']) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="mt-help">ระบบจะกรองรายชื่อช่างตามประเภทงานที่เลือก</div>
                            </div>

                            <div class="mt-field full">
                                <label class="mt-label" for="location">สถานที่ / จุดที่พบปัญหา</label>
                                <input class="mt-control" id="location" name="location" maxlength="255" placeholder="เช่น ห้องฉุกเฉิน ชั้น 1, อาคารผู้ป่วยนอก">
                            </div>

                            <div class="mt-field full">
                                <label class="mt-label" for="details">รายละเอียดแจ้งซ่อม <span class="mt-required">*</span></label>
                                <textarea class="mt-control" id="details" name="details" maxlength="3000" required placeholder="อธิบายอาการเสีย ปัญหาที่พบ หรือข้อมูลที่ช่างควรทราบ"></textarea>
                            </div>

                            <div class="mt-field full">
                                <span class="mt-label">ระดับความเร่งด่วน</span>
                                <div class="mt-priority-options">
                                    <label><input class="mt-priority-radio" type="radio" name="priority" value="normal" checked><span class="mt-priority-card"><i class="mt-dot"></i> ปกติ</span></label>
                                    <label><input class="mt-priority-radio" type="radio" name="priority" value="urgent"><span class="mt-priority-card"><i class="mt-dot urgent"></i> ด่วน</span></label>
                                    <label><input class="mt-priority-radio" type="radio" name="priority" value="emergency"><span class="mt-priority-card"><i class="mt-dot emergency"></i> ด่วนมาก</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-form-actions">
                            <button class="mt-btn mt-btn-primary" type="submit">บันทึกการแจ้งซ่อม</button>
                            <button class="mt-btn mt-btn-outline" type="reset">ล้างข้อมูล</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </section>

            <section class="mt-card">
                <div class="mt-card-head">
                    <div class="mt-card-title-wrap">
                        <div class="mt-section-badge">02</div>
                        <div>
                            <h2>รายการงานซ่อมบำรุง</h2>
                            <p>ค้นหา กรองสถานะ และติดตามงานซ่อมล่าสุดได้จากตารางนี้</p>
                        </div>
                    </div>
                    <div class="mt-badge mt-status-neutral">พบ <?= count($rows) ?> รายการ</div>
                </div>

                <form class="mt-filter-bar" method="get">
                    <div class="mt-search-wrap">
                        <span class="mt-search-icon">⌕</span>
                        <input class="mt-control" name="q" value="<?= mh($search) ?>" placeholder="ค้นหาผู้แจ้ง แผนก ช่าง หรือรายละเอียด">
                    </div>
                    <select class="mt-control" name="status">
                        <option value="">ทุกสถานะ</option>
                        <?php foreach (['pending'=>'รอรับงาน','assigned'=>'มอบหมายแล้ว','in_progress'=>'กำลังดำเนินการ','completed'=>'เสร็จสิ้น','cancelled'=>'ยกเลิก'] as $value=>$label): ?>
                            <option value="<?= $value ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="mt-control" name="system">
                        <option value="">ทุกระบบ</option>
                        <?php foreach (['ประปา','ไฟฟ้า','แอร์'] as $system): ?>
                            <option value="<?= mh($system) ?>" <?= $systemFilter === $system ? 'selected' : '' ?>><?= mh($system) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="mt-btn mt-btn-primary" type="submit">ค้นหา</button>
                </form>

                <?php if (!$rows): ?>
                    <div class="mt-empty">
                        <div class="mt-empty-icon">0</div>
                        <strong>ไม่พบรายการงานซ่อม</strong>
                        <span>ลองเปลี่ยนคำค้นหาหรือตัวกรอง หรือเพิ่มรายการแจ้งซ่อมใหม่</span>
                    </div>
                <?php else: ?>
                    <div class="mt-table-wrap">
                        <table class="mt-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ผู้แจ้ง / แผนก</th>
                                    <th>ระบบ</th>
                                    <th>ช่าง</th>
                                    <th>ความเร่งด่วน</th>
                                    <th>สถานะ</th>
                                    <th>วันที่แจ้ง</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $row):
                                $status = maintenance_status_meta($row['status']);
                                $priority = maintenance_priority_meta($row['priority']);
                                $system = maintenance_system_meta($row['system_type']);
                            ?>
                                <tr>
                                    <td><span class="mt-id">#<?= (int)$row['id'] ?></span></td>
                                    <td><div class="mt-primary-text"><?= mh($row['sender_name']) ?></div><div class="mt-secondary-text"><?= mh($row['department']) ?></div></td>
                                    <td><span class="mt-system-pill"><span class="mt-system-letter"><?= mh($system['icon']) ?></span><?= mh($system['label']) ?></span></td>
                                    <td><?= $row['technician_name'] ? mh($row['technician_name']) : '<span class="mt-secondary-text">ยังไม่ระบุ</span>' ?></td>
                                    <td><span class="mt-badge mt-priority-<?= mh($priority['class']) ?>"><?= mh($priority['label']) ?></span></td>
                                    <td><span class="mt-badge mt-status-<?= mh($status['class']) ?>"><?= mh($status['label']) ?></span></td>
                                    <td><div class="mt-primary-text"><?= mh(date('d/m/Y', strtotime($row['created_at']))) ?></div><div class="mt-secondary-text"><?= mh(date('H:i', strtotime($row['created_at']))) ?> น.</div></td>
                                    <td>
                                        <div class="mt-actions">
                                            <a class="mt-btn mt-btn-soft mt-btn-sm" href="detail.php?id=<?= (int)$row['id'] ?>">รายละเอียด</a>
                                            <?php if ($canEdit): ?><a class="mt-btn mt-btn-outline mt-btn-sm" href="edit.php?id=<?= (int)$row['id'] ?>">แก้ไข</a><?php endif; ?>
                                            <?php if ($canManage): ?><a class="mt-btn mt-btn-teal mt-btn-sm" href="detail.php?id=<?= (int)$row['id'] ?>#manage">จัดการงาน</a><?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <div class="mt-footer-line"><span>แสดงสูงสุด 100 รายการล่าสุด</span><span>อัปเดตข้อมูลจากฐานข้อมูลแบบเรียลไทม์</span></div>
            </section>
        </div>
    <?php endif; ?>
</main>

<script>
(function () {
    const systemRadios = document.querySelectorAll('input[name="system_type"]');
    const technician = document.getElementById('technician_id');
    if (!technician || !systemRadios.length) return;

    const allOptions = Array.from(technician.options).map(o => ({
        value: o.value,
        text: o.textContent,
        specialty: o.dataset.specialty || ''
    }));

    function filterTechnicians() {
        const selectedSystem = document.querySelector('input[name="system_type"]:checked')?.value || '';
        const current = technician.value;
        const matches = allOptions.filter(o => !o.value || !selectedSystem || o.specialty === selectedSystem || o.specialty === 'ซ่อมบำรุง' || o.specialty === 'ทั่วไป');
        const usable = matches.filter(o => o.value);
        const source = usable.length ? matches : allOptions;
        technician.innerHTML = '';
        source.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.value;
            opt.textContent = o.text;
            opt.dataset.specialty = o.specialty;
            technician.appendChild(opt);
        });
        if (source.some(o => o.value === current)) technician.value = current;
    }

    systemRadios.forEach(r => r.addEventListener('change', filterTechnicians));
    document.getElementById('maintenanceForm')?.addEventListener('reset', () => setTimeout(filterTechnicians, 0));
})();
</script>
</body>
</html>
