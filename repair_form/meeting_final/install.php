<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
if (!is_admin_user()) { http_response_code(403); die('403 Forbidden: ติดตั้งฐานข้อมูลได้เฉพาะ Admin'); }

$messages=array();
$errors=array();

function installer_column_exists($conn,$table,$column){
    return meeting_column_exists($conn,$table,$column);
}
function installer_add_column($conn,$table,$column,$definition,&$messages,&$errors){
    if(installer_column_exists($conn,$table,$column)){ $messages[]="พบคอลัมน์ {$table}.{$column} แล้ว"; return; }
    if($conn->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}")){
        $messages[]="เพิ่มคอลัมน์ {$table}.{$column} สำเร็จ";
    }else{
        $errors[]="เพิ่ม {$table}.{$column} ไม่สำเร็จ: ".$conn->error;
    }
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!meeting_verify_csrf(isset($_POST['csrf_token'])?$_POST['csrf_token']:'')){ die('CSRF validation failed'); }

    $sqlRooms="
    CREATE TABLE IF NOT EXISTS meeting_rooms (
      id INT NOT NULL AUTO_INCREMENT,
      room_name VARCHAR(150) NOT NULL,
      room_code VARCHAR(50) DEFAULT NULL,
      capacity INT NOT NULL DEFAULT 0,
      location VARCHAR(255) DEFAULT NULL,
      equipment TEXT NULL,
      color VARCHAR(20) DEFAULT '#0f766e',
      status ENUM('active','inactive') NOT NULL DEFAULT 'active',
      created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY idx_meeting_rooms_status(status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if($conn->query($sqlRooms)){ $messages[]='ตรวจสอบตาราง meeting_rooms เรียบร้อย'; }else{ $errors[]='meeting_rooms: '.$conn->error; }

    $sqlBookings="
    CREATE TABLE IF NOT EXISTS meeting_bookings (
      id INT NOT NULL AUTO_INCREMENT,
      user_id INT NULL,
      room_id INT NOT NULL,
      meeting_title VARCHAR(255) NOT NULL,
      requester_name VARCHAR(150) NOT NULL,
      department VARCHAR(150) DEFAULT NULL,
      phone VARCHAR(50) DEFAULT NULL,
      meeting_date DATE NOT NULL,
      start_time TIME NOT NULL,
      end_time TIME NOT NULL,
      attendees INT NOT NULL DEFAULT 1,
      detail TEXT NULL,
      meeting_platform VARCHAR(30) DEFAULT NULL,
      meeting_url VARCHAR(1000) DEFAULT NULL,
      status ENUM('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
      approved_by VARCHAR(150) DEFAULT NULL,
      approved_at DATETIME DEFAULT NULL,
      created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY idx_meeting_room_date(room_id,meeting_date),
      KEY idx_meeting_status(status),
      KEY idx_meeting_user(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if($conn->query($sqlBookings)){ $messages[]='ตรวจสอบตาราง meeting_bookings เรียบร้อย'; }else{ $errors[]='meeting_bookings: '.$conn->error; }

    if(meeting_table_exists($conn,'meeting_bookings')){
        installer_add_column($conn,'meeting_bookings','user_id',"INT NULL AFTER `id`",$messages,$errors);
        installer_add_column($conn,'meeting_bookings','meeting_platform',"VARCHAR(30) NULL AFTER `detail`",$messages,$errors);
        installer_add_column($conn,'meeting_bookings','meeting_url',"VARCHAR(1000) NULL AFTER `meeting_platform`",$messages,$errors);
        installer_add_column($conn,'meeting_bookings','approved_by',"VARCHAR(150) NULL AFTER `status`",$messages,$errors);
        installer_add_column($conn,'meeting_bookings','approved_at',"DATETIME NULL AFTER `approved_by`",$messages,$errors);
        installer_add_column($conn,'meeting_bookings','updated_at',"TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",$messages,$errors);
    }

    $defaultRooms=array(
        array('ห้องประชุมภูไท','PHUTHAI',30,'อาคารอำนวยการ ชั้น 2','โปรเจคเตอร์, จอภาพ, ไมโครโฟน, เครื่องเสียง, Wi-Fi','#0f766e'),
        array('ห้องประชุมภูทยา','PHUTAYA',20,'อาคารผู้ป่วยนอก ชั้น 2','จอภาพ, ไมโครโฟน, เครื่องเสียง, Wi-Fi','#2563eb'),
        array('ห้องประชุมพุทธา','PHUTTHA',15,'อาคารอำนวยการ ชั้น 1','โปรเจคเตอร์, จอภาพ, Wi-Fi','#7c3aed'),
        array('ห้องประชุมหัวหน้าฝ่ายการ','HEAD',12,'อาคารอำนวยการ ชั้น 2','จอภาพ, ไมโครโฟน, Wi-Fi','#b45309')
    );
    foreach($defaultRooms as $room){
        $stmt=$conn->prepare("SELECT id FROM meeting_rooms WHERE room_name=? LIMIT 1");
        $stmt->bind_param('s',$room[0]); $stmt->execute(); $stmt->store_result(); $exists=$stmt->num_rows>0; $stmt->close();
        if(!$exists){
            $stmt=$conn->prepare("INSERT INTO meeting_rooms(room_name,room_code,capacity,location,equipment,color,status) VALUES(?,?,?,?,?,?,'active')");
            $stmt->bind_param('ssisss',$room[0],$room[1],$room[2],$room[3],$room[4],$room[5]);
            if($stmt->execute()) $messages[]='เพิ่ม '.$room[0].' สำเร็จ'; else $errors[]='เพิ่ม '.$room[0].' ไม่สำเร็จ: '.$stmt->error;
            $stmt->close();
        }else{
            $messages[]='พบ '.$room[0].' อยู่แล้ว (ไม่เขียนทับข้อมูลเดิม)';
        }
    }

    if(empty($errors)){ $messages[]='ติดตั้ง/อัปเดตระบบห้องประชุมเรียบร้อยแล้ว'; }
}
$ready=meeting_schema_ready($conn);
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ติดตั้งระบบห้องประชุม</title><link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="assets/meeting.css?v=20260831"></head>
<body class="meeting-body">
<header class="mh-topbar"><div class="mh-brand"><div class="mh-brand-mark">PDC</div><div><div class="mh-brand-title">ติดตั้งระบบห้องประชุม</div><div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล</div></div></div><a class="mh-btn mh-btn-light" href="index.php">← Dashboard</a></header>
<main class="mh-wrap mh-install-wrap">
<section class="mh-page-title"><div><span class="mh-kicker">DATABASE INSTALLER</span><h1>ติดตั้ง / อัปเดตฐานข้อมูล</h1><p>เพิ่มฟิลด์ระบบประชุมออนไลน์และตรวจสอบห้องประชุมเริ่มต้น โดยไม่ลบรายการจองเดิม</p></div><span class="mh-status <?= $ready?'status-approved':'status-pending' ?>"><?= $ready?'พร้อมใช้งาน':'ต้องอัปเดต' ?></span></section>

<?php if($messages):?><div class="mh-panel"><h2>ผลการดำเนินการ</h2><ul class="mh-install-list"><?php foreach($messages as $m):?><li class="ok">✓ <?= meeting_e($m) ?></li><?php endforeach;?></ul></div><?php endif;?>
<?php if($errors):?><div class="mh-alert mh-alert-danger"><div><strong>พบข้อผิดพลาด</strong><?php foreach($errors as $e):?><span><?= meeting_e($e) ?></span><?php endforeach;?></div></div><?php endif;?>

<section class="mh-panel">
<h2>สิ่งที่ระบบจะติดตั้ง</h2>
<div class="mh-info-grid">
<div><small>ตาราง</small><strong>meeting_rooms</strong><span>ข้อมูลห้องประชุมและสถานะใช้งาน</span></div>
<div><small>ตาราง</small><strong>meeting_bookings</strong><span>รายการจอง วันที่ เวลา และการอนุมัติ</span></div>
<div><small>เพิ่มฟิลด์</small><strong>meeting_platform</strong><span>Zoom / Meet / Teams / Webex / LINE</span></div>
<div><small>เพิ่มฟิลด์</small><strong>meeting_url</strong><span>ลิงก์เข้าห้องประชุมออนไลน์</span></div>
<div><small>เพิ่มฟิลด์</small><strong>user_id</strong><span>ผูกผู้จองกับบัญชีผู้ใช้งาน</span></div>
<div><small>ข้อมูลเริ่มต้น</small><strong>4 ห้องประชุม</strong><span>เพิ่มเฉพาะห้องที่ยังไม่มี ไม่ลบห้องเดิม</span></div>
</div>
<form method="post" class="mh-form-actions"><input type="hidden" name="csrf_token" value="<?= meeting_e(meeting_csrf_token()) ?>"><button class="mh-btn mh-btn-primary" type="submit">ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้</button><?php if($ready):?><a class="mh-btn mh-btn-outline" href="index.php">เข้าสู่ระบบห้องประชุม</a><?php endif;?></form>
</section>
</main></body></html>
