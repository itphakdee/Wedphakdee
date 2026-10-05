<?php
session_start();
require_once '../../config.php';
date_default_timezone_set('Asia/Bangkok');

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf_token'];
$today = date('Y-m-d');

$buildingTypes = ['อาคารผู้ป่วยใน', 'อาคารผู้ป่วยนอก', 'อาคารสำนักงาน', 'อาคารพักอาศัย', 'อาคารสนับสนุนบริการ', 'สิ่งปลูกสร้างอื่น ๆ'];
$budgetTypes = ['งบประมาณ', 'เงินบำรุง', 'เงินบริจาค', 'อื่น ๆ'];
$conditions = ['ใช้งานปกติ', 'ต้องซ่อมแซม', 'ชำรุด', 'รื้อถอน'];
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>เพิ่มข้อมูล อาคาร/สิ่งปลูกสร้าง</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="structure_add.css">
</head>

<body>
    <div class="structure-page">
        <header class="structure-header">
            <div class="header-left">
                <div class="header-icon"><i class="fa-solid fa-building"></i></div>
                <div>
                    <h1>เพิ่มข้อมูล อาคาร/สิ่งปลูกสร้าง</h1>
                    <div class="header-subtitle">ระบบทะเบียนอาคารและสิ่งปลูกสร้าง</div>
                </div>
            </div>
            <a href="index.php" class="btn btn-light btn-back"><i class="fa-solid fa-arrow-left"></i> กลับรายการ</a>
        </header>

        <form id="buildingForm" action="save.php" method="post" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <div class="editor-card">
                <section class="preview-section">
                    <div class="preview-toolbar">
                        <label class="file-button"><i class="fa-solid fa-image"></i> เลือกรูปภาพ
                            <input type="file" id="building_image" name="building_image" accept="image/jpeg,image/png,image/webp">
                        </label>
                        <span id="imageFileName" class="file-name">ยังไม่ได้เลือกไฟล์</span>
                    </div>
                    <div class="preview-box">
                        <div id="previewPlaceholder" class="preview-placeholder"><i class="fa-regular fa-image"></i>
                            <div>เลือกรูปภาพอาคาร</div><small>JPG / JPEG / PNG / WEBP</small>
                        </div>
                        <img id="buildingPreview" class="building-preview" alt="ตัวอย่างรูปภาพอาคาร">
                    </div>
                    <div class="preview-bottom">
                        <label class="file-button file-button-secondary"><i class="fa-solid fa-file-image"></i> แบบแปลน / เอกสารภาพ
                            <input type="file" name="blueprint" accept=".pdf,.jpg,.jpeg,.png">
                        </label>
                        <span class="file-help">รองรับ PDF, JPG, JPEG, PNG</span>
                    </div>
                </section>

                <section class="form-section">
                    <div class="section-title"><i class="fa-solid fa-circle-info"></i> รายละเอียด</div>
                    <div class="form-row"><label>ประเภทสิ่งปลูกสร้าง</label><select name="building_type" class="form-select" required>
                            <option value="">-- กรุณาเลือกประเภท --</option><?php foreach ($buildingTypes as $v): ?><option value="<?= e($v) ?>"><?= e($v) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="form-row"><label>ชื่ออาคาร</label><input name="building_name" class="form-control" placeholder="ระบุชื่ออาคาร" required></div>
                    <div class="form-row"><label>รหัสอาคาร / เลขครุภัณฑ์</label><input name="building_code" class="form-control" placeholder="ระบุรหัสอาคาร"></div>
                    <div class="form-row"><label>งบประมาณ</label><select name="budget_type" class="form-select" required>
                            <option value="">-- เลือกประเภทงบประมาณ --</option><?php foreach ($budgetTypes as $v): ?><option value="<?= e($v) ?>"><?= e($v) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="form-row"><label>สถานะการใช้งาน</label><select name="condition_status" class="form-select">
                            <option value="">-- เลือกสถานะ --</option><?php foreach ($conditions as $v): ?><option value="<?= e($v) ?>"><?= e($v) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="form-row"><label>วงเงินงบประมาณ</label>
                        <div class="input-group"><input type="number" name="amount" class="form-control" min="0" step="0.01" placeholder="0.00"><span class="input-group-text">บาท</span></div>
                    </div>
                    <div class="form-row two-column">
                        <div><label>จำนวนชั้น</label><input type="number" name="floors" class="form-control" min="0"></div>
                        <div><label>อายุการใช้งาน</label>
                            <div class="input-group"><input type="number" name="useful_life" id="useful_life" class="form-control" min="0"><span class="input-group-text">ปี</span></div>
                        </div>
                    </div>
                    <div class="form-row two-column">
                        <div><label>วันที่ก่อสร้าง</label><input type="date" name="construction_date" id="construction_date" class="form-control"></div>
                        <div><label>วันที่สิ้นสุด/ครบกำหนด</label><input type="date" name="end_date" id="end_date" class="form-control"></div>
                    </div>
                    <div class="form-row"><label>ผู้รับผิดชอบ</label><input name="responsible_person" class="form-control" placeholder="ระบุผู้รับผิดชอบ"></div>
                    <div class="form-row"><label>หน่วยงาน</label><input name="department" class="form-control" placeholder="ระบุหน่วยงาน"></div>
                    <div class="form-row"><label>สถานที่ตั้ง</label><input name="location" class="form-control" placeholder="ระบุสถานที่ตั้งอาคาร"></div>
                    <div class="form-row"><label>รายละเอียด</label><textarea name="details" class="form-control" rows="2" placeholder="รายละเอียดเพิ่มเติม"></textarea></div>
                    <div class="form-row"><label>หมายเหตุ</label><textarea name="remark" class="form-control" rows="2" placeholder="หมายเหตุ"></textarea></div>
                    <input type="hidden" name="created_date" value="<?= e($today) ?>">
                </section>
            </div>
            <div class="form-footer"><a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-xmark"></i> ยกเลิก</a><button type="submit" id="saveButton" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> บันทึกข้อมูล</button></div>
        </form>
    </div>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="structure_add.js"></script>
</body>

</html>