<?php
include("../../config.php");
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION["user_id"])) { header("Location: ../../login.php"); exit(); }

$user_id = (int)$_SESSION["user_id"];
$fullname = trim((string)($_SESSION["fullname"] ?? ""));
$jobs = [];
$error = "";
$message = isset($_GET["feedback"]) && $_GET["feedback"] === "success" ? "บันทึก Feedback เรียบร้อยแล้ว" : "";
$messageType = $message ? "success" : "error";

$sql = "SELECT r.id,r.sender_name,r.department,r.repair_system,r.location,r.details,
               r.repair_status,r.priority,r.admin_note,r.created_at,r.updated_at,
               r.technician_id,r.feedback_score,r.feedback_comment,r.feedback_at,
               t.name AS technician_name
        FROM repair_jobs r
        LEFT JOIN technicians t ON t.id=r.technician_id
        WHERE r.user_id=?
        ORDER BY r.id DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = "ไม่สามารถโหลดข้อมูลได้ กรุณารัน SQL เพิ่มช่อง Feedback";
} else {
    $stmt->bind_param("i",$user_id);
    if ($stmt->execute()) {
        $stmt->store_result();
        $stmt->bind_result($id,$sender_name,$department,$repair_system,$location,$details,
            $repair_status,$priority,$admin_note,$created_at,$updated_at,$technician_id,
            $feedback_score,$feedback_comment,$feedback_at,$technician_name);
        while ($stmt->fetch()) {
            $jobs[] = array(
                "id"=>$id,"sender_name"=>$sender_name,"department"=>$department,
                "repair_system"=>$repair_system,"location"=>$location,"details"=>$details,
                "repair_status"=>$repair_status,"priority"=>$priority,"admin_note"=>$admin_note,
                "created_at"=>$created_at,"updated_at"=>$updated_at,"technician_id"=>$technician_id,
                "feedback_score"=>$feedback_score,"feedback_comment"=>$feedback_comment,
                "feedback_at"=>$feedback_at,"technician_name"=>$technician_name
            );
        }
    } else {
        $error = $stmt->error;
    }
    $stmt->close();
}

function h($v){ return htmlspecialchars((string)$v,ENT_QUOTES,"UTF-8"); }
function statusText($s){
    switch($s){case "pending":return "รอดำเนินการ";case "in_progress":return "กำลังดำเนินการ";case "completed":return "เสร็จสิ้น";case "cancelled":return "ยกเลิก";default:return $s?: "ไม่ระบุ";}
}
function statusClass($s){
    switch($s){case "pending":return "pending";case "in_progress":return "working";case "completed":return "completed";case "cancelled":return "cancelled";default:return "normal";}
}
function priorityText($p){
    switch($p){case "normal":return "ปกติ";case "urgent":return "ด่วน";case "emergency":return "ด่วนมาก";default:return $p?: "ไม่ระบุ";}
}
function priorityClass($p){
    switch($p){case "urgent":return "urgent";case "emergency":return "emergency";default:return "normal";}
}
function feedbackLabel($s){
    switch((int)$s){case 5:return "ดีมาก";case 3:return "พอใช้";case 0:return "แย่";default:return "ยังไม่ได้ประเมิน";}
}
function feedbackClass($s){
    switch((int)$s){case 5:return "feedback-green";case 3:return "feedback-blue";case 0:return "feedback-red";default:return "";}
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>📊 งานแจ้งซ่อมของฉัน</title>
<link rel="stylesheet" href="my_jobs.css">
</head>
<body>
<header class="topbar">
  <div class="top-title">🏢 ระบบแจ้งซ่อมและบริหารงาน</div>
  <div class="top-user">👤 <?=h($fullname)?></div>
</header>

<main class="container">
<section class="page-head">
  <div>
    <div class="page-title-row"><span class="page-icon">📊</span><h1>งานแจ้งซ่อมของฉัน</h1></div>
    <p>แสดงเฉพาะรายการแจ้งซ่อมของ User ID #<?=intval($user_id)?></p>
  </div>
  <div class="head-actions">
    <a href="indexrepairlist.php" class="btn btn-secondary">← กลับ</a>
    <a href="repair_form.php" class="btn btn-success">＋ แจ้งซ่อม</a>
  </div>
</section>

<?php if($message): ?><div class="alert success-alert"><?=h($message)?></div><?php endif; ?>
<?php if($error): ?><div class="alert error-alert"><?=h($error)?></div><?php endif; ?>

<section class="summary">
  <div class="summary-item"><span>ผู้ใช้งาน</span><strong><?=h($fullname)?></strong></div>
  <div class="summary-item"><span>User ID</span><strong>#<?=intval($user_id)?></strong></div>
  <div class="summary-item"><span>งานแจ้งซ่อมของฉัน</span><strong><?=number_format(count($jobs))?> รายการ</strong></div>
</section>

<?php if(!$error && !$jobs): ?>
<div class="empty"><div class="empty-icon">📋</div><h2>ยังไม่มีงานแจ้งซ่อมของคุณ</h2><p>เมื่อคุณส่งรายการแจ้งซ่อม รายการจะปรากฏในหน้านี้</p>
<a href="repair_form.php" class="btn btn-success">＋ แจ้งซ่อมรายการใหม่</a></div>
<?php elseif($jobs): ?>
<section class="job-list">
<?php foreach($jobs as $job): ?>
<article class="job-card">
  <div class="job-top">
    <div><span class="job-id">งาน #<?=intval($job["id"])?></span>
      <h2><?=h($job["repair_system"] ? $job["repair_system"] : "งานแจ้งซ่อมคอมพิวเตอร์")?></h2>
    </div>
    <div class="badges">
      <span class="badge <?=h(statusClass($job["repair_status"]))?>"><?=h(statusText($job["repair_status"]))?></span>
      <span class="badge priority-<?=h(priorityClass($job["priority"]))?>"><?=h(priorityText($job["priority"]))?></span>
    </div>
  </div>

  <div class="detail-grid">
    <div><label>ชื่อผู้แจ้ง</label><div><?=h($job["sender_name"])?></div></div>
    <div><label>แผนก</label><div><?=h($job["department"])?></div></div>
    <div><label>ระบบที่แจ้งซ่อม</label><div><?=h($job["repair_system"])?></div></div>
    <div><label>สถานที่พบปัญหา</label><div><?=h($job["location"])?></div></div>
    <div><label>ช่างผู้รับผิดชอบ</label><div><?=h($job["technician_name"] ? $job["technician_name"] : "ยังไม่ได้ระบุ")?></div></div>
    <div><label>สถานะ</label><div><span class="badge <?=h(statusClass($job["repair_status"]))?>"><?=h(statusText($job["repair_status"]))?></span></div></div>
    <div class="full"><label>รายละเอียดงาน</label><div class="description"><?=nl2br(h($job["details"]))?></div></div>
    <div><label>วันที่แจ้ง</label><div><?=h($job["created_at"])?></div></div>
    <div><label>แก้ไขล่าสุด</label><div><?=h($job["updated_at"])?></div></div>
    <?php if(!empty($job["admin_note"])): ?><div class="full note"><label>หมายเหตุจากช่าง / Admin</label><div><?=nl2br(h($job["admin_note"]))?></div></div><?php endif; ?>
  </div>

  <div class="feedback-box">
    <div class="feedback-header">
      <div><h3>📝 Feedback การให้บริการ</h3><p>ประเมินความพึงพอใจของงานแจ้งซ่อมรายการนี้</p></div>
      <?php if($job["feedback_score"] !== null): ?>
        <span class="feedback-current <?=h(feedbackClass($job["feedback_score"]))?>"><?=h(feedbackLabel($job["feedback_score"]))?></span>
      <?php endif; ?>
    </div>

    <?php if($job["repair_status"] === "completed"): ?>
    <form method="post" action="feedback_save.php" class="feedback-form" onsubmit="return confirm('ยืนยันการบันทึก Feedback รายการนี้หรือไม่?');">
      <input type="hidden" name="job_id" value="<?=intval($job["id"])?>">
      <div class="score-options">
        <label class="score-option score-green"><input type="radio" name="feedback_score" value="5" <?=($job["feedback_score"]===5 || $job["feedback_score"]==="5")?"checked":""?> required><span class="score-number">5</span><span class="score-text">ดีมาก</span></label>
        <label class="score-option score-blue"><input type="radio" name="feedback_score" value="3" <?=($job["feedback_score"]===3 || $job["feedback_score"]==="3")?"checked":""?>><span class="score-number">3</span><span class="score-text">พอใช้</span></label>
        <label class="score-option score-red"><input type="radio" name="feedback_score" value="0" <?=($job["feedback_score"]===0 || $job["feedback_score"]==="0")?"checked":""?>><span class="score-number">0</span><span class="score-text">แย่</span></label>
      </div>
      <div class="feedback-comment">
        <label>รายละเอียด Feedback</label>
        <textarea name="feedback_comment" rows="3" placeholder="บอกความคิดเห็นเพิ่มเติมเกี่ยวกับงานซ่อม..."><?=h($job["feedback_comment"])?></textarea>
      </div>
      <div class="feedback-footer">
        <?php if(!empty($job["feedback_at"])): ?><span class="feedback-date">ประเมินเมื่อ <?=h($job["feedback_at"])?></span><?php endif; ?>
        <button type="submit" class="btn feedback-save-btn">💾 <?=($job["feedback_score"]!==null ? "แก้ไข Feedback" : "บันทึก Feedback")?></button>
      </div>
    </form>
    <?php else: ?>
      <div class="feedback-disabled">🔒 สามารถประเมินได้เมื่อสถานะงานเป็น <strong>เสร็จสิ้น</strong></div>
      <?php if($job["feedback_score"] !== null): ?>
      <div class="feedback-readonly"><strong>ผลประเมิน:</strong> <?=h(feedbackLabel($job["feedback_score"]))?><br><?=nl2br(h($job["feedback_comment"]))?></div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</article>
<?php endforeach; ?>
</section>
<?php endif; ?>
</main>
</body>
</html>
