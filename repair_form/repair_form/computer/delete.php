<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../admin/permissions_helper.php";
if (!is_admin_user()) {
    http_response_code(403);
    exit("403 Forbidden: การลบงานซ่อมคอมพิวเตอร์อนุญาตเฉพาะ Admin");
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) die('รหัสงานไม่ถูกต้อง');

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("DELETE FROM repair_receive_jobs WHERE repair_job_id=?");
    if ($stmt) { $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close(); }

    $stmt = $conn->prepare("DELETE FROM repair_jobs WHERE id=?");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('i',$id);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    $affected=$stmt->affected_rows;
    $stmt->close();

    if ($affected < 1) throw new Exception('ไม่พบรายการที่จะลบ');

    $conn->commit();
    header('Location: indexrepairlist.php?deleted=1');
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    die('ลบข้อมูลไม่สำเร็จ: '.htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8'));
}
