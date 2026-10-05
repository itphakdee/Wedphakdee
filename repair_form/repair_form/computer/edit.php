<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../admin/permissions_helper.php";
if (!is_admin_user()) {
    http_response_code(403);
    exit("403 Forbidden: การแก้ไขงานซ่อมคอมพิวเตอร์อนุญาตเฉพาะ Admin");
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) die('ไม่พบรหัสงาน');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_name = trim($_POST['sender_name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $repair_system = trim($_POST['repair_system'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $technician_id = (int)($_POST['technician_id'] ?? 0);
    $details = trim($_POST['details'] ?? '');
    $priority = trim($_POST['priority'] ?? 'normal');

    if ($sender_name === '' || $department === '' || $location === '' || $details === '' || $technician_id <= 0) {
        $error = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบ';
    } else {
        $stmt = $conn->prepare("UPDATE repair_jobs SET sender_name=?, department=?, repair_system=?, location=?, technician_id=?, details=?, priority=? WHERE id=?");
        if (!$stmt) die('SQL ERROR: '.htmlspecialchars($conn->error));
        $stmt->bind_param('ssssissi', $sender_name, $department, $repair_system, $location, $technician_id, $details, $priority, $id);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: indexrepairlist.php?updated=1');
            exit;
        }
        $error = $stmt->error;
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT id,sender_name,department,repair_system,location,technician_id,details,priority FROM repair_jobs WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->store_result();
if (!$stmt->num_rows) die('ไม่พบข้อมูล');
$stmt->bind_result($rid,$sender_name,$department,$repair_system,$location,$technician_id,$details,$priority);
$stmt->fetch();
$stmt->close();

$technicians=[];
$rs=$conn->query("SELECT id,name FROM technicians WHERE status='ใช้งาน' ORDER BY name");
if($rs){ while($t=$rs->fetch_assoc()) $technicians[]=$t; }

function h($v){ return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>แก้ไขงานแจ้งซ่อม</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
<div class="card shadow-sm">
<div class="card-header bg-warning"><strong>✏️ แก้ไขงานแจ้งซ่อม #<?= $id ?></strong></div>
<div class="card-body">
<?php if(!empty($error)): ?><div class="alert alert-danger"><?=h($error)?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="id" value="<?= $id ?>">
<div class="row">
<div class="col-md-6 mb-3"><label class="form-label">ชื่อผู้ส่ง *</label><input class="form-control" name="sender_name" value="<?=h($sender_name)?>" required></div>
<div class="col-md-6 mb-3"><label class="form-label">แผนก *</label><input class="form-control" name="department" value="<?=h($department)?>" required></div>
<div class="col-md-6 mb-3"><label class="form-label">ระบบที่แจ้งซ่อม</label><input class="form-control" name="repair_system" value="<?=h($repair_system)?>"></div>
<div class="col-md-6 mb-3"><label class="form-label">สถานที่ *</label><input class="form-control" name="location" value="<?=h($location)?>" required></div>
<div class="col-md-6 mb-3"><label class="form-label">ช่าง *</label><select class="form-select" name="technician_id" required><option value="">-- เลือกช่าง --</option><?php foreach($technicians as $t): ?><option value="<?= (int)$t['id']?>" <?=((int)$technician_id===(int)$t['id']?'selected':'')?>><?=h($t['name'])?></option><?php endforeach;?></select></div>
<div class="col-md-6 mb-3"><label class="form-label">ความเร่งด่วน</label><select class="form-select" name="priority"><option value="normal" <?= $priority==='normal'?'selected':'' ?>>ปกติ</option><option value="urgent" <?= $priority==='urgent'?'selected':'' ?>>ด่วน</option><option value="emergency" <?= $priority==='emergency'?'selected':'' ?>>ด่วนมาก</option></select></div>
<div class="col-12 mb-3"><label class="form-label">รายละเอียด *</label><textarea class="form-control" name="details" rows="5" required><?=h($details)?></textarea></div>
</div>
<button class="btn btn-warning">💾 บันทึกการแก้ไข</button>
<a href="indexrepairlist.php" class="btn btn-secondary">กลับ</a>
</form>
</div></div></div>
</body></html>
