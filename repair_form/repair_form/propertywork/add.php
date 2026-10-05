<?php
require_once '../../config.php';
require_once __DIR__ . '/property_helpers.php';
property_require_login();
property_csrf_token();
$activePage = 'propertywork';
$basePath = '../../';
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>เพิ่มครุภัณฑ์ | โรงพยาบาลภักดีชุมพล</title>
<link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/property.css?v=20260831" rel="stylesheet">
</head>
<body class="property-body">
<?php if (is_file('../../components/sidebar.php')) { require '../../components/sidebar.php'; } ?>
<main class="main-content property-main"><div class="property-shell property-form-shell">
<section class="property-hero form-hero">
  <div class="hero-copy"><div class="hero-kicker">NEW HOSPITAL ASSET</div><h1>เพิ่มครุภัณฑ์ใหม่</h1><p>ลงทะเบียนข้อมูลครุภัณฑ์ โรงพยาบาลภักดีชุมพล พร้อมราคา หน่วยงาน สถานะ และข้อมูลประกอบให้ครบถ้วน</p></div>
  <div class="hero-actions"><a class="btn-property btn-light-property" href="index.php">← กลับหน้าทะเบียน</a></div>
</section>
<form class="form-card" action="save_property.php" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="csrf_token" value="<?php echo property_e(property_csrf_token()); ?>">
<div class="form-card-header"><div><span class="eyebrow">PROPERTY REGISTRATION</span><h2>แบบฟอร์มลงทะเบียนครุภัณฑ์</h2></div><span class="panel-badge">ฟิลด์ * จำเป็นต้องกรอก</span></div>
<div class="form-section">
  <div class="form-section-title"><span>01</span><div><strong>ข้อมูลทะเบียน</strong><small>ข้อมูลระบุตัวตนของครุภัณฑ์</small></div></div>
  <div class="property-form-grid">
    <div class="property-field field-col-4"><label>เลขครุภัณฑ์ <em>*</em></label><input name="asset_no" required maxlength="100" placeholder="เช่น 7440-016-0003/01/69"></div>
    <div class="property-field field-col-4"><label>ปีงบประมาณ</label><input name="budget_year" maxlength="10" placeholder="เช่น 2569"></div>
    <div class="property-field field-col-4"><label>Serial Number</label><input name="serial_no" maxlength="150"></div>
    <div class="property-field field-col-6"><label>ชื่อครุภัณฑ์ <em>*</em></label><input name="property_name" required maxlength="255" placeholder="ชื่อรายการครุภัณฑ์"></div>
    <div class="property-field field-col-3"><label>ประเภทครุภัณฑ์ <em>*</em></label><input name="property_type" required maxlength="100" list="assetTypes" placeholder="เลือกหรือพิมพ์ประเภท"><datalist id="assetTypes"><option>ครุภัณฑ์คอมพิวเตอร์</option><option>ครุภัณฑ์สำนักงาน</option><option>ครุภัณฑ์ไฟฟ้าและวิทยุ</option><option>ครุภัณฑ์วิทยาศาสตร์และการแพทย์</option><option>ครุภัณฑ์ยานพาหนะและขนส่ง</option><option>ครุภัณฑ์งานบ้านงานครัว</option><option>ครุภัณฑ์โฆษณาและเผยแพร่</option></datalist></div>
    <div class="property-field field-col-3"><label>หมวดหมู่</label><input name="category" maxlength="100" placeholder="เช่น ซื้อ / บริจาค"></div>
  </div>
</div>
<div class="form-section">
  <div class="form-section-title"><span>02</span><div><strong>ข้อมูลอุปกรณ์และการใช้งาน</strong><small>ยี่ห้อ รุ่น หน่วยงาน และผู้รับผิดชอบ</small></div></div>
  <div class="property-form-grid">
    <div class="property-field field-col-3"><label>ยี่ห้อ</label><input name="brand" maxlength="100"></div>
    <div class="property-field field-col-3"><label>รุ่น</label><input name="model" maxlength="100"></div>
    <div class="property-field field-col-3"><label>หน่วยงาน / แผนก</label><input name="department" maxlength="100" placeholder="เช่น IT / OPD / IPD"></div>
    <div class="property-field field-col-3"><label>หน่วยงานย่อย</label><input name="department_unit" maxlength="255"></div>
    <div class="property-field field-col-4"><label>สถานที่ใช้งาน</label><input name="location" maxlength="255" placeholder="อาคาร / ห้อง / จุดติดตั้ง"></div>
    <div class="property-field field-col-4"><label>ผู้รับผิดชอบ</label><input name="responsible_person" maxlength="150"></div>
    <div class="property-field field-col-4"><label>ระดับความเสี่ยง</label><select name="risk_level"><option value="">ไม่ระบุ</option><option>ต่ำ</option><option>ปานกลาง</option><option>สูง</option><option>สูงมาก</option></select></div>
  </div>
</div>
<div class="form-section">
  <div class="form-section-title"><span>03</span><div><strong>ข้อมูลจัดซื้อและมูลค่า</strong><small>ใช้สำหรับสรุปมูลค่าทรัพย์สินของโรงพยาบาล</small></div></div>
  <div class="property-form-grid">
    <div class="property-field field-col-3"><label>วันที่จัดซื้อ</label><input type="date" name="purchase_date"></div>
    <div class="property-field field-col-3"><label>วันหมดประกัน</label><input type="date" name="warranty_date"></div>
    <div class="property-field field-col-3"><label>ผู้จำหน่าย / บริษัท</label><input name="vendor" maxlength="255"></div>
    <div class="property-field field-col-3"><label>ราคาครุภัณฑ์ (บาท) <em>*</em></label><input id="price" type="number" step="0.01" min="0" name="price" value="0.00" required></div>
    <div class="property-field field-col-4"><label>สถานะ <em>*</em></label><select name="status" required><option value="ใช้งาน">ใช้งาน</option><option value="ชำรุด">ชำรุด</option><option value="ส่งซ่อม">ส่งซ่อม</option><option value="จำหน่าย">จำหน่าย</option></select></div>
    <div class="property-field field-col-4"><label>สถานะการเบิกใช้</label><input name="withdraw_status" maxlength="100"></div>
    <div class="property-field field-col-4"><label>หน่วยงานขอยืม</label><input name="borrow_department" maxlength="255"></div>
  </div>
</div>
<div class="form-section">
  <div class="form-section-title"><span>04</span><div><strong>เอกสารประกอบ</strong><small>รูปภาพและหมายเหตุเพิ่มเติม</small></div></div>
  <div class="property-form-grid">
    <div class="property-field field-col-6"><label>รูปครุภัณฑ์</label><input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"><span class="field-help">รองรับ JPG, PNG, WEBP ขนาดไม่เกิน 5 MB</span><div class="image-preview-box"><img id="imagePreview" alt="ตัวอย่างรูป"></div></div>
    <div class="property-field field-col-6"><label>หมายเหตุ</label><textarea name="note" placeholder="รายละเอียดเพิ่มเติม เช่น สภาพอุปกรณ์ ข้อสังเกต หรือเลขเอกสารอ้างอิง"></textarea></div>
  </div>
</div>
<div class="form-actions"><a class="btn-property btn-light-property" href="index.php">ยกเลิก</a><button class="btn-property btn-primary-property" type="submit">✓ บันทึกครุภัณฑ์</button></div>
</form>
</div></main>
<script src="assets/js/property.js?v=20260831"></script>
</body></html>
