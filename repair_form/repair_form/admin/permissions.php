<?php
require_once __DIR__ . '/admin_guard.php';

$userId = (int)($_GET['user_id'] ?? 0);

$selected = null;

if ($userId > 0) {
    $stmt = $conn->prepare("
        SELECT id, fullname, username, role, status
        FROM users
        WHERE id=?
        LIMIT 1
    ");

    if (!$stmt) {
        die('SQL ERROR: ' . ah($conn->error));
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($uid, $fullname, $username, $role, $status);
        $stmt->fetch();

        $selected = [
            'id' => $uid,
            'fullname' => $fullname,
            'username' => $username,
            'role' => $role,
            'status' => $status
        ];
    }

    $stmt->close();
}

$permissions = [];

if ($userId > 0) {
    $stmt = $conn->prepare("
        SELECT permission_key
        FROM user_permissions
        WHERE user_id=?
        ORDER BY permission_key
    ");

    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($permissionKey);

        while ($stmt->fetch()) {
            $permissions[$permissionKey] = true;
        }

        $stmt->close();
    }
}

$users = $conn->query("
    SELECT id, fullname, username, role
    FROM users
    ORDER BY fullname
");

$modules = [
    'dashboard'    => 'Dashboard',
    'leave'        => 'วันลา',
    'e_document'   => 'หนังสือราชการ',
    'vehicle'      => 'ยานพาหนะ',
    'repair'       => 'แจ้งซ่อม',
    'structures'   => 'อาคาร',
    'meeting'      => 'ห้องประชุม',
    'personnel'    => 'บุคลากร',
    'propertywork' => 'ทรัพย์สิน',
    'asset'        => 'งานทรัพย์สิน',
    'attendance'   => 'ลงเวลา',
    'computer'     => 'แจ้งซ่อมคอมพิวเตอร์',
    'estate'       => 'ทะเบียนที่ดิน',
    'finance'      => 'งานพัสดุ',
    'maintenance'  => 'งานซ่อมบำรุง',
    'medical'      => 'ศูนย์เครื่องมือแพทย์',
    'security'     => 'รปภ.',
    'users'        => 'ผู้ใช้งาน',
    'admin'        => 'Admin'
];

$actions = [
    'view'    => 'ดู',
    'create'  => 'เพิ่ม',
    'edit'    => 'แก้ไข',
    'delete'  => 'ลบ',
    'approve' => 'อนุมัติ',
    'manage'  => 'จัดการ'
];
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>กำหนดสิทธิ์</title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/admin.css">
</head>

<body>

<div class="wrap">

    <div class="title">
        <div>
            <h1>กำหนดสิทธิ์ผู้ใช้</h1>
            <p>กำหนดว่าผู้ใช้สามารถดู เพิ่ม แก้ไข ลบ และอนุมัติอะไรได้บ้าง</p>
        </div>

        <a href="index.php" class="btn btn-outline-secondary">
            ← Admin
        </a>
    </div>

    <div class="panel">

        <form method="get">

            <select
                class="form-select"
                name="user_id"
                onchange="this.form.submit()"
            >

                <option value="">
                    -- เลือกผู้ใช้ --
                </option>

                <?php if ($users): ?>

                    <?php while ($u = $users->fetch_assoc()): ?>

                        <option
                            value="<?= (int)$u['id'] ?>"
                            <?= $userId === (int)$u['id'] ? 'selected' : '' ?>
                        >
                            <?= ah($u['fullname']) ?>
                            (<?= ah($u['username']) ?>)
                            - <?= ah($u['role']) ?>
                        </option>

                    <?php endwhile; ?>

                <?php endif; ?>

            </select>

        </form>

    </div>

    <?php if ($selected): ?>

        <div class="panel">

            <div class="selected">

                <strong>
                    <?= ah($selected['fullname']) ?>
                </strong>

                <span>
                    <?= ah($selected['username']) ?>
                </span>

                <span class="role">
                    <?= ah($selected['role']) ?>
                </span>

            </div>

            <form method="post" action="permission_save.php">

                <input
                    type="hidden"
                    name="user_id"
                    value="<?= $userId ?>"
                >

                <div class="perm-grid">

                    <?php foreach ($modules as $moduleKey => $moduleName): ?>

                        <div class="perm-card">

                            <div class="perm-title">

                                <b>
                                    <?= ah($moduleName) ?>
                                </b>

                                <small>
                                    <?= ah($moduleKey) ?>
                                </small>

                            </div>

                            <div class="checks">

                                <?php
                                $moduleActions = $actions;
                                if ($moduleKey === 'computer') {
                                    $moduleActions['receive'] = 'รับงาน';
                                }
                                ?>

                                <?php foreach ($moduleActions as $actionKey => $actionName): ?>

                                <?php
                                $key = $moduleKey . '.' . $actionKey;
                                $meetingApproveAdminOnly = (
                                    $moduleKey === 'meeting'
                                    && $actionKey === 'approve'
                                    && ($selected['role'] ?? '') !== 'admin'
                                );
                                ?>

                                <label class="<?= $meetingApproveAdminOnly ? 'permission-disabled' : '' ?>">

                                    <input
                                        class="perm"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="<?= ah($key) ?>"
                                        <?= isset($permissions[$key]) && !$meetingApproveAdminOnly ? 'checked' : '' ?>
                                        <?= $meetingApproveAdminOnly ? 'disabled' : '' ?>
                                    >

                                    <?= ah($actionName) ?>

                                    <?php if ($meetingApproveAdminOnly): ?>
                                        <small class="text-danger d-block">Admin เท่านั้น</small>
                                    <?php endif; ?>

                                </label>

                            <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

                <div class="mt-3 text-end">

                    <label class="me-3">
                        <input id="all" type="checkbox">
                        เลือกทั้งหมด
                    </label>

                    <button class="btn btn-success">
                        💾 บันทึกสิทธิ์
                    </button>

                </div>

            </form>

        </div>

    <?php endif; ?>

</div>

<script>
document.getElementById('all')?.addEventListener('change', function () {
    document.querySelectorAll('.perm').forEach(function (el) {
        el.checked = document.getElementById('all').checked;
    });
});
</script>

</body>
</html>
