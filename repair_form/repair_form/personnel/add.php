<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('create');

if (!personnel_schema_ready()) {
    header('Location: install.php');
    exit;
}

$departments = personnel_departments();
$flash = personnel_pull_flash();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>เพิ่มบุคลากร</title>
<link rel="stylesheet" href="assets/personnel.css?v=1.0">
</head>
<body>
<div class="personnel-shell">
  <section class="hero">
    <div class="hero-grid">
      <div>
        <div class="eyebrow">HOSPITAL PERSONNEL MANAGEMENT</div>
        <h1>เพิ่มข้อมูลบุคลากร</h1>
        <p>สร้างทะเบียนบุคลากรพร้อมบัญชีสำหรับเข้าสู่ระบบโรงพยาบาล</p>
      </div>
      <div class="hero-actions">
        <a class="btn btn-outline" href="index.php">← กลับหน้ารายการ</a>
      </div>
    </div>
  </section>

  <?php if ($flash): ?>
    <div class="alert <?=($flash['type']==='success'?'alert-success':'alert-error')?>"><?=ph($flash['message'])?></div>
  <?php endif; ?>

  <section class="card">
    <div class="card-head">
      <div class="card-title">
        <div class="icon-box">＋</div>
        <div>
          <h2>แบบฟอร์มข้อมูลบุคลากร</h2>
          <div class="card-sub">ช่องที่มีเครื่องหมาย * จำเป็นต้องกรอก</div>
        </div>
      </div>
    </div>
    <div class="card-body">
      <form method="post" action="store.php" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?=ph(personnel_csrf_token())?>">

        <div class="grid">
          <div class="section-label">ข้อมูลบัญชีเข้าสู่ระบบ</div>
          <div class="field col-6">
            <label>Username <span class="req">*</span></label>
            <input type="text" name="username" maxlength="50" required pattern="[A-Za-z0-9._-]{3,50}" placeholder="เช่น somchai.s">
            <small>ใช้ตัวอักษรอังกฤษ ตัวเลข จุด ขีดกลาง หรือขีดล่าง 3-50 ตัวอักษร</small>
          </div>
          <div class="field col-6">
            <label>รหัสผ่านเริ่มต้น</label>
            <input type="text" value="123456" readonly>
            <small>รหัสผ่านเริ่มต้นกำหนดเป็น 123456 และผู้ดูแลระบบสามารถเปลี่ยนได้ภายหลัง</small>
          </div>

          <div class="section-label">ข้อมูลส่วนบุคคล</div>
          <div class="field col-3">
            <label>คำนำหน้า <span class="req">*</span></label>
            <select name="prefix" required>
              <option value="">-- เลือกคำนำหน้า --</option>
              <?php foreach (personnel_prefix_options() as $prefix): ?>
                <option value="<?=ph($prefix)?>"><?=ph($prefix)?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field col-4">
            <label>ชื่อ <span class="req">*</span></label>
            <input type="text" name="first_name" maxlength="100" required>
          </div>
          <div class="field col-5">
            <label>นามสกุล <span class="req">*</span></label>
            <input type="text" name="last_name" maxlength="100" required>
          </div>

          <div class="field col-4">
            <label>ชื่ออังกฤษ</label>
            <input type="text" name="first_name_en" maxlength="150" placeholder="English name">
          </div>
          <div class="field col-4">
            <label>ชื่อเล่น</label>
            <input type="text" name="nickname" maxlength="100">
          </div>
          <div class="field col-4">
            <label>เพศ <span class="req">*</span></label>
            <select name="gender" required>
              <option value="">-- เลือกเพศ --</option>
              <?php foreach (personnel_gender_options() as $gender): ?>
                <option value="<?=ph($gender)?>"><?=ph($gender)?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field col-4">
            <label>วันเกิด <span class="req">*</span></label>
            <input type="date" name="birth_date" required max="<?=date('Y-m-d')?>">
          </div>
          <div class="field col-4">
            <label>เลขประจำตัวประชาชน <span class="req">*</span></label>
            <input type="text" name="national_id" inputmode="numeric" maxlength="17" required placeholder="เลข 13 หลัก">
            <small>ระบบจะบันทึกเฉพาะตัวเลข 13 หลัก</small>
          </div>
          <div class="field col-4">
            <label>อีเมล <span class="req">*</span></label>
            <input type="email" name="email" maxlength="150" required placeholder="name@hospital.go.th">
          </div>

          <div class="section-label">ข้อมูลการปฏิบัติงาน</div>
          <div class="field col-4">
            <label>รหัสบุคลากร</label>
            <input type="text" name="employee_code" maxlength="50">
          </div>
          <div class="field col-4">
            <label>ตำแหน่ง</label>
            <input type="text" name="position_name" maxlength="150">
          </div>
          <div class="field col-4">
            <label>หน่วยงาน / แผนก</label>
            <select name="department_id" id="department_id">
              <option value="">-- เลือกหน่วยงาน --</option>
              <?php foreach ($departments as $department): ?>
                <option value="<?=intval($department['id'])?>" data-name="<?=ph($department['department_name'])?>"><?=ph($department['department_name'])?></option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" name="department" id="department_name" value="">
          </div>
          <div class="field col-4">
            <label>เบอร์โทรศัพท์</label>
            <input type="text" name="phone" maxlength="50">
          </div>
          <div class="field col-4">
            <label>สถานที่ / ห้อง</label>
            <input type="text" name="room_location" maxlength="150">
          </div>
          <div class="field col-4">
            <label>สถานะ</label>
            <select name="status">
              <option value="active">ปฏิบัติงาน</option>
              <option value="inactive">พ้นสภาพ / ไม่ปฏิบัติงาน</option>
            </select>
          </div>
          <div class="field col-12">
            <label>หมายเหตุ</label>
            <textarea name="notes" maxlength="2000" placeholder="ข้อมูลเพิ่มเติมที่จำเป็นต่อการบริหารบุคลากร"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <a class="btn btn-soft" href="index.php">ยกเลิก</a>
          <button class="btn btn-primary" type="submit">บันทึกข้อมูลบุคลากร</button>
        </div>
      </form>
    </div>
  </section>
</div>
<script>
(function(){
  var select=document.getElementById('department_id');
  var hidden=document.getElementById('department_name');
  if(select&&hidden){select.addEventListener('change',function(){var o=select.options[select.selectedIndex];hidden.value=o&&o.dataset?o.dataset.name||'':'';});}
})();
</script>
</body>
</html>
