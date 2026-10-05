<?php
require_once '../../config.php';
require_once __DIR__ . '/property_helpers.php';
property_require_login();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare('SELECT * FROM properties WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$asset = $result ? $result->fetch_assoc() : null;
$stmt->close();
if (!$asset) {
    property_flash('error', 'ไม่พบข้อมูลครุภัณฑ์ที่ต้องการแก้ไข');
    header('Location: index.php');
    exit;
}
property_csrf_token();
$activePage = 'propertywork';
$basePath = '../../';
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>แก้ไขครุภัณฑ์ | โรงพยาบาลภักดีชุมพล</title><link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/property.css?v=20260831" rel="stylesheet"></head>
<body class="property-body"><?php if (is_file('../../components/sidebar.php')) { require '../../components/sidebar.php'; } ?>
<main class="main-content property-main"><div class="property-shell property-form-shell">
<section class="property-hero form-hero"><div class="hero-copy"><div class="hero-kicker">EDIT HOSPITAL ASSET</div><h1>แก้ไขข้อมูลครุภัณฑ์</h1><p>ปรับปรุงข้อมูลทะเบียนครุภัณฑ์ให้ถูกต้องและเป็นปัจจุบัน</p></div><div class="hero-actions"><a class="btn-property btn-light-property" href="detail.php?id=<?php echo $id; ?>">← กลับรายละเอียด</a></div></section>
<form class="form-card" action="update_property.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?php echo property_e(property_csrf_token()); ?>"><input type="hidden" name="id" value="<?php echo $id; ?>">
<div class="form-card-header"><div><span class="eyebrow">ASSET #<?php echo $id; ?></span><h2><?php echo property_e($asset['asset_no']); ?> · <?php echo property_e($asset['property_name']); ?></h2></div><span class="panel-badge">แก้ไขข้อมูล</span></div>
<div class="form-section"><div class="form-section-title"><span>01</span><div><strong>ข้อมูลทะเบียน</strong><small>ข้อมูลระบุตัวตนของครุภัณฑ์</small></div></div><div class="property-form-grid">
<div class="property-field field-col-4"><label>เลขครุภัณฑ์ <em>*</em></label><input name="asset_no" required maxlength="100" value="<?php echo property_e($asset['asset_no']); ?>"></div>
<div class="property-field field-col-4"><label>ปีงบประมาณ</label><input name="budget_year" maxlength="10" value="<?php echo property_e($asset['budget_year']); ?>"></div>
<div class="property-field field-col-4"><label>Serial Number</label><input name="serial_no" maxlength="150" value="<?php echo property_e($asset['serial_no']); ?>"></div>
<div class="property-field field-col-6"><label>ชื่อครุภัณฑ์ <em>*</em></label><input name="property_name" required maxlength="255" value="<?php echo property_e($asset['property_name']); ?>"></div>
<div class="property-field field-col-3"><label>ประเภทครุภัณฑ์ <em>*</em></label><input name="property_type" required maxlength="100" value="<?php echo property_e($asset['property_type']); ?>"></div>
<div class="property-field field-col-3"><label>หมวดหมู่</label><input name="category" maxlength="100" value="<?php echo property_e($asset['category']); ?>"></div>
</div></div>
<div class="form-section"><div class="form-section-title"><span>02</span><div><strong>ข้อมูลอุปกรณ์และการใช้งาน</strong><small>ยี่ห้อ รุ่น หน่วยงาน และผู้รับผิดชอบ</small></div></div><div class="property-form-grid">
<div class="property-field field-col-3"><label>ยี่ห้อ</label><input name="brand" maxlength="100" value="<?php echo property_e($asset['brand']); ?>"></div>
<div class="property-field field-col-3"><label>รุ่น</label><input name="model" maxlength="100" value="<?php echo property_e($asset['model']); ?>"></div>
<div class="property-field field-col-3"><label>หน่วยงาน / แผนก</label><input name="department" maxlength="100" value="<?php echo property_e($asset['department']); ?>"></div>
<div class="property-field field-col-3"><label>หน่วยงานย่อย</label><input name="department_unit" maxlength="255" value="<?php echo property_e($asset['department_unit']); ?>"></div>
<div class="property-field field-col-4"><label>สถานที่ใช้งาน</label><input name="location" maxlength="255" value="<?php echo property_e($asset['location']); ?>"></div>
<div class="property-field field-col-4"><label>ผู้รับผิดชอบ</label><input name="responsible_person" maxlength="150" value="<?php echo property_e($asset['responsible_person']); ?>"></div>
<div class="property-field field-col-4"><label>ระดับความเสี่ยง</label><input name="risk_level" maxlength="100" value="<?php echo property_e($asset['risk_level']); ?>"></div>
</div></div>
<div class="form-section"><div class="form-section-title"><span>03</span><div><strong>ข้อมูลจัดซื้อและมูลค่า</strong><small>สถานะปัจจุบันและราคาครุภัณฑ์</small></div></div><div class="property-form-grid">
<div class="property-field field-col-3"><label>วันที่จัดซื้อ</label><input type="date" name="purchase_date" value="<?php echo property_e($asset['purchase_date']); ?>"></div>
<div class="property-field field-col-3"><label>วันหมดประกัน</label><input type="date" name="warranty_date" value="<?php echo property_e($asset['warranty_date']); ?>"></div>
<div class="property-field field-col-3"><label>ผู้จำหน่าย / บริษัท</label><input name="vendor" maxlength="255" value="<?php echo property_e($asset['vendor']); ?>"></div>
<div class="property-field field-col-3"><label>ราคา (บาท) <em>*</em></label><input id="price" type="number" step="0.01" min="0" name="price" value="<?php echo property_e($asset['price']); ?>" required></div>
<div class="property-field field-col-4"><label>สถานะ <em>*</em></label><select name="status" required><?php foreach(array('ใช้งาน','ชำรุด','ส่งซ่อม','จำหน่าย') as $s): ?><option value="<?php echo $s; ?>" <?php echo $asset['status']===$s?'selected':''; ?>><?php echo $s; ?></option><?php endforeach; ?></select></div>
<div class="property-field field-col-4"><label>สถานะการเบิกใช้</label><input name="withdraw_status" maxlength="100" value="<?php echo property_e($asset['withdraw_status']); ?>"></div>
<div class="property-field field-col-4"><label>หน่วยงานขอยืม</label><input name="borrow_department" maxlength="255" value="<?php echo property_e($asset['borrow_department']); ?>"></div>
</div></div>
<div class="form-section"><div class="form-section-title"><span>04</span><div><strong>รูปภาพและหมายเหตุ</strong><small>เปลี่ยนรูปใหม่ได้ หรือเก็บรูปเดิมไว้</small></div></div><div class="property-form-grid">
<div class="property-field field-col-6"><label>รูปครุภัณฑ์</label><?php if($asset['image']): ?><img class="current-image" src="../../uploads/property/<?php echo property_e(basename($asset['image'])); ?>" alt="รูปเดิม"><?php endif; ?><input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"><span class="field-help">หากไม่เลือกไฟล์ ระบบจะใช้รูปเดิม</span><div class="image-preview-box"><img id="imagePreview" alt="ตัวอย่างรูป"></div></div>
<div class="property-field field-col-6"><label>หมายเหตุ</label><textarea name="note"><?php echo property_e($asset['note']); ?></textarea></div>
</div></div>
<div class="form-actions"><a class="btn-property btn-light-property" href="detail.php?id=<?php echo $id; ?>">ยกเลิก</a><button class="btn-property btn-primary-property" type="submit">✓ บันทึกการแก้ไข</button></div>
</form></div></main><script src="assets/js/property.js?v=20260831"></script></body></html>
