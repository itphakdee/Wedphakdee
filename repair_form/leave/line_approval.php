<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ถ้าเปิดจาก LINE แล้วยังไม่ได้ Login
 * ให้จำ URL นี้ไว้ แล้ว login.php จะพากลับมาหน้านี้
 */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['after_login_url'] = $_SERVER['REQUEST_URI'];
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/config_leave.php';
require_once __DIR__ . '/lineapi/line_sender.php';

$id = (int)($_GET['id'] ?? 0);
$action = (string)($_GET['action'] ?? '');
$authorizedUserId = (int)($_GET['user'] ?? 0);
$expires = (int)($_GET['expires'] ?? 0);
$sig = (string)($_GET['sig'] ?? '');

$allowed = array('approve', 'reject', 'approve_cancel', 'reject_cancel');

if (
    $id <= 0 ||
    !in_array($action, $allowed, true) ||
    !leave_line_verify_action_signature($id, $action, $authorizedUserId, $expires, $sig)
) {
    http_response_code(403);
    die('ลิงก์ LINE ไม่ถูกต้องหรือหมดอายุ');
}

$row = leave_get_application($id);
if (!$row) {
    http_response_code(404);
    die('ไม่พบใบลา');
}

$uid = (int)$_SESSION['user_id'];

if (
    !leave_is_admin() &&
    $uid !== $authorizedUserId &&
    $uid !== (int)$row['supervisor_user_id']
) {
    http_response_code(403);
    die('บัญชีนี้ไม่มีสิทธิ์พิจารณาใบลานี้');
}

$labels = array(
    'approve' => array('อนุมัติใบลา', 'เห็นชอบ / อนุมัติ', 'success'),
    'reject' => array('ไม่อนุมัติใบลา', 'ไม่เห็นชอบ', 'danger'),
    'approve_cancel' => array('อนุมัติการยกเลิก', 'ยืนยันยกเลิกใบลา', 'success'),
    'reject_cancel' => array('ไม่อนุมัติการยกเลิก', 'ไม่อนุมัติการยกเลิก', 'outline')
);

$label = $labels[$action];

leave_page_start(
    $label[0],
    'เปิดจาก LINE OA · ตรวจสอบข้อมูลก่อนยืนยัน',
    'approval'
);
?>
<section class="leave-card" style="max-width:820px;margin:0 auto">
    <div class="leave-card__head">
        <div>
            <h2><?= leave_e($row['leave_no']) ?> · <?= leave_e($row['leave_type_name']) ?></h2>
            <p><?= leave_e($row['employee_name']) ?> · <?= leave_e($row['department_name'] ?: '-') ?></p>
        </div>
    </div>
    <div class="leave-card__body">
        <div class="leave-info-grid">
            <div class="leave-info">
                <label>วันเริ่มลา</label>
                <strong><?= leave_date_thai($row['start_date']) ?></strong>
            </div>
            <div class="leave-info">
                <label>ลาถึงวันที่</label>
                <strong><?= leave_date_thai($row['end_date']) ?></strong>
            </div>
            <div class="leave-info">
                <label>จำนวนวัน</label>
                <strong><?= leave_format_days($row['leave_days']) ?> วัน</strong>
            </div>
            <div class="leave-info">
                <label>ผู้รับมอบงาน</label>
                <strong><?= leave_e($row['handover_name']) ?></strong>
            </div>
            <div class="leave-info leave-info--full">
                <label>เหตุผล</label>
                <strong><?= nl2br(leave_e($row['reason'])) ?></strong>
            </div>
        </div>

        <div class="leave-alert leave-alert--warning" style="margin-top:18px">
            ปุ่มใน LINE จะเปิดหน้ายืนยันนี้ก่อน ระบบจะไม่อนุมัติด้วย GET โดยตรง
        </div>

        <form method="post" action="approve_action.php" style="margin-top:18px">
            <input type="hidden" name="csrf_token" value="<?= leave_e(leave_csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <input type="hidden" name="action" value="<?= leave_e($action) ?>">

            <?php if (in_array($action, array('approve','reject'), true)): ?>
                <div class="leave-field">
                    <label>ความเห็นหัวหน้างาน</label>
                    <textarea class="leave-textarea" name="comment" placeholder="ระบุความเห็น (ถ้ามี)"></textarea>
                </div>
            <?php endif; ?>

            <div class="leave-actions" style="margin-top:16px">
                <button class="leave-btn leave-btn--<?= leave_e($label[2]) ?>" type="submit">
                    <?= leave_e($label[1]) ?>
                </button>
                <a class="leave-btn leave-btn--outline" href="detail.php?id=<?= (int)$id ?>">ดูรายละเอียดก่อน</a>
            </div>
        </form>
    </div>
</section>
<?php leave_page_end(); ?>
