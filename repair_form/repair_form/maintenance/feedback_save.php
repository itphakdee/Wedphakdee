<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_permission('view');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

if ($requestId <= 0 || $currentUserId <= 0) {
    header('Location: index.php');
    exit;
}

if (!maintenance_verify_csrf($_POST['csrf_token'] ?? '')) {
    maintenance_flash('error', 'ไม่สามารถตรวจสอบความปลอดภัยของแบบฟอร์ม Feedback ได้');
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}

if (!maintenance_ensure_feedback_table()) {
    maintenance_flash('error', 'ยังไม่สามารถเปิดฐานข้อมูล Feedback ได้ กรุณาติดต่อผู้ดูแลระบบ');
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}

$score = (int)($_POST['score'] ?? 0);
$feedbackText = trim((string)($_POST['feedback_text'] ?? ''));
$allowedScores = [5, 3, 1];

if (!in_array($score, $allowedScores, true)) {
    maintenance_flash('error', 'กรุณาเลือกคะแนน Feedback เป็น 5, 3 หรือ 1 คะแนน');
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}

if (function_exists('mb_strlen') ? mb_strlen($feedbackText, 'UTF-8') > 2000 : strlen($feedbackText) > 6000) {
    maintenance_flash('error', 'ความคิดเห็น Feedback ยาวเกินกำหนด');
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}

// สำคัญ: ตรวจสอบเจ้าของงานจาก user_id ในฐานข้อมูล ไม่เชื่อค่า sender_name หรือ hidden field
$stmtRequest = $conn->prepare("SELECT id, user_id, status FROM maintenance_requests WHERE id=? LIMIT 1");
if (!$stmtRequest) {
    maintenance_flash('error', 'ไม่สามารถตรวจสอบเจ้าของงานได้: ' . $conn->error);
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}
$stmtRequest->bind_param('i', $requestId);
$stmtRequest->execute();
$request = $stmtRequest->get_result()->fetch_assoc();
$stmtRequest->close();

if (!$request) {
    http_response_code(404);
    die('404: ไม่พบรายการงานซ่อมบำรุง');
}

if ((int)$request['user_id'] !== $currentUserId) {
    maintenance_flash('error', 'คุณสามารถให้ Feedback ได้เฉพาะงานที่คุณเป็นผู้แจ้งเท่านั้น');
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}

$stmtExisting = $conn->prepare("SELECT id, user_id FROM maintenance_feedback WHERE request_id=? LIMIT 1");
if (!$stmtExisting) {
    maintenance_flash('error', 'ไม่สามารถตรวจสอบ Feedback เดิมได้: ' . $conn->error);
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}
$stmtExisting->bind_param('i', $requestId);
$stmtExisting->execute();
$existing = $stmtExisting->get_result()->fetch_assoc();
$stmtExisting->close();

if ($existing && (int)$existing['user_id'] !== $currentUserId) {
    maintenance_flash('error', 'Feedback ของงานนี้เป็นของบัญชีผู้แจ้งงานเดิม คุณไม่มีสิทธิ์แก้ไข');
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}

// การให้คะแนนครั้งแรกเปิดเมื่อสถานะเสร็จสิ้นเท่านั้น
// ถ้ามี Feedback เดิมแล้ว ให้เจ้าของงานแก้ไขได้แม้ภายหลังสถานะถูกเปลี่ยนโดยผู้ดูแล
if (!$existing && $request['status'] !== 'completed') {
    maintenance_flash('error', 'สามารถให้ Feedback ได้หลังจากงานถูกบันทึกสถานะเป็น “เสร็จสิ้น” แล้วเท่านั้น');
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}

if ($existing) {
    $stmt = $conn->prepare("UPDATE maintenance_feedback
        SET score=?, feedback_text=?, updated_at=CURRENT_TIMESTAMP
        WHERE request_id=? AND user_id=?");
    $successMessage = 'แก้ไข Feedback งาน #' . $requestId . ' เรียบร้อยแล้ว';
} else {
    $stmt = $conn->prepare("INSERT INTO maintenance_feedback (request_id, user_id, score, feedback_text)
        VALUES (?, ?, ?, ?)");
    $successMessage = 'ส่ง Feedback งาน #' . $requestId . ' เรียบร้อยแล้ว';
}

if (!$stmt) {
    maintenance_flash('error', 'ไม่สามารถเตรียมคำสั่งบันทึก Feedback ได้: ' . $conn->error);
    header('Location: detail.php?id=' . $requestId . '#feedback');
    exit;
}

if ($existing) {
    $stmt->bind_param('isii', $score, $feedbackText, $requestId, $currentUserId);
} else {
    $stmt->bind_param('iiis', $requestId, $currentUserId, $score, $feedbackText);
}

if ($stmt->execute()) {
    maintenance_flash('success', $successMessage);
} else {
    maintenance_flash('error', 'บันทึก Feedback ไม่สำเร็จ: ' . $stmt->error);
}
$stmt->close();

header('Location: detail.php?id=' . $requestId . '#feedback');
exit;
