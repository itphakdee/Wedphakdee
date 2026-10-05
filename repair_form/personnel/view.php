<?php
require_once __DIR__ . '/config_personnel.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
SELECT employee_code,prefix,fullname,position_name,department,
       phone,email,room_location,photo,status,notes,created_at,updated_at
FROM personnel WHERE id=? LIMIT 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->store_result();

if (!$stmt->num_rows) {
    $stmt->close();
    die('ไม่พบข้อมูลบุคลากร');
}

$stmt->bind_result(
    $employeeCode,
    $prefix,
    $fullname,
    $position,
    $department,
    $phone,
    $email,
    $location,
    $photo,
    $status,
    $notes,
    $createdAt,
    $updatedAt
);
$stmt->fetch();
$stmt->close();

// ensure variables are strings so IDE/analysis won't complain
$photo = $fullname = $prefix = $position = $employeeCode = $department =
    $phone = $email = $location = $status = $notes = '';

?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ข้อมูลบุคลากร</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="assets/personnel.css">
</head>

<body>
    <div class="personnel-container">
        <div class="page-head">
            <div>
                <h1>ข้อมูลบุคลากร</h1>
                <p>รายละเอียดบุคลากร</p>
            </div>
            <div><a href="edit.php?id=<?= $id ?>" class="btn btn-warning">แก้ไข</a> <a href="index.php" class="btn btn-outline-secondary">← กลับ</a></div>
        </div>

        <div class="panel profile-panel">
            <div class="profile-avatar">
                <?php if ($photo): ?><img src="uploads/<?= h($photo) ?>"><?php else: ?><span><?= h(mb_substr($fullname, 0, 1, 'UTF-8')) ?></span><?php endif; ?>
            </div>
            <h2><?= h(trim($prefix . ' ' . $fullname)) ?></h2>
            <p class="profile-position"><?= h($position ?: 'ไม่ระบุตำแหน่ง') ?></p>

            <div class="profile-grid">
                <div><label>รหัสบุคลากร</label><strong><?= h($employeeCode ?: '-') ?></strong></div>
                <div><label>หน่วยงาน</label><strong><?= h($department ?: '-') ?></strong></div>
                <div><label>โทรศัพท์</label><strong><?= h($phone ?: '-') ?></strong></div>
                <div><label>อีเมล</label><strong><?= h($email ?: '-') ?></strong></div>
                <div><label>สถานที่ / ห้อง</label><strong><?= h($location ?: '-') ?></strong></div>
                <div><label>สถานะ</label><strong><?= $status === 'active' ? 'ปฏิบัติงาน' : 'ไม่ใช้งาน' ?></strong></div>
                <div class="full"><label>หมายเหตุ</label><strong><?= nl2br(h($notes ?: '-')) ?></strong></div>
            </div>
        </div>
    </div>
</body>

</html>