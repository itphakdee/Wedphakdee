<?php
require_once __DIR__ . '/admin_guard.php';

$q = trim((string)($_GET['q'] ?? ''));
$role = trim((string)($_GET['role'] ?? ''));
$status = trim((string)($_GET['status'] ?? 'all'));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$allowedRoles = ['', 'admin', 'technician', 'manager', 'user'];
$allowedStatuses = ['all', 'active', 'inactive', 'banned'];

if (!in_array($role, $allowedRoles, true)) $role = '';
if (!in_array($status, $allowedStatuses, true)) $status = 'all';

$where = ['1=1'];

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

$whereSql = implode(' AND ', $where);

$stats = ['total'=>0,'active'=>0,'admins'=>0,'inactive'=>0];
$statsRs = $conn->query("SELECT COUNT(*) AS total, SUM(status='active') AS active_count, SUM(role='admin') AS admin_count, SUM(status<>'active') AS inactive_count FROM users");
if ($statsRs && ($sr = $statsRs->fetch_assoc())) {
    $stats['total'] = (int)($sr['total'] ?? 0);
    $stats['active'] = (int)($sr['active_count'] ?? 0);
    $stats['admins'] = (int)($sr['admin_count'] ?? 0);
    $stats['inactive'] = (int)($sr['inactive_count'] ?? 0);
}

$countRs = $conn->query("SELECT COUNT(*) AS total_rows FROM users WHERE {$whereSql}");
if (!$countRs) {
    die('<h3>SQL ERROR: users count</h3><pre>'.htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8').'</pre>');
}

$totalRows = (int)($countRs->fetch_assoc()['total_rows'] ?? 0);
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$rs = $conn->query("SELECT id, fullname, username, email, department, department_id, status, role, created_at FROM users WHERE {$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
if (!$rs) {
    die('<h3>SQL ERROR: users</h3><pre>'.htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8').'</pre>');
}

function users_page_url(int $targetPage): string {
    $params = $_GET;
    $params['page'] = $targetPage;
    return 'users.php?' . http_build_query($params);
}

function users_role_label(string $role): string {
    $labels = [
        'admin'=>'ผู้ดูแลระบบ',
        'technician'=>'ช่าง / ผู้รับผิดชอบ',
        'manager'=>'หัวหน้างาน',
        'user'=>'ผู้ใช้งาน'
    ];
    return $labels[$role] ?? $role;
}

function users_status_label(string $status): string {
    $labels = [
        'active'=>'ใช้งาน',
        'inactive'=>'ปิดใช้งาน',
        'banned'=>'ระงับบัญชี'
    ];
    return $labels[$status] ?? $status;
}

$windowSize = 5;
$startPage = max(1, $page - 2);
$endPage = min($totalPages, $startPage + $windowSize - 1);
if (($endPage - $startPage + 1) < $windowSize) {
    $startPage = max(1, $endPage - $windowSize + 1);
}

$showFrom = $totalRows > 0 ? $offset + 1 : 0;
$showTo = min($offset + $perPage, $totalRows);
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>จัดการผู้ใช้งาน | โรงพยาบาลภักดีชุมพล</title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/admin.css">
<link rel="stylesheet" href="assets/users.css">
</head>
<body>
<div class="users-shell">
    <section class="users-hero">
        <div>
            <div class="users-eyebrow">PHAKDEE CHUMPHON HOSPITAL · USER MANAGEMENT</div>
            <h1>จัดการผู้ใช้งานระบบ</h1>
            <p>ตรวจสอบบัญชีผู้ใช้งาน กำหนด Role สถานะบัญชี และสิทธิ์การเข้าใช้งานแต่ละระบบ</p>
        </div>
        <a href="index.php" class="users-btn users-btn--light">← กลับหน้า Admin</a>
    </section>

    <section class="users-summary">
        <article class="users-stat"><div class="users-stat__icon">👥</div><div><span>ผู้ใช้งานทั้งหมด</span><strong><?= number_format($stats['total']) ?></strong><small>บัญชีในระบบ</small></div></article>
        <article class="users-stat"><div class="users-stat__icon">✓</div><div><span>กำลังใช้งาน</span><strong><?= number_format($stats['active']) ?></strong><small>สถานะ Active</small></div></article>
        <article class="users-stat"><div class="users-stat__icon">A</div><div><span>ผู้ดูแลระบบ</span><strong><?= number_format($stats['admins']) ?></strong><small>Role Admin</small></div></article>
        <article class="users-stat"><div class="users-stat__icon">—</div><div><span>ปิด / ระงับ</span><strong><?= number_format($stats['inactive']) ?></strong><small>ไม่ได้ใช้งานปัจจุบัน</small></div></article>
    </section>

    <section class="users-card">
        <div class="users-section-head">
            <div class="users-section-title"><span class="users-section-no">01</span><div><h2>ค้นหาและกรองผู้ใช้งาน</h2><p>ค้นหาจากชื่อ Username อีเมล หรือหน่วยงาน</p></div></div>
        </div>
        <form method="get" class="users-filter">
            <div class="users-field users-field--search">
                <label for="q">ค้นหา</label>
                <div class="users-input-wrap"><span>⌕</span><input id="q" name="q" value="<?= ah($q) ?>" placeholder="ชื่อ, Username, Email หรือหน่วยงาน" autocomplete="off"></div>
            </div>
            <div class="users-field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="">ทุก Role</option>
                    <option value="admin" <?= $role==='admin'?'selected':'' ?>>ผู้ดูแลระบบ</option>
                    <option value="technician" <?= $role==='technician'?'selected':'' ?>>ช่าง / ผู้รับผิดชอบ</option>
                    <option value="manager" <?= $role==='manager'?'selected':'' ?>>หัวหน้างาน</option>
                    <option value="user" <?= $role==='user'?'selected':'' ?>>ผู้ใช้งาน</option>
                </select>
            </div>
            <div class="users-field">
                <label for="status">สถานะ</label>
                <select id="status" name="status">
                    <option value="all">ทุกสถานะ</option>
                    <option value="active" <?= $status==='active'?'selected':'' ?>>ใช้งาน</option>
                    <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>ปิดใช้งาน</option>
                    <option value="banned" <?= $status==='banned'?'selected':'' ?>>ระงับบัญชี</option>
                </select>
            </div>
            <div class="users-filter__actions"><button class="users-btn users-btn--primary" type="submit">🔎 ค้นหา</button><a class="users-btn users-btn--outline" href="users.php">รีเซ็ต</a></div>
        </form>
    </section>

    <section class="users-card">
        <div class="users-section-head users-section-head--table">
            <div class="users-section-title"><span class="users-section-no">02</span><div><h2>ทะเบียนผู้ใช้งาน</h2><p>พบ <?= number_format($totalRows) ?> รายการ · แสดง <?= number_format($showFrom) ?>–<?= number_format($showTo) ?></p></div></div>
            <div class="users-result-chip">หน้า <?= number_format($page) ?> / <?= number_format($totalPages) ?></div>
        </div>

        <div class="users-table-wrap">
            <table class="users-table">
                <thead><tr><th>ID</th><th>บุคลากร</th><th>Username</th><th>หน่วยงาน</th><th>Role</th><th>สถานะ</th><th>สร้างเมื่อ</th><th>จัดการ</th></tr></thead>
                <tbody>
                <?php if ($rs->num_rows > 0): ?>
                    <?php while ($u = $rs->fetch_assoc()): ?>
                    <?php
                        $fullName = trim((string)($u['fullname'] ?? ''));
                        $initial = $fullName !== '' ? mb_substr($fullName,0,1,'UTF-8') : 'U';
                        $statusClass = 'users-status--inactive';
                        if ($u['status'] === 'active') $statusClass = 'users-status--active';
                        elseif ($u['status'] === 'banned') $statusClass = 'users-status--banned';
                        $roleClass = 'users-role--user';
                        if ($u['role'] === 'admin') $roleClass = 'users-role--admin';
                        elseif ($u['role'] === 'manager') $roleClass = 'users-role--manager';
                        elseif ($u['role'] === 'technician') $roleClass = 'users-role--technician';
                    ?>
                    <tr>
                        <td data-label="ID" class="users-id">#<?= (int)$u['id'] ?></td>
                        <td data-label="บุคลากร"><div class="users-person"><div class="users-avatar"><?= ah($initial) ?></div><div class="users-person__text"><strong><?= ah($u['fullname']) ?></strong><small><?= !empty($u['email']) ? ah($u['email']) : 'ยังไม่มีอีเมล' ?></small></div></div></td>
                        <td data-label="Username"><span class="users-username"><?= ah($u['username']) ?></span></td>
                        <td data-label="หน่วยงาน"><span class="users-department"><?= ah($u['department'] ?: '-') ?></span></td>
                        <td data-label="Role"><span class="users-role <?= ah($roleClass) ?>"><?= ah(users_role_label((string)$u['role'])) ?></span></td>
                        <td data-label="สถานะ"><span class="users-status <?= ah($statusClass) ?>"><i></i><?= ah(users_status_label((string)$u['status'])) ?></span></td>
                        <td data-label="สร้างเมื่อ"><span class="users-date"><?= ah($u['created_at'] ?? '-') ?></span></td>
                        <td data-label="จัดการ"><div class="users-actions"><a class="users-action users-action--edit" href="user_edit.php?id=<?= (int)$u['id'] ?>">แก้ไข</a><a class="users-action users-action--permission" href="permissions.php?user_id=<?= (int)$u['id'] ?>">สิทธิ์</a></div></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="users-empty"><div class="users-empty__icon">⌕</div><strong>ไม่พบข้อมูลผู้ใช้งาน</strong><span>ลองเปลี่ยนคำค้นหา หรือล้างตัวกรองแล้วค้นหาใหม่</span></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="users-pagination" aria-label="หน้ารายการผู้ใช้งาน">
            <?php if ($page > 1): ?><a class="users-page users-page--nav" href="<?= ah(users_page_url($page-1)) ?>">‹ ก่อนหน้า</a><?php else: ?><span class="users-page users-page--nav is-disabled">‹ ก่อนหน้า</span><?php endif; ?>
            <?php if ($startPage > 1): ?><a class="users-page" href="<?= ah(users_page_url(1)) ?>">1</a><?php if ($startPage > 2): ?><span class="users-page users-page--dots">…</span><?php endif; ?><?php endif; ?>
            <?php for ($p=$startPage; $p<=$endPage; $p++): ?><?php if ($p === $page): ?><span class="users-page is-active"><?= $p ?></span><?php else: ?><a class="users-page" href="<?= ah(users_page_url($p)) ?>"><?= $p ?></a><?php endif; ?><?php endfor; ?>
            <?php if ($endPage < $totalPages): ?><?php if ($endPage < $totalPages-1): ?><span class="users-page users-page--dots">…</span><?php endif; ?><a class="users-page" href="<?= ah(users_page_url($totalPages)) ?>"><?= $totalPages ?></a><?php endif; ?>
            <?php if ($page < $totalPages): ?><a class="users-page users-page--nav" href="<?= ah(users_page_url($page+1)) ?>">ถัดไป ›</a><?php else: ?><span class="users-page users-page--nav is-disabled">ถัดไป ›</span><?php endif; ?>
        </nav>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
