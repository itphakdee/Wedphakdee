<?php
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

if (empty($_SESSION['repair_csrf_token'])) {
    $_SESSION['repair_csrf_token'] = bin2hex(random_bytes(32));
}

$technicians = [];
$result = $conn->query("SELECT id, name FROM technicians WHERE status='ใช้งาน' ORDER BY name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $technicians[] = $row;
    }
}

$senderDefault = trim((string)($_SESSION['fullname'] ?? $_SESSION['name'] ?? ''));
$flashType = '';
$flashMessage = '';
$flashDetail = '';

$lineDebug = isset($_SESSION['repair_line_result']) && is_array($_SESSION['repair_line_result'])
    ? $_SESSION['repair_line_result']
    : array();
unset($_SESSION['repair_line_result']);

$formError = isset($_SESSION['repair_form_error']) ? (string)$_SESSION['repair_form_error'] : '';
unset($_SESSION['repair_form_error']);

if (isset($_GET['saved']) && $_GET['saved'] === '1') {
    $jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
    $lineStatus = isset($_GET['line']) ? (string)$_GET['line'] : '';

    if ($lineStatus === 'success') {
        $flashType = 'success';
        $flashMessage = 'บันทึกงานแจ้งซ่อม #' . $jobId . ' และส่งแจ้งเตือน LINE เรียบร้อยแล้ว';
    } elseif ($lineStatus === 'disabled') {
        $flashType = 'warning';
        $flashMessage = 'บันทึกงานแจ้งซ่อม #' . $jobId . ' แล้ว แต่ยังไม่ได้ตั้งค่า LINE Apps Script';
    } else {
        $flashType = 'warning';
        $flashMessage = 'บันทึกงานแจ้งซ่อม #' . $jobId . ' แล้ว แต่ส่งแจ้งเตือน LINE ไม่สำเร็จ';

        if (!empty($lineDebug['error'])) {
            $flashDetail = (string)$lineDebug['error'];
        } elseif (!empty($lineDebug['response'])) {
            $flashDetail = (string)$lineDebug['response'];
        }

        if (!empty($lineDebug['http_code'])) {
            $flashDetail = 'HTTP ' . (int)$lineDebug['http_code'] . ($flashDetail !== '' ? ' — ' . $flashDetail : '');
        }
    }
} elseif (isset($_GET['error'])) {
    $flashType = 'error';
    $flashMessage = $formError !== '' ? $formError : 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งซ่อม | โรงพยาบาลภักดีชุมพล</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="repair_form.css?v=20260831-1">
</head>
<body class="repair-form-page">
<?php
$activePage = 'computer';
$basePath = '../../';
require __DIR__ . '/../../components/sidebar.php';
?>

<main class="rf-main">
    <header class="rf-page-header">
        <div>
            <div class="rf-eyebrow">PHAKDEE CHUMPHON HOSPITAL · MAINTENANCE SERVICE</div>
            <h1>แจ้งซ่อมและขอรับบริการ</h1>
            <p>บันทึกรายละเอียดปัญหาเพื่อส่งต่อให้ผู้รับผิดชอบ พร้อมแจ้งเตือนผ่าน LINE Messaging API</p>
        </div>
        <a href="indexrepairlist.php" class="rf-back-btn" aria-label="กลับหน้ารายการแจ้งซ่อม">
            <span aria-hidden="true">←</span> กลับหน้ารายการ
        </a>
    </header>

    <?php if ($flashMessage !== ''): ?>
        <div class="rf-alert rf-alert-<?= htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8') ?>" role="alert">
            <span class="rf-alert-dot" aria-hidden="true"></span>
            <span>
                <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
                <?php if ($flashDetail !== ''): ?>
                    <small style="display:block;margin-top:6px;opacity:.85;word-break:break-word;">
                        รายละเอียด: <?= htmlspecialchars($flashDetail, ENT_QUOTES, 'UTF-8') ?>
                    </small>
                <?php endif; ?>
            </span>
        </div>
    <?php endif; ?>

    <section class="rf-card">
        <div class="rf-card-head">
            <div class="rf-title-icon" aria-hidden="true">🔧</div>
            <div>
                <div class="rf-section-kicker">REPAIR REQUEST FORM</div>
                <h2>แบบฟอร์มแจ้งรายละเอียดการซ่อม</h2>
                <p>กรุณาระบุข้อมูลให้ครบถ้วน เพื่อให้เจ้าหน้าที่ประเมินและดำเนินงานได้รวดเร็ว</p>
            </div>
            <div class="rf-line-status">
                <span class="rf-line-dot"></span>
                แจ้งเตือน LINE ผ่าน Apps Script
            </div>
        </div>

        <form action="save_repair.php" method="POST" class="rf-form" id="repairRequestForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['repair_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="rf-section">
                <div class="rf-section-title">
                    <span class="rf-section-no">01</span>
                    <div>
                        <h3>ข้อมูลผู้แจ้ง</h3>
                        <p>ข้อมูลสำหรับติดต่อและระบุต้นทางของงานแจ้งซ่อม</p>
                    </div>
                </div>

                <div class="rf-grid rf-grid-2">
                    <div class="rf-field">
                        <label for="sender_name">ชื่อผู้ส่ง <span>*</span></label>
                        <div class="rf-control-wrap">
                            <span class="rf-control-icon">👤</span>
                            <input id="sender_name" type="text" name="sender_name" required maxlength="150"
                                placeholder="ชื่อ - นามสกุล ผู้แจ้ง"
                                value="<?= htmlspecialchars($senderDefault, ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="rf-field">
                        <label for="department">แผนก / หน่วยงาน <span>*</span></label>
                        <div class="rf-control-wrap">
                            <span class="rf-control-icon">🏥</span>
                            <select id="department" name="department" required>
                                <option value="">เลือกแผนก / หน่วยงาน</option>
                                <option value="IT">IT</option>
                                <option value="บริหาร">บริหาร</option>
                                <option value="OPD">OPD</option>
                                <option value="IPD">IPD</option>
                                <option value="ห้องฉุกเฉิน">ห้องฉุกเฉิน</option>
                                <option value="ห้องคลอด">ห้องคลอด</option>
                                <option value="เภสัชกรรม">เภสัชกรรม</option>
                                <option value="ห้องปฏิบัติการ">ห้องปฏิบัติการ</option>
                                <option value="อื่น ๆ">อื่น ๆ</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rf-divider"></div>

            <div class="rf-section">
                <div class="rf-section-title">
                    <span class="rf-section-no">02</span>
                    <div>
                        <h3>รายละเอียดงานซ่อม</h3>
                        <p>ระบุประเภทระบบ สถานที่ ผู้รับผิดชอบ และระดับความเร่งด่วน</p>
                    </div>
                </div>

                <div class="rf-grid rf-grid-2">
                    <div class="rf-field">
                        <label for="repair_system">ระบบที่ต้องการแจ้งซ่อม <span>*</span></label>
                        <div class="rf-control-wrap">
                            <span class="rf-control-icon">🛠️</span>
                            <select id="repair_system" name="repair_system" required>
                                <option value="">เลือกระบบที่ต้องการแจ้งซ่อม</option>
                                <option value="ระบบไฟฟ้า">ระบบไฟฟ้า</option>
                                <option value="ระบบคอมพิวเตอร์">ระบบคอมพิวเตอร์</option>
                                <option value="ระบบจัดการเครื่องมือแพทย์">ระบบจัดการเครื่องมือแพทย์</option>
                                <option value="ระบบประปา">ระบบประปา</option>
                                <option value="ระบบปรับอากาศ">ระบบปรับอากาศ</option>
                                <option value="ระบบทั่วไป">ระบบทั่วไป</option>
                            </select>
                        </div>
                    </div>

                    <div class="rf-field">
                        <label for="location">สถานที่พบปัญหา <span>*</span></label>
                        <div class="rf-control-wrap">
                            <span class="rf-control-icon">📍</span>
                            <select id="location" name="location" required>
                                <option value="">เลือกสถานที่</option>
                                <option value="ผู้ป่วยนอก">ผู้ป่วยนอก (OPD)</option>
                                <option value="ผู้ป่วยใน">ผู้ป่วยใน (IPD)</option>
                                <option value="บริหาร">อาคารบริหาร</option>
                                <option value="ตึก10เตียง">ตึก 10 เตียง</option>
                                <option value="ห้องฉุกเฉิน">ห้องฉุกเฉิน</option>
                                <option value="อื่น ๆ">อื่น ๆ</option>
                            </select>
                        </div>
                    </div>

                    <div class="rf-field">
                        <label for="technician_id">เลือกช่าง / ผู้รับผิดชอบ <span>*</span></label>
                        <div class="rf-control-wrap">
                            <span class="rf-control-icon">👷</span>
                            <select id="technician_id" name="technician_id" required>
                                <option value="">เลือกช่าง / ผู้รับผิดชอบ</option>
                                <?php foreach ($technicians as $tech): ?>
                                    <option value="<?= (int)$tech['id'] ?>">
                                        <?= htmlspecialchars($tech['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (empty($technicians)): ?>
                            <small class="rf-help rf-help-warning">ยังไม่มีรายชื่อช่างที่มีสถานะ “ใช้งาน” ในระบบ</small>
                        <?php endif; ?>
                    </div>

                    <div class="rf-field">
                        <label for="priority">ความเร่งด่วน <span>*</span></label>
                        <div class="rf-priority-group" role="radiogroup" aria-label="ความเร่งด่วน">
                            <label class="rf-priority rf-priority-normal">
                                <input type="radio" name="priority" value="normal" checked>
                                <span><b>ปกติ</b><small>ดำเนินการตามลำดับงาน</small></span>
                            </label>
                            <label class="rf-priority rf-priority-urgent">
                                <input type="radio" name="priority" value="urgent">
                                <span><b>ด่วน</b><small>ควรเร่งตรวจสอบ</small></span>
                            </label>
                            <label class="rf-priority rf-priority-emergency">
                                <input type="radio" name="priority" value="emergency">
                                <span><b>ด่วนมาก</b><small>กระทบการให้บริการ</small></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="rf-field rf-field-full">
                    <label for="details">รายละเอียดแจ้งซ่อม <span>*</span></label>
                    <textarea id="details" name="details" required maxlength="2000"
                        placeholder="อธิบายอาการ ปัญหาที่พบ หมายเลขเครื่อง หรือข้อมูลที่ช่วยให้ช่างตรวจสอบได้รวดเร็ว"></textarea>
                    <div class="rf-field-meta">
                        <span>โปรดหลีกเลี่ยงข้อมูลผู้ป่วยหรือข้อมูลส่วนบุคคลที่ไม่จำเป็น</span>
                        <span id="detailCounter">0 / 2000</span>
                    </div>
                </div>
            </div>

            <div class="rf-submit-panel">
                <div class="rf-submit-note">
                    <span class="rf-submit-note-icon">🔔</span>
                    <div>
                        <strong>ระบบจะแจ้งเตือน LINE อัตโนมัติ</strong>
                        <small>ข้อมูลจะถูกบันทึกในระบบก่อน แล้วจึงส่งแจ้งเตือนผ่าน Google Apps Script ไปยัง LINE Messaging API</small>
                    </div>
                </div>
                <button type="submit" class="rf-submit-btn" id="submitBtn">
                    <span class="rf-submit-btn-icon">✓</span>
                    <span>บันทึกและส่งแจ้งเตือน LINE</span>
                </button>
            </div>
        </form>
    </section>

    <footer class="rf-footer">
        โรงพยาบาลภักดีชุมพล · ระบบแจ้งซ่อมและบริหารงานภายใน
    </footer>
</main>

<script>
(function () {
    const form = document.getElementById('repairRequestForm');
    const details = document.getElementById('details');
    const counter = document.getElementById('detailCounter');
    const button = document.getElementById('submitBtn');

    function updateCounter() {
        counter.textContent = details.value.length + ' / 2000';
    }

    details.addEventListener('input', updateCounter);
    updateCounter();

    form.addEventListener('submit', function () {
        button.disabled = true;
        button.classList.add('is-loading');
        button.querySelector('span:last-child').textContent = 'กำลังบันทึกและส่งแจ้งเตือน...';
    });
})();
</script>
</body>
</html>
