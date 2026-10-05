<?php
require_once __DIR__ . '/layout.php';

if (!leave_is_admin()) {
    http_response_code(403);
    die('403 Forbidden: เฉพาะ Admin');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    leave_require_csrf();

    $mode = (string)($_POST['mode'] ?? 'save');
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($mode === 'delete') {
        $stmt = $conn->prepare("DELETE FROM leave_line_accounts WHERE user_id=?");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();
        }
        leave_flash('success', 'ลบการผูก LINE ID แล้ว');
        leave_redirect('line_accounts.php');
    }

    $lineId = trim((string)($_POST['line_user_id'] ?? ''));
    $displayName = trim((string)($_POST['display_name'] ?? ''));
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($userId <= 0 || $lineId === '') {
        leave_flash('error', 'กรุณาเลือกผู้ใช้และระบุ LINE User ID');
        leave_redirect('line_accounts.php');
    }

    if (!in_array(strtoupper(substr($lineId, 0, 1)), array('U','C','R'), true)) {
        leave_flash('error', 'LINE ID ต้องขึ้นต้นด้วย U, C หรือ R');
        leave_redirect('line_accounts.php');
    }

    $stmt = $conn->prepare(
        "INSERT INTO leave_line_accounts(user_id,line_user_id,display_name,is_active,created_at,updated_at)
         VALUES(?,?,?,?,NOW(),NOW())
         ON DUPLICATE KEY UPDATE
           line_user_id=VALUES(line_user_id),
           display_name=VALUES(display_name),
           is_active=VALUES(is_active),
           updated_at=NOW()"
    );
    if (!$stmt) {
        leave_flash('error', 'เตรียมคำสั่งฐานข้อมูลไม่สำเร็จ: ' . $conn->error);
        leave_redirect('line_accounts.php');
    }

    $stmt->bind_param('issi', $userId, $lineId, $displayName, $isActive);

    if ($stmt->execute()) {
        leave_flash('success', 'บันทึก LINE ID เรียบร้อยแล้ว');
    } else {
        leave_flash('error', 'บันทึกไม่สำเร็จ: ' . $stmt->error);
    }
    $stmt->close();

    leave_redirect('line_accounts.php');
}

$rows = array();
$sql = "SELECT u.id,u.fullname,u.username,u.department,
               la.line_user_id,la.display_name,la.is_active,la.updated_at
        FROM users u
        LEFT JOIN leave_line_accounts la ON la.user_id=u.id
        WHERE u.status='active'
        ORDER BY u.fullname";
$res = $conn->query($sql);
while ($res && $r = $res->fetch_assoc()) $rows[] = $r;

leave_page_start(
    'จัดการ LINE OA ระบบลา',
    'ผูก LINE User ID กับบุคลากร เพื่อแจ้งเตือนรายบุคคลหลายคน',
    'line'
);
?>
<div class="leave-grid-2">
    <section class="leave-card">
        <div class="leave-card__head">
            <div>
                <h2>ผูก LINE ID</h2>
                <p>เลือกบุคลากรแล้วกำหนด LINE User ID</p>
            </div>
        </div>
        <div class="leave-card__body">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= leave_e(leave_csrf_token()) ?>">
                <input type="hidden" name="mode" value="save">

                <div class="leave-field">
                    <label>บุคลากร <span class="req">*</span></label>
                    <select class="leave-select" name="user_id" required>
                        <option value="">-- เลือกบุคลากร --</option>
                        <?php foreach ($rows as $r): ?>
                            <option
                                value="<?= (int)$r['id'] ?>"
                                data-line="<?= leave_e($r['line_user_id'] ?? '') ?>"
                                data-display="<?= leave_e($r['display_name'] ?? '') ?>"
                            >
                                <?= leave_e($r['fullname']) ?>
                                <?= trim((string)$r['department']) !== '' ? ' · ' . leave_e($r['department']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="leave-field" style="margin-top:12px">
                    <label>LINE User ID <span class="req">*</span></label>
                    <input
                        class="leave-input"
                        name="line_user_id"
                        placeholder="เช่น Uxxxxxxxxxxxxxxxxxxxxxxxx"
                        required
                    >
                    <div class="leave-help">User ID ปกติขึ้นต้นด้วย U</div>
                </div>

                <div class="leave-field" style="margin-top:12px">
                    <label>ชื่อใน LINE</label>
                    <input class="leave-input" name="display_name" placeholder="ชื่อสำหรับอ้างอิง">
                </div>

                <label style="display:flex;gap:8px;align-items:center;margin-top:14px">
                    <input type="checkbox" name="is_active" value="1" checked>
                    เปิดใช้งานการแจ้งเตือน
                </label>

                <button class="leave-btn leave-btn--success" style="margin-top:16px">
                    บันทึก LINE ID
                </button>
            </form>
        </div>
    </section>

    <section class="leave-card">
        <div class="leave-card__head">
            <div>
                <h2>ผู้รับสำเนาเริ่มต้น</h2>
                <p>ค่าจาก line_apps_script_config.php</p>
            </div>
        </div>
        <div class="leave-card__body">
            <div class="leave-info">
                <label>Default / Admin CC</label>
                <strong><?= leave_e(defined('LEAVE_LINE_DEFAULT_TARGETS') ? LEAVE_LINE_DEFAULT_TARGETS : '-') ?></strong>
            </div>
            <p class="leave-help" style="margin-top:12px">
                เมื่อแจ้งหัวหน้างาน ระบบจะส่งสำเนาไป Target นี้ด้วย และตัด ID ซ้ำอัตโนมัติ
            </p>
            <a class="leave-btn leave-btn--outline" href="lineapi/test_line.php" style="margin-top:12px">
                ทดสอบ LINE OA
            </a>
        </div>
    </section>
</div>

<section class="leave-card" style="margin-top:18px">
    <div class="leave-card__head">
        <div>
            <h2>บัญชี LINE ที่ผูกแล้ว</h2>
            <p><?= count($rows) ?> บัญชีผู้ใช้งานในระบบ</p>
        </div>
    </div>
    <div class="leave-card__body">
        <div style="overflow:auto">
            <table class="leave-table">
                <thead>
                    <tr>
                        <th>ชื่อ</th>
                        <th>หน่วยงาน</th>
                        <th>LINE ID</th>
                        <th>สถานะ</th>
                        <th>คำสั่ง</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <strong><?= leave_e($r['fullname']) ?></strong>
                            <div class="leave-help"><?= leave_e($r['username']) ?></div>
                        </td>
                        <td><?= leave_e($r['department'] ?: '-') ?></td>
                        <td><?= leave_e($r['line_user_id'] ?: '-') ?></td>
                        <td>
                            <?php if ($r['line_user_id']): ?>
                                <?= (int)$r['is_active'] === 1 ? 'ใช้งาน' : 'ปิดใช้งาน' ?>
                            <?php else: ?>
                                ยังไม่ผูก
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['line_user_id']): ?>
                            <form method="post" onsubmit="return confirm('ลบ LINE ID ของบุคลากรคนนี้?')">
                                <input type="hidden" name="csrf_token" value="<?= leave_e(leave_csrf_token()) ?>">
                                <input type="hidden" name="mode" value="delete">
                                <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>">
                                <button class="leave-btn leave-btn--danger" type="submit">ลบ</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.querySelector('select[name="user_id"]');
    var line = document.querySelector('input[name="line_user_id"]');
    var display = document.querySelector('input[name="display_name"]');

    if (!select || !line || !display) return;

    select.addEventListener('change', function () {
        var option = select.options[select.selectedIndex];
        line.value = option ? (option.dataset.line || '') : '';
        display.value = option ? (option.dataset.display || '') : '';
    });
});
</script>
<?php leave_page_end(); ?>
