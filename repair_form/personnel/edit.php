<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('edit');
if (!personnel_schema_ready()) {
    header('Location:install.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location:index.php');
    exit;
}
$stmt = $conn->prepare('SELECT * FROM personnel WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$person = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$person) {
    http_response_code(404);
    die('ไม่พบข้อมูลบุคลากร');
}
$departments = personnel_departments();
$career = [];
foreach (array_keys(personnel_career_option_types()) as $type) $career[$type] = personnel_career_options($type, false);
$flash = personnel_pull_flash();
function selected_id($a, $b)
{
    return (string)$a === (string)$b ? 'selected' : '';
}
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>แก้ไขข้อมูลบุคลากร</title>
    <link rel="stylesheet" href="assets/personnel.css?v=2.0">
</head>

<body>
    <div class="personnel-shell">
        <section class="hero">
            <div class="hero-grid">
                <div>
                    <div class="eyebrow">EDIT PERSONNEL #<?= intval($id) ?></div>
                    <h1>แก้ไขข้อมูลบุคลากร</h1>
                    <p>ปรับปรุงข้อมูลประจำตัว ข้อมูลอาชีพ และบัญชีเข้าสู่ระบบ</p>
                </div>
                <div class="hero-actions"><?php if (personnel_is_admin()): ?><a class="btn btn-soft" href="career_options.php">⚙ ตัวเลือกอาชีพ</a><?php endif; ?><a class="btn btn-outline" href="detail.php?id=<?= $id ?>">← กลับรายละเอียด</a></div>
            </div>
        </section>
        <?php if ($flash): ?><div class="alert <?= ($flash['type'] === 'success' ? 'alert-success' : 'alert-error') ?>"><?= ph($flash['message']) ?></div><?php endif; ?>
        <section class="card">
            <div class="card-head">
                <div>
                    <h2>แก้ไขทะเบียนบุคลากร</h2>
                    <div class="card-sub">ข้อมูลอาชีพที่เป็นหมวดหมู่ใช้ Dropdown จากฐานข้อมูลกลาง</div>
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="update.php"><input type="hidden" name="csrf_token" value="<?= ph(personnel_csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>">
                    <div class="grid">
                        <div class="section-label">ข้อมูลบัญชีเข้าสู่ระบบ</div>
                        <div class="field col-6"><label>Username <span class="req">*</span></label><input type="text" name="username" required pattern="[A-Za-z0-9._-]{3,50}" maxlength="50" value="<?= ph($person['username']) ?>"></div>
                        <div class="field col-6"><label>อีเมล <span class="req">*</span></label><input type="email" name="email" required value="<?= ph($person['email']) ?>"></div>
                        <div class="section-label">ข้อมูลส่วนบุคคล</div>
                        <div class="field col-3"><label>คำนำหน้า <span class="req">*</span></label><select name="prefix" required>
                                <option value="">-- เลือก --</option><?php foreach (personnel_prefix_options() as $v): ?><option value="<?= ph($v) ?>" <?= $person['prefix'] === $v ? 'selected' : '' ?>><?= ph($v) ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="field col-4"><label>ชื่อ <span class="req">*</span></label><input name="first_name" required value="<?= ph($person['first_name']) ?>"></div>
                        <div class="field col-5"><label>นามสกุล <span class="req">*</span></label><input name="last_name" required value="<?= ph($person['last_name']) ?>"></div>
                        <div class="field col-4"><label>ชื่ออังกฤษ</label><input name="first_name_en" value="<?= ph($person['first_name_en']) ?>"></div>
                        <div class="field col-4"><label>ชื่อเล่น</label><input name="nickname" value="<?= ph($person['nickname']) ?>"></div>
                        <div class="field col-4"><label>เพศ <span class="req">*</span></label><select name="gender" required><?php foreach (personnel_gender_options() as $v): ?><option value="<?= ph($v) ?>" <?= $person['gender'] === $v ? 'selected' : '' ?>><?= ph($v) ?></option><?php endforeach; ?></select></div>
                        <div class="field col-4"><label>วันเกิด <span class="req">*</span></label><input type="date" name="birth_date" required value="<?= ph($person['birth_date']) ?>"></div>
                        <div class="field col-4"><label>เลขประจำตัวประชาชน <span class="req">*</span></label><input name="national_id" required value="<?= ph(personnel_format_national_id($person['national_id'])) ?>"></div>
                        <div class="field col-4"><label>รหัสบุคลากร</label><input name="employee_code" value="<?= ph($person['employee_code']) ?>"></div>
                        <div class="col-12 career-wrap">
                            <details class="career-panel" open>
                                <summary>
                                    <div><strong>ข้อมูลอาชีพ <span class="req">*</span></strong><small>แก้ไขโครงสร้างงาน ตำแหน่ง ระดับ และสิทธิ์กำลังคน</small></div><span class="career-chevron">⌃</span>
                                </summary>
                                <div class="career-body">
                                    <div class="grid">
                                        <div class="field col-4"><label>กลุ่มงาน <span class="req">*</span></label><select name="work_group_id" required>
                                                <option value="">-- เลือกกลุ่มงาน --</option><?php foreach ($career['work_group'] as $o): ?><option value="<?= $o['id'] ?>" <?= selected_id($person['work_group_id'], $o['id']) ?>><?= ph($o['option_name']) ?><?= $o['status'] === 'inactive' ? ' (ปิดใช้งาน)' : '' ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>ฝ่าย / แผนก <span class="req">*</span></label><select name="division_id" required>
                                                <option value="">-- เลือกฝ่าย / แผนก --</option><?php foreach ($career['division'] as $o): ?><option value="<?= $o['id'] ?>" <?= selected_id($person['division_id'], $o['id']) ?>><?= ph($o['option_name']) ?><?= $o['status'] === 'inactive' ? ' (ปิดใช้งาน)' : '' ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>หน่วยงาน <span class="req">*</span></label><select name="department_id" id="department_id" required>
                                                <option value="">-- เลือกหน่วยงาน --</option><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" data-name="<?= ph($d['department_name']) ?>" <?= selected_id($person['department_id'], $d['id']) ?>><?= ph($d['department_name']) ?></option><?php endforeach; ?>
                                            </select><input type="hidden" name="department" id="department_name" value="<?= ph($person['department']) ?>"></div>
                                        <div class="field col-4"><label>วันที่บรรจุ <span class="req">*</span></label><input type="date" name="appointment_date" required value="<?= ph($person['appointment_date']) ?>"></div>
                                        <div class="field col-4"><label>เลขตำแหน่ง</label><input name="position_number" value="<?= ph($person['position_number']) ?>"></div>
                                        <div class="field col-4"><label>เลขใบประกอบวิชาชีพ</label><input name="professional_license_no" value="<?= ph($person['professional_license_no']) ?>"></div>
                                        <div class="field col-4"><label>ว.ด.ป. รับใบประกอบ</label><input type="date" name="license_issue_date" value="<?= ph($person['license_issue_date']) ?>"></div>
                                        <div class="field col-4"><label>ตำแหน่ง <span class="req">*</span></label><select name="position_id" required>
                                                <option value="">-- เลือกตำแหน่ง --</option><?php foreach ($career['position'] as $o): ?><option value="<?= $o['id'] ?>" <?= selected_id($person['position_id'], $o['id']) ?>><?= ph($o['option_name']) ?><?= $o['status'] === 'inactive' ? ' (ปิดใช้งาน)' : '' ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>ระดับ <span class="req">*</span></label><select name="level_id" required>
                                                <option value="">-- เลือกระดับ --</option><?php foreach ($career['level'] as $o): ?><option value="<?= $o['id'] ?>" <?= selected_id($person['level_id'], $o['id']) ?>><?= ph($o['option_name']) ?><?= $o['status'] === 'inactive' ? ' (ปิดใช้งาน)' : '' ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>สถานะปัจจุบัน <span class="req">*</span></label><select name="current_status_id" required>
                                                <option value="">-- เลือกสถานะ --</option><?php foreach ($career['current_status'] as $o): ?><option value="<?= $o['id'] ?>" <?= selected_id($person['current_status_id'], $o['id']) ?>><?= ph($o['option_name']) ?><?= $o['status'] === 'inactive' ? ' (ปิดใช้งาน)' : '' ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>กลุ่มข้าราชการ <span class="req">*</span></label><select name="civil_service_group_id" required>
                                                <option value="">-- เลือกกลุ่ม --</option><?php foreach ($career['civil_group'] as $o): ?><option value="<?= $o['id'] ?>" <?= selected_id($person['civil_service_group_id'], $o['id']) ?>><?= ph($o['option_name']) ?><?= $o['status'] === 'inactive' ? ' (ปิดใช้งาน)' : '' ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>ประเภทข้าราชการ <span class="req">*</span></label><select name="civil_service_type_id" required>
                                                <option value="">-- เลือกประเภท --</option><?php foreach ($career['civil_type'] as $o): ?><option value="<?= $o['id'] ?>" <?= selected_id($person['civil_service_type_id'], $o['id']) ?>><?= ph($o['option_name']) ?><?= $o['status'] === 'inactive' ? ' (ปิดใช้งาน)' : '' ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>กลุ่มบุคลากร <span class="req">*</span></label><select name="personnel_group_id" required>
                                                <option value="">-- เลือกกลุ่มบุคลากร --</option><?php foreach ($career['personnel_group'] as $o): ?><option value="<?= $o['id'] ?>" <?= selected_id($person['personnel_group_id'], $o['id']) ?>><?= ph($o['option_name']) ?><?= $o['status'] === 'inactive' ? ' (ปิดใช้งาน)' : '' ?></option><?php endforeach; ?>
                                            </select></div>
                                        <div class="field col-4"><label>ต้นสังกัด</label><input name="affiliation" value="<?= ph($person['affiliation']) ?>"></div>
                                        <div class="field col-4"><label>เงินเดือน</label>
                                            <div class="money-input"><span>฿</span><input type="number" step="0.01" min="0" name="salary" value="<?= ph($person['salary']) ?>"></div>
                                        </div>
                                        <div class="field col-4"><label>เงินประจำตำแหน่ง</label>
                                            <div class="money-input"><span>฿</span><input type="number" step="0.01" min="0" name="position_allowance" value="<?= ph($person['position_allowance']) ?>"></div>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </div>
                        <div class="section-label">ข้อมูลการติดต่อและทะเบียนระบบ</div>
                        <div class="field col-4"><label>เบอร์โทรศัพท์</label><input name="phone" value="<?= ph($person['phone']) ?>"></div>
                        <div class="field col-4"><label>สถานที่ / ห้อง</label><input name="room_location" value="<?= ph($person['room_location']) ?>"></div>
                        <div class="field col-4"><label>สถานะทะเบียน</label><select name="status">
                                <option value="active" <?= $person['status'] === 'active' ? 'selected' : '' ?>>ปฏิบัติงาน</option>
                                <option value="inactive" <?= $person['status'] === 'inactive' ? 'selected' : '' ?>>พ้นสภาพ / ไม่ปฏิบัติงาน</option>
                            </select></div>
                        <div class="field col-12"><label>หมายเหตุ</label><textarea name="notes"><?= ph($person['notes']) ?></textarea></div>
                    </div>
                    <div class="form-actions"><a class="btn btn-soft" href="detail.php?id=<?= $id ?>">ยกเลิก</a><button class="btn btn-primary">💾 บันทึกการแก้ไข</button></div>
                </form>
            </div>
        </section>
        <?php if (personnel_is_admin()): ?><section class="card">
                <div class="card-head">
                    <div>
                        <h3>จัดการรหัสผ่าน</h3>
                        <div class="card-sub">สำหรับผู้ดูแลระบบ</div>
                    </div>
                </div>
                <div class="card-body"><?php if ($person['user_id']): ?><form method="post" action="password_update.php"><input type="hidden" name="csrf_token" value="<?= ph(personnel_csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>">
                            <div class="grid">
                                <div class="field col-6"><label>รหัสผ่านใหม่ <span class="req">*</span></label><input type="password" name="new_password" minlength="6" maxlength="72" required></div>
                                <div class="field col-6"><label>ยืนยันรหัสผ่าน <span class="req">*</span></label><input type="password" name="confirm_password" minlength="6" maxlength="72" required></div>
                            </div>
                            <div class="form-actions"><button class="btn btn-outline" type="submit" name="reset_default" value="1" formnovalidate>รีเซ็ตเป็น 123456</button><button class="btn btn-navy">เปลี่ยนรหัสผ่าน</button></div>
                        </form><?php endif; ?></div>
            </section><?php endif; ?>
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