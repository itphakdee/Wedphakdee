<?php
require_once __DIR__ . '/layout.php';

if (!leave_is_admin()) {
    http_response_code(403);
    die('403 Forbidden: เฉพาะ Admin เท่านั้น');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    leave_require_csrf();
    $action = isset($_POST['action']) ? (string)$_POST['action'] : 'save';

    if ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $stmt = $conn->prepare("DELETE FROM leave_supervisors WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        leave_flash('success', 'ลบการกำหนดหัวหน้างานแล้ว');
        leave_redirect('supervisors.php');
    }

    $departmentId = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
    $departmentName = isset($_POST['department_name']) ? trim((string)$_POST['department_name']) : '';
    $supervisorId = isset($_POST['supervisor_user_id']) ? (int)$_POST['supervisor_user_id'] : 0;

    if ($departmentName === '' || $supervisorId <= 0) {
        leave_flash('error', 'กรุณาเลือกหน่วยงานและหัวหน้างาน');
        leave_redirect('supervisors.php');
    }

    // ตรวจว่าบัญชีหัวหน้าที่เลือกอยู่ในแผนกเดียวกับที่กำหนดจริง
    $supervisorProfile = leave_user_profile_by_id($supervisorId);
    if (!$supervisorProfile || $supervisorProfile['status'] !== 'active') {
        leave_flash('error', 'ไม่พบบัญชีหัวหน้างาน หรือบัญชีไม่ได้เปิดใช้งาน');
        leave_redirect('supervisors.php');
    }

    $supervisorDepartment = leave_user_department_info($supervisorProfile);
    $sameDepartment = false;
    if ($departmentId > 0 && (int)$supervisorDepartment['id'] > 0) {
        $sameDepartment = ($departmentId === (int)$supervisorDepartment['id']);
    } elseif ($departmentName !== '' && trim((string)$supervisorDepartment['name']) !== '') {
        $sameDepartment = (trim($departmentName) === trim((string)$supervisorDepartment['name']));
    }

    if (!$sameDepartment) {
        leave_flash(
            'error',
            'หัวหน้างานที่เลือกไม่ได้อยู่ในแผนก “' . $departmentName . '” กรุณาเลือกบุคลากรในแผนกเดียวกัน'
        );
        leave_redirect('supervisors.php');
    }

    $uid = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare(
        "INSERT INTO leave_supervisors(department_id,department_name,supervisor_user_id,created_by)
         VALUES(?,?,?,?)
         ON DUPLICATE KEY UPDATE
           department_id=VALUES(department_id),
           supervisor_user_id=VALUES(supervisor_user_id),
           created_by=VALUES(created_by),
           updated_at=NOW()"
    );
    $stmt->bind_param('isii', $departmentId, $departmentName, $supervisorId, $uid);
    $stmt->execute();
    $stmt->close();

    // sync users.supervisor_id เฉพาะสมาชิกในแผนกนี้
    if ($departmentId > 0) {
        $stmt = $conn->prepare("UPDATE users SET supervisor_id=? WHERE department_id=? AND status='active'");
        $stmt->bind_param('ii', $supervisorId, $departmentId);
        $stmt->execute();
        $stmt->close();
    } elseif ($departmentName !== '') {
        $stmt = $conn->prepare("UPDATE users SET supervisor_id=? WHERE TRIM(department)=TRIM(?) AND status='active'");
        $stmt->bind_param('is', $supervisorId, $departmentName);
        $stmt->execute();
        $stmt->close();
    }

    leave_flash('success', 'บันทึกหัวหน้าแผนก “' . $departmentName . '” เรียบร้อยแล้ว');
    leave_redirect('supervisors.php');
}

$departments = array();
if (leave_table_exists('departments')) {
    $res = $conn->query("SELECT id,department_name FROM departments WHERE status='ใช้งาน' OR status='active' ORDER BY department_name");
    while ($res && $r = $res->fetch_assoc()) {
        $departments[] = $r;
    }
}

// ดึงบุคลากรพร้อมแผนกจริง เพื่อใช้กรองหัวหน้าเมื่อเลือกแผนก
$users = array();
$res = $conn->query("SELECT id FROM users WHERE status='active' ORDER BY fullname");
while ($res && $r = $res->fetch_assoc()) {
    $profile = leave_user_profile_by_id((int)$r['id']);
    if (!$profile) continue;
    $d = leave_user_department_info($profile);
    $users[] = array(
        'id' => (int)$profile['id'],
        'fullname' => (string)$profile['fullname'],
        'role' => (string)$profile['role'],
        'department_id' => (int)$d['id'],
        'department_name' => (string)$d['name']
    );
}

$maps = array();
$res = $conn->query(
    "SELECT ls.*,u.fullname supervisor_name,u.role supervisor_role
     FROM leave_supervisors ls
     JOIN users u ON u.id=ls.supervisor_user_id
     ORDER BY ls.department_name"
);
while ($res && $r = $res->fetch_assoc()) {
    $maps[] = $r;
}

leave_page_start('กำหนดหัวหน้างาน', 'กำหนดหัวหน้าแยกตามหน่วยงาน ระบบจะตรวจแผนกของ User แล้วส่งใบลาไปยังหัวหน้าแผนกนั้นอัตโนมัติ', 'supervisors');
?>
<div class="leave-grid-2">
    <section class="leave-card">
        <div class="leave-card__head">
            <div>
                <h2>หัวหน้างานตามหน่วยงาน</h2>
                <p>ระบบจับคู่จากแผนกของผู้ยื่นใบลา ไม่ส่งไปยังหัวหน้าของแผนกอื่น</p>
            </div>
        </div>
        <div class="leave-table-wrap">
            <table class="leave-table" style="min-width:650px">
                <thead>
                <tr><th>หน่วยงาน</th><th>หัวหน้างาน</th><th>บทบาทบัญชี</th><th>คำสั่ง</th></tr>
                </thead>
                <tbody>
                <?php if (!$maps): ?>
                    <tr><td colspan="4"><div class="leave-empty">ยังไม่ได้กำหนดหัวหน้างาน</div></td></tr>
                <?php endif; ?>
                <?php foreach ($maps as $m): ?>
                    <tr>
                        <td><strong><?= leave_e($m['department_name']) ?></strong></td>
                        <td><?= leave_e($m['supervisor_name']) ?></td>
                        <td><?= leave_e($m['supervisor_role']) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= leave_e(leave_csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                <button class="leave-btn leave-btn--sm leave-btn--danger" data-confirm="ลบการกำหนดหัวหน้างานนี้?">ลบ</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <aside>
        <section class="leave-card">
            <div class="leave-card__head">
                <div>
                    <h3>เพิ่ม / เปลี่ยนหัวหน้าแผนก</h3>
                    <p>เมื่อเลือกแผนก รายชื่อด้านล่างจะแสดงเฉพาะบุคลากรในแผนกนั้น</p>
                </div>
            </div>
            <div class="leave-card__body">
                <form method="post" id="supervisorForm">
                    <input type="hidden" name="csrf_token" value="<?= leave_e(leave_csrf_token()) ?>">
                    <input type="hidden" name="action" value="save">

                    <div class="leave-field">
                        <label>หน่วยงาน <span class="req">*</span></label>
                        <select class="leave-select" id="deptSelect" required>
                            <?php if (!$departments): ?>
                                <option value="">ไม่มีข้อมูล departments</option>
                            <?php else: ?>
                                <option value="">-- เลือกหน่วยงาน --</option>
                                <?php foreach ($departments as $d): ?>
                                    <option data-name="<?= leave_e($d['department_name']) ?>" value="<?= (int)$d['id'] ?>">
                                        <?= leave_e($d['department_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <input type="hidden" name="department_id" id="deptId">
                        <input type="hidden" name="department_name" id="deptName">
                    </div>

                    <div class="leave-field" style="margin-top:13px">
                        <label>หัวหน้างาน <span class="req">*</span></label>
                        <select class="leave-select" name="supervisor_user_id" id="supervisorSelect" required disabled>
                            <option value="">-- กรุณาเลือกหน่วยงานก่อน --</option>
                            <?php foreach ($users as $u): ?>
                                <option
                                    value="<?= (int)$u['id'] ?>"
                                    data-dept-id="<?= (int)$u['department_id'] ?>"
                                    data-dept-name="<?= leave_e($u['department_name']) ?>"
                                    hidden
                                >
                                    <?= leave_e($u['fullname']) ?> · <?= leave_e($u['department_name'] !== '' ? $u['department_name'] : 'ไม่ระบุแผนก') ?> · <?= leave_e($u['role']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="leave-help" id="supervisorHelp">เลือกหน่วยงานก่อน ระบบจะกรองบุคลากรในแผนกเดียวกัน</div>
                    </div>

                    <button class="leave-btn leave-btn--primary" style="margin-top:15px;width:100%">บันทึกหัวหน้าแผนก</button>
                </form>
            </div>
        </section>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var deptSelect = document.getElementById('deptSelect');
    var deptId = document.getElementById('deptId');
    var deptName = document.getElementById('deptName');
    var supervisorSelect = document.getElementById('supervisorSelect');
    var help = document.getElementById('supervisorHelp');

    if (!deptSelect || !supervisorSelect) return;

    function filterSupervisors() {
        var selected = deptSelect.options[deptSelect.selectedIndex];
        var selectedId = deptSelect.value || '';
        var selectedName = selected ? (selected.getAttribute('data-name') || '') : '';

        deptId.value = selectedId;
        deptName.value = selectedName;
        supervisorSelect.value = '';

        var count = 0;
        var options = supervisorSelect.querySelectorAll('option[data-dept-id]');
        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            var sameId = selectedId !== '' && option.getAttribute('data-dept-id') === selectedId;
            var sameName = selectedName !== '' && option.getAttribute('data-dept-name') === selectedName;
            var show = sameId || sameName;
            option.hidden = !show;
            option.disabled = !show;
            if (show) count++;
        }

        if (selectedId === '') {
            supervisorSelect.disabled = true;
            supervisorSelect.options[0].text = '-- กรุณาเลือกหน่วยงานก่อน --';
            help.textContent = 'เลือกหน่วยงานก่อน ระบบจะกรองบุคลากรในแผนกเดียวกัน';
        } else {
            supervisorSelect.disabled = false;
            supervisorSelect.options[0].text = count > 0 ? '-- เลือกหัวหน้าแผนก --' : '-- ไม่พบบุคลากรในแผนกนี้ --';
            help.textContent = count > 0
                ? 'พบบุคลากรในแผนก “' + selectedName + '” จำนวน ' + count + ' คน'
                : 'ไม่พบบุคลากรที่มีแผนก “' + selectedName + '” กรุณาตรวจข้อมูลบุคลากรก่อน';
        }
    }

    deptSelect.addEventListener('change', filterSupervisors);
    filterSupervisors();
});
</script>
<?php leave_page_end(); ?>
