<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('create');
if (!personnel_schema_ready()) {
    header('Location: install.php');
    exit;
}
$departments = personnel_departments();
$career = [];
foreach (array_keys(personnel_career_option_types()) as $type) $career[$type] = personnel_career_options($type);
$flash = personnel_pull_flash();
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>เพิ่มบุคลากร</title>
    <link rel="stylesheet" href="assets/personnel.css?v=2.0">
</head>

<body>
    <div class="personnel-shell">
        <section class="hero">
            <div class="hero-grid">
                <div>
                    <div class="eyebrow">PHAKDEE CHUMPHON HOSPITAL · PERSONNEL</div>
                    <h1>เพิ่มข้อมูลบุคลากร</h1>
                    <p>สร้างทะเบียนบุคลากร บัญชีเข้าสู่ระบบ และข้อมูลอาชีพสำหรับการบริหารกำลังคน</p>
                </div>
                <div class="hero-actions"><?php if (personnel_is_admin()): ?><a class="btn btn-soft" href="career_options.php">⚙ จัดการตัวเลือกอาชีพ</a><?php endif; ?><a class="btn btn-outline" href="index.php">← กลับหน้ารายการ</a></div>
            </div>
        </section>
        <?php if ($flash): ?><div class="alert <?= ($flash['type'] === 'success' ? 'alert-success' : 'alert-error') ?>"><?= ph($flash['message']) ?></div><?php endif; ?>
        <section class="card">
            <div class="card-head">
                <div class="card-title">
                    <div class="icon-box">＋</div>
                    <div>
                        <h2>แบบฟอร์มข้อมูลบุคลากร</h2>
                        <div class="card-sub">ช่องที่มีเครื่องหมาย * จำเป็นต้องกรอก</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="store.php" autocomplete="off"><input type="hidden" name="csrf_token" value="<?= ph(personnel_csrf_token()) ?>">
                    <div class="grid">
                        <div class="section-label">ข้อมูลบัญชีเข้าสู่ระบบ</div>
                        <div class="field col-6"><label>Username <span class="req">*</span></label><input type="text" name="username" maxlength="50" required pattern="[A-Za-z0-9._-]{3,50}" placeholder="เช่น somchai.s"><small>ใช้ตัวอักษรอังกฤษ ตัวเลข จุด ขีดกลาง หรือขีดล่าง 3-50 ตัวอักษร</small></div>
                        <div class="field col-6"><label>รหัสผ่านเริ่มต้น</label><input type="text" value="123456" readonly><small>ผู้ดูแลระบบสามารถเปลี่ยนได้ภายหลัง</small></div>
                        <div class="section-label">ข้อมูลส่วนบุคคล</div>
                        <div class="field col-3"><label>คำนำหน้า <span class="req">*</span></label><select name="prefix" required>
                                <option value="">-- เลือกคำนำหน้า --</option><?php foreach (personnel_prefix_options() as $v): ?><option value="<?= ph($v) ?>"><?= ph($v) ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="field col-4"><label>ชื่อ <span class="req">*</span></label><input type="text" name="first_name" maxlength="100" required></div>
                        <div class="field col-5"><label>นามสกุล <span class="req">*</span></label><input type="text" name="last_name" maxlength="100" required></div>
                        <div class="field col-4"><label>ชื่ออังกฤษ</label><input type="text" name="first_name_en" maxlength="150" placeholder="English name"></div>
                        <div class="field col-4"><label>ชื่อเล่น</label><input type="text" name="nickname" maxlength="100"></div>
                        <div class="field col-4"><label>เพศ <span class="req">*</span></label><select name="gender" required>
                                <option value="">-- เลือกเพศ --</option><?php foreach (personnel_gender_options() as $v): ?><option value="<?= ph($v) ?>"><?= ph($v) ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="field col-4"><label>วันเกิด <span class="req">*</span></label><input type="date" name="birth_date" required max="<?= date('Y-m-d') ?>"></div>
                        <div class="field col-4"><label>เลขประจำตัวประชาชน <span class="req">*</span></label><input type="text" name="national_id" inputmode="numeric" maxlength="17" required placeholder="เลข 13 หลัก"><small>ระบบจะบันทึกเฉพาะตัวเลข 13 หลัก</small></div>
                        <div class="field col-4"><label>อีเมล <span class="req">*</span></label><input type="email" name="email" maxlength="150" required placeholder="name@hospital.go.th"></div>

                        <div class="col-12 career-wrap">
                            <details class="career-panel" open>
                                <summary>
                                    <div><strong>ข้อมูลอาชีพ <span class="req">*</span></strong><small>ข้อมูลโครงสร้างงาน ตำแหน่ง ระดับ และกลุ่มบุคลากร</small></div><span class="career-chevron">⌃</span>
                                </summary>
                                <div class="career-body">
                                    <div class="grid">
                                        <div class="field col-4"><label>กลุ่มงาน <span class="req">*</span></label><select name="work_group_id" required>
                                                <option value="">-- กรุณาเลือกกลุ่มงาน --</option><?php foreach ($career['work_group'] as $o): ?><option value="<?= intval($o['id']) ?>"><?= ph($o['option_name']) ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>ฝ่าย / แผนก <span class="req">*</span></label><select name="division_id" required>
                                                <option value="">-- กรุณาเลือกฝ่าย / แผนก --</option><?php foreach ($career['division'] as $o): ?><option value="<?= intval($o['id']) ?>"><?= ph($o['option_name']) ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>หน่วยงาน <span class="req">*</span></label><select name="department_id" id="department_id" required>
                                                <option value="">-- กรุณาเลือกหน่วยงาน --</option><?php foreach ($departments as $d): ?><option value="<?= intval($d['id']) ?>" data-name="<?= ph($d['department_name']) ?>"><?= ph($d['department_name']) ?></option><?php endforeach; ?>
                                            </select><input type="hidden" name="department" id="department_name" value=""></div>
                                        <div class="field col-4"><label>วันที่บรรจุ <span class="req">*</span></label><input type="date" name="appointment_date" required></div>
                                        <div class="field col-4"><label>เลขตำแหน่ง</label><input type="text" name="position_number" maxlength="100"></div>
                                        <div class="field col-4"><label>เลขใบประกอบวิชาชีพ</label><input type="text" name="professional_license_no" maxlength="100"></div>
                                        <div class="field col-4"><label>ว.ด.ป. รับใบประกอบ</label><input type="date" name="license_issue_date"></div>
                                        <div class="field col-4"><label>ตำแหน่ง <span class="req">*</span></label><select name="position_id" required>
                                                <option value="">-- กรุณาเลือกตำแหน่ง --</option><?php foreach ($career['position'] as $o): ?><option value="<?= intval($o['id']) ?>"><?= ph($o['option_name']) ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>ระดับ <span class="req">*</span></label><select name="level_id" required>
                                                <option value="">-- กรุณาเลือกระดับ --</option><?php foreach ($career['level'] as $o): ?><option value="<?= intval($o['id']) ?>"><?= ph($o['option_name']) ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>สถานะปัจจุบัน <span class="req">*</span></label><select name="current_status_id" required>
                                                <option value="">-- กรุณาเลือกสถานะ --</option><?php foreach ($career['current_status'] as $o): ?><option value="<?= intval($o['id']) ?>"><?= ph($o['option_name']) ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>กลุ่มข้าราชการ <span class="req">*</span></label><select name="civil_service_group_id" required>
                                                <option value="">-- กรุณาเลือกกลุ่มข้าราชการ --</option><?php foreach ($career['civil_group'] as $o): ?><option value="<?= intval($o['id']) ?>"><?= ph($o['option_name']) ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>ประเภทข้าราชการ <span class="req">*</span></label><select name="civil_service_type_id" required>
                                                <option value="">-- กรุณาเลือกประเภทข้าราชการ --</option><?php foreach ($career['civil_type'] as $o): ?><option value="<?= intval($o['id']) ?>"><?= ph($o['option_name']) ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>กลุ่มบุคลากร <span class="req">*</span></label><select name="personnel_group_id" required>
                                                <option value="">-- กรุณาเลือกกลุ่มบุคลากร --</option><?php foreach ($career['personnel_group'] as $o): ?><option value="<?= intval($o['id']) ?>"><?= ph($o['option_name']) ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>ต้นสังกัด</label><input type="text" name="affiliation" maxlength="150" value="โรงพยาบาลภักดีชุมพล"></div>
                                        <div class="field col-4"><label>เงินเดือน</label>
                                            <div class="money-input"><span>฿</span><input type="number" name="salary" min="0" step="0.01" placeholder="0.00"></div>
                                        </div>
                                        <div class="field col-4"><label>เงินประจำตำแหน่ง</label>
                                            <div class="money-input"><span>฿</span><input type="number" name="position_allowance" min="0" step="0.01" placeholder="0.00"></div>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </div>

                        <div class="section-label">ข้อมูลการติดต่อและทะเบียนระบบ</div>
                        <div class="field col-4"><label>รหัสบุคลากร</label><input type="text" name="employee_code" maxlength="50"></div>
                        <div class="field col-4"><label>เบอร์โทรศัพท์</label><input type="text" name="phone" maxlength="50"></div>
                        <div class="field col-4"><label>สถานที่ / ห้อง</label><input type="text" name="room_location" maxlength="150"></div>
                        <div class="field col-4"><label>สถานะทะเบียน</label><select name="status">
                                <option value="active">ปฏิบัติงาน</option>
                                <option value="inactive">พ้นสภาพ / ไม่ปฏิบัติงาน</option>
                            </select></div>
                        <div class="field col-8"><label>หมายเหตุ</label><textarea name="notes" maxlength="2000" placeholder="ข้อมูลเพิ่มเติมที่จำเป็นต่อการบริหารบุคลากร"></textarea></div>
                    </div>
                    <div class="form-actions"><a class="btn btn-soft" href="index.php">ยกเลิก</a><button class="btn btn-primary" type="submit">💾 บันทึกข้อมูลบุคลากร</button></div>
                </form>
            </div>
        </section>
    </div>
    <script>
        (function() {
            var s = document.getElementById('department_id'),
                h = document.getElementById('department_name');

            function sync() {
                if (!s || !h) return;
                var o = s.options[s.selectedIndex];
                h.value = o && o.dataset ? o.dataset.name || '' : '';
            }
            if (s && h) {
                s.addEventListener('change', sync);
                sync();
            }
        })();
    </script>
</body>

</html>