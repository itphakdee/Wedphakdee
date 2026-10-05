<?php
/*
 * บันทึกรับงานซ่อม
 * Path:
 * repair_form/computer/savejob/save_receive_job.php
 *
 * เมื่อบันทึกแล้ว:
 * 1) INSERT ลง repair_receive_jobs
 * 2) UPDATE repair_jobs.repair_status
 * 3) รายการจะหายจาก indexrepairlist.php เพราะ index ใช้ NOT EXISTS
 */

include("../../../config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../login.php");
    exit();
}

require_once __DIR__ . "/../../admin/permissions_helper.php";

/* รับงานได้เฉพาะ Admin ตามระบบสิทธิ์ที่ตั้งไว้ */
if (!is_admin_user()) {
    http_response_code(403);
    exit("403 Forbidden: การรับงานซ่อมคอมพิวเตอร์อนุญาตเฉพาะ Admin");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../indexrepairlist.php");
    exit();
}

$jobId = (int)($_POST["id"] ?? 0);
$status = trim($_POST["status"] ?? "กำลังดำเนินการ");
$repairType = trim($_POST["repair_type"] ?? "");
$deviceName = trim($_POST["device_name"] ?? "");
$price = (float)($_POST["price"] ?? 0);
$qty = (int)($_POST["qty"] ?? 1);
$totalPrice = (float)($_POST["total_price"] ?? ($price * $qty));
$technicianNote = trim($_POST["technician_note"] ?? "");

$technicianId = (int)$_SESSION["user_id"];
$technicianName = trim((string)($_SESSION["fullname"] ?? ""));

if ($jobId <= 0) {
    die("ไม่พบเลขที่งาน");
}

if (!in_array($status, ["กำลังดำเนินการ", "เสร็จสิ้น"], true)) {
    die("สถานะงานไม่ถูกต้อง");
}

if (!in_array($repairType, ["ซ่อมเอง", "ส่งซ่อมข้างนอก"], true)) {
    die("ประเภทการซ่อมไม่ถูกต้อง");
}

if ($qty < 1) {
    $qty = 1;
}

if ($price < 0) {
    $price = 0;
}

$totalPrice = $price * $qty;

/* ตรวจว่างานมีอยู่และยังไม่ถูกรับงานมาก่อน */
$stmt = $conn->prepare("SELECT id FROM repair_jobs WHERE id=? LIMIT 1");
if (!$stmt) {
    die("SQL ERROR: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}
$stmt->bind_param("i", $jobId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    die("ไม่พบรายการแจ้งซ่อม #" . $jobId);
}
$stmt->close();

$dup = $conn->prepare("SELECT id FROM repair_receive_jobs WHERE repair_job_id=? LIMIT 1");
if (!$dup) {
    die("SQL ERROR: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}
$dup->bind_param("i", $jobId);
$dup->execute();
$dup->store_result();

if ($dup->num_rows > 0) {
    $dup->close();
    header("Location: ../indexrepairlist.php?saved=already");
    exit();
}
$dup->close();

/* แปลงสถานะรับงานให้ตรงกับ repair_jobs.repair_status */
$repairStatus = ($status === "เสร็จสิ้น") ? "completed" : "in_progress";

$conn->begin_transaction();

try {
    /* 1. เก็บรายละเอียดการรับงาน */
    $insert = $conn->prepare("\n        INSERT INTO repair_receive_jobs\n        (repair_job_id, technician_id, technician_name, status, repair_type, device_name, price, qty, total_price, technician_note)\n        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\n    ");

    if (!$insert) {
        throw new Exception($conn->error);
    }

    $insert->bind_param(
        "iissssdiis",
        $jobId,
        $technicianId,
        $technicianName,
        $status,
        $repairType,
        $deviceName,
        $price,
        $qty,
        $totalPrice,
        $technicianNote
    );

    if (!$insert->execute()) {
        throw new Exception($insert->error);
    }
    $insert->close();

    /* 2. อัปเดตสถานะงานหลัก */
    $update = $conn->prepare("\n        UPDATE repair_jobs\n        SET repair_status=?\n        WHERE id=?\n    ");

    if (!$update) {
        throw new Exception($conn->error);
    }

    $update->bind_param("si", $repairStatus, $jobId);

    if (!$update->execute()) {
        throw new Exception($update->error);
    }
    $update->close();

    $conn->commit();

    header("Location: ../indexrepairlist.php?saved=1&job_id=" . urlencode((string)$jobId));
    exit();

} catch (Throwable $e) {
    $conn->rollback();

    http_response_code(500);
    echo "<div style='font-family:Tahoma;padding:30px'>";
    echo "<h3>บันทึกรับงานไม่สำเร็จ</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
    echo "<a href='../receive_job.php?id=" . (int)$jobId . "'>← กลับ</a>";
    echo "</div>";
}
?>
