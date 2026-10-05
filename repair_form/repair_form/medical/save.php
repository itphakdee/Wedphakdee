<?php
require_once __DIR__ . '/config_medical.php';
medical_require_permission('create');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
if (!medical_schema_ready()) { medical_flash('error','ยังไม่ได้ติดตั้งฐานข้อมูลศูนย์เครื่องมือแพทย์'); header('Location:index.php'); exit; }
if (!medical_verify_csrf(isset($_POST['csrf_token'])?$_POST['csrf_token']:'')) { medical_flash('error','Session ของแบบฟอร์มไม่ถูกต้อง กรุณาลองใหม่'); header('Location:index.php#new-request'); exit; }

$u=medical_current_user();
$userId=(int)$u['id'];
$requester=trim(isset($_POST['requester_name'])?$_POST['requester_name']:'');
$department=trim(isset($_POST['department'])?$_POST['department']:'');
$phone=trim(isset($_POST['contact_phone'])?$_POST['contact_phone']:'');
$location=trim(isset($_POST['location'])?$_POST['location']:'');
$equipmentId=(int)(isset($_POST['equipment_id'])?$_POST['equipment_id']:0);
$asset=trim(isset($_POST['asset_code'])?$_POST['asset_code']:'');
$name=trim(isset($_POST['equipment_name'])?$_POST['equipment_name']:'');
$category=trim(isset($_POST['category'])?$_POST['category']:'');
$brand=trim(isset($_POST['brand'])?$_POST['brand']:'');
$model=trim(isset($_POST['model'])?$_POST['model']:'');
$serial=trim(isset($_POST['serial_no'])?$_POST['serial_no']:'');
$problem=trim(isset($_POST['problem_type'])?$_POST['problem_type']:'');
$detail=trim(isset($_POST['problem_detail'])?$_POST['problem_detail']:'');
$priority=trim(isset($_POST['priority'])?$_POST['priority']:'normal');
$allowedProblems=array('malfunction','not_power','alarm','broken','calibration','preventive','accessory','other');
if ($requester===''||$department===''||$location===''||$name===''||$detail===''||!in_array($problem,$allowedProblems,true)) { medical_flash('error','กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบถ้วน'); header('Location:index.php#new-request'); exit; }
if (!in_array($priority,array('normal','urgent','emergency'),true)) $priority='normal';

if ($equipmentId>0) {
    $stmt=$conn->prepare("SELECT asset_code,equipment_name,category,brand,model,serial_no FROM medical_equipment WHERE id=? AND status='active' LIMIT 1");
    if($stmt){$stmt->bind_param('i',$equipmentId);$stmt->execute();$res=$stmt->get_result();if($res&&($e=$res->fetch_assoc())){$asset=$e['asset_code'];$name=$e['equipment_name'];$category=$e['category'];$brand=$e['brand'];$model=$e['model'];$serial=$e['serial_no'];}else{$equipmentId=0;}$stmt->close();}
}
$status='pending';
$stmt=$conn->prepare("INSERT INTO medical_repair_requests(user_id,requester_name,department,contact_phone,equipment_id,asset_code,equipment_name,category,brand,model,serial_no,location,problem_type,problem_detail,priority,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
if(!$stmt){medical_flash('error','ไม่สามารถเตรียมคำสั่งบันทึกข้อมูลได้: '.$conn->error);header('Location:index.php#new-request');exit;}
$stmt->bind_param('isssisssssssssss',$userId,$requester,$department,$phone,$equipmentId,$asset,$name,$category,$brand,$model,$serial,$location,$problem,$detail,$priority,$status);
if(!$stmt->execute()){ $err=$stmt->error;$stmt->close();medical_flash('error','บันทึกไม่สำเร็จ: '.$err);header('Location:index.php#new-request');exit; }
$id=$stmt->insert_id;$stmt->close();
$requestNo=medical_request_no($id);
$stmt=$conn->prepare("UPDATE medical_repair_requests SET request_no=? WHERE id=?"); if($stmt){$stmt->bind_param('si',$requestNo,$id);$stmt->execute();$stmt->close();}
medical_log($id,'create','',$status,'สร้างรายการแจ้งซ่อม');
$pm=medical_priority_meta($priority);
$message="🩺 แจ้งซ่อมเครื่องมือแพทย์\nโรงพยาบาลภักดีชุมพล\n━━━━━━━━━━━━━━\nเลขที่: {$requestNo}\nผู้แจ้ง: {$requester}\nหน่วยงาน: {$department}\nเครื่องมือ: {$name}\nเลขครุภัณฑ์: ".($asset!==''?$asset:'-')."\nสถานที่: {$location}\nปัญหา: ".medical_problem_meta($problem)."\nความเร่งด่วน: {$pm['label']}\nรายละเอียด: {$detail}\n━━━━━━━━━━━━━━\nกรุณาตรวจสอบและรับงานในระบบ";
$line=medical_send_line($message,'medical_repair_created');
$lineStatus=!empty($line['ok'])?'success':'failed';$lineError=!empty($line['error'])?(string)$line['error']:'';
$stmt=$conn->prepare("UPDATE medical_repair_requests SET line_notify_status=?,line_notify_error=? WHERE id=?");if($stmt){$stmt->bind_param('ssi',$lineStatus,$lineError,$id);$stmt->execute();$stmt->close();}
medical_flash(!empty($line['ok'])?'success':'warning', 'บันทึกงาน '.$requestNo.' เรียบร้อย'.(!empty($line['ok'])?' และส่งแจ้งเตือน LINE สำเร็จ':' แต่ส่ง LINE ไม่สำเร็จ: '.$lineError));
header('Location: detail.php?id='.$id);exit;
