<?php
require_once '../../config.php';
require_once __DIR__ . '/property_helpers.php';
property_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add.php');
    exit;
}

if (!property_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
    property_flash('error', 'คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
    header('Location: add.php');
    exit;
}

$assetNo = trim(isset($_POST['asset_no']) ? $_POST['asset_no'] : '');
$budgetYear = trim(isset($_POST['budget_year']) ? $_POST['budget_year'] : '');
$propertyName = trim(isset($_POST['property_name']) ? $_POST['property_name'] : '');
$propertyType = trim(isset($_POST['property_type']) ? $_POST['property_type'] : '');
$category = trim(isset($_POST['category']) ? $_POST['category'] : '');
$brand = trim(isset($_POST['brand']) ? $_POST['brand'] : '');
$model = trim(isset($_POST['model']) ? $_POST['model'] : '');
$serialNo = trim(isset($_POST['serial_no']) ? $_POST['serial_no'] : '');
$department = trim(isset($_POST['department']) ? $_POST['department'] : '');
$departmentUnit = trim(isset($_POST['department_unit']) ? $_POST['department_unit'] : '');
$location = trim(isset($_POST['location']) ? $_POST['location'] : '');
$responsible = trim(isset($_POST['responsible_person']) ? $_POST['responsible_person'] : '');
$purchaseDate = property_nullable_date(isset($_POST['purchase_date']) ? $_POST['purchase_date'] : '');
$warrantyDate = property_nullable_date(isset($_POST['warranty_date']) ? $_POST['warranty_date'] : '');
$vendor = trim(isset($_POST['vendor']) ? $_POST['vendor'] : '');
$price = isset($_POST['price']) ? (float)str_replace(',', '', $_POST['price']) : 0.0;
$riskLevel = trim(isset($_POST['risk_level']) ? $_POST['risk_level'] : '');
$withdrawStatus = trim(isset($_POST['withdraw_status']) ? $_POST['withdraw_status'] : '');
$borrowDepartment = trim(isset($_POST['borrow_department']) ? $_POST['borrow_department'] : '');
$status = trim(isset($_POST['status']) ? $_POST['status'] : 'ใช้งาน');
$note = trim(isset($_POST['note']) ? $_POST['note'] : '');
$createdBy = (int)$_SESSION['user_id'];

if ($assetNo === '' || $propertyName === '' || $propertyType === '') {
    property_flash('error', 'กรุณากรอกเลขครุภัณฑ์ ชื่อครุภัณฑ์ และประเภทครุภัณฑ์ให้ครบ');
    header('Location: add.php');
    exit;
}
if (!in_array($status, array('ใช้งาน','ชำรุด','ส่งซ่อม','จำหน่าย'), true)) {
    $status = 'ใช้งาน';
}
if ($price < 0) {
    $price = 0;
}

$dup = $conn->prepare('SELECT id FROM properties WHERE asset_no=? LIMIT 1');
$dup->bind_param('s', $assetNo);
$dup->execute();
$dup->store_result();
if ($dup->num_rows > 0) {
    $dup->close();
    property_flash('error', 'เลขครุภัณฑ์นี้มีอยู่ในระบบแล้ว กรุณาตรวจสอบก่อนบันทึก');
    header('Location: add.php');
    exit;
}
$dup->close();

$image = '';
try {
    $image = property_upload_image(isset($_FILES['image']) ? $_FILES['image'] : array('error' => UPLOAD_ERR_NO_FILE), '');

    $sql = "INSERT INTO properties (asset_no,budget_year,property_name,property_type,category,brand,model,serial_no,department,department_unit,location,responsible_person,purchase_date,warranty_date,vendor,price,risk_level,withdraw_status,borrow_department,status,image,note,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('ไม่สามารถเตรียมคำสั่งฐานข้อมูลได้: ' . $conn->error);
    }
    $types = 'sssssssssssssssdssssssi';
    $stmt->bind_param($types, $assetNo, $budgetYear, $propertyName, $propertyType, $category, $brand, $model, $serialNo, $department, $departmentUnit, $location, $responsible, $purchaseDate, $warrantyDate, $vendor, $price, $riskLevel, $withdrawStatus, $borrowDepartment, $status, $image, $note, $createdBy);
    if (!$stmt->execute()) {
        throw new Exception('บันทึกข้อมูลไม่สำเร็จ: ' . $stmt->error);
    }
    $newId = $stmt->insert_id;
    $stmt->close();

    property_flash('success', 'เพิ่มครุภัณฑ์เลขที่ ' . $assetNo . ' เรียบร้อยแล้ว');
    header('Location: detail.php?id=' . (int)$newId);
    exit;
} catch (Exception $e) {
    if ($image) {
        property_delete_image($image);
    }
    property_flash('error', $e->getMessage());
    header('Location: add.php');
    exit;
}
