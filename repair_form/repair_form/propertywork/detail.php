<?php
require_once '../../config.php';
require_once __DIR__ . '/property_helpers.php';
property_require_login();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare('SELECT * FROM properties WHERE id=? LIMIT 1');
$stmt->bind_param('i',$id);$stmt->execute();$res=$stmt->get_result();$asset=$res?$res->fetch_assoc():null;$stmt->close();
if(!$asset){property_flash('error','ไม่พบข้อมูลครุภัณฑ์');header('Location:index.php');exit;}
$flash=property_take_flash();$meta=property_status_meta($asset['status']);$activePage='propertywork';$basePath='../../';
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>รายละเอียดครุภัณฑ์ | โรงพยาบาลภักดีชุมพล</title><link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/property.css?v=20260831" rel="stylesheet"></head><body class="property-body">
<?php if(is_file('../../components/sidebar.php')){require '../../components/sidebar.php';} ?>
<main class="main-content property-main"><div class="property-shell property-form-shell">
<section class="property-hero form-hero"><div class="hero-copy"><div class="hero-kicker">ASSET DETAIL #<?php echo $id; ?></div><h1>รายละเอียดครุภัณฑ์</h1><p><?php echo property_e($asset['asset_no']); ?> · <?php echo property_e($asset['property_name']); ?></p></div><div class="hero-actions"><a class="btn-property btn-light-property" href="index.php">← กลับทะเบียน</a><a class="btn-property btn-light-property" href="edit.php?id=<?php echo $id; ?>">แก้ไขข้อมูล</a><a class="btn-property btn-primary-property" target="_blank" href="print_property.php?id=<?php echo $id; ?>">⎙ พิมพ์รายละเอียด</a></div></section>
<?php if($flash):?><div class="property-alert <?php echo $flash['type']==='success'?'alert-success-property':'alert-danger-property'; ?>"><span><?php echo $flash['type']==='success'?'✓':'!'; ?></span><?php echo property_e($flash['message']); ?></div><?php endif; ?>
<div class="detail-grid"><section class="detail-card"><div class="detail-header"><span class="eyebrow">PROPERTY INFORMATION</span><h2>ข้อมูลทะเบียนครุภัณฑ์</h2></div><div class="detail-list">
<?php
$items=array(
'เลขครุภัณฑ์'=>$asset['asset_no'],'ปีงบประมาณ'=>$asset['budget_year'],'ชื่อครุภัณฑ์'=>$asset['property_name'],'ประเภท'=>$asset['property_type'],'หมวดหมู่'=>$asset['category'],'ยี่ห้อ'=>$asset['brand'],'รุ่น'=>$asset['model'],'Serial Number'=>$asset['serial_no'],'หน่วยงาน / แผนก'=>$asset['department'],'หน่วยงานย่อย'=>$asset['department_unit'],'สถานที่ใช้งาน'=>$asset['location'],'ผู้รับผิดชอบ'=>$asset['responsible_person'],'วันที่จัดซื้อ'=>$asset['purchase_date'],'วันหมดประกัน'=>$asset['warranty_date'],'ผู้จำหน่าย'=>$asset['vendor'],'ระดับความเสี่ยง'=>$asset['risk_level'],'สถานะการเบิกใช้'=>$asset['withdraw_status'],'หน่วยงานขอยืม'=>$asset['borrow_department']);
foreach($items as $label=>$value):?><div class="detail-item"><span><?php echo property_e($label); ?></span><strong><?php echo property_e($value!==''&&$value!==null?$value:'-'); ?></strong></div><?php endforeach; ?>
<div class="detail-item full"><span>หมายเหตุ</span><strong><?php echo nl2br(property_e($asset['note']?$asset['note']:'ไม่มีหมายเหตุ')); ?></strong></div>
</div></section>
<aside class="detail-card"><div class="detail-header"><span class="eyebrow">STATUS & VALUE</span><h2>สถานะและมูลค่า</h2></div><div class="side-inner">
<?php if($asset['image']):?><img class="detail-side-image" src="../../uploads/property/<?php echo property_e(basename($asset['image'])); ?>" alt="รูปครุภัณฑ์"><?php else:?><div class="detail-side-image asset-placeholder" style="display:grid;place-items:center;font-size:48px">▦</div><?php endif; ?>
<div class="side-summary"><span>ราคาครุภัณฑ์</span><strong class="detail-price">฿<?php echo property_currency($asset['price']); ?></strong></div>
<div class="side-summary"><span>สถานะปัจจุบัน</span><strong><span class="status-pill <?php echo property_e($meta['class']); ?>"><i><?php echo $meta['icon']; ?></i><?php echo property_e($meta['label']); ?></span></strong></div>
<div class="side-summary"><span>เพิ่มข้อมูลเมื่อ</span><strong><?php echo property_e($asset['created_at']?$asset['created_at']:'-'); ?></strong></div>
<div class="side-summary"><span>ปรับปรุงล่าสุด</span><strong><?php echo property_e($asset['updated_at']?$asset['updated_at']:'-'); ?></strong></div>
</div></aside></div></div></main></body></html>
