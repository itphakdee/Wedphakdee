<?php
require_once '../../config.php';require_once __DIR__.'/property_helpers.php';property_require_login();
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}
if(!property_verify_csrf(isset($_POST['csrf_token'])?$_POST['csrf_token']:'')){property_flash('error','คำขอลบไม่ถูกต้อง');header('Location:index.php');exit;}
$id=isset($_POST['id'])?(int)$_POST['id']:0;$stmt=$conn->prepare('SELECT asset_no,image FROM properties WHERE id=? LIMIT 1');$stmt->bind_param('i',$id);$stmt->execute();$res=$stmt->get_result();$asset=$res?$res->fetch_assoc():null;$stmt->close();
if(!$asset){property_flash('error','ไม่พบรายการที่ต้องการลบ');header('Location:index.php');exit;}
$stmt=$conn->prepare('DELETE FROM properties WHERE id=?');$stmt->bind_param('i',$id);if($stmt->execute()){property_delete_image($asset['image']);property_flash('success','ลบครุภัณฑ์ '.$asset['asset_no'].' เรียบร้อยแล้ว');}else{property_flash('error','ลบข้อมูลไม่สำเร็จ: '.$stmt->error);} $stmt->close();header('Location:index.php');exit;
