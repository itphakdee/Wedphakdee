<?php
require_once __DIR__ . '/layout.php';
$id=(int)($_GET['id']??0);$row=leave_get_application($id);if(!$row)die('ไม่พบข้อมูลใบลา');if(!leave_can_view_application($row)){http_response_code(403);die('403 Forbidden: คุณไม่มีสิทธิ์ดูใบลานี้');}
$uid=(int)$_SESSION['user_id'];$statusInfo=leave_status_info($row['status']);$handoverInfo=leave_handover_info($row['handover_status']);$superInfo=leave_supervisor_info($row['supervisor_status']);
$logs=array();$stmt=$conn->prepare("SELECT l.*,u.fullname actor_name FROM leave_audit_logs l LEFT JOIN users u ON u.id=l.actor_user_id WHERE l.application_id=? ORDER BY l.id DESC LIMIT 20");if($stmt){$stmt->bind_param('i',$id);$stmt->execute();$res=$stmt->get_result();while($r=$res->fetch_assoc())$logs[]=$r;$stmt->close();}
leave_page_start('รายละเอียดใบลา','เลขที่ '.(string)$row['leave_no'].' · ติดตามการรับมอบงานและสถานะการเห็นชอบ','');
?>
<div class="leave-grid-2">
 <div>
  <section class="leave-card"><div class="leave-card__head"><div><h2><?=leave_e($row['leave_no'])?></h2><p>ยื่นเมื่อ <?=leave_date_thai($row['created_at'],true)?></p></div><span class="leave-badge leave-badge--<?=$statusInfo[1]?>"><?=leave_e($statusInfo[0])?></span></div><div class="leave-card__body">
   <div class="leave-info-grid">
    <div class="leave-info"><label>ชื่อผู้ลา</label><strong><?=leave_e($row['employee_name'])?></strong></div><div class="leave-info"><label>หน่วยงาน</label><strong><?=leave_e($row['department_name']?:'-')?></strong></div>
    <div class="leave-info"><label>ประเภทการลา</label><strong><?=leave_e($row['leave_type_name'])?></strong></div><div class="leave-info"><label>ปีงบประมาณ</label><strong><?=leave_e($row['fiscal_year'])?></strong></div>
    <div class="leave-info"><label>วันเริ่มลา</label><strong><?=leave_date_thai($row['start_date'])?></strong></div><div class="leave-info"><label>ลาถึงวันที่</label><strong><?=leave_date_thai($row['end_date'])?></strong></div>
    <div class="leave-info"><label>จำนวนวันลา</label><strong><?=leave_format_days($row['leave_days'])?> วัน</strong></div><div class="leave-info"><label>เบอร์ติดต่อ</label><strong><?=leave_e($row['contact_phone']?:'-')?></strong></div>
    <div class="leave-info leave-info--full"><label>เหตุผลการลา</label><strong><?=nl2br(leave_e($row['reason']))?></strong></div>
    <?php if($row['cancel_reason']):?><div class="leave-info leave-info--full"><label>เหตุผลการแจ้งยกเลิก</label><strong><?=nl2br(leave_e($row['cancel_reason']))?></strong></div><?php endif;?>
   </div>
   <div style="margin-top:18px"><label style="display:block;font-size:11px;color:#7d8f95;margin-bottom:8px">เอกสารประกอบ</label><div class="leave-docs"><?php if($row['medical_certificate']):?><a class="leave-doc" href="download.php?id=<?=$id?>&type=medical">📄 ใบรับรองแพทย์</a><?php endif;?><?php if($row['other_attachment']):?><a class="leave-doc" href="download.php?id=<?=$id?>&type=other">📎 เอกสารประกอบ</a><?php endif;?><?php if(!$row['medical_certificate']&&!$row['other_attachment']):?><span class="leave-help">ไม่มีเอกสารแนบ</span><?php endif;?></div></div>
  </div></section>

  <section class="leave-card"><div class="leave-card__head"><div><h3>ลำดับการดำเนินงาน</h3><p>เวิร์กโฟลว์รับมอบงานและการเห็นชอบ</p></div></div><div class="leave-card__body"><div class="leave-timeline">
   <div class="leave-timeline__item"><div class="leave-timeline__dot">1</div><div class="leave-timeline__content"><strong>ยื่นใบลาโดย <?=leave_e($row['employee_name'])?></strong><span><?=leave_date_thai($row['created_at'],true)?></span></div></div>
   <div class="leave-timeline__item"><div class="leave-timeline__dot">2</div><div class="leave-timeline__content"><strong>รับมอบงาน: <?=leave_e($row['handover_name'])?> · <span class="leave-badge leave-badge--<?=$handoverInfo[1]?>"><?=leave_e($handoverInfo[0])?></span></strong><span><?=leave_e($row['handover_comment']?:'ยังไม่มีหมายเหตุ')?><?=$row['handover_at']?' · '.leave_date_thai($row['handover_at'],true):''?></span></div></div>
   <div class="leave-timeline__item"><div class="leave-timeline__dot">3</div><div class="leave-timeline__content"><strong>หัวหน้างาน: <?=leave_e($row['supervisor_name'])?> · <span class="leave-badge leave-badge--<?=$superInfo[1]?>"><?=leave_e($superInfo[0])?></span></strong><span><?=leave_e($row['supervisor_comment']?:'ยังไม่มีหมายเหตุ')?><?=$row['supervisor_at']?' · '.leave_date_thai($row['supervisor_at'],true):''?></span></div></div>
  </div></div></section>

  <?php if($logs):?><section class="leave-card"><div class="leave-card__head"><div><h3>ประวัติการทำรายการ</h3><p>Audit trail ล่าสุด</p></div></div><div class="leave-card__body"><div class="leave-mini-list"><?php foreach($logs as $log):?><div class="leave-mini-item"><div><strong><?=leave_e($log['detail'])?></strong><span><?=leave_e($log['actor_name']?:'ระบบ')?> · <?=leave_date_thai($log['created_at'],true)?></span></div></div><?php endforeach;?></div></div></section><?php endif;?>
 </div>

 <aside>
  <section class="leave-card no-print"><div class="leave-card__head"><div><h3>คำสั่ง</h3><p>แสดงตามสิทธิ์ของผู้ใช้งาน</p></div></div><div class="leave-card__body"><div class="leave-actions">
   <a class="leave-btn leave-btn--outline" href="index.php">← กลับรายการ</a>
   <?php if(leave_can_edit_application($row)):?><a class="leave-btn leave-btn--secondary" href="edit.php?id=<?=$id?>">แก้ไขข้อมูล</a><?php endif;?>
   <button class="leave-btn leave-btn--outline" type="button" onclick="window.print()">พิมพ์</button>
  </div></div></section>

  <?php if((int)$row['handover_user_id']===$uid && $row['status']==='pending_handover'):?><section class="leave-card no-print"><div class="leave-card__head"><div><h3>รับมอบงาน</h3><p>ยืนยันการดูแลงานแทนในช่วงวันลา</p></div></div><div class="leave-card__body"><form action="handover_action.php" method="post"><input type="hidden" name="csrf_token" value="<?=leave_e(leave_csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><div class="leave-field"><label>หมายเหตุ</label><textarea class="leave-textarea" name="comment" placeholder="ระบุงานที่รับมอบหรือเหตุผลที่ส่งคืน"></textarea></div><div class="leave-actions" style="margin-top:12px"><button class="leave-btn leave-btn--success" name="action" value="accept">รับมอบงาน</button><button class="leave-btn leave-btn--danger" name="action" value="reject">ส่งคืนให้แก้ไข</button></div></form></div></section><?php endif;?>

  <?php if((leave_is_admin()||(int)$row['supervisor_user_id']===$uid) && $row['status']==='pending_supervisor' && $row['handover_status']==='accepted'):?><section class="leave-card no-print"><div class="leave-card__head"><div><h3>หัวหน้างานเห็นชอบ</h3><p>พิจารณาหลังผู้รับมอบงานยืนยันแล้ว</p></div></div><div class="leave-card__body"><form action="approve_action.php" method="post"><input type="hidden" name="csrf_token" value="<?=leave_e(leave_csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><div class="leave-field"><label>ความเห็นหัวหน้างาน</label><textarea class="leave-textarea" name="comment" placeholder="บันทึกความเห็นประกอบการพิจารณา"></textarea></div><div class="leave-actions" style="margin-top:12px"><button class="leave-btn leave-btn--success" name="action" value="approve">เห็นชอบ / อนุมัติ</button><button class="leave-btn leave-btn--danger" name="action" value="reject">ไม่เห็นชอบ</button></div></form></div></section><?php endif;?>

  <?php if((leave_is_admin()||(int)$row['supervisor_user_id']===$uid) && $row['status']==='cancel_requested'):?><section class="leave-card no-print"><div class="leave-card__head"><div><h3>พิจารณาคำขอยกเลิก</h3><p><?=leave_e($row['cancel_reason'])?></p></div></div><div class="leave-card__body"><form action="approve_action.php" method="post"><input type="hidden" name="csrf_token" value="<?=leave_e(leave_csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><div class="leave-actions"><button class="leave-btn leave-btn--success" name="action" value="approve_cancel">ยืนยันยกเลิก</button><button class="leave-btn leave-btn--outline" name="action" value="reject_cancel">ไม่อนุมัติการยกเลิก</button></div></form></div></section><?php endif;?>

  <?php if((int)$row['user_id']===$uid && !in_array($row['status'],array('cancelled','rejected','cancel_requested'),true)):?><section class="leave-card no-print" id="cancel"><div class="leave-card__head"><div><h3>แจ้งยกเลิกใบลา</h3><p>ส่งคำขอให้หัวหน้างานยืนยัน</p></div></div><div class="leave-card__body"><form action="cancel.php" method="post"><input type="hidden" name="csrf_token" value="<?=leave_e(leave_csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><div class="leave-field"><label>เหตุผลการยกเลิก <span class="req">*</span></label><textarea class="leave-textarea" name="reason" required></textarea></div><button class="leave-btn leave-btn--danger" data-confirm="ยืนยันส่งคำขอยกเลิกใบลานี้?" style="margin-top:12px">แจ้งยกเลิก</button></form></div></section><?php endif;?>

  <?php if(leave_is_admin()):?><section class="leave-card no-print"><div class="leave-card__head"><div><h3>ผู้ดูแลระบบ</h3><p>ลบข้อมูลถาวร เฉพาะ Admin</p></div></div><div class="leave-card__body"><form action="delete.php" method="post"><input type="hidden" name="csrf_token" value="<?=leave_e(leave_csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><button class="leave-btn leave-btn--danger" data-confirm="ลบใบลานี้ถาวร? การดำเนินการนี้ย้อนกลับไม่ได้">ลบข้อมูล</button></form></div></section><?php endif;?>
 </aside>
</div>
<?php leave_page_end(); ?>
