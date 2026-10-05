<?php
require_once __DIR__ . '/config_vehicle.php';
if($_SERVER['REQUEST_METHOD']!=='POST')vehicle_redirect('index.php?page=list');vehicle_check_csrf();
$id=isset($_POST['id'])?(int)$_POST['id']:0;$reason=isset($_POST['reason'])?trim($_POST['reason']):'';$row=vehicle_get_request($id);
if(!$row||((int)$row['user_id']!==(int)$_SESSION['user_id'])){http_response_code(403);die('403 Forbidden');}
if(in_array($row['status'],array('completed','cancelled','rejected','cancel_requested'),true)){vehicle_flash('warning','รายการนี้ไม่สามารถแจ้งยกเลิกได้');vehicle_redirect('detail.php?id='.$id);}
if($reason===''){vehicle_flash('danger','กรุณาระบุเหตุผลที่ต้องการยกเลิก');vehicle_redirect('detail.php?id='.$id);}
$old=$row['status'];$new='cancel_requested';$stmt=$conn->prepare("UPDATE vehicle_requests SET status=?,cancel_reason=?,cancel_requested_at=NOW() WHERE id=?");$stmt->bind_param('ssi',$new,$reason,$id);$stmt->execute();$stmt->close();vehicle_add_log($id,'cancel_requested',$old,$new,$reason);
vehicle_send_line("🚐 แจ้งขอยกเลิกการใช้รถ\nเลขที่: ".$row['request_no']."\nผู้ร้องขอ: ".$row['fullname']."\nเหตุผล: ".$reason,'vehicle_cancel_requested');
vehicle_flash('success','ส่งคำขอยกเลิกให้เจ้าหน้าที่แล้ว');vehicle_redirect('detail.php?id='.$id);
