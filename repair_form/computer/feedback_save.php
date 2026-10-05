<?php
include("../../config.php");
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION["user_id"])) { header("Location: ../../login.php"); exit(); }

$user_id = (int)$_SESSION["user_id"];
$job_id = (int)($_POST["job_id"] ?? 0);
$score = isset($_POST["feedback_score"]) ? (int)$_POST["feedback_score"] : -1;
$comment = trim($_POST["feedback_comment"] ?? "");

if ($job_id <= 0 || !in_array($score,array(5,3,0),true)) {
    header("Location: my_jobs.php?feedback=error"); exit();
}

$stmt = $conn->prepare("SELECT id FROM repair_jobs WHERE id=? AND user_id=? AND repair_status='completed' LIMIT 1");
if (!$stmt) die("SQL ERROR: ".htmlspecialchars($conn->error));
$stmt->bind_param("ii",$job_id,$user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    http_response_code(403);
    die("ไม่มีสิทธิ์ประเมินงานนี้ หรือ งานยังไม่เสร็จสิ้น");
}
$stmt->close();

$stmt = $conn->prepare("UPDATE repair_jobs SET feedback_score=?, feedback_comment=?, feedback_at=NOW() WHERE id=? AND user_id=?");
if (!$stmt) die("SQL ERROR: ".htmlspecialchars($conn->error));
$stmt->bind_param("isii",$score,$comment,$job_id,$user_id);

if (!$stmt->execute()) {
    $err=$stmt->error;
    $stmt->close();
    die("บันทึก Feedback ไม่สำเร็จ: ".htmlspecialchars($err));
}
$stmt->close();

header("Location: my_jobs.php?feedback=success");
exit();
?>
