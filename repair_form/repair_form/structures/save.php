<?php
session_start();
require_once "../../config.php";
date_default_timezone_set("Asia/Bangkok");

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
}

function backToIndex(string $status, string $message): void
{
    header(
        "Location: index.php?status=" .
            urlencode($status) .
            "&message=" .
            urlencode($message)
    );
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    backToIndex("error", "ไม่อนุญาตให้เรียกไฟล์นี้โดยตรง");
}

$sessionToken = $_SESSION["structure_csrf"] ?? "";
$postToken    = $_POST["csrf_token"] ?? "";

if (
    $sessionToken === "" ||
    $postToken === "" ||
    !hash_equals($sessionToken, $postToken)
) {
    backToIndex("error", "เซสชันหมดอายุ กรุณาเปิดหน้าเพิ่มอาคารใหม่");
}

$buildingName = trim($_POST["building_name"] ?? "");
$budgetType   = trim($_POST["budget_type"] ?? "");
$amountInput  = trim($_POST["amount"] ?? "");
$startDate    = trim($_POST["start_date"] ?? "");

if ($buildingName === "") {
    backToIndex("error", "กรุณากรอกชื่ออาคาร");
}

if ($budgetType === "") {
    backToIndex("error", "กรุณาเลือกประเภทงบประมาณ");
}

if ($startDate === "") {
    backToIndex("error", "กรุณาเลือกวันที่เริ่มสร้าง");
}

$dateObject = DateTime::createFromFormat("Y-m-d", $startDate);

if (
    !$dateObject ||
    $dateObject->format("Y-m-d") !== $startDate
) {
    backToIndex("error", "รูปแบบวันที่ไม่ถูกต้อง");
}

$amount = ($amountInput === "") ? 0 : (float)$amountInput;

if ($amount < 0) {
    backToIndex("error", "จำนวนเงินไม่ถูกต้อง");
}

$today = new DateTime("today");

if ($dateObject > $today) {
    backToIndex("error", "วันที่เริ่มสร้างต้องไม่มากกว่าวันที่ปัจจุบัน");
}

$age = (int)$dateObject->diff($today)->y;

$sql = "
    INSERT INTO buildings
    (
        building_name,
        amount,
        start_date,
        age,
        budget_type,
        created_at,
        updated_at
    )
    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    backToIndex(
        "error",
        "เตรียมคำสั่ง SQL ไม่สำเร็จ: " . $conn->error
    );
}

/*
 * s = building_name
 * d = amount
 * s = start_date
 * i = age
 * s = budget_type
 */
$stmt->bind_param(
    "sdsis",
    $buildingName,
    $amount,
    $startDate,
    $age,
    $budgetType
);

if (!$stmt->execute()) {

    $error = $stmt->error;
    $stmt->close();

    backToIndex(
        "error",
        "บันทึกข้อมูลไม่สำเร็จ: " . $error
    );
}

$newId = $stmt->insert_id;
$stmt->close();

backToIndex(
    "success",
    "เพิ่มข้อมูลอาคารเรียบร้อยแล้ว เลขที่รายการ #" . $newId
);
