<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_permission('view');

if (!maintenance_table_exists()) {
    header('Location: index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM maintenance_requests WHERE id=? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request) {
    http_response_code(404);
    die('404: ไม่พบรายการงานซ่อมบำรุง');
}

// Feedback ผูกกับ user_id ของผู้สร้างรายการเท่านั้น
// พยายามสร้างตารางอัตโนมัติสำหรับโปรเจกต์ที่อัปเดตจากเวอร์ชันเดิม
$feedbackTableReady = maintenance_ensure_feedback_table();
$feedback = null;
if ($feedbackTableReady) {
    $stmtFeedback = $conn->prepare("SELECT id, request_id, user_id, score, feedback_text, created_at, updated_at FROM maintenance_feedback WHERE request_id=? LIMIT 1");
    if ($stmtFeedback) {
        $stmtFeedback->bind_param('i', $id);
        $stmtFeedback->execute();
        $feedback = $stmtFeedback->get_result()->fetch_assoc();
        $stmtFeedback->close();
    }
}

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$isRequestOwner = (int)($request['user_id'] ?? 0) > 0 && (int)$request['user_id'] === $currentUserId;
// ให้ประเมินครั้งแรกเมื่อปิดงานแล้ว และยังคงแก้ไข Feedback เดิมได้ภายหลัง
$canSubmitFeedback = $feedbackTableReady && $isRequestOwner && ($request['status'] === 'completed' || $feedback);
$feedbackMeta = $feedback ? maintenance_feedback_meta((int)$feedback['score']) : null;

$technicians = [];
$techResult = $conn->query("SELECT id, name, department FROM technicians WHERE status='ใช้งาน' ORDER BY department, name");
if ($techResult) {
    while ($row = $techResult->fetch_assoc()) {
        $technicians[] = $row;
    }
}

$flash = maintenance_pull_flash();
$status = maintenance_status_meta($request['status']);
$priority = maintenance_priority_meta($request['priority']);
$system = maintenance_system_meta($request['system_type']);
$canEdit = maintenance_can('edit');
$canDelete = maintenance_can('delete');
$canManage = maintenance_can('manage');
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>รายละเอียดงาน #<?= (int)$request['id'] ?> | งานซ่อมบำรุง</title>
<link rel="stylesheet" href="assets/maintenance.css?v=<?= filemtime(__DIR__ . '/assets/maintenance.css') ?>">
</head>
<body>
<?php $activePage='maintenance'; $basePath='../../'; require __DIR__ . '/../../components/sidebar.php'; ?>
<main class="main-content maintenance-main">
    <div class="mt-page-top">
        <div class="mt-breadcrumb"><a href="index.php">งานซ่อมบำรุง</a><span>›</span><span>รายละเอียด #<?= (int)$request['id'] ?></span></div>
        <div class="mt-user-chip"><span class="mt-user-avatar"><?= mh(maintenance_initial($_SESSION['fullname'] ?? 'U')) ?></span><span><?= mh($_SESSION['fullname'] ?? 'ผู้ใช้งาน') ?></span></div>
    </div>

    <section class="mt-hero">
        <div class="mt-hero-copy">
            <p class="mt-eyebrow">MAINTENANCE REQUEST #<?= (int)$request['id'] ?></p>
            <h1>รายละเอียดงานซ่อมบำรุง</h1>
            <p>ตรวจสอบข้อมูลผู้แจ้ง ช่างผู้รับผิดชอบ ความเร่งด่วน และความคืบหน้าของงาน</p>
        </div>
        <div class="mt-hero-actions">
            <a class="mt-btn mt-btn-outline" href="index.php">← กลับหน้ารายการ</a>
            <?php if ($canEdit): ?><a class="mt-btn mt-btn-soft" href="edit.php?id=<?= (int)$request['id'] ?>">แก้ไขข้อมูล</a><?php endif; ?>
            <button class="mt-btn mt-btn-primary" type="button" onclick="window.print()">พิมพ์รายละเอียด</button>
        </div>
    </section>

    <?php if ($flash): ?><div class="mt-flash <?= mh($flash['type']) ?>"><?= mh($flash['message']) ?></div><?php endif; ?>

    <div class="mt-detail-grid">
        <div class="mt-detail-main">
        <section class="mt-card">
            <div class="mt-card-head">
                <div class="mt-card-title-wrap"><div class="mt-section-badge">#<?= (int)$request['id'] ?></div><div><h2>ข้อมูลรายการแจ้งซ่อม</h2><p>วันที่แจ้ง <?= mh(maintenance_format_datetime($request['created_at'])) ?></p></div></div>
                <span class="mt-badge mt-status-<?= mh($status['class']) ?>"><?= mh($status['label']) ?></span>
            </div>
            <div class="mt-card-body">
                <div class="mt-info-grid">
                    <div class="mt-info-item"><div class="mt-info-label">ชื่อผู้ส่ง</div><div class="mt-info-value"><?= mh($request['sender_name']) ?></div></div>
                    <div class="mt-info-item"><div class="mt-info-label">แผนก</div><div class="mt-info-value"><?= mh($request['department']) ?></div></div>
                    <div class="mt-info-item"><div class="mt-info-label">ระบบที่แจ้งซ่อม</div><div class="mt-info-value"><span class="mt-system-pill"><span class="mt-system-letter"><?= mh($system['icon']) ?></span><?= mh($system['label']) ?></span></div></div>
                    <div class="mt-info-item"><div class="mt-info-label">ช่างผู้รับผิดชอบ</div><div class="mt-info-value"><?= mh($request['technician_name'] ?: 'ยังไม่ระบุ') ?></div></div>
                    <div class="mt-info-item"><div class="mt-info-label">ความเร่งด่วน</div><div class="mt-info-value"><span class="mt-badge mt-priority-<?= mh($priority['class']) ?>"><?= mh($priority['label']) ?></span></div></div>
                    <div class="mt-info-item"><div class="mt-info-label">สถานะปัจจุบัน</div><div class="mt-info-value"><span class="mt-badge mt-status-<?= mh($status['class']) ?>"><?= mh($status['label']) ?></span></div></div>
                    <div class="mt-info-item full"><div class="mt-info-label">สถานที่ / จุดที่พบปัญหา</div><div class="mt-info-value"><?= mh($request['location'] ?: '-') ?></div></div>
                    <div class="mt-info-item full"><div class="mt-info-label">รายละเอียดแจ้งซ่อม</div><div class="mt-info-value mt-note-box"><?= mh($request['details']) ?></div></div>
                    <div class="mt-info-item full"><div class="mt-info-label">หมายเหตุจากช่าง / ผู้ดูแล</div><div class="mt-info-value mt-note-box"><?= mh($request['technician_note'] ?: 'ยังไม่มีหมายเหตุ') ?></div></div>
                    <div class="mt-info-item"><div class="mt-info-label">ปรับปรุงล่าสุด</div><div class="mt-info-value"><?= mh(maintenance_format_datetime($request['updated_at'])) ?></div></div>
                    <div class="mt-info-item"><div class="mt-info-label">เสร็จสิ้นเมื่อ</div><div class="mt-info-value"><?= mh(maintenance_format_datetime($request['completed_at'])) ?></div></div>
                    <?php if (array_key_exists('line_notify_status', $request)): ?>
                    <div class="mt-info-item">
                        <div class="mt-info-label">แจ้งเตือน LINE ล่าสุด</div>
                        <div class="mt-info-value">
                            <?php if (($request['line_notify_status'] ?? '') === 'success'): ?>
                                <span class="mt-badge mt-status-completed">ส่งสำเร็จ</span>
                                <?= !empty($request['line_notified_at']) ? '<div class="mt-secondary-text">'.mh(maintenance_format_datetime($request['line_notified_at'])).'</div>' : '' ?>
                            <?php elseif (($request['line_notify_status'] ?? '') === 'failed'): ?>
                                <span class="mt-badge mt-priority-emergency">ส่งไม่สำเร็จ</span>
                                <?php if (!empty($request['line_notify_error'])): ?><div class="mt-secondary-text"><?= mh($request['line_notify_error']) ?></div><?php endif; ?>
                            <?php else: ?>
                                <span class="mt-secondary-text">ยังไม่มีข้อมูลการแจ้งเตือน</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="mt-card mt-feedback-card" id="feedback">
            <div class="mt-card-head">
                <div class="mt-card-title-wrap">
                    <div class="mt-section-badge mt-feedback-badge">FB</div>
                    <div>
                        <h2>Feedback การให้คะแนนงานซ่อม</h2>
                        <p>ประเมินคุณภาพหลังงานเสร็จสิ้น โดยผู้แจ้งงานของรายการนี้เท่านั้น</p>
                    </div>
                </div>
                <?php if ($feedback): ?>
                    <span class="mt-feedback-score mt-feedback-<?= mh($feedbackMeta['class']) ?>">
                        <strong><?= (int)$feedback['score'] ?></strong><span>คะแนน · <?= mh($feedbackMeta['label']) ?></span>
                    </span>
                <?php endif; ?>
            </div>
            <div class="mt-card-body">
                <?php if (!$feedbackTableReady): ?>
                    <div class="mt-feedback-message error">
                        ยังไม่สามารถเปิดระบบ Feedback ได้ กรุณาให้ผู้ดูแลระบบเข้า <a href="install.php">หน้าติดตั้งฐานข้อมูล</a> เพื่ออัปเดตตาราง
                    </div>
                <?php elseif ($canSubmitFeedback): ?>
                    <form method="post" action="feedback_save.php" class="mt-feedback-form">
                        <input type="hidden" name="csrf_token" value="<?= mh(maintenance_csrf_token()) ?>">
                        <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">

                        <div class="mt-feedback-intro">
                            <div>
                                <strong><?= $feedback ? 'แก้ไข Feedback ของคุณ' : 'ให้คะแนนงานซ่อมรายการนี้' ?></strong>
                                <span><?= $feedback ? 'คุณสามารถเปลี่ยนคะแนนหรือข้อความ แล้วกดบันทึกอีกครั้งได้' : 'เลือกคะแนนที่ตรงกับความพึงพอใจของคุณ' ?></span>
                            </div>
                            <?php if ($feedback): ?><small>อัปเดตล่าสุด <?= mh(maintenance_format_datetime($feedback['updated_at'])) ?></small><?php endif; ?>
                        </div>

                        <div class="mt-rating-options" role="radiogroup" aria-label="คะแนน Feedback">
                            <?php
                            $ratingOptions = [
                                5 => ['label' => 'ดีมาก', 'desc' => 'งานเรียบร้อยและพึงพอใจมาก', 'class' => 'excellent', 'symbol' => '★'],
                                3 => ['label' => 'พอใช้', 'desc' => 'งานใช้งานได้ แต่ยังมีจุดที่พัฒนาได้', 'class' => 'fair', 'symbol' => '●'],
                                1 => ['label' => 'ปรับปรุง', 'desc' => 'ควรตรวจสอบหรือปรับปรุงการให้บริการ', 'class' => 'improve', 'symbol' => '!'],
                            ];
                            foreach ($ratingOptions as $scoreValue => $rating):
                                $checked = $feedback && (int)$feedback['score'] === $scoreValue;
                            ?>
                            <label class="mt-rating-choice mt-rating-<?= mh($rating['class']) ?>">
                                <input type="radio" name="score" value="<?= $scoreValue ?>" <?= $checked ? 'checked' : '' ?> required>
                                <span class="mt-rating-card">
                                    <span class="mt-rating-icon"><?= mh($rating['symbol']) ?></span>
                                    <span class="mt-rating-copy"><strong><?= $scoreValue ?> คะแนน · <?= mh($rating['label']) ?></strong><small><?= mh($rating['desc']) ?></small></span>
                                    <span class="mt-rating-check">✓</span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-field mt-feedback-text-field">
                            <label class="mt-label" for="feedback_text">ความคิดเห็นเพิ่มเติม <span class="mt-optional">(ไม่บังคับ)</span></label>
                            <textarea class="mt-control" id="feedback_text" name="feedback_text" maxlength="2000" placeholder="เช่น งานเรียบร้อย ช่างบริการดี หรือข้อเสนอแนะที่ต้องการให้ปรับปรุง"><?= mh($feedback['feedback_text'] ?? '') ?></textarea>
                            <div class="mt-feedback-help">Feedback นี้จะผูกกับงาน #<?= (int)$request['id'] ?> และบัญชีผู้แจ้งงานเท่านั้น</div>
                        </div>

                        <div class="mt-form-actions">
                            <button class="mt-btn mt-btn-teal" type="submit"><?= $feedback ? 'บันทึกการแก้ไข Feedback' : 'ส่ง Feedback' ?></button>
                        </div>
                    </form>
                <?php elseif ($isRequestOwner && !$feedback && $request['status'] !== 'completed'): ?>
                    <div class="mt-feedback-message info">
                        <strong>ยังไม่เปิดให้ประเมินงาน</strong>
                        <span>คุณเป็นผู้แจ้งรายการนี้ ระบบจะเปิด Feedback เมื่อสถานะงานถูกบันทึกเป็น “เสร็จสิ้น”</span>
                    </div>
                <?php elseif ($feedback): ?>
                    <div class="mt-feedback-display">
                        <div class="mt-feedback-display-score mt-feedback-<?= mh($feedbackMeta['class']) ?>">
                            <span class="mt-feedback-display-number"><?= (int)$feedback['score'] ?></span>
                            <div><strong><?= mh($feedbackMeta['label']) ?></strong><small>คะแนนจากผู้แจ้งงาน</small></div>
                        </div>
                        <div class="mt-feedback-display-text">
                            <span>ความคิดเห็น</span>
                            <p><?= $feedback['feedback_text'] !== null && trim($feedback['feedback_text']) !== '' ? nl2br(mh($feedback['feedback_text'])) : 'ไม่มีความคิดเห็นเพิ่มเติม' ?></p>
                            <small>อัปเดตล่าสุด <?= mh(maintenance_format_datetime($feedback['updated_at'])) ?></small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mt-feedback-message neutral">
                        <strong>ยังไม่มี Feedback</strong>
                        <span>รอผู้แจ้งงานของรายการนี้ประเมินหลังงานเสร็จสิ้น</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        </div>

        <aside>
            <?php if ($canManage): ?>
            <section class="mt-card mt-manage-card" id="manage">
                <div class="mt-card-head">
                    <div><h2>จัดการสถานะงาน</h2><p>สำหรับช่างหรือผู้ดูแลระบบ</p></div>
                </div>
                <div class="mt-card-body">
                    <form method="post" action="status_update.php">
                        <input type="hidden" name="csrf_token" value="<?= mh(maintenance_csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$request['id'] ?>">

                        <div class="mt-field">
                            <label class="mt-label" for="status">สถานะงาน</label>
                            <select class="mt-control" id="status" name="status" required>
                                <?php foreach (['pending'=>'รอรับงาน','assigned'=>'มอบหมายแล้ว','in_progress'=>'กำลังดำเนินการ','completed'=>'เสร็จสิ้น','cancelled'=>'ยกเลิก'] as $value=>$label): ?>
                                    <option value="<?= $value ?>" <?= $request['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mt-field" style="margin-top:14px">
                            <label class="mt-label" for="technician_id">ช่างผู้รับผิดชอบ</label>
                            <select class="mt-control" id="technician_id" name="technician_id">
                                <option value="0">-- ยังไม่มอบหมาย --</option>
                                <?php foreach ($technicians as $tech): ?>
                                    <option value="<?= (int)$tech['id'] ?>" <?= (int)$request['technician_id'] === (int)$tech['id'] ? 'selected' : '' ?>><?= mh(trim((string)$tech['name'])) ?><?= $tech['department'] ? ' — ' . mh($tech['department']) : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mt-field" style="margin-top:14px">
                            <label class="mt-label" for="technician_note">หมายเหตุการดำเนินงาน</label>
                            <textarea class="mt-control" id="technician_note" name="technician_note" maxlength="3000" placeholder="บันทึกผลการตรวจสอบ การแก้ไข หรืออะไหล่ที่ใช้"><?= mh($request['technician_note']) ?></textarea>
                        </div>
                        <div class="mt-form-actions"><button class="mt-btn mt-btn-teal mt-btn-block" type="submit">บันทึกสถานะงาน</button></div>
                    </form>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($canDelete): ?>
            <section class="mt-card" style="margin-top:20px">
                <div class="mt-card-head"><div><h2>การจัดการรายการ</h2><p>ลบเฉพาะรายการที่ไม่ต้องการใช้งานจริง</p></div></div>
                <div class="mt-card-body">
                    <form method="post" action="delete.php" onsubmit="return confirm('ยืนยันการลบรายการ #<?= (int)$request['id'] ?> ? ข้อมูลที่ลบจะไม่สามารถกู้คืนจากหน้านี้ได้');">
                        <input type="hidden" name="csrf_token" value="<?= mh(maintenance_csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$request['id'] ?>">
                        <button class="mt-btn mt-btn-danger mt-btn-block" type="submit">ลบรายการนี้</button>
                    </form>
                </div>
            </section>
            <?php endif; ?>
        </aside>
    </div>
</main>
</body>
</html>
