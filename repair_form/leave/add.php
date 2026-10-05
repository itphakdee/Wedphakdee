<?php
require_once __DIR__ . '/layout.php';
if (!leave_table_exists('leave_applications')) leave_redirect(leave_is_admin() ? 'install.php' : 'index.php');
$user = leave_current_user();
$uid = (int)$user['id'];
$fy = leave_fiscal_year();
$leaveNo = leave_generate_no($fy);
$department = leave_user_department_info($user);
$supervisor = leave_supervisor_for_user($user);
$types = array();
$res = $conn->query("SELECT * FROM leave_types_master WHERE is_active=1 ORDER BY sort_order,id");
while ($res && $r = $res->fetch_assoc()) $types[] = $r;
$coworkers = array();
$dept = trim((string)$department['name']);
if ($dept !== '') {
  $stmt = $conn->prepare("SELECT id,fullname,department FROM users WHERE status='active' AND id<>? ORDER BY (department=? ) DESC, fullname");
  $stmt->bind_param('is', $uid, $dept);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $coworkers[] = $r;
  $stmt->close();
} else {
  $stmt = $conn->prepare("SELECT id,fullname,department FROM users WHERE status='active' AND id<>? ORDER BY fullname");
  $stmt->bind_param('i', $uid);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $coworkers[] = $r;
  $stmt->close();
}
leave_page_start('ยื่นใบลางาน', 'กรอกข้อมูลการลา เลือกผู้รับมอบงาน และส่งต่อหัวหน้างานเพื่อพิจารณา', 'add');
?>
<section class="leave-card">
  <div class="leave-card__head">
    <div>
      <h2>แบบคำขอลางาน</h2>
      <p>เลขที่ <?= leave_e($leaveNo) ?> · ปีงบประมาณ <?= $fy ?></p>
    </div><a class="leave-btn leave-btn--outline no-print" href="index.php">← กลับหน้ารายการ</a>
  </div>
  <form class="leave-form" action="save.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= leave_e(leave_csrf_token()) ?>"><input type="hidden" name="leave_no" value="<?= leave_e($leaveNo) ?>">
    <div class="leave-form-section">
      <div class="leave-form-section__title"><span class="leave-step">01</span>
        <div>
          <h3>ข้อมูลผู้ลา</h3>
          <p>ข้อมูลจากบัญชีผู้ใช้งานและทะเบียนบุคลากร</p>
        </div>
      </div>
      <div class="leave-form-grid">
        <div class="leave-field leave-col-4"><label>ชื่อ - นามสกุล</label><input class="leave-input" value="<?= leave_e($user['fullname']) ?>" readonly></div>
        <div class="leave-field leave-col-4"><label>รหัสบุคลากร</label><input class="leave-input" value="<?= leave_e($user['employee_code'] ?: '-') ?>" readonly></div>
        <div class="leave-field leave-col-4"><label>ตำแหน่ง</label><input class="leave-input" value="<?= leave_e($user['position_name'] ?: '-') ?>" readonly></div>
        <div class="leave-field leave-col-6"><label>หน่วยงาน / แผนก</label><input class="leave-input" value="<?= leave_e($department['name'] !== '' ? $department['name'] : 'ยังไม่ได้กำหนดแผนก') ?>" readonly>
          <div class="leave-help">ระบบตรวจจากข้อมูลบุคลากร/บัญชีผู้ใช้ แล้วจับคู่กับฐานข้อมูลหน่วยงานอัตโนมัติ</div>
        </div>
        <div class="leave-field leave-col-6"><label>เบอร์ติดต่อระหว่างลา</label><input class="leave-input" name="contact_phone" value="<?= leave_e($user['phone'] ?: '') ?>" placeholder="ระบุเบอร์โทรศัพท์ที่ติดต่อได้"></div>
      </div>
    </div>

    <div class="leave-form-section">
      <div class="leave-form-section__title"><span class="leave-step">02</span>
        <div>
          <h3>รายละเอียดการลา</h3>
          <p>เลือกประเภท ช่วงวันที่ และระบุเหตุผล</p>
        </div>
      </div>
      <div class="leave-form-grid">
        <div class="leave-field leave-col-4"><label>ประเภทการลา <span class="req">*</span></label><select class="leave-select" name="leave_type_id" required>
            <option value="">-- เลือกประเภทการลา --</option><?php foreach ($types as $t): ?><option value="<?= $t['id'] ?>"><?= leave_e($t['name']) ?><?= $t['default_quota_days'] !== null ? ' (สิทธิ์เริ่มต้น ' . leave_format_days($t['default_quota_days']) . ' วัน)' : '' ?></option><?php endforeach; ?>
          </select></div>
        <div class="leave-field leave-col-3"><label>วันเริ่มลา <span class="req">*</span></label><input class="leave-input" data-leave-start type="date" name="start_date" required></div>
        <div class="leave-field leave-col-3"><label>ลาถึงวันที่ <span class="req">*</span></label><input class="leave-input" data-leave-end type="date" name="end_date" required></div>
        <div class="leave-field leave-col-2"><label>จำนวนวันลา</label><input class="leave-input" data-leave-days type="number" min="0.5" step="0.5" name="leave_days" placeholder="0" required>
          <div class="leave-help">ระบบคำนวณแบบรวมวันเริ่มและวันสิ้นสุด สามารถปรับเป็น 0.5 วันได้</div>
        </div>
        <div class="leave-field leave-col-12"><label>เหตุผลการลา <span class="req">*</span></label><textarea class="leave-textarea" name="reason" required placeholder="ระบุเหตุผลหรือรายละเอียดที่จำเป็นต่อการพิจารณา"></textarea></div>
      </div>
    </div>

    <div class="leave-form-section">
      <div class="leave-form-section__title"><span class="leave-step">03</span>
        <div>
          <h3>รับมอบงานและหัวหน้างาน</h3>
          <p>ผู้รับมอบงานต้องยืนยันก่อนจึงส่งต่อหัวหน้างาน</p>
        </div>
      </div>
      <div class="leave-form-grid">
        <div class="leave-field leave-col-6"><label>เพื่อนร่วมงานที่รับมอบงาน <span class="req">*</span></label><select class="leave-select" name="handover_user_id" required>
            <option value="">-- เลือกผู้รับมอบงาน --</option><?php foreach ($coworkers as $c): ?><option value="<?= $c['id'] ?>"><?= leave_e($c['fullname']) ?><?= trim((string)$c['department']) !== '' ? ' · ' . leave_e($c['department']) : '' ?></option><?php endforeach; ?>
          </select>
          <div class="leave-help">ระบบจะแจ้งรายการนี้ในเมนู “งานที่รับมอบ” ของบุคคลที่เลือก</div>
        </div>
        <div class="leave-field leave-col-6"><label>หัวหน้างานผู้เห็นชอบ</label><input class="leave-input" value="<?= leave_e($supervisor ? $supervisor['fullname'] : 'ยังไม่ได้กำหนดหัวหน้างาน') ?>" readonly><?php if ($supervisor): ?><input type="hidden" name="supervisor_user_id" value="<?= $supervisor['id'] ?>"><?php endif; ?><div class="leave-help"><?php if (!$department['has_department']): ?>ไม่พบแผนกของบัญชีนี้ กรุณาให้ Admin กำหนดแผนกในข้อมูลบุคลากรก่อน<?php elseif (!$supervisor): ?>ตรวจพบแผนก “<?= leave_e($department['name']) ?>” แต่ยังไม่ได้กำหนดหัวหน้าแผนกในเมนู “หัวหน้างาน”<?php else: ?>ตรวจพบแผนก “<?= leave_e($department['name']) ?>” และดึงหัวหน้าแผนกนี้อัตโนมัติ<?php endif; ?></div>
        </div>
      </div>
    </div>

    <div class="leave-form-section">
      <div class="leave-form-section__title"><span class="leave-step">04</span>
        <div>
          <h3>เอกสารประกอบ</h3>
          <p>รองรับ PDF / JPG / PNG ขนาดไม่เกิน 10 MB ต่อไฟล์</p>
        </div>
      </div>
      <div class="leave-form-grid">
        <div class="leave-field leave-col-6"><label>ใบรับรองแพทย์</label><input class="leave-input" type="file" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png">
          <div class="leave-help">แนบเมื่อมีเอกสารรับรองการรักษาหรือแพทย์นัด</div>
        </div>
        <div class="leave-field leave-col-6"><label>เอกสารอื่น ๆ</label><input class="leave-input" type="file" name="other_attachment" accept=".pdf,.jpg,.jpeg,.png">
          <div class="leave-help">เช่น หนังสือเชิญอบรม คำสั่ง หรือเอกสารประกอบ</div>
        </div>
      </div>
    </div>

    <?php if (!$department['has_department']): ?><div class="leave-alert leave-alert--warning">บัญชีผู้ใช้นี้ยังไม่มีข้อมูลแผนก ระบบจึงไม่สามารถเลือกหัวหน้าแผนกได้ กรุณาให้ Admin แก้ไขข้อมูลบุคลากรก่อน</div><?php elseif (!$supervisor): ?><div class="leave-alert leave-alert--warning">พบแผนก “<?= leave_e($department['name']) ?>” แล้ว แต่ยังไม่ได้กำหนดหัวหน้าของแผนกนี้ กรุณาให้ Admin ตั้งค่าที่เมนู “หัวหน้างาน”</div><?php endif; ?>
    <div class="leave-form-actions"><a class="leave-btn leave-btn--outline" href="index.php">ยกเลิก</a><button class="leave-btn leave-btn--primary" type="submit" <?= $supervisor ? '' : 'disabled' ?>>บันทึกและส่งใบลา</button></div>
  </form>
</section>
<?php leave_page_end(); ?>