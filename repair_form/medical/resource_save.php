<?php
require_once __DIR__ . '/config_medical.php';
medical_require_permission('manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !medical_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
    http_response_code(400);
    die('Bad Request');
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$returnPage = isset($_POST['return_page']) ? max(1, (int)$_POST['return_page']) : 1;
$isEquipmentAction = strpos($action, 'equipment_') === 0;
$returnUrl = $isEquipmentAction ? ('resources.php?equipment_page=' . $returnPage . '#equipment-register') : 'resources.php#technician-register';

if ($action === 'equipment_add') {
    $asset = trim(isset($_POST['asset_code']) ? $_POST['asset_code'] : '');
    $name = trim(isset($_POST['equipment_name']) ? $_POST['equipment_name'] : '');
    $received = trim(isset($_POST['received_date']) ? $_POST['received_date'] : '');
    $priceRaw = trim(isset($_POST['price']) ? $_POST['price'] : '');
    $category = trim(isset($_POST['category']) ? $_POST['category'] : '');
    $brand = trim(isset($_POST['brand']) ? $_POST['brand'] : '');
    $model = trim(isset($_POST['model']) ? $_POST['model'] : '');
    $serial = trim(isset($_POST['serial_no']) ? $_POST['serial_no'] : '');
    $dept = trim(isset($_POST['department']) ? $_POST['department'] : '');
    $location = trim(isset($_POST['location']) ? $_POST['location'] : '');

    if ($name === '') {
        medical_flash('error', 'กรุณาระบุชื่อเครื่องมือ');
        header('Location:resources.php');
        exit;
    }
    if ($received !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $received)) {
        medical_flash('error', 'รูปแบบวันที่เข้ารับไม่ถูกต้อง');
        header('Location:resources.php');
        exit;
    }
    if ($priceRaw !== '' && (!is_numeric($priceRaw) || (float)$priceRaw < 0)) {
        medical_flash('error', 'กรุณาระบุราคาเป็นตัวเลขตั้งแต่ 0 ขึ้นไป');
        header('Location:resources.php');
        exit;
    }

    $display = ($asset !== '' ? $asset . ' · ' : '') . $name;
    $receivedDb = $received !== '' ? $received : null;
    $priceDb = $priceRaw !== '' ? number_format((float)$priceRaw, 2, '.', '') : '';
    $stmt = $conn->prepare("INSERT INTO medical_equipment(asset_code,display_name,equipment_name,received_date,price,category,brand,model,serial_no,department,location,status) VALUES(?,?,?,?,NULLIF(?,''),?,?,?,?,?,?,'active')");
    $stmt->bind_param('sssssssssss', $asset, $display, $name, $receivedDb, $priceDb, $category, $brand, $model, $serial, $dept, $location);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    medical_flash($ok ? 'success' : 'error', $ok ? 'เพิ่มเครื่องมือและบันทึกราคาเรียบร้อย' : 'เพิ่มไม่สำเร็จ: ' . $err);
}
elseif ($action === 'equipment_update') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $asset = trim(isset($_POST['asset_code']) ? $_POST['asset_code'] : '');
    $name = trim(isset($_POST['equipment_name']) ? $_POST['equipment_name'] : '');
    $received = trim(isset($_POST['received_date']) ? $_POST['received_date'] : '');
    $priceRaw = trim(isset($_POST['price']) ? $_POST['price'] : '');
    $category = trim(isset($_POST['category']) ? $_POST['category'] : '');
    $brand = trim(isset($_POST['brand']) ? $_POST['brand'] : '');
    $model = trim(isset($_POST['model']) ? $_POST['model'] : '');
    $serial = trim(isset($_POST['serial_no']) ? $_POST['serial_no'] : '');
    $dept = trim(isset($_POST['department']) ? $_POST['department'] : '');
    $location = trim(isset($_POST['location']) ? $_POST['location'] : '');

    if ($id <= 0 || $name === '') {
        medical_flash('error', 'ข้อมูลสำหรับแก้ไขไม่ครบถ้วน');
        header('Location:resources.php');
        exit;
    }
    if ($received !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $received)) {
        medical_flash('error', 'รูปแบบวันที่เข้ารับไม่ถูกต้อง');
        header('Location:resource_edit.php?type=equipment&id=' . $id . '&return_page=' . $returnPage);
        exit;
    }
    if ($priceRaw !== '' && (!is_numeric($priceRaw) || (float)$priceRaw < 0)) {
        medical_flash('error', 'กรุณาระบุราคาเป็นตัวเลขตั้งแต่ 0 ขึ้นไป');
        header('Location:resource_edit.php?type=equipment&id=' . $id . '&return_page=' . $returnPage);
        exit;
    }

    $display = ($asset !== '' ? $asset . ' · ' : '') . $name;
    $receivedDb = $received !== '' ? $received : null;
    $priceDb = $priceRaw !== '' ? number_format((float)$priceRaw, 2, '.', '') : '';

    $stmt = $conn->prepare("UPDATE medical_equipment SET asset_code=?,display_name=?,equipment_name=?,received_date=?,price=NULLIF(?,''),category=?,brand=?,model=?,serial_no=?,department=?,location=? WHERE id=?");
    $stmt->bind_param('sssssssssssi', $asset, $display, $name, $receivedDb, $priceDb, $category, $brand, $model, $serial, $dept, $location, $id);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();

    medical_flash($ok ? 'success' : 'error', $ok ? 'บันทึกการแก้ไขเครื่องมือแพทย์เรียบร้อย' : 'แก้ไขไม่สำเร็จ: ' . $err);
}
elseif ($action === 'technician_add') {
    $name = trim(isset($_POST['fullname']) ? $_POST['fullname'] : '');
    $sp = trim(isset($_POST['specialty']) ? $_POST['specialty'] : '');
    $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    if ($name === '') {
        medical_flash('error', 'กรุณาระบุชื่อ');
        header('Location:resources.php');
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO medical_technicians(fullname,specialty,phone,email,status) VALUES(?,?,?,?,'active')");
    $stmt->bind_param('ssss', $name, $sp, $phone, $email);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    medical_flash($ok ? 'success' : 'error', $ok ? 'เพิ่มรายชื่อเรียบร้อย' : 'เพิ่มไม่สำเร็จ: ' . $err);
}
elseif ($action === 'technician_update') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim(isset($_POST['fullname']) ? $_POST['fullname'] : '');
    $sp = trim(isset($_POST['specialty']) ? $_POST['specialty'] : '');
    $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');

    if ($id <= 0 || $name === '') {
        medical_flash('error', 'ข้อมูลสำหรับแก้ไขไม่ครบถ้วน');
        header('Location:resources.php');
        exit;
    }

    $stmt = $conn->prepare("UPDATE medical_technicians SET fullname=?,specialty=?,phone=?,email=? WHERE id=?");
    $stmt->bind_param('ssssi', $name, $sp, $phone, $email, $id);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();

    medical_flash($ok ? 'success' : 'error', $ok ? 'บันทึกการแก้ไขผู้รับผิดชอบเรียบร้อย' : 'แก้ไขไม่สำเร็จ: ' . $err);
}
elseif (in_array($action, array('equipment_toggle', 'technician_toggle'), true)) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $table = $action === 'equipment_toggle' ? 'medical_equipment' : 'medical_technicians';
    $stmt = $conn->prepare("UPDATE `$table` SET status=IF(status='active','inactive','active') WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    medical_flash('success', 'เปลี่ยนสถานะเรียบร้อย');
}
elseif (in_array($action, array('equipment_delete', 'technician_delete'), true)) {
    if (!medical_is_admin()) {
        http_response_code(403);
        die('Admin only');
    }
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $table = $action === 'equipment_delete' ? 'medical_equipment' : 'medical_technicians';
    $stmt = $conn->prepare("DELETE FROM `$table` WHERE id=?");
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    medical_flash($ok ? 'success' : 'error', $ok ? 'ลบข้อมูลเรียบร้อย' : 'ไม่สามารถลบได้ อาจมีรายการงานอ้างอิง: ' . $err);
}

header('Location:' . $returnUrl);
exit;
