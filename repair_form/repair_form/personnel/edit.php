<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('edit');
if (!personnel_schema_ready()) { header('Location: install.php'); exit; }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location:index.php'); exit; }
$stmt=$conn->prepare('SELECT * FROM personnel WHERE id=? LIMIT 1'); $stmt->bind_param('i',$id); $stmt->execute(); $person=$stmt->get_result()->fetch_assoc(); $stmt->close();
if(!$person){http_response_code(404);die('ไม่พบข้อมูลบุคลากร');}
$departments=personnel_departments(); $flash=personnel_pull_flash();
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>แก้ไขข้อมูลบุคลากร</title><link rel="stylesheet" href="assets/personnel.css?v=1.0"></head><body>
<div class="personnel-shell">
<section class="hero"><div class="hero-grid"><div><div class="eyebrow">EDIT PERSONNEL #<?=intval($id)?></div><h1>แก้ไขข้อมูลบุคลากร</h1><p>ปรับปรุงข้อมูลประจำตัว ข้อมูลการทำงาน และบัญชีเข้าสู่ระบบ</p></div><div class="hero-actions"><a class="btn btn-outline" href="detail.php?id=<?=$id?>">← กลับรายละเอียด</a></div></div></section>
<?php if($flash):?><div class="alert <?=($flash['type']==='success'?'alert-success':'alert-error')?>"><?=ph($flash['message'])?></div><?php endif;?>
<section class="card"><div class="card-head"><div class="card-title"><div class="icon-box">✎</div><div><h2>ข้อมูลบุคลากร</h2><div class="card-sub">ข้อมูลที่แก้ไขจะเชื่อมกับบัญชีผู้ใช้งานที่ผูกไว้</div></div></div></div><div class="card-body">
<form method="post" action="update.php">
<input type="hidden" name="csrf_token" value="<?=ph(personnel_csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>">
<div class="grid">
<div class="section-label">ข้อมูลบัญชีเข้าสู่ระบบ</div>
<div class="field col-6"><label>Username <span class="req">*</span></label><input type="text" name="username" required maxlength="50" pattern="[A-Za-z0-9._-]{3,50}" value="<?=ph($person['username'])?>"><small>หากรายการเก่ายังไม่มีบัญชี ระบบจะสร้างบัญชีใหม่และใช้รหัสผ่านเริ่มต้น 123456</small></div>
<div class="field col-6"><label>สถานะบัญชี</label><input type="text" value="<?=ph($person['user_id']?'เชื่อมบัญชีแล้ว (#'.$person['user_id'].')':'ยังไม่ได้เชื่อมบัญชี')?>" readonly></div>
<div class="section-label">ข้อมูลส่วนบุคคล</div>
<div class="field col-3"><label>คำนำหน้า <span class="req">*</span></label><select name="prefix" required><option value="">-- เลือก --</option><?php foreach(personnel_prefix_options() as $v):?><option value="<?=ph($v)?>" <?=$person['prefix']===$v?'selected':''?>><?=ph($v)?></option><?php endforeach;?></select></div>
<div class="field col-4"><label>ชื่อ <span class="req">*</span></label><input type="text" name="first_name" required maxlength="100" value="<?=ph($person['first_name'])?>"></div>
<div class="field col-5"><label>นามสกุล <span class="req">*</span></label><input type="text" name="last_name" required maxlength="100" value="<?=ph($person['last_name'])?>"></div>
<div class="field col-4"><label>ชื่ออังกฤษ</label><input type="text" name="first_name_en" maxlength="150" value="<?=ph($person['first_name_en'])?>"></div>
<div class="field col-4"><label>ชื่อเล่น</label><input type="text" name="nickname" maxlength="100" value="<?=ph($person['nickname'])?>"></div>
<div class="field col-4"><label>เพศ <span class="req">*</span></label><select name="gender" required><option value="">-- เลือก --</option><?php foreach(personnel_gender_options() as $v):?><option value="<?=ph($v)?>" <?=$person['gender']===$v?'selected':''?>><?=ph($v)?></option><?php endforeach;?></select></div>
<div class="field col-4"><label>วันเกิด <span class="req">*</span></label><input type="date" name="birth_date" required max="<?=date('Y-m-d')?>" value="<?=ph($person['birth_date'])?>"></div>
<div class="field col-4"><label>เลขประจำตัวประชาชน <span class="req">*</span></label><input type="text" name="national_id" required maxlength="17" value="<?=ph(personnel_format_national_id($person['national_id']))?>"></div>
<div class="field col-4"><label>อีเมล <span class="req">*</span></label><input type="email" name="email" required maxlength="150" value="<?=ph($person['email'])?>"></div>
<div class="section-label">ข้อมูลการปฏิบัติงาน</div>
<div class="field col-4"><label>รหัสบุคลากร</label><input type="text" name="employee_code" maxlength="50" value="<?=ph($person['employee_code'])?>"></div>
<div class="field col-4"><label>ตำแหน่ง</label><input type="text" name="position_name" maxlength="150" value="<?=ph($person['position_name'])?>"></div>
<div class="field col-4"><label>หน่วยงาน / แผนก</label><select name="department_id" id="department_id"><option value="">-- เลือกหน่วยงาน --</option><?php foreach($departments as $d):?><option value="<?=intval($d['id'])?>" data-name="<?=ph($d['department_name'])?>" <?=((string)$person['department_id']===(string)$d['id'])?'selected':''?>><?=ph($d['department_name'])?></option><?php endforeach;?></select><input type="hidden" name="department" id="department_name" value="<?=ph($person['department'])?>"></div>
<div class="field col-4"><label>เบอร์โทรศัพท์</label><input type="text" name="phone" maxlength="50" value="<?=ph($person['phone'])?>"></div>
<div class="field col-4"><label>สถานที่ / ห้อง</label><input type="text" name="room_location" maxlength="150" value="<?=ph($person['room_location'])?>"></div>
<div class="field col-4"><label>สถานะ</label><select name="status"><option value="active" <?=$person['status']==='active'?'selected':''?>>ปฏิบัติงาน</option><option value="inactive" <?=$person['status']==='inactive'?'selected':''?>>พ้นสภาพ / ไม่ปฏิบัติงาน</option></select></div>
<div class="field col-12"><label>หมายเหตุ</label><textarea name="notes" maxlength="2000"><?=ph($person['notes'])?></textarea></div>
</div>
<div class="form-actions"><a class="btn btn-soft" href="detail.php?id=<?=$id?>">ยกเลิก</a><button class="btn btn-primary" type="submit">บันทึกการแก้ไข</button></div>
</form></div></section>
<?php if(personnel_is_admin()):?>
<section class="card"><div class="card-head"><div class="card-title"><div class="icon-box">🔐</div><div><h3>จัดการรหัสผ่านเข้าสู่ระบบ</h3><div class="card-sub">เฉพาะผู้ดูแลระบบเท่านั้น</div></div></div></div><div class="card-body">
<?php if($person['user_id']):?><form method="post" action="password_update.php"><input type="hidden" name="csrf_token" value="<?=ph(personnel_csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><div class="grid"><div class="field col-6"><label>รหัสผ่านใหม่ <span class="req">*</span></label><input type="password" name="new_password" minlength="6" maxlength="72" required placeholder="อย่างน้อย 6 ตัวอักษร"></div><div class="field col-6"><label>ยืนยันรหัสผ่านใหม่ <span class="req">*</span></label><input type="password" name="confirm_password" minlength="6" maxlength="72" required></div></div><div class="form-actions"><button class="btn btn-outline" type="submit" name="reset_default" value="1" formnovalidate onclick="return confirm('รีเซ็ตรหัสผ่านกลับเป็น 123456 ?')">รีเซ็ตเป็น 123456</button><button class="btn btn-navy" type="submit">เปลี่ยนรหัสผ่าน</button></div></form><?php else:?><div class="alert alert-info">ยังไม่มีบัญชีผู้ใช้ เมื่อบันทึก Username ในแบบฟอร์มด้านบน ระบบจะสร้างบัญชีและตั้งรหัสผ่านเริ่มต้นเป็น 123456</div><?php endif;?>
</div></section><?php endif;?>
</div>
<script>(function(){var s=document.getElementById('department_id'),h=document.getElementById('department_name');if(s&&h){s.addEventListener('change',function(){var o=s.options[s.selectedIndex];h.value=o&&o.dataset?o.dataset.name||'':'';});}})();</script>
</body></html>
