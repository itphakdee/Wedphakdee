<?php
require_once __DIR__ . '/layout.php';
medical_require_permission('view');

$ready = medical_schema_ready();
$u = medical_current_user();
$canAll = medical_can_view_all();

$equipment = array();
$technicians = array();
if ($ready) {
    $rs = $conn->query("SELECT id,asset_code,display_name,equipment_name,category,brand,model,serial_no,department,location FROM medical_equipment WHERE status='active' ORDER BY equipment_name,asset_code");
    if ($rs) while ($r=$rs->fetch_assoc()) $equipment[]=$r;
    $rs = $conn->query("SELECT id,fullname,specialty,phone FROM medical_technicians WHERE status='active' ORDER BY fullname");
    if ($rs) while ($r=$rs->fetch_assoc()) $technicians[]=$r;
}

$scopeSql = $canAll ? '1=1' : 'user_id=' . (int)$u['id'];
$stats = array('total'=>0,'pending'=>0,'progress'=>0,'completed'=>0,'emergency'=>0);
if ($ready) {
    $rs = $conn->query("SELECT COUNT(*) total,
        SUM(status IN ('pending','assigned')) pending,
        SUM(status IN ('in_progress','waiting_parts')) progress,
        SUM(status='completed') completed,
        SUM(priority='emergency' AND status NOT IN ('completed','cancelled')) emergency
        FROM medical_repair_requests WHERE $scopeSql");
    if ($rs && ($s=$rs->fetch_assoc())) {
        foreach ($stats as $k=>$v) $stats[$k]=(int)$s[$k];
    }
}

medical_page_start('ศูนย์เครื่องมือแพทย์', 'ระบบแจ้งซ่อม ติดตามสถานะ และบริหารงานเครื่องมือแพทย์อย่างเป็นระบบ', 'dashboard');
?>
<?php if (!$ready): ?>
<div class="med-alert med-alert--warning"><strong>ระบบยังไม่ได้ติดตั้งฐานข้อมูล</strong> <?php if (medical_is_admin()): ?><a href="install.php">คลิกเพื่อติดตั้งระบบ</a><?php else: ?>กรุณาติดต่อผู้ดูแลระบบ<?php endif; ?></div>
<?php endif; ?>

<section class="med-stat-grid">
    <div class="med-stat"><span>งานทั้งหมด</span><strong><?= (int)$stats['total'] ?></strong><small>รายการในระบบ</small></div>
    <div class="med-stat"><span>รอตรวจสอบ / มอบหมาย</span><strong><?= (int)$stats['pending'] ?></strong><small>ต้องดำเนินการ</small></div>
    <div class="med-stat"><span>กำลังดำเนินการ</span><strong><?= (int)$stats['progress'] ?></strong><small>กำลังซ่อม / รออะไหล่</small></div>
    <div class="med-stat"><span>เสร็จสิ้น</span><strong><?= (int)$stats['completed'] ?></strong><small>ปิดงานเรียบร้อย</small></div>
    <div class="med-stat med-stat--danger"><span>ด่วนมากคงค้าง</span><strong><?= (int)$stats['emergency'] ?></strong><small>ควรเร่งดำเนินการ</small></div>
</section>

<section class="med-card" id="new-request">
    <div class="med-card-head">
        <div class="med-section-no">01</div>
        <div><h2>แบบฟอร์มแจ้งซ่อมเครื่องมือแพทย์</h2><p>ระบุเครื่องมือ อาการเสีย ตำแหน่งใช้งาน และระดับความเร่งด่วน</p></div>
        <span class="med-card-tag">MEDICAL REPAIR REQUEST</span>
    </div>
    <?php if ($ready): ?>
    <form class="med-form" method="post" action="save.php" id="medicalRequestForm">
        <input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>">
        <div class="med-form-grid">
            <div class="med-field"><label>ผู้แจ้ง <b>*</b></label><input name="requester_name" required maxlength="150" value="<?= medical_e($u['fullname']) ?>"></div>
            <div class="med-field"><label>แผนก / หน่วยงาน <b>*</b></label><input name="department" required maxlength="150" value="<?= medical_e($u['department']) ?>" placeholder="ระบุหน่วยงาน"></div>
            <div class="med-field"><label>เบอร์ติดต่อ</label><input name="contact_phone" maxlength="50" value="<?= medical_e($u['phone']) ?>" placeholder="เบอร์ภายใน / โทรศัพท์"></div>
            <div class="med-field"><label>สถานที่ใช้งานเครื่องมือ <b>*</b></label><input name="location" required maxlength="255" placeholder="เช่น ER, OPD, ห้องคลอด เตียง 3"></div>

            <div class="med-field med-full"><label>เลือกจากทะเบียนเครื่องมือแพทย์</label>
                <select name="equipment_id" id="equipment_id">
                    <option value="">-- ไม่เลือก / กรอกข้อมูลเครื่องมือเอง --</option>
                    <?php foreach($equipment as $e): ?>
                    <option value="<?= (int)$e['id'] ?>" data-asset="<?= medical_e($e['asset_code']) ?>" data-name="<?= medical_e($e['equipment_name']) ?>" data-category="<?= medical_e($e['category']) ?>" data-brand="<?= medical_e($e['brand']) ?>" data-model="<?= medical_e($e['model']) ?>" data-serial="<?= medical_e($e['serial_no']) ?>" data-location="<?= medical_e($e['location']) ?>">
                        <?= medical_e($e['asset_code'] ? $e['asset_code'].' · '.$e['equipment_name'] : $e['equipment_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="med-field"><label>เลขครุภัณฑ์ / Asset No.</label><input id="asset_code" name="asset_code" maxlength="100" placeholder="เช่น 6515-xxx"></div>
            <div class="med-field"><label>ชื่อเครื่องมือแพทย์ <b>*</b></label><input id="equipment_name" name="equipment_name" required maxlength="200" placeholder="เช่น เครื่องวัดสัญญาณชีพ"></div>
            <div class="med-field"><label>ประเภท / หมวด</label><input id="category" name="category" maxlength="120" placeholder="เช่น เครื่องตรวจวินิจฉัย"></div>
            <div class="med-field"><label>ยี่ห้อ / รุ่น</label><div class="med-inline"><input id="brand" name="brand" maxlength="100" placeholder="ยี่ห้อ"><input id="model" name="model" maxlength="100" placeholder="รุ่น"></div></div>
            <div class="med-field"><label>Serial Number</label><input id="serial_no" name="serial_no" maxlength="100"></div>
            <div class="med-field"><label>ประเภทปัญหา <b>*</b></label><select name="problem_type" required><option value="">-- เลือกประเภทปัญหา --</option><option value="malfunction">เครื่องทำงานผิดปกติ</option><option value="not_power">เปิดเครื่องไม่ติด</option><option value="alarm">มีสัญญาณเตือน / Error</option><option value="broken">ชำรุด / แตกหัก</option><option value="calibration">ต้องการสอบเทียบ</option><option value="preventive">บำรุงรักษาเชิงป้องกัน</option><option value="accessory">อุปกรณ์ประกอบมีปัญหา</option><option value="other">อื่น ๆ</option></select></div>
            <div class="med-field med-full"><label>รายละเอียดอาการ / ปัญหาที่พบ <b>*</b></label><textarea name="problem_detail" required maxlength="4000" placeholder="อธิบายอาการเสีย รหัส Error เหตุการณ์ก่อนเกิดปัญหา และข้อมูลที่ช่วยในการตรวจสอบ"></textarea></div>
            <div class="med-field med-full"><label>ระดับความเร่งด่วน</label><div class="med-priority"><label><input type="radio" name="priority" value="normal" checked><span><i></i>ปกติ<small>ไม่กระทบการบริการเร่งด่วน</small></span></label><label><input type="radio" name="priority" value="urgent"><span><i></i>ด่วน<small>กระทบการให้บริการบางส่วน</small></span></label><label><input type="radio" name="priority" value="emergency"><span><i></i>ด่วนมาก<small>กระทบผู้ป่วย / งานวิกฤต</small></span></label></div></div>
        </div>
        <div class="med-form-actions"><button class="med-btn med-btn-primary" type="submit">บันทึกแจ้งซ่อมและแจ้งเตือน LINE</button><button class="med-btn med-btn-light" type="reset">ล้างข้อมูล</button></div>
    </form>
    <?php endif; ?>
</section>

<?php medical_page_end(); ?>
