<?php
require_once __DIR__ . '/layout.php';
medical_require_permission('manage');
if (!medical_schema_ready()) {
    header('Location:install.php');
    exit;
}

$type = isset($_GET['type']) ? trim((string)$_GET['type']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$returnPage = isset($_GET['return_page']) ? max(1, (int)$_GET['return_page']) : 1;
$returnUrl = 'resources.php?equipment_page=' . $returnPage . '#equipment-register';

if ($id <= 0 || !in_array($type, array('equipment', 'technician'), true)) {
    medical_flash('error', 'ไม่พบข้อมูลที่ต้องการแก้ไข');
    header('Location:resources.php');
    exit;
}

$row = null;
if ($type === 'equipment') {
    $stmt = $conn->prepare("SELECT * FROM medical_equipment WHERE id=? LIMIT 1");
} else {
    $stmt = $conn->prepare("SELECT * FROM medical_technicians WHERE id=? LIMIT 1");
}

if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
}

if (!$row) {
    medical_flash('error', 'ไม่พบข้อมูลที่ต้องการแก้ไข');
    header('Location:resources.php');
    exit;
}

if ($type === 'equipment') {
    medical_page_start('แก้ไขเครื่องมือแพทย์', 'ปรับปรุงข้อมูลทะเบียนเครื่องมือแพทย์และบันทึกลงฐานข้อมูล', 'resources');
    ?>
    <section class="med-card">
        <div class="med-card-head">
            <div class="med-section-no">E</div>
            <div>
                <h2>แก้ไขข้อมูลเครื่องมือแพทย์</h2>
                <p><?= medical_e($row['equipment_name']) ?></p>
            </div>
        </div>
        <form class="med-form" method="post" action="resource_save.php">
            <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
            <input type="hidden" name="action" value="equipment_update">
            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
            <input type="hidden" name="return_page" value="<?= (int)$returnPage ?>">

            <div class="med-form-grid">
                <div class="med-field">
                    <label>เลขครุภัณฑ์</label>
                    <input name="asset_code" value="<?= medical_e($row['asset_code']) ?>">
                </div>
                <div class="med-field">
                    <label>ชื่อเครื่องมือ <b>*</b></label>
                    <input name="equipment_name" value="<?= medical_e($row['equipment_name']) ?>" required>
                </div>
                <div class="med-field">
                    <label>วันที่เข้ารับ</label>
                    <input type="date" name="received_date" value="<?= medical_e($row['received_date']) ?>">
                </div>
                <div class="med-field">
                    <label>ราคา (บาท)</label>
                    <input type="number" name="price" min="0" step="0.01" value="<?= $row['price'] !== null && $row['price'] !== '' ? medical_e(number_format((float)$row['price'], 2, '.', '')) : '' ?>">
                </div>
                <div class="med-field">
                    <label>ประเภท</label>
                    <input name="category" value="<?= medical_e($row['category']) ?>">
                </div>
                <div class="med-field">
                    <label>ยี่ห้อ</label>
                    <input name="brand" value="<?= medical_e($row['brand']) ?>">
                </div>
                <div class="med-field">
                    <label>รุ่น</label>
                    <input name="model" value="<?= medical_e($row['model']) ?>">
                </div>
                <div class="med-field">
                    <label>Serial Number</label>
                    <input name="serial_no" value="<?= medical_e($row['serial_no']) ?>">
                </div>
                <div class="med-field">
                    <label>หน่วยงาน</label>
                    <input name="department" value="<?= medical_e($row['department']) ?>">
                </div>
                <div class="med-field">
                    <label>สถานที่</label>
                    <input name="location" value="<?= medical_e($row['location']) ?>">
                </div>
            </div>

            <div class="med-form-actions">
                <button class="med-btn med-btn-primary" type="submit">บันทึกการแก้ไข</button>
                <a class="med-btn med-btn-light" href="<?= medical_e($returnUrl) ?>">ยกเลิก / กลับ</a>
            </div>
        </form>
    </section>
    <?php
} else {
    medical_page_start('แก้ไขผู้รับผิดชอบ', 'ปรับปรุงข้อมูลช่างหรือผู้รับผิดชอบเครื่องมือแพทย์', 'resources');
    ?>
    <section class="med-card">
        <div class="med-card-head">
            <div class="med-section-no">T</div>
            <div>
                <h2>แก้ไขช่าง / ผู้รับผิดชอบ</h2>
                <p><?= medical_e($row['fullname']) ?></p>
            </div>
        </div>
        <form class="med-form" method="post" action="resource_save.php">
            <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
            <input type="hidden" name="action" value="technician_update">
            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

            <div class="med-form-grid">
                <div class="med-field">
                    <label>ชื่อ - สกุล <b>*</b></label>
                    <input name="fullname" value="<?= medical_e($row['fullname']) ?>" required>
                </div>
                <div class="med-field">
                    <label>ความเชี่ยวชาญ</label>
                    <input name="specialty" value="<?= medical_e($row['specialty']) ?>">
                </div>
                <div class="med-field">
                    <label>โทรศัพท์</label>
                    <input name="phone" value="<?= medical_e($row['phone']) ?>">
                </div>
                <div class="med-field">
                    <label>อีเมล</label>
                    <input type="email" name="email" value="<?= medical_e($row['email']) ?>">
                </div>
            </div>

            <div class="med-form-actions">
                <button class="med-btn med-btn-primary" type="submit">บันทึกการแก้ไข</button>
                <a class="med-btn med-btn-light" href="resources.php">ยกเลิก / กลับ</a>
            </div>
        </form>
    </section>
    <?php
}

medical_page_end();
