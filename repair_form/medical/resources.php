<?php
require_once __DIR__ . '/layout.php';
medical_require_permission('manage');

if (!medical_schema_ready()) {
    header('Location:install.php');
    exit;
}

/*
 * ทะเบียนเครื่องมือแพทย์แบ่งหน้าละ 10 รายการ
 * ใช้ชื่อ parameter เฉพาะ equipment_page เพื่อไม่ชนกับตัวแปร page ของระบบอื่น
 */
$equipmentPerPage = 10;
$equipmentPage = isset($_GET['equipment_page']) ? (int)$_GET['equipment_page'] : 1;
if ($equipmentPage < 1) {
    $equipmentPage = 1;
}

$totalEquipment = 0;
$countResult = $conn->query("SELECT COUNT(*) AS total FROM medical_equipment");
if ($countResult && ($countRow = $countResult->fetch_assoc())) {
    $totalEquipment = (int)$countRow['total'];
}

$totalEquipmentPages = max(1, (int)ceil($totalEquipment / $equipmentPerPage));
if ($equipmentPage > $totalEquipmentPages) {
    $equipmentPage = $totalEquipmentPages;
}

$equipmentOffset = ($equipmentPage - 1) * $equipmentPerPage;
$equipment = array();
$equipmentSql = "SELECT * FROM medical_equipment
                 ORDER BY status='active' DESC, equipment_name, asset_code
                 LIMIT " . (int)$equipmentPerPage . " OFFSET " . (int)$equipmentOffset;
$rs = $conn->query($equipmentSql);
if ($rs) {
    while ($r = $rs->fetch_assoc()) {
        $equipment[] = $r;
    }
}

$techs = array();
$rs = $conn->query("SELECT * FROM medical_technicians ORDER BY status='active' DESC, fullname");
if ($rs) {
    while ($r = $rs->fetch_assoc()) {
        $techs[] = $r;
    }
}

function medical_resource_page_url($page)
{
    $page = max(1, (int)$page);
    return 'resources.php?equipment_page=' . $page . '#equipment-register';
}

medical_page_start(
    'ทะเบียนเครื่องมือแพทย์และผู้รับผิดชอบ',
    'จัดการทะเบียนเครื่องมือแพทย์และรายชื่อช่างสำหรับมอบหมายงาน',
    'resources'
);
?>

<div class="med-resource-grid">
    <section class="med-card">
        <div class="med-card-head">
            <div class="med-section-no">A</div>
            <div>
                <h2>เพิ่มเครื่องมือแพทย์</h2>
                <p>ข้อมูลนี้จะปรากฏใน Dropdown หน้าแจ้งซ่อม</p>
            </div>
        </div>
        <form class="med-form" method="post" action="resource_save.php">
            <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
            <input type="hidden" name="action" value="equipment_add">
            <div class="med-form-grid">
                <div class="med-field"><label>เลขครุภัณฑ์</label><input name="asset_code"></div>
                <div class="med-field"><label>ชื่อเครื่องมือ <b>*</b></label><input name="equipment_name" required></div>
                <div class="med-field"><label>วันที่เข้ารับ</label><input type="date" name="received_date"></div>
                <div class="med-field"><label>ราคา (บาท)</label><input type="number" name="price" min="0" step="0.01" placeholder="0.00"></div>
                <div class="med-field"><label>ประเภท</label><input name="category"></div>
                <div class="med-field"><label>ยี่ห้อ</label><input name="brand"></div>
                <div class="med-field"><label>รุ่น</label><input name="model"></div>
                <div class="med-field"><label>Serial Number</label><input name="serial_no"></div>
                <div class="med-field"><label>หน่วยงาน</label><input name="department"></div>
                <div class="med-field"><label>สถานที่</label><input name="location"></div>
            </div>
            <button class="med-btn med-btn-primary">เพิ่มเครื่องมือ</button>
        </form>
    </section>

    <section class="med-card">
        <div class="med-card-head">
            <div class="med-section-no">B</div>
            <div>
                <h2>เพิ่มช่าง / ผู้รับผิดชอบ</h2>
                <p>ใช้สำหรับมอบหมายงานซ่อม</p>
            </div>
        </div>
        <form class="med-form" method="post" action="resource_save.php">
            <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
            <input type="hidden" name="action" value="technician_add">
            <div class="med-form-grid">
                <div class="med-field"><label>ชื่อ - สกุล <b>*</b></label><input name="fullname" required></div>
                <div class="med-field"><label>ความเชี่ยวชาญ</label><input name="specialty" placeholder="เช่น เครื่องช่วยหายใจ / ไฟฟ้าการแพทย์"></div>
                <div class="med-field"><label>โทรศัพท์</label><input name="phone"></div>
                <div class="med-field"><label>อีเมล</label><input name="email"></div>
            </div>
            <button class="med-btn med-btn-primary">เพิ่มผู้รับผิดชอบ</button>
        </form>
    </section>
</div>

<section class="med-card" id="equipment-register">
    <div class="med-card-head med-card-head--register">
        <div class="med-section-no">01</div>
        <div>
            <h2>ทะเบียนเครื่องมือแพทย์</h2>
            <p>
                ทั้งหมด <?= number_format($totalEquipment) ?> รายการ
                <?php if ($totalEquipment > 0): ?>
                    · หน้า <?= number_format($equipmentPage) ?> จาก <?= number_format($totalEquipmentPages) ?>
                    · แสดงครั้งละ <?= $equipmentPerPage ?> รายการ
                <?php endif; ?>
            </p>
        </div>
        <?php if ($totalEquipment > 0): ?>
            <div class="med-register-range">
                รายการ <?= number_format($equipmentOffset + 1) ?>–<?= number_format(min($equipmentOffset + $equipmentPerPage, $totalEquipment)) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="med-table-wrap med-table-wrap--responsive">
        <table class="med-table med-table--equipment">
            <thead>
                <tr>
                    <th class="med-col-seq">ลำดับ</th>
                    <th>เลขครุภัณฑ์</th>
                    <th>เครื่องมือ</th>
                    <th>วันที่เข้ารับ</th>
                    <th>ราคา</th>
                    <th>ประเภท</th>
                    <th>ยี่ห้อ / รุ่น</th>
                    <th>หน่วยงาน / สถานที่</th>
                    <th>สถานะ</th>
                    <th>คำสั่ง</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$equipment): ?>
                <tr><td class="med-empty" colspan="10">ยังไม่มีข้อมูลเครื่องมือแพทย์</td></tr>
            <?php else: ?>
                <?php foreach ($equipment as $index => $r): ?>
                    <?php $sequence = $equipmentOffset + $index + 1; ?>
                    <tr>
                        <td class="med-col-seq" data-label="ลำดับ"><strong><?= number_format($sequence) ?></strong></td>
                        <td data-label="เลขครุภัณฑ์"><?= medical_e($r['asset_code'] ?: '-') ?></td>
                        <td data-label="เครื่องมือ">
                            <strong><?= medical_e($r['equipment_name']) ?></strong>
                            <small><?= medical_e($r['serial_no'] ?: '') ?></small>
                        </td>
                        <td data-label="วันที่เข้ารับ"><?= !empty($r['received_date']) ? medical_e(date('d/m/Y', strtotime($r['received_date']))) : '-' ?></td>
                        <td data-label="ราคา"><?= $r['price'] !== null && $r['price'] !== '' ? '฿' . number_format((float)$r['price'], 2) : '-' ?></td>
                        <td data-label="ประเภท"><?= medical_e($r['category'] ?: '-') ?></td>
                        <td data-label="ยี่ห้อ / รุ่น"><?= medical_e(trim($r['brand'] . ' ' . $r['model']) ?: '-') ?></td>
                        <td data-label="หน่วยงาน / สถานที่">
                            <?= medical_e($r['department'] ?: '-') ?>
                            <small><?= medical_e($r['location'] ?: '') ?></small>
                        </td>
                        <td data-label="สถานะ">
                            <span class="med-badge <?= $r['status'] === 'active' ? 'med-status-completed' : 'med-status-cancelled' ?>">
                                <?= $r['status'] === 'active' ? 'ใช้งาน' : 'ปิดใช้งาน' ?>
                            </span>
                        </td>
                        <td data-label="คำสั่ง">
                            <div class="med-row-actions">
                                <form method="post" action="resource_save.php" class="med-inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="equipment_toggle">
                                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="return_page" value="<?= (int)$equipmentPage ?>">
                                    <button class="med-link-btn"><?= $r['status'] === 'active' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?></button>
                                </form>

                                <a class="med-link-btn edit" href="resource_edit.php?type=equipment&amp;id=<?= (int)$r['id'] ?>&amp;return_page=<?= (int)$equipmentPage ?>">แก้ไข</a>

                                <?php if (medical_is_admin()): ?>
                                    <form method="post" action="resource_save.php" class="med-inline-form" onsubmit="return confirm('ลบเครื่องมือนี้?')">
                                        <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
                                        <input type="hidden" name="action" value="equipment_delete">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="return_page" value="<?= (int)$equipmentPage ?>">
                                        <button class="med-link-btn danger">ลบ</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalEquipmentPages > 1): ?>
        <nav class="med-pagination med-pagination--resources" aria-label="หน้าทะเบียนเครื่องมือแพทย์">
            <?php if ($equipmentPage > 1): ?>
                <a class="med-page-arrow" href="<?= medical_e(medical_resource_page_url($equipmentPage - 1)) ?>">‹ ก่อนหน้า</a>
            <?php else: ?>
                <span class="med-page-arrow disabled">‹ ก่อนหน้า</span>
            <?php endif; ?>

            <?php
            $startPage = max(1, $equipmentPage - 2);
            $endPage = min($totalEquipmentPages, $equipmentPage + 2);

            if ($startPage > 1):
            ?>
                <a href="<?= medical_e(medical_resource_page_url(1)) ?>">1</a>
                <?php if ($startPage > 2): ?><span class="med-page-dots">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                <a class="<?= $p === $equipmentPage ? 'active' : '' ?>" href="<?= medical_e(medical_resource_page_url($p)) ?>"<?= $p === $equipmentPage ? ' aria-current="page"' : '' ?>><?= $p ?></a>
            <?php endfor; ?>

            <?php if ($endPage < $totalEquipmentPages): ?>
                <?php if ($endPage < $totalEquipmentPages - 1): ?><span class="med-page-dots">…</span><?php endif; ?>
                <a href="<?= medical_e(medical_resource_page_url($totalEquipmentPages)) ?>"><?= $totalEquipmentPages ?></a>
            <?php endif; ?>

            <?php if ($equipmentPage < $totalEquipmentPages): ?>
                <a class="med-page-arrow" href="<?= medical_e(medical_resource_page_url($equipmentPage + 1)) ?>">ถัดไป ›</a>
            <?php else: ?>
                <span class="med-page-arrow disabled">ถัดไป ›</span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>

<section class="med-card" id="technician-register">
    <div class="med-card-head">
        <div class="med-section-no">02</div>
        <div>
            <h2>รายชื่อช่าง / ผู้รับผิดชอบ</h2>
            <p><?= count($techs) ?> รายการ</p>
        </div>
    </div>
    <div class="med-table-wrap">
        <table class="med-table">
            <thead>
                <tr><th>ชื่อ - สกุล</th><th>ความเชี่ยวชาญ</th><th>โทรศัพท์</th><th>อีเมล</th><th>สถานะ</th><th>คำสั่ง</th></tr>
            </thead>
            <tbody>
            <?php foreach ($techs as $r): ?>
                <tr>
                    <td><strong><?= medical_e($r['fullname']) ?></strong></td>
                    <td><?= medical_e($r['specialty'] ?: '-') ?></td>
                    <td><?= medical_e($r['phone'] ?: '-') ?></td>
                    <td><?= medical_e($r['email'] ?: '-') ?></td>
                    <td><?= $r['status'] === 'active' ? 'ใช้งาน' : 'ปิดใช้งาน' ?></td>
                    <td>
                        <form method="post" action="resource_save.php" class="med-inline-form">
                            <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
                            <input type="hidden" name="action" value="technician_toggle">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="med-link-btn"><?= $r['status'] === 'active' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?></button>
                        </form>
                        <a class="med-link-btn edit" href="resource_edit.php?type=technician&amp;id=<?= (int)$r['id'] ?>">แก้ไข</a>
                        <?php if (medical_is_admin()): ?>
                            <form method="post" action="resource_save.php" class="med-inline-form" onsubmit="return confirm('ลบรายชื่อนี้?')">
                                <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
                                <input type="hidden" name="action" value="technician_delete">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button class="med-link-btn danger">ลบ</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php medical_page_end(); ?>
