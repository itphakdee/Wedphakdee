<?php
require_once '../../config.php';
require_once __DIR__ . '/property_helpers.php';
property_require_login();

if (!property_table_exists($conn)) {
    header('Location: install.php');
    exit;
}

$flash = property_take_flash();
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$department = isset($_GET['department']) ? trim($_GET['department']) : '';
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
if (!in_array($perPage, array(10, 15, 20, 50), true)) {
    $perPage = 15;
}
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

$where = array('1=1');
if ($q !== '') {
    $safe = $conn->real_escape_string($q);
    $where[] = "(asset_no LIKE '%{$safe}%' OR property_name LIKE '%{$safe}%' OR serial_no LIKE '%{$safe}%' OR brand LIKE '%{$safe}%' OR model LIKE '%{$safe}%' OR department LIKE '%{$safe}%' OR location LIKE '%{$safe}%')";
}
if ($status !== '') {
    $safe = $conn->real_escape_string($status);
    $where[] = "status='{$safe}'";
}
if ($type !== '') {
    $safe = $conn->real_escape_string($type);
    $where[] = "property_type='{$safe}'";
}
if ($department !== '') {
    $safe = $conn->real_escape_string($department);
    $where[] = "department='{$safe}'";
}
$whereSql = implode(' AND ', $where);

$totalRecords = (int)property_query_value($conn, "SELECT COUNT(*) AS c FROM properties WHERE {$whereSql}", 'c', 0);
$totalPages = max(1, (int)ceil($totalRecords / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;

$listSql = "SELECT * FROM properties WHERE {$whereSql} ORDER BY id DESC LIMIT {$offset}, {$perPage}";
$result = $conn->query($listSql);

$stats = array(
    'total' => (int)property_query_value($conn, "SELECT COUNT(*) AS c FROM properties", 'c', 0),
    'value' => (float)property_query_value($conn, "SELECT COALESCE(SUM(price),0) AS v FROM properties", 'v', 0),
    'active' => (int)property_query_value($conn, "SELECT COUNT(*) AS c FROM properties WHERE status='ใช้งาน'", 'c', 0),
    'repair' => (int)property_query_value($conn, "SELECT COUNT(*) AS c FROM properties WHERE status IN ('ชำรุด','ส่งซ่อม')", 'c', 0),
    'retired' => (int)property_query_value($conn, "SELECT COUNT(*) AS c FROM properties WHERE status='จำหน่าย'", 'c', 0),
    'avg' => (float)property_query_value($conn, "SELECT COALESCE(AVG(NULLIF(price,0)),0) AS v FROM properties", 'v', 0)
);

$typeOptions = array();
$r = $conn->query("SELECT property_type, COUNT(*) AS total FROM properties WHERE property_type IS NOT NULL AND property_type<>'' GROUP BY property_type ORDER BY total DESC, property_type ASC");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $typeOptions[] = $row;
    }
}
$departmentOptions = array();
$r = $conn->query("SELECT department, COUNT(*) AS total FROM properties WHERE department IS NOT NULL AND department<>'' GROUP BY department ORDER BY total DESC, department ASC");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $departmentOptions[] = $row;
    }
}
$topTypes = array_slice($typeOptions, 0, 5);

function property_page_url($targetPage)
{
    $params = $_GET;
    $params['page'] = $targetPage;
    return '?' . http_build_query($params);
}

$activePage = 'propertywork';
$basePath = '../../';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ทะเบียนครุภัณฑ์ | โรงพยาบาลภักดีชุมพล</title>
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/property.css?v=20260831" rel="stylesheet">
</head>
<body class="property-body">
<?php if (is_file('../../components/sidebar.php')) { require '../../components/sidebar.php'; } ?>
<main class="main-content property-main">
    <div class="property-shell">
        <section class="property-hero">
            <div class="hero-copy">
                <div class="hero-kicker">PHAKDEE CHUMPHON HOSPITAL · ASSET MANAGEMENT</div>
                <h1>ทะเบียนครุภัณฑ์</h1>
                <p>ระบบบริหารครุภัณฑ์และทรัพย์สิน โรงพยาบาลภักดีชุมพล สำหรับตรวจสอบมูลค่า สถานะ หน่วยงาน และประวัติข้อมูลจากฐานข้อมูลกลาง</p>
                <div class="hero-actions">
                    <a class="btn-property btn-primary-property" href="add.php"><span>＋</span> เพิ่มครุภัณฑ์</a>
                    <a class="btn-property btn-light-property" href="export_csv.php"><span>⇩</span> ส่งออก CSV</a>
                    <button class="btn-property btn-light-property" type="button" onclick="window.print()"><span>⎙</span> พิมพ์รายงาน</button>
                </div>
            </div>
            <div class="hero-emblem" aria-hidden="true">
                <div class="hospital-cross">+</div>
                <div><strong>PDC</strong><small>PROPERTY</small></div>
            </div>
        </section>

        <?php if ($flash): ?>
            <div class="property-alert <?php echo $flash['type'] === 'success' ? 'alert-success-property' : 'alert-danger-property'; ?>">
                <span><?php echo $flash['type'] === 'success' ? '✓' : '!'; ?></span>
                <?php echo property_e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <section class="metric-grid" aria-label="สรุปครุภัณฑ์">
            <article class="metric-card metric-primary">
                <div class="metric-icon">▦</div>
                <div><span>ครุภัณฑ์ทั้งหมด</span><strong><?php echo number_format($stats['total']); ?></strong><small>รายการในฐานข้อมูล</small></div>
            </article>
            <article class="metric-card metric-value">
                <div class="metric-icon">฿</div>
                <div><span>มูลค่ารวมทั้งหมด</span><strong><?php echo property_currency($stats['value']); ?></strong><small>บาท</small></div>
            </article>
            <article class="metric-card metric-success">
                <div class="metric-icon">✓</div>
                <div><span>พร้อมใช้งาน</span><strong><?php echo number_format($stats['active']); ?></strong><small>รายการ</small></div>
            </article>
            <article class="metric-card metric-warning">
                <div class="metric-icon">⌁</div>
                <div><span>ชำรุด / ส่งซ่อม</span><strong><?php echo number_format($stats['repair']); ?></strong><small>รายการที่ต้องติดตาม</small></div>
            </article>
        </section>

        <section class="dashboard-grid">
            <div class="panel-card asset-overview-card">
                <div class="panel-header">
                    <div><span class="eyebrow">ASSET OVERVIEW</span><h2>ภาพรวมทะเบียนครุภัณฑ์</h2></div>
                    <span class="panel-badge">อัปเดตจากฐานข้อมูล</span>
                </div>
                <div class="overview-row">
                    <div class="overview-value"><span>ราคาเฉลี่ยต่อรายการ</span><strong>฿<?php echo property_currency($stats['avg']); ?></strong></div>
                    <div class="overview-value"><span>จำหน่ายแล้ว</span><strong><?php echo number_format($stats['retired']); ?> รายการ</strong></div>
                    <div class="overview-value"><span>รายการที่ค้นพบ</span><strong><?php echo number_format($totalRecords); ?> รายการ</strong></div>
                </div>
                <div class="type-bars">
                    <?php if (!empty($topTypes)): ?>
                        <?php foreach ($topTypes as $item): ?>
                            <?php $pct = $stats['total'] > 0 ? min(100, round(((int)$item['total'] / $stats['total']) * 100)) : 0; ?>
                            <div class="type-bar-item">
                                <div class="type-bar-label"><span><?php echo property_e($item['property_type']); ?></span><strong><?php echo number_format($item['total']); ?></strong></div>
                                <div class="type-track"><i style="width:<?php echo $pct; ?>%"></i></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-mini">ยังไม่มีข้อมูลประเภทครุภัณฑ์</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel-card quick-card">
                <div class="panel-header compact"><div><span class="eyebrow">QUICK ACTION</span><h2>เมนูด่วน</h2></div></div>
                <a href="add.php" class="quick-link"><span class="quick-icon">＋</span><div><strong>เพิ่มครุภัณฑ์ใหม่</strong><small>ลงทะเบียนเลขครุภัณฑ์และราคา</small></div><b>›</b></a>
                <a href="?status=ชำรุด" class="quick-link"><span class="quick-icon">!</span><div><strong>รายการชำรุด</strong><small>ตรวจสอบรายการที่ต้องดำเนินการ</small></div><b>›</b></a>
                <a href="?status=ส่งซ่อม" class="quick-link"><span class="quick-icon">⌁</span><div><strong>รายการส่งซ่อม</strong><small>ติดตามครุภัณฑ์ระหว่างซ่อม</small></div><b>›</b></a>
            </div>
        </section>

        <section class="panel-card registry-card">
            <div class="registry-head">
                <div>
                    <span class="eyebrow">PROPERTY REGISTRY</span>
                    <h2>ทะเบียนครุภัณฑ์ทั้งหมด</h2>
                    <p>แสดงข้อมูลจากฐานข้อมูลพร้อมราคาครุภัณฑ์ และสามารถค้นหา กรอง ดูรายละเอียด แก้ไข หรือลบรายการได้</p>
                </div>
                <div class="result-count"><strong><?php echo number_format($totalRecords); ?></strong><span>รายการ</span></div>
            </div>

            <form class="filter-grid" method="get" action="index.php">
                <div class="filter-field filter-search">
                    <label for="q">ค้นหาครุภัณฑ์</label>
                    <div class="input-with-icon"><span>⌕</span><input id="q" name="q" value="<?php echo property_e($q); ?>" placeholder="เลขครุภัณฑ์ / ชื่อ / Serial / แผนก / สถานที่"></div>
                </div>
                <div class="filter-field">
                    <label for="status">สถานะ</label>
                    <select id="status" name="status">
                        <option value="">ทุกสถานะ</option>
                        <?php foreach (array('ใช้งาน','ชำรุด','ส่งซ่อม','จำหน่าย') as $s): ?>
                            <option value="<?php echo property_e($s); ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo property_e($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="type">ประเภท</label>
                    <select id="type" name="type">
                        <option value="">ทุกประเภท</option>
                        <?php foreach ($typeOptions as $item): ?>
                            <option value="<?php echo property_e($item['property_type']); ?>" <?php echo $type === $item['property_type'] ? 'selected' : ''; ?>><?php echo property_e($item['property_type']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="department">หน่วยงาน</label>
                    <select id="department" name="department">
                        <option value="">ทุกหน่วยงาน</option>
                        <?php foreach ($departmentOptions as $item): ?>
                            <option value="<?php echo property_e($item['department']); ?>" <?php echo $department === $item['department'] ? 'selected' : ''; ?>><?php echo property_e($item['department']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn-property btn-primary-property" type="submit">ค้นหา</button>
                    <a class="btn-property btn-light-property" href="index.php">ล้างตัวกรอง</a>
                </div>
            </form>

            <div class="table-toolbar">
                <div>หน้า <strong><?php echo number_format($currentPage); ?></strong> จาก <strong><?php echo number_format($totalPages); ?></strong></div>
                <form method="get" class="per-page-form">
                    <?php foreach ($_GET as $key => $value): ?>
                        <?php if ($key !== 'per_page' && $key !== 'page'): ?><input type="hidden" name="<?php echo property_e($key); ?>" value="<?php echo property_e($value); ?>"><?php endif; ?>
                    <?php endforeach; ?>
                    <label>แสดง</label>
                    <select name="per_page" onchange="this.form.submit()">
                        <?php foreach (array(10,15,20,50) as $n): ?><option value="<?php echo $n; ?>" <?php echo $perPage === $n ? 'selected' : ''; ?>><?php echo $n; ?></option><?php endforeach; ?>
                    </select>
                    <span>รายการ/หน้า</span>
                </form>
            </div>

            <div class="property-table-wrap">
                <table class="property-table">
                    <thead>
                        <tr>
                            <th>#</th><th>เลขครุภัณฑ์</th><th>รายละเอียดครุภัณฑ์</th><th>ประเภท</th><th>หน่วยงาน / สถานที่</th><th class="text-end">ราคา (บาท)</th><th>สถานะ</th><th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $rowNo = $offset + 1; while ($row = $result->fetch_assoc()): $meta = property_status_meta($row['status']); ?>
                            <tr>
                                <td class="row-number"><?php echo number_format($rowNo++); ?></td>
                                <td><a class="asset-code" href="detail.php?id=<?php echo (int)$row['id']; ?>"><?php echo property_e($row['asset_no']); ?></a><small class="cell-muted">ปีงบ <?php echo property_e($row['budget_year'] ? $row['budget_year'] : '-'); ?></small></td>
                                <td><div class="asset-name-cell">
                                    <?php if (!empty($row['image'])): ?><img src="../../uploads/property/<?php echo property_e(basename($row['image'])); ?>" alt="ครุภัณฑ์"><?php else: ?><span class="asset-placeholder">▦</span><?php endif; ?>
                                    <div><strong><?php echo property_e($row['property_name']); ?></strong><small><?php echo property_e(trim($row['brand'] . ' ' . $row['model']) ?: 'ไม่ระบุยี่ห้อ/รุ่น'); ?></small></div>
                                </div></td>
                                <td><?php echo property_e($row['property_type'] ?: '-'); ?><small class="cell-muted"><?php echo property_e($row['category'] ?: 'ไม่ระบุหมวด'); ?></small></td>
                                <td><strong class="cell-strong"><?php echo property_e($row['department'] ?: '-'); ?></strong><small class="cell-muted">⌖ <?php echo property_e($row['location'] ?: '-'); ?></small></td>
                                <td class="price-cell">฿<?php echo property_currency($row['price']); ?></td>
                                <td><span class="status-pill <?php echo property_e($meta['class']); ?>"><i><?php echo $meta['icon']; ?></i><?php echo property_e($meta['label']); ?></span></td>
                                <td><div class="action-group">
                                    <a class="icon-btn view" href="detail.php?id=<?php echo (int)$row['id']; ?>" title="รายละเอียด">ดู</a>
                                    <a class="icon-btn edit" href="edit.php?id=<?php echo (int)$row['id']; ?>" title="แก้ไข">แก้</a>
                                    <form action="delete.php" method="post" class="delete-form" data-asset="<?php echo property_e($row['asset_no']); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo property_e(property_csrf_token()); ?>"><input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>"><button class="icon-btn delete" type="submit" title="ลบ">ลบ</button>
                                    </form>
                                </div></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8"><div class="empty-state"><span>⌕</span><strong>ไม่พบข้อมูลครุภัณฑ์</strong><p>ลองเปลี่ยนคำค้นหาหรือตัวกรอง แล้วค้นหาใหม่อีกครั้ง</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="property-pagination" aria-label="แบ่งหน้า">
                    <a class="page-nav <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>" href="<?php echo $currentPage > 1 ? property_e(property_page_url($currentPage - 1)) : '#'; ?>">‹ ก่อนหน้า</a>
                    <div class="page-numbers">
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        if ($startPage > 1): ?><a href="<?php echo property_e(property_page_url(1)); ?>">1</a><?php if ($startPage > 2): ?><span>…</span><?php endif; ?><?php endif;
                        for ($p = $startPage; $p <= $endPage; $p++): ?><a class="<?php echo $p === $currentPage ? 'active' : ''; ?>" href="<?php echo property_e(property_page_url($p)); ?>"><?php echo $p; ?></a><?php endfor;
                        if ($endPage < $totalPages): ?><?php if ($endPage < $totalPages - 1): ?><span>…</span><?php endif; ?><a href="<?php echo property_e(property_page_url($totalPages)); ?>"><?php echo $totalPages; ?></a><?php endif; ?>
                    </div>
                    <a class="page-nav <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>" href="<?php echo $currentPage < $totalPages ? property_e(property_page_url($currentPage + 1)) : '#'; ?>">ถัดไป ›</a>
                </nav>
            <?php endif; ?>
        </section>

        <footer class="property-footer">งานทรัพย์สิน · โรงพยาบาลภักดีชุมพล <span>ข้อมูลทะเบียนครุภัณฑ์จากฐานข้อมูลกลาง</span></footer>
    </div>
</main>
<script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/property.js?v=20260831"></script>
</body>
</html>
