<?php
require_once __DIR__ . '/admin_guard.php';

$q = trim($_GET['q'] ?? '');
$role = trim($_GET['role'] ?? '');
$status = $_GET['status'] ?? 'all';

$where = ["1=1"];

if ($q !== '') {
    $safe = $conn->real_escape_string($q);
    $like = "%{$safe}%";
    $where[] = "(fullname LIKE '{$like}' OR username LIKE '{$like}' OR email LIKE '{$like}' OR department LIKE '{$like}')";
}

if ($role !== '') {
    $safe = $conn->real_escape_string($role);
    $where[] = "role='{$safe}'";
}

if ($status !== 'all') {
    $safe = $conn->real_escape_string($status);
    $where[] = "status='{$safe}'";
}

$sql = "
    SELECT id, fullname, username, email, department,
           department_id, status, role, created_at
    FROM users
    WHERE " . implode(' AND ', $where) . "
    ORDER BY id DESC
";

$rs = $conn->query($sql);

if (!$rs) {
    die(
        '<h3>SQL ERROR: users</h3><pre>' .
        htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8') .
        '</pre>'
    );
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>จัดการผู้ใช้งาน</title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="wrap">

    <div class="title">
        <div>
            <h1>จัดการผู้ใช้งาน</h1>
            <p>กำหนด Role และสถานะบัญชี</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">← Admin</a>
    </div>

    <div class="panel">
        <form class="row g-2" method="get">
            <div class="col-md-5">
                <input class="form-control"
                       name="q"
                       value="<?= ah($q) ?>"
                       placeholder="ค้นหาชื่อ Username Email หน่วยงาน">
            </div>

            <div class="col-md-2">
                <select class="form-select" name="role">
                    <option value="">ทุก Role</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>admin</option>
                    <option value="technician" <?= $role === 'technician' ? 'selected' : '' ?>>technician</option>
                    <option value="manager" <?= $role === 'manager' ? 'selected' : '' ?>>manager</option>
                    <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>user</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="all">ทุกสถานะ</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>inactive</option>
                    <option value="banned" <?= $status === 'banned' ? 'selected' : '' ?>>banned</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill">🔎 ค้นหา</button>
                <a href="users.php" class="btn btn-outline-secondary">รีเซ็ต</a>
            </div>
        </form>
    </div>

    <div class="panel table-responsive">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อ</th>
                    <th>Username</th>
                    <th>หน่วยงาน</th>
                    <th>Role</th>
                    <th>สถานะ</th>
                    <th>สร้างเมื่อ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($rs->num_rows > 0): ?>

                <?php while ($u = $rs->fetch_assoc()): ?>

                <tr>

                    <td><?= (int)$u['id'] ?></td>

                    <td>
                        <strong><?= ah($u['fullname']) ?></strong>

                        <?php if (!empty($u['email'])): ?>
                            <br>
                            <small class="text-muted">
                                <?= ah($u['email']) ?>
                            </small>
                        <?php endif; ?>
                    </td>

                    <td><?= ah($u['username']) ?></td>

                    <td><?= ah($u['department'] ?? '-') ?></td>

                    <td>
                        <span class="role"><?= ah($u['role']) ?></span>
                    </td>

                    <td>
                        <?php if ($u['status'] === 'active'): ?>
                            <span class="ok">active</span>
                        <?php else: ?>
                            <span class="off">inactive</span>
                        <?php endif; ?>
                    </td>

                    <td><?= ah($u['created_at'] ?? '-') ?></td>

                    <td>
                        <a class="btn btn-warning btn-sm"
                           href="user_edit.php?id=<?= (int)$u['id'] ?>">
                            แก้ไข
                        </a>

                        <a class="btn btn-primary btn-sm"
                           href="permissions.php?user_id=<?= (int)$u['id'] ?>">
                            สิทธิ์
                        </a>
                    </td>

                </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        ไม่พบข้อมูลผู้ใช้งาน
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>
</body>
</html>
