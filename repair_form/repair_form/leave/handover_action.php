<?php
require_once __DIR__ . '/config_leave.php';if($_SERVER['REQUEST_METHOD']!=='POST')leave_redirect('handovers.php');leave_require_csrf();
$id=(int)($_POST['id']??0);$action=(string)($_POST['action']??'');$comment=trim((string)($_POST['comment']??''));$row=leave_get_application($id);$uid=(int)$_SESSION['user_id'];
if(!$row||(int)$row['handover_user_id']!==$uid||$row['status']!=='pending_handover'){http_response_code(403);die('ไม่มีสิทธิ์ดำเนินการรายการนี้');}
if($action==='accept'){$stmt=$conn->prepare("UPDATE leave_applications SET handover_status='accepted',handover_comment=?,handover_at=NOW(),status='pending_supervisor',updated_at=NOW() WHERE id=?");$stmt->bind_param('si',$comment,$id);$stmt->execute();$stmt->close();leave_audit($id,'handover_accept','ผู้รับมอบงานยืนยันรับมอบงาน');leave_flash('success','รับมอบงานเรียบร้อยแล้ว ระบบส่งใบลาไปยังหัวหน้างาน');}
elseif($action==='reject'){$stmt=$conn->prepare("UPDATE leave_applications SET handover_status='rejected',handover_comment=?,handover_at=NOW(),updated_at=NOW() WHERE id=?");$stmt->bind_param('si',$comment,$id);$stmt->execute();$stmt->close();leave_audit($id,'handover_reject','ผู้รับมอบงานส่งคืนใบลาให้ผู้ยื่นแก้ไข');leave_flash('warning','ส่งคืนรายการให้ผู้ยื่นใบลาแก้ไขแล้ว');}
else leave_flash('error','คำสั่งไม่ถูกต้อง');leave_redirect('detail.php?id='.$id);
