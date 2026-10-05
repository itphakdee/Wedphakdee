<?php
require_once __DIR__ . '/config_leave.php';
require_once __DIR__ . '/lineapi/leave_notifications.php';

if($_SERVER['REQUEST_METHOD']!=='POST') leave_redirect('approvals.php');
leave_require_csrf();

$id=(int)($_POST['id']??0);
$action=(string)($_POST['action']??'');
$comment=trim((string)($_POST['comment']??''));
$row=leave_get_application($id);
$uid=(int)$_SESSION['user_id'];

if(!$row||(!leave_is_admin()&&(int)$row['supervisor_user_id']!==$uid)){
    http_response_code(403);
    die('ไม่มีสิทธิ์อนุมัติรายการนี้');
}

$lineMode = '';

if($action==='approve' && $row['status']==='pending_supervisor' && $row['handover_status']==='accepted'){
    $stmt=$conn->prepare("UPDATE leave_applications SET supervisor_status='approved',supervisor_comment=?,supervisor_at=NOW(),status='approved',updated_at=NOW() WHERE id=?");
    $stmt->bind_param('si',$comment,$id);
    $stmt->execute();
    $stmt->close();

    leave_audit($id,'approve','หัวหน้างานเห็นชอบและอนุมัติใบลา');
    $lineMode='approved';
    leave_flash('success','อนุมัติใบลาเรียบร้อยแล้ว');
}
elseif($action==='reject' && $row['status']==='pending_supervisor'){
    $stmt=$conn->prepare("UPDATE leave_applications SET supervisor_status='rejected',supervisor_comment=?,supervisor_at=NOW(),status='rejected',updated_at=NOW() WHERE id=?");
    $stmt->bind_param('si',$comment,$id);
    $stmt->execute();
    $stmt->close();

    leave_audit($id,'reject','หัวหน้างานไม่เห็นชอบใบลา');
    $lineMode='rejected';
    leave_flash('warning','บันทึกผลไม่อนุมัติเรียบร้อยแล้ว');
}
elseif($action==='approve_cancel' && $row['status']==='cancel_requested'){
    $stmt=$conn->prepare("UPDATE leave_applications SET status='cancelled',cancel_decision_by=?,cancel_decision_at=NOW(),updated_at=NOW() WHERE id=?");
    $stmt->bind_param('ii',$uid,$id);
    $stmt->execute();
    $stmt->close();

    leave_audit($id,'cancel_approved','ยืนยันยกเลิกใบลา');
    $lineMode='cancel_approved';
    leave_flash('success','ยืนยันยกเลิกใบลาเรียบร้อยแล้ว');
}
elseif($action==='reject_cancel' && $row['status']==='cancel_requested'){
    $restore=in_array($row['status_before_cancel'],array('pending_handover','pending_supervisor','approved'),true)
        ? $row['status_before_cancel']
        : 'approved';

    $stmt=$conn->prepare("UPDATE leave_applications SET status=?,cancel_decision_by=?,cancel_decision_at=NOW(),updated_at=NOW() WHERE id=?");
    $stmt->bind_param('sii',$restore,$uid,$id);
    $stmt->execute();
    $stmt->close();

    leave_audit($id,'cancel_rejected','ไม่อนุมัติคำขอยกเลิกใบลา');
    $lineMode='cancel_rejected';
    leave_flash('info','ไม่อนุมัติการยกเลิก และคืนสถานะใบลาเดิม');
}
else{
    leave_flash('error','สถานะรายการไม่รองรับคำสั่งนี้');
}

if($lineMode!==''){
    try {
        $lineRow=leave_get_application($id);
        if($lineRow){
            if($lineMode==='approved') leave_line_notify_decision($conn,$lineRow,true);
            elseif($lineMode==='rejected') leave_line_notify_decision($conn,$lineRow,false);
            elseif($lineMode==='cancel_approved') leave_line_notify_cancel_decision($conn,$lineRow,true);
            elseif($lineMode==='cancel_rejected') leave_line_notify_cancel_decision($conn,$lineRow,false);
        }
    } catch(Throwable $lineError) {
        leave_audit($id,'line_error','LINE OA แจ้งผลการพิจารณาไม่สำเร็จ: '.$lineError->getMessage());
    }
}

leave_redirect('detail.php?id='.$id);
