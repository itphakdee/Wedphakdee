<?php
require_once __DIR__ . '/layout.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = $id > 0 ? vehicle_get_request($id) : null;
if (!$row) {
    http_response_code(404);
    die('ไม่พบรายการคำขอใช้รถ');
}
if (!vehicle_request_access($row)) {
    http_response_code(403);
    die('403 Forbidden: คุณไม่มีสิทธิ์ดูรายการนี้');
}

$uid=(int)$_SESSION['user_id'];
$isOwner=((int)$row['user_id']===$uid);
$isSupervisor=((int)$row['supervisor_user_id']===$uid);
$canEdit=$currentVehicleIsAdmin||($isOwner&&in_array($row['status'],array('pending_supervisor','pending_admin'),true));
$canCancel=$isOwner&&!in_array($row['status'],array('completed','cancelled','rejected','cancel_requested'),true);
$statusMeta=vehicle_status_meta($row['status']);
$typeMeta=vehicle_request_type_meta($row['request_type']);

$companions=array();
if(vehicle_table_exists('vehicle_request_companions')){
    $stmt=$conn->prepare("SELECT * FROM vehicle_request_companions WHERE request_id=? ORDER BY id");$stmt->bind_param('i',$id);$stmt->execute();$rs=$stmt->get_result();while($rs&&($r=$rs->fetch_assoc()))$companions[]=$r;$stmt->close();
}
$logs=array();
if(vehicle_table_exists('vehicle_status_logs')){
    $stmt=$conn->prepare("SELECT l.*,u.fullname actor_name FROM vehicle_status_logs l LEFT JOIN users u ON u.id=l.user_id WHERE l.request_id=? ORDER BY l.id DESC");$stmt->bind_param('i',$id);$stmt->execute();$rs=$stmt->get_result();while($rs&&($r=$rs->fetch_assoc()))$logs[]=$r;$stmt->close();
}
$feedback=null;
if(vehicle_table_exists('vehicle_feedback')){
    $stmt=$conn->prepare("SELECT vf.*,u.fullname FROM vehicle_feedback vf LEFT JOIN users u ON u.id=vf.user_id WHERE vf.request_id=? LIMIT 1");$stmt->bind_param('i',$id);$stmt->execute();$rs=$stmt->get_result();$feedback=$rs?$rs->fetch_assoc():null;$stmt->close();
}
$fleet=$currentVehicleIsAdmin?$conn->query("SELECT id,registration,display_name,status FROM vehicle_fleet WHERE status<>'inactive' ORDER BY sort_order,registration"):null;
$drivers=$currentVehicleIsAdmin?$conn->query("SELECT id,fullname,phone,status FROM vehicle_drivers WHERE status='active' ORDER BY fullname"):null;

vehicle_page_start('รายละเอียดคำขอใช้รถ','ตรวจสอบข้อมูลการเดินทาง ผู้ร่วมเดินทาง การรับรอง สถานะการจัดรถ และประวัติการดำเนินงาน','list');
?>
<div class="vehicle-grid">
<section class="vehicle-card vehicle-col-8">
  <div class="vehicle-card__header"><div><h2><?= vehicle_e($row['request_no']?$row['request_no']:'#'.$row['id']) ?> · <?= vehicle_e($typeMeta[0]) ?></h2><p>สร้างเมื่อ <?= vehicle_e(date('d/m/Y H:i',strtotime($row['created_at']))) ?> น.</p></div><div style="display:flex;gap:8px;align-items:center"><span class="vehicle-badge vehicle-badge--<?= vehicle_e($statusMeta[1]) ?>"><?= vehicle_e($statusMeta[0]) ?></span><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" href="index.php?page=list">← รายการ</a></div></div>
  <div class="vehicle-card__body">
    <div class="vehicle-detail-grid">
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">ผู้ร้องขอ</span><div class="vehicle-detail-value"><?= vehicle_e($row['fullname']) ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">ผู้ทำรายการ</span><div class="vehicle-detail-value"><?= vehicle_e($row['operator_name']?$row['operator_name']:$row['fullname']) ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">แผนก / หน่วยงาน</span><div class="vehicle-detail-value"><?= vehicle_e($row['department_name']?$row['department_name']:'-') ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">หัวหน้างานรับรอง</span><div class="vehicle-detail-value"><?= vehicle_e($row['supervisor_name']?$row['supervisor_name']:'ยังไม่ได้กำหนด') ?><br><small>สถานะ: <?= vehicle_e($row['supervisor_status']) ?></small></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">ตามหนังสือ</span><div class="vehicle-detail-value"><?= vehicle_e($row['book_reference']?$row['book_reference']:'-') ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">เลขที่หนังสือ / ลงวันที่</span><div class="vehicle-detail-value"><?= vehicle_e($row['book_no']?$row['book_no']:'-') ?> · <?= $row['book_date']?vehicle_format_thai_date($row['book_date']):'-' ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">ความเร่งด่วน</span><div class="vehicle-detail-value"><?= vehicle_e($row['urgency']) ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">รถที่ใช้</span><div class="vehicle-detail-value"><?= vehicle_e($row['private_registration']?('รถยนต์ส่วนตัว '.$row['private_registration']):('รถโรงพยาบาล '.($row['hospital_registration']?$row['hospital_registration']:$row['fleet_registration']))) ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">พนักงานขับ</span><div class="vehicle-detail-value"><?= vehicle_e($row['driver_name']?$row['driver_name']:'ยังไม่ระบุ') ?><?= $row['driver_phone']?'<br><small>'.vehicle_e($row['driver_phone']).'</small>':'' ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">เลขไมล์ขาไป</span><div class="vehicle-detail-value"><?= $row['odometer_out']!==null && $row['odometer_out']!=='' ? vehicle_e(number_format((float)$row['odometer_out'],1).' กม.') : '-' ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">เลขไมล์ขากลับ</span><div class="vehicle-detail-value"><?= $row['odometer_return']!==null && $row['odometer_return']!=='' ? vehicle_e(number_format((float)$row['odometer_return'],1).' กม.') : '-' ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">ระยะทางที่ใช้</span><div class="vehicle-detail-value"><?php if($row['odometer_out']!==null && $row['odometer_return']!==null && (float)$row['odometer_return'] >= (float)$row['odometer_out']): ?><?= vehicle_e(number_format((float)$row['odometer_return']-(float)$row['odometer_out'],1).' กม.') ?><?php else: ?>-<?php endif; ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">สถานที่ไป</span><div class="vehicle-detail-value"><?= vehicle_e($row['location']) ?></div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">วันเวลาเริ่ม</span><div class="vehicle-detail-value"><?= vehicle_format_thai_date($row['use_date']) ?> · <?= vehicle_e(substr((string)$row['use_time'],0,5)) ?> น.</div></div>
      <div class="vehicle-detail-item"><span class="vehicle-detail-label">วันเวลาสิ้นสุด</span><div class="vehicle-detail-value"><?= $row['end_date']?vehicle_format_thai_date($row['end_date']):'-' ?><?= $row['end_time']?' · '.vehicle_e(substr((string)$row['end_time'],0,5)).' น.':'' ?></div></div>
      <div class="vehicle-detail-item vehicle-detail-item--full"><span class="vehicle-detail-label">เหตุผลขอใช้รถ</span><div class="vehicle-detail-value"><?= vehicle_e($row['reason']?$row['reason']:$row['subject']) ?></div></div>
      <div class="vehicle-detail-item vehicle-detail-item--full"><span class="vehicle-detail-label">รายละเอียด</span><div class="vehicle-detail-value"><?= vehicle_e($row['detail']?$row['detail']:'-') ?></div></div>
      <div class="vehicle-detail-item vehicle-detail-item--full"><span class="vehicle-detail-label">เอกสารอ้างอิง</span><div class="vehicle-detail-value"><?php if($row['document']):?><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" href="<?= vehicle_e($row['document']) ?>" target="_blank">📎 เปิดเอกสารแนบ</a><?php else:?>-<?php endif;?></div></div>
    </div>

    <div style="margin-top:20px"><h3 style="font-size:15px;margin:0 0 10px">ผู้ร่วมเดินทาง</h3><?php if($companions):?><div class="vehicle-table-wrap"><table class="vehicle-table" style="min-width:600px"><thead><tr><th>#</th><th>ชื่อ–สกุล</th><th>ตำแหน่ง</th><th>ระดับ</th></tr></thead><tbody><?php foreach($companions as $i=>$p):?><tr><td><?= $i+1 ?></td><td><strong><?= vehicle_e($p['person_name']) ?></strong></td><td><?= vehicle_e($p['position_name']?$p['position_name']:'-') ?></td><td><?= vehicle_e($p['level_name']?$p['level_name']:'-') ?></td></tr><?php endforeach;?></tbody></table></div><?php else:?><div class="vehicle-inline-note">ไม่มีผู้ร่วมเดินทางที่เลือกจากระบบ</div><?php endif;?></div>

    <div class="vehicle-form-actions" style="margin-top:18px;justify-content:flex-start">
      <a class="vehicle-btn vehicle-btn--ghost" target="_blank" href="print.php?id=<?= $id ?>">🖨 พิมพ์คำขอ</a>
      <?php if($canEdit):?><a class="vehicle-btn vehicle-btn--warning" href="edit.php?id=<?= $id ?>">✎ แก้ไขข้อมูล</a><?php endif;?>
      <?php if($isOwner&&$row['status']==='completed'):?><a class="vehicle-btn vehicle-btn--success" href="feedback.php?id=<?= $id ?>">★ ความพึงพอใจ</a><?php endif;?>
    </div>
  </div>
</section>

<aside class="vehicle-col-4">
  <?php if($isSupervisor&&$row['status']==='pending_supervisor'&&$row['supervisor_status']==='pending'):?>
  <section class="vehicle-card" style="margin-bottom:18px"><div class="vehicle-card__header"><div><h3>หัวหน้างานรับรอง</h3><p>คุณถูกกำหนดเป็นหัวหน้างานของผู้ร้องขอ</p></div></div><div class="vehicle-card__body"><form method="post" action="status_update.php"><input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="mode" value="supervisor"><label style="font-size:12px;font-weight:700">หมายเหตุการรับรอง</label><textarea class="vehicle-control vehicle-textarea" name="note" placeholder="หมายเหตุ (ถ้ามี)"></textarea><div class="vehicle-form-actions" style="margin-top:10px"><button class="vehicle-btn vehicle-btn--danger" name="decision" value="reject" type="submit">ไม่รับรอง</button><button class="vehicle-btn vehicle-btn--success" name="decision" value="approve" type="submit">รับรองคำขอ</button></div></form></div></section>
  <?php endif;?>

  <?php if($currentVehicleIsAdmin):?>
  <section class="vehicle-card" style="margin-bottom:18px"><div class="vehicle-card__header"><div><h3>จัดการคำขอ</h3><p>สำหรับ Admin: จัดรถ พนักงานขับ และสถานะงาน</p></div></div><div class="vehicle-card__body"><form method="post" action="status_update.php"><input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="mode" value="admin">
    <div class="vehicle-field" style="margin-bottom:12px"><label>สถานะ</label><select class="vehicle-control" name="status"><?php foreach(array('pending_admin','approved','assigned','in_use','completed','cancelled','rejected') as $s):$m=vehicle_status_meta($s);?><option value="<?= vehicle_e($s) ?>" <?= $row['status']===$s?'selected':'' ?>><?= vehicle_e($m[0]) ?></option><?php endforeach;?></select></div>
    <div class="vehicle-field" style="margin-bottom:12px"><label>รถโรงพยาบาล</label><select class="vehicle-control" name="vehicle_id"><option value="0">-- ไม่ใช้ / รถส่วนตัว --</option><?php if($fleet):while($v=$fleet->fetch_assoc()):?><option value="<?= (int)$v['id'] ?>" <?= (int)$row['vehicle_id']===(int)$v['id']?'selected':'' ?>><?= vehicle_e($v['registration']) ?><?= $v['status']!=='active'?' · '.$v['status']:'' ?></option><?php endwhile;endif;?></select></div>
    <div class="vehicle-field" style="margin-bottom:12px"><label>พนักงานขับ</label><select class="vehicle-control" name="driver_id"><option value="0">-- ยังไม่ระบุ --</option><?php if($drivers):while($d=$drivers->fetch_assoc()):?><option value="<?= (int)$d['id'] ?>" <?= (int)$row['driver_id']===(int)$d['id']?'selected':'' ?>><?= vehicle_e($d['fullname']) ?><?= $d['phone']?' · '.vehicle_e($d['phone']):'' ?></option><?php endwhile;endif;?></select></div>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:12px" class="vehicle-odometer-grid">
      <div class="vehicle-field">
        <label>เลขไมล์ขาไป (กม.)</label>
        <input class="vehicle-control" id="odometer_out" name="odometer_out" type="number" min="0" step="0.1" inputmode="decimal" value="<?= $row['odometer_out']!==null ? vehicle_e($row['odometer_out']) : '' ?>" placeholder="เช่น 125430">
      </div>
      <div class="vehicle-field">
        <label>เลขไมล์ขากลับ (กม.)</label>
        <input class="vehicle-control" id="odometer_return" name="odometer_return" type="number" min="0" step="0.1" inputmode="decimal" value="<?= $row['odometer_return']!==null ? vehicle_e($row['odometer_return']) : '' ?>" placeholder="เช่น 125612">
      </div>
    </div>
    <div class="vehicle-inline-note" id="odometer_distance_note" style="margin:-2px 0 12px">ระยะทางจะคำนวณอัตโนมัติเมื่อกรอกเลขไมล์ขาไปและขากลับ</div>
    <div class="vehicle-field"><label>หมายเหตุเจ้าหน้าที่</label><textarea class="vehicle-control vehicle-textarea" name="admin_note"><?= vehicle_e($row['admin_note']) ?></textarea></div>
    <button class="vehicle-btn vehicle-btn--primary vehicle-btn--wide" type="submit">บันทึกการจัดการ</button>
  </form></div></section>
  <?php endif;?>

  <?php if($canCancel):?>
  <section class="vehicle-card" style="margin-bottom:18px"><div class="vehicle-card__header"><div><h3>แจ้งยกเลิก</h3><p>ส่งคำขอยกเลิกให้เจ้าหน้าที่ตรวจสอบ</p></div></div><div class="vehicle-card__body"><form method="post" action="cancel.php"><input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><textarea class="vehicle-control vehicle-textarea" name="reason" required placeholder="ระบุเหตุผลที่ต้องการยกเลิก"></textarea><button class="vehicle-btn vehicle-btn--danger vehicle-btn--wide" type="submit" data-confirm="ยืนยันแจ้งยกเลิกรายการนี้?">แจ้งยกเลิก</button></form></div></section>
  <?php endif;?>

  <section class="vehicle-card" style="margin-bottom:18px"><div class="vehicle-card__header"><div><h3>ความพึงพอใจ</h3><p>ผู้ร้องขอประเมินหลังงานเสร็จสิ้น</p></div></div><div class="vehicle-card__body"><?php if($feedback):?><div style="font-size:30px;font-weight:800;color:#0b5b63"><?= (int)$feedback['rating'] ?>/5</div><p style="font-size:12px;color:#647f8c;line-height:1.7"><?= vehicle_e($feedback['comment']?$feedback['comment']:'ไม่มีความคิดเห็นเพิ่มเติม') ?></p><small>โดย <?= vehicle_e($feedback['fullname']) ?></small><?php else:?><div class="vehicle-empty" style="padding:15px"><strong>ยังไม่มีการประเมิน</strong></div><?php endif;?></div></section>

  <section class="vehicle-card"><div class="vehicle-card__header"><div><h3>ประวัติการดำเนินงาน</h3><p>บันทึกการเปลี่ยนแปลงของรายการ</p></div></div><div class="vehicle-card__body"><div class="vehicle-timeline"><?php if($logs):foreach($logs as $log):$m=vehicle_status_meta($log['new_status']);?><div class="vehicle-timeline__item"><span class="vehicle-timeline__dot"></span><strong><?= vehicle_e($m[0]) ?></strong><span><?= vehicle_e($log['actor_name']?$log['actor_name']:'ระบบ') ?> · <?= vehicle_e(date('d/m/Y H:i',strtotime($log['created_at']))) ?></span><?php if($log['note']):?><p><?= vehicle_e($log['note']) ?></p><?php endif;?></div><?php endforeach;else:?><div class="vehicle-empty" style="padding:10px"><strong>ยังไม่มีประวัติ</strong></div><?php endif;?></div></div></section>

  <?php if($currentVehicleIsAdmin):?><form method="post" action="delete.php" style="margin-top:18px"><input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><button class="vehicle-btn vehicle-btn--danger vehicle-btn--wide" type="submit" data-confirm="ยืนยันลบคำขอนี้ถาวร? การกระทำนี้ย้อนกลับไม่ได้">ลบรายการถาวร (Admin)</button></form><?php endif;?>
</aside>
</div>

<style>
@media (max-width: 640px){
  .vehicle-odometer-grid{grid-template-columns:1fr !important;}
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var out = document.getElementById('odometer_out');
  var ret = document.getElementById('odometer_return');
  var note = document.getElementById('odometer_distance_note');
  if (!out || !ret || !note) return;

  function updateDistance() {
    var a = parseFloat(out.value);
    var b = parseFloat(ret.value);
    if (Number.isFinite(a) && Number.isFinite(b)) {
      if (b < a) {
        note.textContent = '⚠ เลขไมล์ขากลับต้องไม่น้อยกว่าเลขไมล์ขาไป';
        note.style.color = '#b42318';
      } else {
        note.textContent = 'ระยะทางที่ใช้ ' + (b - a).toLocaleString('th-TH', {minimumFractionDigits:1, maximumFractionDigits:1}) + ' กม.';
        note.style.color = '';
      }
    } else {
      note.textContent = 'ระยะทางจะคำนวณอัตโนมัติเมื่อกรอกเลขไมล์ขาไปและขากลับ';
      note.style.color = '';
    }
  }
  out.addEventListener('input', updateDistance);
  ret.addEventListener('input', updateDistance);
  updateDistance();
});
</script>
<?php vehicle_page_end(); ?>
