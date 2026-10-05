<?php
require_once __DIR__ . '/layout.php';medical_require_login();
$u=medical_current_user();$allowed=medical_is_admin() || (function_exists('has_permission') && has_permission('medical.manage'));
if(!$allowed){http_response_code(403);die('403 Forbidden: สำหรับผู้ดูแลระบบหรือผู้มีสิทธิ์ medical.manage เท่านั้น');}
$result='';$type='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!medical_verify_csrf(isset($_POST['csrf_token'])?$_POST['csrf_token']:'')){die('CSRF invalid');}
 $sql=file_get_contents(__DIR__.'/medical.sql');
 if($sql===false){$result='ไม่พบไฟล์ medical.sql';$type='error';}
 else{
   $ok=$conn->multi_query($sql);$errors=array();
   if(!$ok)$errors[]=$conn->error;
   do{if($conn->errno)$errors[]=$conn->error;if(!$conn->more_results())break;}while($conn->next_result());
   if($errors){$result='ติดตั้งบางส่วนไม่สำเร็จ: '.implode(' | ',array_unique($errors));$type='error';}else{$result='ติดตั้ง / อัปเดตฐานข้อมูลศูนย์เครื่องมือแพทย์เรียบร้อยแล้ว';$type='success';}
 }
}
medical_page_start('ติดตั้งระบบศูนย์เครื่องมือแพทย์','สร้างตารางฐานข้อมูลสำหรับทะเบียนเครื่องมือ งานซ่อม ช่าง ประวัติ และ Feedback','install');
?>
<section class="med-card med-install"><h2>Database Installer</h2><p>ฐานข้อมูลที่ใช้งาน: <strong>login_db</strong> ตัวติดตั้งนี้ไม่ลบตารางระบบอื่น</p><?php if($result): ?><div class="med-alert med-alert--<?= medical_e($type) ?>"><?= medical_e($result) ?></div><?php endif; ?><div class="med-install-list"><span>medical_equipment</span><span>medical_technicians</span><span>medical_repair_requests</span><span>medical_status_logs</span><span>medical_feedback</span></div><form method="post"><input type="hidden" name="csrf_token" value="<?= medical_e(medical_csrf_token()) ?>"><button class="med-btn med-btn-primary" type="submit">ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้</button></form><a class="med-btn med-btn-light" href="index.php">กลับหน้าระบบ</a></section>
<?php medical_page_end(); ?>
