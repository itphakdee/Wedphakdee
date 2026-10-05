<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('delete');
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}
if(!personnel_verify_csrf($_POST['csrf_token']??'')){personnel_flash('error','คำขอไม่ถูกต้อง');header('Location:index.php');exit;}
$id=(int)($_POST['id']??0);$deleteUser=isset($_POST['delete_user'])&&$_POST['delete_user']==='1';
$stmt=$conn->prepare('SELECT id,user_id,fullname,username FROM personnel WHERE id=? LIMIT 1');$stmt->bind_param('i',$id);$stmt->execute();$person=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$person){personnel_flash('error','ไม่พบข้อมูล');header('Location:index.php');exit;}
try{$conn->begin_transaction();$stmt=$conn->prepare('DELETE FROM personnel WHERE id=?');$stmt->bind_param('i',$id);if(!$stmt->execute())throw new Exception('ลบข้อมูลบุคลากรไม่สำเร็จ');$stmt->close();if($deleteUser&&!empty($person['user_id'])){$uid=(int)$person['user_id'];if($uid===(int)($_SESSION['user_id']??0))throw new Exception('ไม่สามารถลบบัญชีที่กำลังเข้าสู่ระบบอยู่ได้');$stmt=$conn->prepare('DELETE FROM users WHERE id=?');$stmt->bind_param('i',$uid);if(!$stmt->execute())throw new Exception('ลบบัญชีผู้ใช้ไม่สำเร็จ');$stmt->close();}$conn->commit();personnel_log_action(null,'delete','ลบบุคลากร #'.$id.' '.$person['fullname'].($deleteUser?' พร้อมบัญชีผู้ใช้':''));personnel_flash('success','ลบข้อมูลบุคลากรเรียบร้อยแล้ว');header('Location:index.php');exit;}catch(Throwable $e){$conn->rollback();personnel_flash('error',$e->getMessage());header('Location:detail.php?id='.$id);exit;}
