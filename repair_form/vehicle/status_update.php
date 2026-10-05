<?php
require_once __DIR__ . '/config_vehicle.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') vehicle_redirect('index.php?page=list');
vehicle_check_csrf();
$id=isset($_POST['id'])?(int)$_POST['id']:0;$row=vehicle_get_request($id);
if(!$row){vehicle_flash('danger','ไม่พบรายการคำขอ');vehicle_redirect('index.php?page=list');}
$mode=isset($_POST['mode'])?$_POST['mode']:'';$uid=(int)$_SESSION['user_id'];

if($mode==='supervisor'){
    if((int)$row['supervisor_user_id']!==$uid || $row['status']!=='pending_supervisor' || $row['supervisor_status']!=='pending'){
        http_response_code(403);die('403 Forbidden');
    }
    $decision=isset($_POST['decision'])?$_POST['decision']:'';$note=isset($_POST['note'])?trim($_POST['note']):'';
    if($decision==='approve'){$sup='approved';$new='pending_admin';$action='supervisor_approved';$label='หัวหน้างานรับรองแล้ว';}
    elseif($decision==='reject'){$sup='rejected';$new='rejected';$action='supervisor_rejected';$label='หัวหน้างานไม่รับรอง';}
    else{vehicle_flash('danger','คำสั่งไม่ถูกต้อง');vehicle_redirect('detail.php?id='.$id);}
    $old=$row['status'];
    $stmt=$conn->prepare("UPDATE vehicle_requests SET supervisor_status=?,supervisor_note=?,supervisor_at=NOW(),status=? WHERE id=?");
    $stmt->bind_param('sssi',$sup,$note,$new,$id);$stmt->execute();$stmt->close();
    vehicle_add_log($id,$action,$old,$new,$note);
    $msg="🚐 อัปเดตคำขอใช้รถ\nเลขที่: ".$row['request_no']."\nผู้ร้องขอ: ".$row['fullname']."\nสถานะ: ".$label."\nหัวหน้างาน: ".(isset($currentVehicleUser['fullname'])?$currentVehicleUser['fullname']:'-');
    vehicle_send_line($msg,'vehicle_supervisor_update');
    vehicle_flash('success',$label);
    vehicle_redirect('detail.php?id='.$id);
}

if($mode==='admin'){
    if(!$currentVehicleIsAdmin){http_response_code(403);die('403 Forbidden: Admin เท่านั้น');}
    $allowed=array('pending_admin','approved','assigned','in_use','completed','cancelled','rejected');
    $new=isset($_POST['status'])?$_POST['status']:'';if(!in_array($new,$allowed,true))$new=$row['status'];
    $vehicleId=isset($_POST['vehicle_id'])?(int)$_POST['vehicle_id']:0;$driverId=isset($_POST['driver_id'])?(int)$_POST['driver_id']:0;$note=isset($_POST['admin_note'])?trim($_POST['admin_note']):'';
    $hospitalReg=$row['hospital_registration'];$privateReg=$row['private_registration'];
    if($vehicleId>0){$stmt=$conn->prepare("SELECT registration FROM vehicle_fleet WHERE id=? LIMIT 1");$stmt->bind_param('i',$vehicleId);$stmt->execute();$stmt->bind_result($reg);if($stmt->fetch()){$hospitalReg=$reg;$privateReg='';}else{$vehicleId=0;}$stmt->close();}
    $driverName='';if($driverId>0){$stmt=$conn->prepare("SELECT fullname FROM vehicle_drivers WHERE id=? AND status='active' LIMIT 1");$stmt->bind_param('i',$driverId);$stmt->execute();$stmt->bind_result($driverName);if(!$stmt->fetch())$driverId=0;$stmt->close();}
    if($new==='cancelled'){$stmt=$conn->prepare("UPDATE vehicle_requests SET status=?,vehicle_id=?,hospital_registration=?,private_registration=?,driver_id=?,admin_note=?,cancelled_at=NOW() WHERE id=?");}
    else{$stmt=$conn->prepare("UPDATE vehicle_requests SET status=?,vehicle_id=?,hospital_registration=?,private_registration=?,driver_id=?,admin_note=? WHERE id=?");}
    $stmt->bind_param('sissisi',$new,$vehicleId,$hospitalReg,$privateReg,$driverId,$note,$id);$stmt->execute();$stmt->close();
    vehicle_add_log($id,'admin_status',$row['status'],$new,$note);
    $m=vehicle_status_meta($new);
    $msg="🚐 อัปเดตสถานะคำขอใช้รถ\nเลขที่: ".$row['request_no']."\nผู้ร้องขอ: ".$row['fullname']."\nสถานะ: ".$m[0];
    if($hospitalReg!=='')$msg.="\nรถ: ".$hospitalReg;if($driverName!=='')$msg.="\nพนักงานขับ: ".$driverName;
    vehicle_send_line($msg,'vehicle_status_update');
    vehicle_flash('success','บันทึกการจัดการและสถานะเรียบร้อยแล้ว');
    vehicle_redirect('detail.php?id='.$id);
}

vehicle_flash('danger','ไม่พบคำสั่งที่ต้องดำเนินการ');
vehicle_redirect('detail.php?id='.$id);
