<?php
require_once __DIR__ . '/admin_guard.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('ไม่พบ User ID');
}

$stmt = $conn->prepare("
    SELECT fullname, username, email, department,
           department_id, status, role
    FROM users
    WHERE id=?
    LIMIT 1
");

if (!$stmt) {
    die('SQL ERROR: ' . ah($conn->error));
}

$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->store_result();

if (!$stmt->num_rows) {
    $stmt->close();
    die('ไม่พบข้อมูลผู้ใช้งาน');
}

$stmt->bind_result(
    $fullname,
    $username,
    $email,
    $department,
    $departmentId,
    $status,
    $role
);
$stmt->fetch();
$stmt->close();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>แก้ไขผู้ใช้</title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="wrap">

    <div class="title">
        <div>
            <h1>แก้ไขผู้ใช้งาน</h1>
            <p><?= ah($fullname) ?> (<?= ah($username) ?>)</p>
        </div>

        <a href="users.php" class="btn btn-outline-secondary">
            ← กลับ
        </a>
    </div>

    <div class="panel">

        <form method="post"
              action="user_update.php"
              class="row g-3">

            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="col-md-6">
                <label class="form-label">ชื่อ-นามสกุล</label>
                <input class="form-control"
                       name="fullname"
                       value="<?= ah($fullname) ?>"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input class="form-control"
                       value="<?= ah($username) ?>"
                       disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input class="form-control"
                       type="email"
                       name="email"
                       value="<?= ah($email) ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">หน่วยงาน</label>
                <input class="form-control"
                       name="department"
                       value="<?= ah($department) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Role</label>
                <select class="form-select" name="role">
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>admin</option>
                    <option value="technician" <?= $role === 'technician' ? 'selected' : '' ?>>technician</option>
                    <option value="manager" <?= $role === 'manager' ? 'selected' : '' ?>>manager</option>
                    <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>user</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>inactive</option>
                    <option value="banned" <?= $status === 'banned' ? 'selected' : '' ?>>banned</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Department ID</label>
                <input class="form-control"
                       type="number"
                       name="department_id"
                       value="<?= ah($departmentId) ?>">
            </div>

            <div class="col-12 text-end">
                <a href="users.php" class="btn btn-secondary">ยกเลิก</a>
                <button class="btn btn-success">บันทึก</button>
            </div>

        </form>

    </div>

</div>
</body>
</html>
