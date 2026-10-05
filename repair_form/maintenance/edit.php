<?php
require_once __DIR__ . '/config_maintenance.php';
maintenance_require_permission('edit');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0 || !maintenance_table_exists()) {
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

$departments = [];
$rs = $conn->query("SELECT department_name FROM departments WHERE status='ใช้งาน' ORDER BY department_name");
if ($rs) while ($r = $rs->fetch_assoc()) $departments[] = $r['department_name'];
if ($request['department'] && !in_array($request['department'], $departments, true)) $departments[] = $request['department'];

$technicians = [];
$rs = $conn->query("SELECT id, name, department FROM technicians WHERE status='ใช้งาน' ORDER BY department, name");
if ($rs) while ($r = $rs->fetch_assoc()) $technicians[] = $r;

$flash = maintenance_pull_flash();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>แก้ไขงาน #<?= (int)$id ?> | งานซ่อมบำรุง</title>
<link rel="stylesheet" href="assets/maintenance.css?v=<?= filemtime(__DIR__ . '/assets/maintenance.css') ?>">
</head>
<body>
<?php $activePage='maintenance'; $basePath='../../'; require __DIR__ . '/../../components/sidebar.php'; ?>
<main class="main-content maintenance-main">
    <div class="mt-page-top"><div class="mt-breadcrumb"><a href="index.php">งานซ่อมบำรุง</a><span>›</span><a href="detail.php?id=<?= (int)$id ?>">รายการ #<?= (int)$id ?></a><span>›</span><span>แก้ไข</span></div></div>

    <section class="mt-hero">
        <div class="mt-hero-copy"><p class="mt-eyebrow">EDIT MAINTENANCE REQUEST</p><h1>แก้ไขข้อมูลแจ้งซ่อม #<?= (int)$id ?></h1><p>แก้ไขข้อมูลผู้แจ้ง ประเภทงาน ช่างผู้รับผิดชอบ และรายละเอียดงานซ่อมบำรุง</p></div>
        <div class="mt-hero-actions"><a class="mt-btn mt-btn-outline" href="detail.php?id=<?= (int)$id ?>">← ยกเลิกและกลับ</a></div>
    </section>

    <?php if ($flash): ?><div class="mt-flash <?= mh($flash['type']) ?>"><?= mh($flash['message']) ?></div><?php endif; ?>

    <section class="mt-card" style="max-width:920px;margin:0 auto">
        <div class="mt-card-head"><div class="mt-card-title-wrap"><div class="mt-section-badge">✎</div><div><h2>ข้อมูลรายการ</h2><p>ช่องที่มีเครื่องหมาย * จำเป็นต้องกรอก</p></div></div></div>
        <div class="mt-card-body">
            <form method="post" action="update.php" id="maintenanceEditForm">
                <input type="hidden" name="csrf_token" value="<?= mh(maintenance_csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$id ?>">
                <div class="mt-form-grid">
                    <div class="mt-field">
                        <label class="mt-label" for="sender_name">ชื่อผู้ส่ง <span class="mt-required">*</span></label>
                        <input class="mt-control" id="sender_name" name="sender_name" maxlength="150" required value="<?= mh($request['sender_name']) ?>">
                    </div>
                    <div class="mt-field">
                        <label class="mt-label" for="department">แผนก <span class="mt-required">*</span></label>
                        <select class="mt-control" id="department" name="department" required>
                            <?php foreach ($departments as $department): ?><option value="<?= mh($department) ?>" <?= $request['department'] === $department ? 'selected' : '' ?>><?= mh($department) ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mt-field full">
                        <span class="mt-label">ระบบที่ต้องการแจ้งซ่อม <span class="mt-required">*</span></span>
                        <div class="mt-system-options">
                            <?php foreach (['ประปา'=>'W','ไฟฟ้า'=>'E','แอร์'=>'A'] as $system=>$letter): ?>
                            <label><input class="mt-system-radio" type="radio" name="system_type" value="<?= mh($system) ?>" <?= $request['system_type'] === $system ? 'checked' : '' ?> required><span class="mt-system-card"><strong><?= $letter ?></strong><span><?= mh($system) ?></span></span></label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mt-field full">
                        <label class="mt-label" for="technician_id">เลือกช่าง <span class="mt-required">*</span></label>
                        <select class="mt-control" id="technician_id" name="technician_id" required>
                            <option value="">-- เลือกช่างผู้รับผิดชอบ --</option>
                            <?php foreach ($technicians as $tech): ?>
                            <option value="<?= (int)$tech['id'] ?>" data-specialty="<?= mh(trim((string)$tech['department'])) ?>" <?= (int)$request['technician_id'] === (int)$tech['id'] ? 'selected' : '' ?>><?= mh(trim((string)$tech['name'])) ?><?= $tech['department'] ? ' — ' . mh($tech['department']) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mt-field full"><label class="mt-label" for="location">สถานที่ / จุดที่พบปัญหา</label><input class="mt-control" id="location" name="location" maxlength="255" value="<?= mh($request['location']) ?>"></div>
                    <div class="mt-field full"><label class="mt-label" for="details">รายละเอียดแจ้งซ่อม <span class="mt-required">*</span></label><textarea class="mt-control" id="details" name="details" maxlength="3000" required><?= mh($request['details']) ?></textarea></div>

                    <div class="mt-field full">
                        <span class="mt-label">ระดับความเร่งด่วน</span>
                        <div class="mt-priority-options">
                            <label><input class="mt-priority-radio" type="radio" name="priority" value="normal" <?= $request['priority']==='normal'?'checked':'' ?>><span class="mt-priority-card"><i class="mt-dot"></i> ปกติ</span></label>
                            <label><input class="mt-priority-radio" type="radio" name="priority" value="urgent" <?= $request['priority']==='urgent'?'checked':'' ?>><span class="mt-priority-card"><i class="mt-dot urgent"></i> ด่วน</span></label>
                            <label><input class="mt-priority-radio" type="radio" name="priority" value="emergency" <?= $request['priority']==='emergency'?'checked':'' ?>><span class="mt-priority-card"><i class="mt-dot emergency"></i> ด่วนมาก</span></label>
                        </div>
                    </div>
                </div>
                <div class="mt-form-actions"><button class="mt-btn mt-btn-primary" type="submit">บันทึกการแก้ไข</button><a class="mt-btn mt-btn-outline" href="detail.php?id=<?= (int)$id ?>">ยกเลิก</a></div>
            </form>
        </div>
    </section>
</main>
<script>
(function(){
 const radios=document.querySelectorAll('input[name="system_type"]');
 const select=document.getElementById('technician_id');
 if(!select||!radios.length)return;
 const original=Array.from(select.options).map(o=>({value:o.value,text:o.textContent,specialty:o.dataset.specialty||'',selected:o.selected}));
 function filter(){
   const system=document.querySelector('input[name="system_type"]:checked')?.value||'';
   const current=select.value;
   let source=original.filter(o=>!o.value||!system||o.specialty===system||o.specialty==='ซ่อมบำรุง'||o.specialty==='ทั่วไป');
   if(!source.some(o=>o.value)) source=original;
   select.innerHTML='';
   source.forEach(o=>{const x=document.createElement('option');x.value=o.value;x.textContent=o.text;x.dataset.specialty=o.specialty;select.appendChild(x);});
   if(source.some(o=>o.value===current)) select.value=current;
 }
 radios.forEach(r=>r.addEventListener('change',filter));
 filter();
})();
</script>
</body>
</html>
