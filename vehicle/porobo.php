<?php
require_once __DIR__ . '/layout.php';

if (!$currentVehicleIsAdmin) {
    http_response_code(403);
    die('403 Forbidden: Admin เท่านั้น');
}

$poroboReady = vehicle_column_exists('vehicle_fleet', 'porobo_expiry_date');
$poroboLogReady = vehicle_table_exists('vehicle_porobo_line_logs');
$poroboRows = array();
$poroboStats = array('expired'=>0,'seven'=>0,'thirty'=>0,'ok'=>0,'unset'=>0);

if ($poroboReady) {
    $sql = "SELECT vf.*";
    if ($poroboLogReady) {
        $sql .= ", (SELECT MAX(pl.created_at) FROM vehicle_porobo_line_logs pl WHERE pl.vehicle_id=vf.id AND pl.send_status='success') AS last_line_at";
    } else {
        $sql .= ", NULL AS last_line_at";
    }
    $sql .= " FROM vehicle_fleet vf WHERE vf.status<>'inactive' ORDER BY CASE WHEN vf.porobo_expiry_date IS NULL THEN 1 ELSE 0 END, vf.porobo_expiry_date, vf.registration";
    $prs = $conn->query($sql);
    while ($prs && ($r = $prs->fetch_assoc())) {
        $r['days_remaining'] = vehicle_porobo_days_remaining($r['porobo_expiry_date']);
        $poroboRows[] = $r;
        $d = $r['days_remaining'];
        if ($d === null) $poroboStats['unset']++;
        elseif ($d < 0) $poroboStats['expired']++;
        elseif ($d <= 7) $poroboStats['seven']++;
        elseif ($d <= 30) $poroboStats['thirty']++;
        else $poroboStats['ok']++;
    }
}

vehicle_page_start(
    'แจ้งเตือนและติดตาม พ.ร.บ. รถ',
    'ติดตามวันหมดอายุ พ.ร.บ. ของรถโรงพยาบาล และส่งแจ้งเตือน LINE แยกจากหน้ารอรับรองคำขอใช้รถ',
    'porobo'
);
?>
<section class="vehicle-card vehicle-porobo-card" id="porobo-alerts">
    <div class="vehicle-card__header vehicle-porobo-head">
        <div>
            <p class="vehicle-eyebrow" style="margin:0 0 6px">COMPULSORY MOTOR INSURANCE · พ.ร.บ.</p>
            <h2>แจ้งเตือนใกล้วันต่อ พ.ร.บ. รถ</h2>
            <p>ระบบแจ้ง LINE ตามเกณฑ์ที่กำหนดของรถแต่ละคัน และไม่ส่งระดับเดิมซ้ำสำหรับวันหมดอายุรอบเดียวกัน</p>
        </div>
        <div class="vehicle-actions vehicle-print-hide">
            <?php if ($poroboReady): ?>
            <form method="post" action="porobo_notify.php">
                <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>">
                <input type="hidden" name="action" value="scan">
                <button class="vehicle-btn vehicle-btn--primary" type="submit">📣 ตรวจและส่ง LINE ตามกำหนด</button>
            </form>
            <?php endif; ?>
            <a class="vehicle-btn vehicle-btn--ghost" href="porobo_test.php">🧪 ทดสอบ LINE</a>
            <a class="vehicle-btn vehicle-btn--ghost" href="resources.php">⚙ จัดการข้อมูลรถ</a>
        </div>
    </div>

    <?php if (!$poroboReady): ?>
        <div class="vehicle-card__body">
            <div class="vehicle-alert vehicle-alert--warning">
                ยังไม่ได้ติดตั้งฐานข้อมูล พ.ร.บ. รถ กรุณาเข้า <a href="install.php"><strong>ติดตั้ง / อัปเดตฐานข้อมูล</strong></a> ก่อนใช้งาน
            </div>
        </div>
    <?php else: ?>
        <div class="vehicle-card__body">
            <div class="vehicle-porobo-kpis">
                <div class="vehicle-porobo-kpi danger"><span>หมดอายุแล้ว</span><strong><?= (int)$poroboStats['expired'] ?></strong><small>คัน</small></div>
                <div class="vehicle-porobo-kpi danger-soft"><span>ภายใน 7 วัน</span><strong><?= (int)$poroboStats['seven'] ?></strong><small>คัน</small></div>
                <div class="vehicle-porobo-kpi warning"><span>8–30 วัน</span><strong><?= (int)$poroboStats['thirty'] ?></strong><small>คัน</small></div>
                <div class="vehicle-porobo-kpi success"><span>มากกว่า 30 วัน</span><strong><?= (int)$poroboStats['ok'] ?></strong><small>คัน</small></div>
                <div class="vehicle-porobo-kpi muted"><span>ยังไม่กำหนด</span><strong><?= (int)$poroboStats['unset'] ?></strong><small>คัน</small></div>
            </div>

            <div class="vehicle-table-wrap" style="margin-top:18px">
                <table class="vehicle-table vehicle-porobo-table">
                    <thead>
                        <tr>
                            <th>รถ / ทะเบียน</th>
                            <th>เลขที่ พ.ร.บ.</th>
                            <th>บริษัท</th>
                            <th>วันหมดอายุ</th>
                            <th>แจ้งล่วงหน้า</th>
                            <th>สถานะ</th>
                            <th>LINE ล่าสุด</th>
                            <th>คำสั่ง</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($poroboRows): ?>
                        <?php foreach ($poroboRows as $r): $meta = vehicle_porobo_status_meta($r['days_remaining']); ?>
                        <tr>
                            <td data-label="รถ / ทะเบียน">
                                <strong><?= vehicle_e($r['registration']) ?></strong>
                                <br><small><?= vehicle_e($r['display_name'] ?: trim($r['brand'].' '.$r['model'])) ?></small>
                            </td>
                            <td data-label="เลขที่ พ.ร.บ."><?= vehicle_e($r['porobo_policy_no'] ?: '-') ?></td>
                            <td data-label="บริษัท"><?= vehicle_e($r['porobo_provider'] ?: '-') ?></td>
                            <td data-label="วันหมดอายุ"><strong><?= vehicle_format_thai_date($r['porobo_expiry_date']) ?></strong></td>
                            <td data-label="แจ้งล่วงหน้า"><?= (int)($r['porobo_alert_days'] ?: 30) ?> วัน</td>
                            <td data-label="สถานะ"><span class="vehicle-porobo-status <?= vehicle_e($meta[1]) ?>"><?= vehicle_e($meta[0]) ?></span></td>
                            <td data-label="LINE ล่าสุด"><?= $r['last_line_at'] ? vehicle_e(date('d/m/Y H:i', strtotime($r['last_line_at']))) : '-' ?></td>
                            <td data-label="คำสั่ง">
                                <div class="vehicle-actions">
                                    <a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" href="resources.php?edit=<?= (int)$r['id'] ?>#porobo">แก้ไข พ.ร.บ.</a>
                                    <?php if ($r['porobo_expiry_date']): ?>
                                    <form method="post" action="porobo_notify.php">
                                        <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>">
                                        <input type="hidden" name="action" value="single">
                                        <input type="hidden" name="vehicle_id" value="<?= (int)$r['id'] ?>">
                                        <button class="vehicle-btn vehicle-btn--warning vehicle-btn--small" type="submit">ส่ง LINE ตอนนี้</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8"><div class="vehicle-empty"><strong>ยังไม่มีรถในทะเบียน</strong></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="vehicle-inline-note" style="margin-top:16px">
                <strong>เกณฑ์แจ้งเตือน:</strong> ระบบส่งครั้งแรกเมื่อเข้าสู่ช่วงแจ้งล่วงหน้าของรถแต่ละคัน จากนั้นเตือนอีกที่ 7 วัน, 1 วัน, วันครบกำหนด และเมื่อหมดอายุ โดยไม่ส่งระดับเดิมซ้ำในรอบ พ.ร.บ. เดียวกัน
            </div>
        </div>
    <?php endif; ?>
</section>
<?php vehicle_page_end(); ?>
