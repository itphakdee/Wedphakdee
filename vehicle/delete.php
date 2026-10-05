<?php
require_once __DIR__ . '/config_vehicle.php';
if(!$currentVehicleIsAdmin){http_response_code(403);die('403 Forbidden: Admin เท่านั้น');}
if($_SERVER['REQUEST_METHOD']!=='POST')vehicle_redirect('index.php?page=list');vehicle_check_csrf();$id=isset($_POST['id'])?(int)$_POST['id']:0;$row=vehicle_get_request($id);if(!$row){vehicle_flash('danger','ไม่พบรายการ');vehicle_redirect('index.php?page=list');}
$conn->begin_transaction();try{
 foreach(array('vehicle_request_companions','vehicle_feedback','vehicle_status_logs') as $t){if(vehicle_table_exists($t)){$stmt=$conn->prepare("DELETE FROM `$t` WHERE request_id=?");$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();}}
 $stmt=$conn->prepare("DELETE FROM vehicle_requests WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();$conn->commit();
 if($row['document']&&strpos($row['document'],'uploads/vehicle_documents/')===0&&is_file(__DIR__.'/'.$row['document']))@unlink(__DIR__.'/'.$row['document']);
 vehicle_flash('success','ลบรายการ '.($row['request_no']?$row['request_no']:'#'.$id).' เรียบร้อยแล้ว');
}catch(Exception $e){$conn->rollback();vehicle_flash('danger','ลบรายการไม่สำเร็จ: '.$e->getMessage());}
vehicle_redirect('index.php?page=list');
