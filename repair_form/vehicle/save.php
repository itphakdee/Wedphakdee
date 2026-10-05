<?php
require_once __DIR__ . '/config_vehicle.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    vehicle_redirect('index.php?page=add');
}
vehicle_check_csrf();

if (!vehicle_table_exists('vehicle_fleet') || !vehicle_column_exists('vehicle_requests', 'request_no')) {
    vehicle_flash('warning', 'ฐานข้อมูลระบบยานพาหนะยังไม่ได้ติดตั้ง');
    vehicle_redirect($currentVehicleIsAdmin ? 'install.php' : 'index.php');
}

$type = isset($_POST['request_type']) ? trim($_POST['request_type']) : 'general';
if (!in_array($type, array('general','refer'), true)) {
    $type = 'general';
}
$bookReference = isset($_POST['book_reference']) ? trim($_POST['book_reference']) : '';
$bookNo = isset($_POST['book_no']) ? trim($_POST['book_no']) : '';
$bookDate = isset($_POST['book_date']) && $_POST['book_date'] !== '' ? $_POST['book_date'] : null;
$urgency = isset($_POST['urgency']) ? trim($_POST['urgency']) : '';
$vehicleId = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
$privateReg = isset($_POST['private_registration']) ? trim($_POST['private_registration']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$detail = isset($_POST['detail']) ? trim($_POST['detail']) : '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';
$useDate = isset($_POST['use_date']) ? $_POST['use_date'] : '';
$useTime = isset($_POST['use_time']) ? $_POST['use_time'] : '';
$endDate = isset($_POST['end_date']) && $_POST['end_date'] !== '' ? $_POST['end_date'] : null;
$endTime = isset($_POST['end_time']) && $_POST['end_time'] !== '' ? $_POST['end_time'] : null;
$driverId = isset($_POST['driver_id']) ? (int)$_POST['driver_id'] : 0;
$members = isset($_POST['members']) && is_array($_POST['members']) ? $_POST['members'] : array();

if (!in_array($urgency, array('ปกติ','ด่วน','ด่วนมาก'), true)) {
    $urgency = ($type === 'refer') ? 'ด่วนมาก' : 'ปกติ';
}
if ($reason === '' || $location === '' || $useDate === '' || $useTime === '') {
    vehicle_flash('danger', 'กรุณากรอกเหตุผล สถานที่ วันที่ และเวลาให้ครบถ้วน');
    vehicle_redirect('index.php?page=add&type=' . urlencode($type));
}
if ($vehicleId <= 0 && $privateReg === '') {
    vehicle_flash('danger', 'กรุณาเลือกรถโรงพยาบาล หรือกรอกทะเบียนรถยนต์ส่วนตัว');
    vehicle_redirect('index.php?page=add&type=' . urlencode($type));
}
if ($vehicleId > 0 && $privateReg !== '') {
    vehicle_flash('danger', 'กรุณาเลือกใช้รถเพียงประเภทเดียว: รถโรงพยาบาล หรือรถยนต์ส่วนตัว');
    vehicle_redirect('index.php?page=add&type=' . urlencode($type));
}
if ($endDate !== null && $endDate < $useDate) {
    vehicle_flash('danger', 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มใช้รถ');
    vehicle_redirect('index.php?page=add&type=' . urlencode($type));
}

$hospitalReg = '';
if ($vehicleId > 0) {
    $stmt = $conn->prepare("SELECT registration FROM vehicle_fleet WHERE id=? AND status='active' LIMIT 1");
    $stmt->bind_param('i', $vehicleId);
    $stmt->execute();
    $stmt->bind_result($hospitalReg);
    if (!$stmt->fetch()) {
        $stmt->close();
        vehicle_flash('danger', 'ไม่พบรถโรงพยาบาลที่เลือก หรือรถถูกปิดใช้งานแล้ว');
        vehicle_redirect('index.php?page=add&type=' . urlencode($type));
    }
    $stmt->close();
}

$driverName = '';
if ($driverId > 0) {
    $stmt = $conn->prepare("SELECT fullname FROM vehicle_drivers WHERE id=? AND status='active' LIMIT 1");
    $stmt->bind_param('i', $driverId);
    $stmt->execute();
    $stmt->bind_result($driverName);
    if (!$stmt->fetch()) {
        $driverId = 0;
        $driverName = '';
    }
    $stmt->close();
}

$user = vehicle_current_user();
$dept = vehicle_department_for_user($user);
$supervisor = vehicle_supervisor_for_user($user);
$supervisorId = isset($supervisor['id']) ? (int)$supervisor['id'] : 0;
$supervisorName = isset($supervisor['fullname']) ? trim((string)$supervisor['fullname']) : '';
$supervisorStatus = $supervisorId > 0 ? 'pending' : 'not_required';
$status = $supervisorId > 0 ? 'pending_supervisor' : 'pending_admin';
$requesterName = isset($user['fullname']) ? $user['fullname'] : '';
$operatorName = $requesterName;
$operatorUserId = (int)$_SESSION['user_id'];
$uid = (int)$_SESSION['user_id'];
$deptId = (int)$dept['id'];
$deptName = $dept['name'];
$carSnapshot = $privateReg !== '' ? $privateReg : $hospitalReg;
$subject = $bookReference !== '' ? $bookReference : (function_exists('mb_substr') ? mb_substr($reason, 0, 250, 'UTF-8') : substr($reason, 0, 250));

$document = '';
try {
    $document = vehicle_upload_document('document', '');
} catch (Exception $e) {
    vehicle_flash('danger', $e->getMessage());
    vehicle_redirect('index.php?page=add&type=' . urlencode($type));
}

$companionRows = array();
$companionNames = array();
$seen = array();
foreach ($members as $memberIdRaw) {
    $memberId = (int)$memberIdRaw;
    if ($memberId <= 0 || $memberId === $uid || isset($seen[$memberId])) {
        continue;
    }
    $seen[$memberId] = true;
    if (vehicle_table_exists('personnel')) {
        $sql = "SELECT u.id,u.fullname,COALESCE((SELECT p.position_name FROM personnel p WHERE p.user_id=u.id ORDER BY p.id DESC LIMIT 1),'') position_name
                FROM users u WHERE u.id=? AND u.status='active' LIMIT 1";
    } else {
        $sql = "SELECT u.id,u.fullname,'' position_name FROM users u WHERE u.id=? AND u.status='active' LIMIT 1";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && ($p = $res->fetch_assoc())) {
        $companionRows[] = array('user_id'=>(int)$p['id'],'name'=>$p['fullname'],'position'=>$p['position_name'],'level'=>'');
        $companionNames[] = $p['fullname'];
    }
    $stmt->close();
}
$companionsText = implode(', ', $companionNames);

$requestId = 0;
try {
    $conn->begin_transaction();

    $fields = array(
        'user_id','operator_user_id','operator_name','fullname','department_id','department_name',
        'subject','book_reference','book_no','book_date','urgency','location','car','vehicle_id',
        'hospital_registration','private_registration','reason','use_date','end_date','use_time','end_time',
        'driver_id','detail','document','companions','request_type','supervisor_user_id','supervisor_name',
        'supervisor_status','status'
    );
    $values = array(
        $uid,$operatorUserId,$operatorName,$requesterName,$deptId,$deptName,
        $subject,$bookReference,$bookNo,$bookDate,$urgency,$location,$carSnapshot,$vehicleId,
        $hospitalReg,$privateReg,$reason,$useDate,$endDate,$useTime,$endTime,
        $driverId,$detail,$document,$companionsText,$type,$supervisorId,$supervisorName,
        $supervisorStatus,$status
    );
    $types = 'iissii' . 'sssssss' . 'i' . 'sssssss' . 'i' . 'sssss' . 'i' . 'sss';
    // ปรับจำนวนชนิดให้ตรงกับจำนวนค่าโดยสร้างอัตโนมัติเมื่อจำเป็น
    $types = '';
    foreach ($values as $idx=>$v) {
        if (in_array($idx, array(0,1,4,13,21,26), true)) $types .= 'i'; else $types .= 's';
    }
    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    $sql = "INSERT INTO vehicle_requests (`" . implode('`,`', $fields) . "`) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('ไม่สามารถเตรียมคำสั่งบันทึกได้: ' . $conn->error);
    vehicle_bind_values($stmt, $types, $values);
    if (!$stmt->execute()) throw new Exception('บันทึกคำขอไม่สำเร็จ: ' . $stmt->error);
    $requestId = (int)$stmt->insert_id;
    $stmt->close();

    $requestNo = 'VR-' . ((int)date('Y') + 543) . '-' . str_pad($requestId, 5, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("UPDATE vehicle_requests SET request_no=? WHERE id=?");
    $stmt->bind_param('si', $requestNo, $requestId);
    $stmt->execute();
    $stmt->close();

    if ($companionRows) {
        $stmt = $conn->prepare("INSERT INTO vehicle_request_companions(request_id,user_id,person_name,position_name,level_name) VALUES(?,?,?,?,?)");
        foreach ($companionRows as $p) {
            $pid = (int)$p['user_id'];
            $name = $p['name'];
            $pos = $p['position'];
            $level = $p['level'];
            $stmt->bind_param('iisss', $requestId, $pid, $name, $pos, $level);
            $stmt->execute();
        }
        $stmt->close();
    }

    $conn->commit();
    vehicle_add_log($requestId, 'created', '', $status, 'สร้างคำขอใช้รถ');
} catch (Exception $e) {
    $conn->rollback();
    if ($document !== '' && is_file(__DIR__ . '/' . $document)) @unlink(__DIR__ . '/' . $document);
    vehicle_flash('danger', $e->getMessage());
    vehicle_redirect('index.php?page=add&type=' . urlencode($type));
}

$typeMeta = vehicle_request_type_meta($type);
$supervisorLine = $supervisorName !== '' ? $supervisorName : 'ยังไม่ได้กำหนด';
$vehicleLine = $privateReg !== '' ? ('รถยนต์ส่วนตัว ' . $privateReg) : ('รถโรงพยาบาล ' . $hospitalReg);
$message = "🚐 คำขอใช้รถ โรงพยาบาลภักดีชุมพล\n"
         . "━━━━━━━━━━━━━━━━\n"
         . "เลขที่: " . $requestNo . "\n"
         . "ประเภท: " . $typeMeta[0] . "\n"
         . "ผู้ร้องขอ: " . $requesterName . "\n"
         . "แผนก: " . ($deptName !== '' ? $deptName : '-') . "\n"
         . "ความเร่งด่วน: " . $urgency . "\n"
         . "รถ: " . $vehicleLine . "\n"
         . "สถานที่: " . $location . "\n"
         . "วันที่: " . vehicle_format_thai_date($useDate) . " เวลา " . substr($useTime,0,5) . " น.\n"
         . "เหตุผล: " . $reason . "\n"
         . "พนักงานขับ: " . ($driverName !== '' ? $driverName : 'รอจัดพนักงานขับ') . "\n"
         . "หัวหน้างานรับรอง: " . $supervisorLine . "\n"
         . "━━━━━━━━━━━━━━━━";

$line = vehicle_send_line($message, 'vehicle_request_created');
$lineStatus = !empty($line['ok']) ? 'success' : 'failed';
$lineRaw = isset($line['raw']) ? $line['raw'] : (isset($line['error']) ? $line['error'] : '');
$stmt = $conn->prepare("UPDATE vehicle_requests SET line_notify_status=?,line_notify_response=? WHERE id=?");
if ($stmt) {
    $stmt->bind_param('ssi', $lineStatus, $lineRaw, $requestId);
    $stmt->execute();
    $stmt->close();
}

if ($lineStatus === 'success') {
    vehicle_flash('success', 'บันทึกคำขอ ' . $requestNo . ' และส่งแจ้งเตือน LINE เรียบร้อยแล้ว');
} else {
    vehicle_flash('warning', 'บันทึกคำขอ ' . $requestNo . ' แล้ว แต่ส่งแจ้งเตือน LINE ไม่สำเร็จ ข้อมูลยังถูกบันทึกไว้ครบถ้วน');
}
vehicle_redirect('detail.php?id=' . $requestId);
