<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_permission('view');
if (!personnel_schema_ready()) {
    header('Location:install.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location:index.php');
    exit;
}
$stmt = $conn->prepare('SELECT p.*,u.role AS user_role,u.status AS user_status,u.created_at AS account_created_at FROM personnel p LEFT JOIN users u ON u.id=p.user_id WHERE p.id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$person = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$person) {
    http_response_code(404);
    die('ไม่พบข้อมูลบุคลากร');
}
$statusMeta = personnel_status_meta($person['status']);
$flash = personnel_pull_flash();
$careerNames = [
    'work_group' => personnel_career_option_name($person['work_group_id'], 'work_group'),
    'division' => personnel_career_option_name($person['division_id'], 'division'),
    'position' => personnel_career_option_name($person['position_id'], 'position'),
    'level' => personnel_career_option_name($person['level_id'], 'level'),
    'current_status' => personnel_career_option_name($person['current_status_id'], 'current_status'),
    'civil_group' => personnel_career_option_name($person['civil_service_group_id'], 'civil_group'),
    'civil_type' => personnel_career_option_name($person['civil_service_type_id'], 'civil_type'),
    'personnel_group' => personnel_career_option_name($person['personnel_group_id'], 'personnel_group')
];
function pd_date($v)
{
    return $v ? date('d/m/Y', strtotime($v)) : '-';
}
function pd_money($v)
{
    return ($v !== null && $v !== '') ? '฿' . number_format((float)$v, 2) : '-';
}
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>รายละเอียดบุคลากร</title>
    <link rel="stylesheet" href="assets/personnel.css?v=2.0">
</head>

<body>
    <div class="personnel-shell">
        <section class="hero">
            <div class="hero-grid">
                <div>
                    <div class="eyebrow">PERSONNEL PROFILE #<?= intval($person['id']) ?></div>
                    <h1>รายละเอียดข้อมูลบุคลากร</h1>
                    <p>ข้อมูลประจำตัว ข้อมูลอาชีพ โครงสร้างหน่วยงาน และบัญชีเข้าสู่ระบบ</p>
                </div>
                <div class="hero-actions"><a class="btn btn-outline" href="index.php">← กลับหน้ารายการ</a><?php if (personnel_can('edit')): ?><a class="btn btn-soft" href="edit.php?id=<?= $id ?>">แก้ไขข้อมูล</a><?php endif; ?></div>
            </div>
        </section>
        <?php if ($flash): ?><div class="alert <?= ($flash['type'] === 'success' ? 'alert-success' : 'alert-error') ?>"><?= ph($flash['message']) ?></div><?php endif; ?>
        <div class="detail-grid">
            <main>
                <section class="card">
                    <div class="card-head">
                        <div class="profile-head">
                            <div class="avatar"><?= ph(function_exists('mb_substr') ? mb_substr($person['first_name'] ?: $person['fullname'], 0, 1, 'UTF-8') : substr($person['fullname'], 0, 1)) ?></div>
                            <div>
                                <h2><?= ph($person['fullname']) ?></h2>
                                <p><?= ph(($person['position_name'] ?: 'ไม่ระบุตำแหน่ง') . ' • ' . ($person['department'] ?: 'ไม่ระบุหน่วยงาน')) ?></p>
                            </div>
                        </div><span class="badge <?= ph($statusMeta['class']) ?>"><?= ph($statusMeta['label']) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="info-list">
                            <div class="info-row"><label>Username</label><strong><?= ph($person['username'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>รหัสบุคลากร</label><strong><?= ph($person['employee_code'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>ชื่อ - นามสกุล</label><strong><?= ph($person['fullname']) ?></strong></div>
                            <div class="info-row"><label>ชื่ออังกฤษ</label><strong><?= ph($person['first_name_en'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>ชื่อเล่น</label><strong><?= ph($person['nickname'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>เพศ</label><strong><?= ph($person['gender'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>วันเกิด</label><strong><?= ph(pd_date($person['birth_date'])) ?></strong></div>
                            <div class="info-row"><label>เลขประจำตัวประชาชน</label><strong><?= ph(personnel_format_national_id($person['national_id'])) ?></strong></div>
                            <div class="info-row"><label>อีเมล</label><strong><?= ph($person['email'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>โทรศัพท์</label><strong><?= ph($person['phone'] ?: '-') ?></strong></div>
                        </div>
                    </div>
                </section>
                <section class="card">
                    <div class="card-head">
                        <div>
                            <h2>ข้อมูลอาชีพ</h2>
                            <div class="card-sub">ข้อมูลตามโครงสร้างกำลังคนและตำแหน่งงาน</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="info-list career-info">
                            <div class="info-row"><label>กลุ่มงาน</label><strong><?= ph($careerNames['work_group'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>ฝ่าย / แผนก</label><strong><?= ph($careerNames['division'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>หน่วยงาน</label><strong><?= ph($person['department'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>วันที่บรรจุ</label><strong><?= ph(pd_date($person['appointment_date'])) ?></strong></div>
                            <div class="info-row"><label>เลขตำแหน่ง</label><strong><?= ph($person['position_number'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>ตำแหน่ง</label><strong><?= ph($careerNames['position'] ?: $person['position_name'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>ระดับ</label><strong><?= ph($careerNames['level'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>สถานะปัจจุบัน</label><strong><?= ph($careerNames['current_status'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>กลุ่มข้าราชการ</label><strong><?= ph($careerNames['civil_group'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>ประเภทข้าราชการ</label><strong><?= ph($careerNames['civil_type'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>กลุ่มบุคลากร</label><strong><?= ph($careerNames['personnel_group'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>ต้นสังกัด</label><strong><?= ph($person['affiliation'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>เลขใบประกอบวิชาชีพ</label><strong><?= ph($person['professional_license_no'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>ว.ด.ป. รับใบประกอบ</label><strong><?= ph(pd_date($person['license_issue_date'])) ?></strong></div>
                            <div class="info-row"><label>เงินเดือน</label><strong><?= ph(pd_money($person['salary'])) ?></strong></div>
                            <div class="info-row"><label>เงินประจำตำแหน่ง</label><strong><?= ph(pd_money($person['position_allowance'])) ?></strong></div>
                            <div class="info-row"><label>สถานที่ / ห้อง</label><strong><?= ph($person['room_location'] ?: '-') ?></strong></div>
                            <div class="info-row"><label>วันที่เพิ่มข้อมูล</label><strong><?= ph($person['created_at'] ? date('d/m/Y H:i', strtotime($person['created_at'])) . ' น.' : '-') ?></strong></div>
                        </div><?php if (trim((string)$person['notes']) !== ''): ?><div class="notes-box"><strong>หมายเหตุ</strong>
                                <p><?= nl2br(ph($person['notes'])) ?></p>
                            </div><?php endif; ?>
                    </div>
                </section>
            </main>
            <aside>
                <section class="card">
                    <div class="card-head">
                        <div>
                            <h3>บัญชีเข้าสู่ระบบ</h3>
                            <div class="card-sub">เชื่อมกับตาราง users</div>
                        </div>
                    </div>
                    <div class="card-body"><?php if ($person['user_id']): ?><div class="account-box">
                                <div class="account-line"><span>Username</span><strong><?= ph($person['username']) ?></strong></div>
                                <div class="account-line"><span>ประเภทบัญชี</span><strong><?= ph($person['user_role'] ?: 'user') ?></strong></div>
                                <div class="account-line"><span>สถานะบัญชี</span><strong><?= ph($person['user_status'] ?: '-') ?></strong></div>
                            </div><?php else: ?><div class="alert alert-info">ยังไม่ได้เชื่อมบัญชีเข้าสู่ระบบ</div><?php endif; ?></div>
                </section>
                <?php if (personnel_can('delete')): ?><section class="card">
                        <div class="card-head">
                            <div>
                                <h3>การจัดการรายการ</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="post" action="delete.php" onsubmit="return confirm('ยืนยันการลบข้อมูลบุคลากรรายการนี้?');"><input type="hidden" name="csrf_token" value="<?= ph(personnel_csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><label class="check-line"><input type="checkbox" name="delete_user" value="1"> ลบบัญชีเข้าสู่ระบบด้วย</label><button class="btn btn-danger" type="submit" style="width:100%">ลบข้อมูลบุคลากร</button></form>
                        </div>
                    </section><?php endif; ?>
            </aside>
        </div>
    </div>
</body>

</html>