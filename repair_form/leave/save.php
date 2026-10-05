<?php
require_once __DIR__ . '/config_leave.php';
require_once __DIR__ . '/lineapi/leave_notifications.php';
if($_SERVER['REQUEST_METHOD']!=='POST')leave_redirect('add.php');
leave_require_csrf();
$user=leave_current_user();$uid=(int)$user['id'];
$conn->begin_transaction();
$medical=null;$other=null;
try{
  $leaveNo=trim((string)($_POST['leave_no']??''));$typeId=(int)($_POST['leave_type_id']??0);$start=trim((string)($_POST['start_date']??''));$end=trim((string)($_POST['end_date']??''));$days=(float)($_POST['leave_days']??0);$reason=trim((string)($_POST['reason']??''));$phone=trim((string)($_POST['contact_phone']??''));$handoverId=(int)($_POST['handover_user_id']??0);$supervisorId=(int)($_POST['supervisor_user_id']??0);
  if($leaveNo===''||$typeId<=0||$start===''||$end===''||$days<=0||$reason===''||$handoverId<=0||$supervisorId<=0)throw new Exception('กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบ');
  if(strtotime($end)<strtotime($start))throw new Exception('วันที่สิ้นสุดต้องไม่น้อยกว่าวันเริ่มลา');
  if($handoverId===$uid)throw new Exception('ผู้รับมอบงานต้องเป็นบุคคลอื่น');
  $fy=leave_fiscal_year($start);
  $leaveNo=leave_generate_no($fy); // ออกเลขใหม่ตอนบันทึกจริง ป้องกันเลขซ้ำเมื่อมีผู้ยื่นพร้อมกัน
  $stmt=$conn->prepare("SELECT id,name FROM leave_types_master WHERE id=? AND is_active=1 LIMIT 1");$stmt->bind_param('i',$typeId);$stmt->execute();$type=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$type)throw new Exception('ไม่พบประเภทการลาที่เลือก');
  $stmt=$conn->prepare("SELECT id,fullname FROM users WHERE id=? AND status='active' LIMIT 1");$stmt->bind_param('i',$handoverId);$stmt->execute();$handover=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$handover)throw new Exception('ไม่พบผู้รับมอบงาน');
  $department=leave_user_department_info($user);if(!$department['has_department'])throw new Exception('บัญชีผู้ใช้นี้ยังไม่ได้กำหนดแผนก กรุณาติดต่อ Admin');$resolved=leave_supervisor_for_user($user);if(!$resolved||$supervisorId!==(int)$resolved['id'])throw new Exception('ไม่พบหัวหน้าแผนกของผู้ใช้นี้ หรือข้อมูลหัวหน้างานไม่ถูกต้อง กรุณากลับไปเปิดแบบฟอร์มใหม่');
  $supervisor=$resolved;
  $medical=leave_upload_file('medical_certificate','medical','medical');$other=leave_upload_file('other_attachment','attachments','leave');
  $employeeCode=(string)$user['employee_code'];$employeeName=(string)$user['fullname'];$position=(string)$user['position_name'];$deptId=(int)$department['id'];$deptName=(string)$department['name'];$handoverName=$handover['fullname'];$supervisorName=$supervisor['fullname'];$typeName=$type['name'];$status='pending_handover';$handoverStatus='pending';$supervisorStatus='pending';
  $stmt=$conn->prepare("INSERT INTO leave_applications(leave_no,user_id,employee_code,employee_name,position_name,department_id,department_name,leave_type_id,leave_type_name,fiscal_year,start_date,end_date,leave_days,reason,contact_phone,medical_certificate,other_attachment,handover_user_id,handover_name,handover_status,supervisor_user_id,supervisor_name,supervisor_status,status,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?,NOW())");
  if(!$stmt)throw new Exception($conn->error);
  $stmt->bind_param('sisssisisissdssssississs',$leaveNo,$uid,$employeeCode,$employeeName,$position,$deptId,$deptName,$typeId,$typeName,$fy,$start,$end,$days,$reason,$phone,$medical,$other,$handoverId,$handoverName,$handoverStatus,$supervisorId,$supervisorName,$supervisorStatus,$status);
  if(!$stmt->execute())throw new Exception($stmt->error);$id=$stmt->insert_id;$stmt->close();leave_audit($id,'create','ยื่นใบลาและส่งให้ '.$handoverName.' รับมอบงาน');
  $conn->commit();

  // แจ้ง LINE หลังบันทึกฐานข้อมูลสำเร็จแล้ว เพื่อไม่ให้ LINE ล้มแล้วข้อมูลใบลาหาย
  try {
    $lineRow = leave_get_application($id);
    if ($lineRow) {
      leave_line_notify_created($conn, $lineRow);
    }
  } catch (Throwable $lineError) {
    // LINE เป็นระบบเสริม ไม่ทำให้การยื่นใบลาล้ม
    leave_audit($id, 'line_error', 'LINE OA แจ้งเตือนไม่สำเร็จ: ' . $lineError->getMessage());
  }

  leave_flash('success','บันทึกใบลา '.$leaveNo.' เรียบร้อยแล้ว และส่งแจ้งเตือน LINE OA ตามผู้รับที่ผูกไว้');
  leave_redirect('detail.php?id='.$id);
}catch(Exception $e){$conn->rollback();if($medical)leave_delete_file($medical);if($other)leave_delete_file($other);leave_flash('error',$e->getMessage());leave_redirect('add.php');}
