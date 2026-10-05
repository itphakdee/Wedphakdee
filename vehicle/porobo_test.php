<?php
require_once __DIR__ . '/layout.php';
if (!$currentVehicleIsAdmin) { http_response_code(403); die('403 Forbidden: Admin เท่านั้น'); }

$poroboReady = vehicle_column_exists('vehicle_fleet','porobo_expiry_date');
$fleetRows = array();
if ($poroboReady) {
    $rs = $conn->query("SELECT * FROM vehicle_fleet WHERE status<>'inactive' ORDER BY registration");
    while ($rs && ($r=$rs->fetch_assoc())) $fleetRows[]=$r;
}

$result = null;
$selectedId = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    vehicle_check_csrf();
    if (!$poroboReady) {
        $result = array('ok'=>false,'error'=>'ยังไม่ได้ติดตั้งฟิลด์ พ.ร.บ.');
    } else {
        $row = null;
        foreach ($fleetRows as $r) if ((int)$r['id']===$selectedId) { $row=$r; break; }
        if (!$row && $fleetRows) $row=$fleetRows[0];
        if (!$row) {
            $result = array('ok'=>false,'error'=>'ยังไม่มีรถสำหรับทดสอบ');
        } else {
            if (!$row['porobo_expiry_date']) {
                $row['porobo_expiry_date'] = date('Y-m-d', strtotime('+15 days'));
                $row['porobo_alert_days'] = 30;
                $row['porobo_policy_no'] = $row['porobo_policy_no'] ?: 'TEST-POROBO';
                $row['porobo_provider'] = $row['porobo_provider'] ?: 'ข้อมูลทดสอบ';
            }
            $result = vehicle_porobo_send_vehicle($row, true, true);
            $selectedId=(int)$row['id'];
        }
    }
}

vehicle_page_start('ทดสอบ LINE แจ้งเตือน พ.ร.บ.','ทดสอบ PHP → Google Apps Script → LINE Messaging API → กลุ่ม LINE โดยไม่กระทบสถานะการแจ้งเตือนจริง','porobo');
?>
<section class="vehicle-card" style="max-width:900px;margin:0 auto">
    <div class="vehicle-card__header"><div><h2>🧪 ทดสอบแจ้งเตือน พ.ร.บ. รถ</h2><p>ข้อความทดสอบจะถูกส่งไปยังกลุ่ม LINE ที่กำหนดไว้ใน Apps Script ของระบบยานพาหนะ</p></div><a class="vehicle-btn vehicle-btn--ghost" href="porobo.php">← กลับหน้าแจ้งเตือน</a></div>
    <div class="vehicle-card__body">
        <?php if(!$poroboReady): ?>
            <div class="vehicle-alert vehicle-alert--warning">กรุณา <a href="install.php"><strong>อัปเดตฐานข้อมูล</strong></a> ก่อนทดสอบ</div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>">
                <div class="vehicle-field"><label>เลือกรถสำหรับทดสอบ</label><select class="vehicle-control" name="vehicle_id" required><option value="">-- เลือกรถ --</option><?php foreach($fleetRows as $r): ?><option value="<?= (int)$r['id'] ?>" <?= $selectedId===(int)$r['id']?'selected':'' ?>><?= vehicle_e($r['registration'].' · '.($r['display_name']?:'รถโรงพยาบาล')) ?></option><?php endforeach; ?></select></div>
                <button class="vehicle-btn vehicle-btn--primary" type="submit" style="margin-top:14px">📣 ส่งข้อความทดสอบเข้า LINE Group</button>
            </form>

            <?php if($result!==null): ?>
                <div class="vehicle-alert vehicle-alert--<?= !empty($result['ok'])?'success':'danger' ?>" style="margin-top:18px"><?= !empty($result['ok'])?'✅ ส่งข้อความทดสอบสำเร็จ':'❌ ส่งข้อความทดสอบไม่สำเร็จ' ?></div>
                <div class="vehicle-inline-note"><strong>HTTP:</strong> <?= vehicle_e(isset($result['http'])?$result['http']:'-') ?><br><strong>Error:</strong> <?= vehicle_e(isset($result['error'])&&$result['error']!==''?$result['error']:'ไม่มี') ?><br><strong>Response:</strong><br><pre style="white-space:pre-wrap;word-break:break-word;margin:8px 0 0"><?= vehicle_e(isset($result['raw'])?$result['raw']:'') ?></pre></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php vehicle_page_end(); ?>
