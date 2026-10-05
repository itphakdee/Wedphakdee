<?php
require_once __DIR__ . '/config_leave.php';if($_SERVER['REQUEST_METHOD']!=='POST')leave_redirect('index.php');leave_require_csrf();if(!leave_is_admin()){http_response_code(403);die('403 Forbidden: ผู้ใช้งานทั่วไปไม่สามารถลบใบลาได้');}
$id=(int)($_POST['id']??0);$row=leave_get_application($id);if(!$row){leave_flash('warning','ไม่พบข้อมูล');leave_redirect('index.php');}
leave_delete_file($row['medical_certificate']);leave_delete_file($row['other_attachment']);$stmt=$conn->prepare("DELETE FROM leave_applications WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();leave_audit($id,'delete','Admin ลบใบลา '.$row['leave_no']);leave_flash('success','ลบข้อมูลใบลา '.$row['leave_no'].' เรียบร้อยแล้ว');leave_redirect('index.php');
