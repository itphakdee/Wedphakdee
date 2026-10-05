<?php
require_once __DIR__ . '/config_personnel.php';
personnel_require_login();
if (!personnel_is_admin()) {
    http_response_code(403);
    die('403 Forbidden: สำหรับผู้ดูแลระบบเท่านั้น');
}
if (!personnel_schema_ready()) {
    header('Location:install.php');
    exit;
}
$types = personnel_career_option_types();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!personnel_verify_csrf($_POST['csrf_token'] ?? '')) {
        personnel_flash('error', 'คำขอไม่ถูกต้อง');
        header('Location:career_options.php');
        exit;
    }
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add') {
            $type = trim((string)($_POST['option_type'] ?? ''));
            $name = trim((string)($_POST['option_name'] ?? ''));
            $sort = (int)($_POST['sort_order'] ?? 0);
            if (!isset($types[$type]) || $name === '') throw new Exception('กรุณาเลือกประเภทและกรอกชื่อตัวเลือก');
            $stmt = $conn->prepare("INSERT INTO personnel_career_options (option_type,option_name,sort_order,status) VALUES (?,?,?,'active')");
            $stmt->bind_param('ssi', $type, $name, $sort);
            if (!$stmt->execute()) throw new Exception($stmt->errno === 1062 ? 'มีตัวเลือกนี้อยู่แล้ว' : 'เพิ่มตัวเลือกไม่สำเร็จ: ' . $stmt->error);
            $stmt->close();
            personnel_flash('success', 'เพิ่มตัวเลือกเรียบร้อยแล้ว');
        } elseif ($action === 'toggle') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $conn->prepare("UPDATE personnel_career_options SET status=IF(status='active','inactive','active') WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            personnel_flash('success', 'เปลี่ยนสถานะตัวเลือกแล้ว');
        } elseif ($action === 'rename') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim((string)($_POST['option_name'] ?? ''));
            if ($id <= 0 || $name === '') throw new Exception('ข้อมูลไม่ครบ');
            $stmt = $conn->prepare('UPDATE personnel_career_options SET option_name=? WHERE id=?');
            $stmt->bind_param('si', $name, $id);
            if (!$stmt->execute()) throw new Exception('แก้ไขไม่สำเร็จ: ' . $stmt->error);
            $stmt->close();
            personnel_flash('success', 'แก้ไขชื่อตัวเลือกแล้ว');
        }
    } catch (Throwable $e) {
        personnel_flash('error', $e->getMessage());
    }
    header('Location:career_options.php');
    exit;
}
$all = [];
foreach ($types as $key => $label) $all[$key] = personnel_career_options($key, false);
$flash = personnel_pull_flash();
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ตั้งค่าข้อมูลอาชีพ</title>
    <link rel="stylesheet" href="assets/personnel.css?v=2.0">
</head>

<body>
    <div class="personnel-shell">
        <section class="hero">
            <div class="hero-grid">
                <div>
                    <div class="eyebrow">CAREER MASTER DATA</div>
                    <h1>ตั้งค่าตัวเลือกข้อมูลอาชีพ</h1>
                    <p>จัดการ Dropdown กลุ่มงาน ฝ่าย/แผนก ตำแหน่ง ระดับ และกลุ่มบุคลากรจากฐานข้อมูลกลาง</p>
                </div>
                <div class="hero-actions"><a class="btn btn-outline" href="index.php">← ทะเบียนบุคลากร</a></div>
            </div>
        </section><?php if ($flash): ?><div class="alert <?= ($flash['type'] === 'success' ? 'alert-success' : 'alert-error') ?>"><?= ph($flash['message']) ?></div><?php endif; ?>
        <section class="card">
            <div class="card-head">
                <div>
                    <h2>เพิ่มตัวเลือก</h2>
                    <div class="card-sub">เมื่อเพิ่มแล้วจะแสดงในหน้าเพิ่ม/แก้ไขบุคลากรทันที</div>
                </div>
            </div>
            <div class="card-body">
                <form method="post"><input type="hidden" name="csrf_token" value="<?= ph(personnel_csrf_token()) ?>"><input type="hidden" name="action" value="add">
                    <div class="grid">
                        <div class="field col-4"><label>ประเภทข้อมูล</label><select name="option_type" required>
                                <option value="">-- เลือกประเภท --</option><?php foreach ($types as $k => $v): ?><option value="<?= ph($k) ?>"><?= ph($v) ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="field col-6"><label>ชื่อตัวเลือก</label><input name="option_name" required maxlength="180"></div>
                        <div class="field col-2"><label>ลำดับ</label><input type="number" name="sort_order" value="100"></div>
                    </div>
                    <div class="form-actions"><button class="btn btn-primary">＋ เพิ่มตัวเลือก</button></div>
                </form>
            </div>
        </section>
        <?php foreach ($types as $key => $label): ?><section class="card option-card">
                <div class="card-head">
                    <div>
                        <h3><?= ph($label) ?></h3>
                        <div class="card-sub"><?= count($all[$key]) ?> รายการ</div>
                    </div>
                </div>
                <div class="card-body option-list"><?php foreach ($all[$key] as $o): ?><div class="option-row">
                            <form method="post" class="option-name-form"><input type="hidden" name="csrf_token" value="<?= ph(personnel_csrf_token()) ?>"><input type="hidden" name="action" value="rename"><input type="hidden" name="id" value="<?= $o['id'] ?>"><input name="option_name" value="<?= ph($o['option_name']) ?>"><button class="btn btn-sm btn-outline">บันทึกชื่อ</button></form>
                            <form method="post"><input type="hidden" name="csrf_token" value="<?= ph(personnel_csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $o['id'] ?>"><button class="btn btn-sm <?= $o['status'] === 'active' ? 'btn-soft' : 'btn-danger' ?>"><?= $o['status'] === 'active' ? 'ใช้งาน' : 'ปิดใช้งาน' ?></button></form>
                        </div><?php endforeach; ?></div>
            </section><?php endforeach; ?>
    </div>
</body>

</html>