<?php
require_once __DIR__ . '/layout.php';

if (!$currentVehicleIsAdmin) {
    http_response_code(403);
    die('403 Forbidden: Admin เท่านั้น');
}

if (!vehicle_table_exists('vehicle_repairs')) {
    vehicle_flash('warning', 'ยังไม่ได้ติดตั้งตารางประวัติซ่อมรถ กรุณาเข้าเมนูติดตั้งฐานข้อมูลก่อน');
    vehicle_redirect('install.php');
}

$vehicleId = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$vehicle = null;
$editRepair = null;

if ($vehicleId > 0) {
    $stmt = $conn->prepare("SELECT * FROM vehicle_fleet WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $vehicleId);
    $stmt->execute();
    $res = $stmt->get_result();
    $vehicle = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$vehicle) {
        vehicle_flash('danger', 'ไม่พบข้อมูลรถที่เลือก');
        vehicle_redirect('vehicle_repairs.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    vehicle_check_csrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $postVehicleId = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;

    if ($action === 'save') {
        $repairDate = trim(isset($_POST['repair_date']) ? $_POST['repair_date'] : '');
        $shopName = trim(isset($_POST['shop_name']) ? $_POST['shop_name'] : '');
        $amountRaw = trim(isset($_POST['amount']) ? $_POST['amount'] : '0');
        $amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
        $detail = trim(isset($_POST['detail']) ? $_POST['detail'] : '');
        $receiptNo = trim(isset($_POST['receipt_no']) ? $_POST['receipt_no'] : '');
        $odometerRaw = trim(isset($_POST['odometer']) ? $_POST['odometer'] : '');
        $odometer = $odometerRaw !== '' && is_numeric($odometerRaw) ? (int)$odometerRaw : null;
        $notes = trim(isset($_POST['notes']) ? $_POST['notes'] : '');
        $createdBy = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($postVehicleId <= 0 || $repairDate === '' || $shopName === '') {
            vehicle_flash('danger', 'กรุณาเลือกรถ ระบุวันที่ซ่อม และชื่อร้าน/อู่ให้ครบ');
            vehicle_redirect('vehicle_repairs.php?vehicle_id=' . $postVehicleId);
        }
        if ($amount < 0) {
            vehicle_flash('danger', 'จำนวนเงินค่าซ่อมต้องไม่ติดลบ');
            vehicle_redirect('vehicle_repairs.php?vehicle_id=' . $postVehicleId);
        }

        $eventKey = '';
        $successText = '';
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE vehicle_repairs
                SET vehicle_id=?, repair_date=?, shop_name=?, amount=?, detail=?, receipt_no=?, odometer=?, notes=?
                WHERE id=?");
            $stmt->bind_param('issdssisi', $postVehicleId, $repairDate, $shopName, $amount, $detail, $receiptNo, $odometer, $notes, $id);
            $ok = $stmt->execute();
            $err = $stmt->error;
            $stmt->close();
            $eventKey = 'vehicle_repair_updated';
            $successText = 'แก้ไขประวัติการซ่อมเรียบร้อยแล้ว';
        } else {
            $stmt = $conn->prepare("INSERT INTO vehicle_repairs
                (vehicle_id, repair_date, shop_name, amount, detail, receipt_no, odometer, notes, created_by)
                VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('issdssisi', $postVehicleId, $repairDate, $shopName, $amount, $detail, $receiptNo, $odometer, $notes, $createdBy);
            $ok = $stmt->execute();
            $err = $stmt->error;
            $stmt->close();
            $eventKey = 'vehicle_repair_created';
            $successText = 'บันทึกการส่งซ่อมรถเรียบร้อยแล้ว';
        }

        if ($ok) {
            $registration = '';
            $stmt = $conn->prepare("SELECT registration FROM vehicle_fleet WHERE id=? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $postVehicleId);
                $stmt->execute();
                $stmt->bind_result($registration);
                $stmt->fetch();
                $stmt->close();
            }

            $repairUrl = vehicle_public_url('vehicle_repairs.php?vehicle_id=' . $postVehicleId);
            $msg = "🔧 บันทึกการซ่อมรถ โรงพยาบาลภักดีชุมพล\n"
                . "ทะเบียนรถ: " . ($registration !== '' ? $registration : '-') . "\n"
                . "วันที่ซ่อม: " . vehicle_format_thai_date($repairDate) . "\n"
                . "ร้าน / อู่: " . $shopName . "\n"
                . "จำนวนเงิน: ฿" . number_format($amount, 2) . "\n"
                . "รายละเอียด: " . ($detail !== '' ? $detail : '-') . "\n"
                . "🔗 ประวัติซ่อมรถ: " . $repairUrl;

            $line = vehicle_send_line($msg, $eventKey);
            if (!empty($line['ok'])) {
                vehicle_flash('success', $successText . ' และแจ้ง LINE เรียบร้อยแล้ว');
            } else {
                vehicle_flash('warning', $successText . ' แต่แจ้ง LINE ไม่สำเร็จ');
            }
        } else {
            vehicle_flash('danger', ($id > 0 ? 'แก้ไขข้อมูลไม่สำเร็จ: ' : 'บันทึกข้อมูลไม่สำเร็จ: ') . $err);
        }
        vehicle_redirect('vehicle_repairs.php?vehicle_id=' . $postVehicleId);
    }

    if ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("SELECT r.*, f.registration
                                FROM vehicle_repairs r
                                LEFT JOIN vehicle_fleet f ON f.id=r.vehicle_id
                                WHERE r.id=? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        $backVehicleId = $row ? (int)$row['vehicle_id'] : $postVehicleId;

        $stmt = $conn->prepare("DELETE FROM vehicle_repairs WHERE id=?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $err = $stmt->error;
        $stmt->close();

        if ($ok) {
            if ($row) {
                $msg = "🗑 ยกเลิกรายการประวัติซ่อมรถ\n"
                    . "ทะเบียนรถ: " . ($row['registration'] ? $row['registration'] : '-') . "\n"
                    . "วันที่ซ่อม: " . vehicle_format_thai_date($row['repair_date']) . "\n"
                    . "ร้าน / อู่: " . $row['shop_name'] . "\n"
                    . "จำนวนเงิน: ฿" . number_format((float)$row['amount'], 2) . "\n"
                    . "🔗 ประวัติซ่อมรถ: " . vehicle_public_url('vehicle_repairs.php?vehicle_id=' . $backVehicleId);
                $line = vehicle_send_line($msg, 'vehicle_repair_deleted');
                if (!empty($line['ok'])) {
                    vehicle_flash('success', 'ลบประวัติการซ่อมและแจ้ง LINE เรียบร้อยแล้ว');
                } else {
                    vehicle_flash('warning', 'ลบประวัติการซ่อมแล้ว แต่แจ้ง LINE ไม่สำเร็จ');
                }
            } else {
                vehicle_flash('success', 'ลบประวัติการซ่อมเรียบร้อยแล้ว');
            }
        } else {
            vehicle_flash('danger', 'ลบข้อมูลไม่สำเร็จ: ' . $err);
        }
        vehicle_redirect('vehicle_repairs.php?vehicle_id=' . $backVehicleId);
    }
}

if ($vehicleId > 0 && $editId > 0) {
    $stmt = $conn->prepare("SELECT * FROM vehicle_repairs WHERE id=? AND vehicle_id=? LIMIT 1");
    $stmt->bind_param('ii', $editId, $vehicleId);
    $stmt->execute();
    $res = $stmt->get_result();
    $editRepair = $res ? $res->fetch_assoc() : null;
    $stmt->close();
}

if ($vehicleId <= 0) {
    $fleetSummary = $conn->query("SELECT f.*,
        COUNT(r.id) AS repair_count,
        COALESCE(SUM(r.amount),0) AS repair_total,
        MAX(r.repair_date) AS last_repair_date
        FROM vehicle_fleet f
        LEFT JOIN vehicle_repairs r ON r.vehicle_id=f.id
        GROUP BY f.id
        ORDER BY FIELD(f.status,'active','maintenance','inactive'), f.sort_order, f.registration");

    vehicle_page_start('ประวัติการส่งซ่อมรถ', 'ตรวจสอบจำนวนครั้ง ค่าใช้จ่าย และประวัติการซ่อมของรถโรงพยาบาลแต่ละคัน', 'resources');
?>
    <div class="vehicle-resource-tabs">
        <a href="resources.php">ทะเบียนรถ</a>
        <a href="resources.php?tab=drivers">พนักงานขับ</a>
        <a class="active" href="vehicle_repairs.php">ประวัติการส่งซ่อม</a>
        <a href="../repair_form/leave/supervisors.php">หัวหน้างานตามแผนก</a>
    </div>

    <section class="vehicle-card">
        <div class="vehicle-card__header">
            <div>
                <h2>สรุปประวัติซ่อมรถทุกคัน</h2>
                <p>เลือก “รายละเอียดซ่อม” เพื่อเพิ่ม แก้ไข ลบ และดูประวัติการซ่อมรายคัน</p>
            </div>
        </div>
        <div class="vehicle-table-wrap">
            <table class="vehicle-table vehicle-repair-summary-table" style="min-width:850px">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ทะเบียนรถ</th>
                        <th>รายละเอียดรถ</th>
                        <th>ซ่อมแล้ว</th>
                        <th>ค่าใช้จ่ายรวม</th>
                        <th>ซ่อมล่าสุด</th>
                        <th>คำสั่ง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    if ($fleetSummary && $fleetSummary->num_rows): while ($r = $fleetSummary->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= vehicle_e($r['registration']) ?></strong></td>
                                <td><?= vehicle_e($r['display_name']) ?><br><small><?= vehicle_e(trim($r['brand'] . ' ' . $r['model'])) ?></small></td>
                                <td><span class="vehicle-repair-count"><?= (int)$r['repair_count'] ?> ครั้ง</span></td>
                                <td><strong>฿<?= number_format((float)$r['repair_total'], 2) ?></strong></td>
                                <td><?= $r['last_repair_date'] ? vehicle_e(date('d/m/Y', strtotime($r['last_repair_date']))) : '-' ?></td>
                                <td><a class="vehicle-btn vehicle-btn--info vehicle-btn--small" href="vehicle_repairs.php?vehicle_id=<?= (int)$r['id'] ?>">รายละเอียดซ่อม</a></td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="vehicle-empty">ยังไม่มีข้อมูลรถในทะเบียน</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php
    vehicle_page_end();
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) AS repair_count, COALESCE(SUM(amount),0) AS repair_total,
                        MAX(repair_date) AS last_repair_date
                        FROM vehicle_repairs WHERE vehicle_id=?");
$stmt->bind_param('i', $vehicleId);
$stmt->execute();
$summaryRes = $stmt->get_result();
$summary = $summaryRes ? $summaryRes->fetch_assoc() : array('repair_count' => 0, 'repair_total' => 0, 'last_repair_date' => null);
$stmt->close();

$stmt = $conn->prepare("SELECT r.*, u.fullname AS creator_name
                        FROM vehicle_repairs r
                        LEFT JOIN users u ON u.id=r.created_by
                        WHERE r.vehicle_id=?
                        ORDER BY r.repair_date DESC, r.id DESC");
$stmt->bind_param('i', $vehicleId);
$stmt->execute();
$repairs = $stmt->get_result();

vehicle_page_start('รายละเอียดการส่งซ่อมรถ', 'บันทึกและตรวจสอบประวัติการซ่อม ค่าใช้จ่าย วันที่ซ่อม และร้าน/อู่ของรถแต่ละคัน', 'resources');
?>
<div class="vehicle-resource-tabs">
    <a href="resources.php">ทะเบียนรถ</a>
    <a href="resources.php?tab=drivers">พนักงานขับ</a>
    <a class="active" href="vehicle_repairs.php">ประวัติการส่งซ่อม</a>
    <a href="../repair_form/leave/supervisors.php">หัวหน้างานตามแผนก</a>
</div>

<section class="vehicle-card vehicle-repair-vehicle-head">
    <div class="vehicle-card__body">
        <div class="vehicle-repair-vehicle-row">
            <div>
                <span class="vehicle-eyebrow">VEHICLE MAINTENANCE RECORD</span>
                <h2><?= vehicle_e($vehicle['registration']) ?></h2>
                <p><?= vehicle_e($vehicle['display_name']) ?><?= trim($vehicle['brand'] . ' ' . $vehicle['model']) !== '' ? ' · ' . vehicle_e(trim($vehicle['brand'] . ' ' . $vehicle['model'])) : '' ?></p>
            </div>
            <a class="vehicle-btn vehicle-btn--ghost" href="resources.php">← กลับทะเบียนรถ</a>
        </div>
    </div>
</section>

<div class="vehicle-kpi-grid vehicle-repair-kpis">
    <div class="vehicle-kpi"><span class="vehicle-kpi__label">รถคันนี้</span><strong class="vehicle-kpi__value vehicle-kpi__value--text"><?= vehicle_e($vehicle['registration']) ?></strong><small><?= vehicle_e($vehicle['vehicle_type']) ?></small></div>
    <div class="vehicle-kpi"><span class="vehicle-kpi__label">ซ่อมไปทั้งหมด</span><strong class="vehicle-kpi__value"><?= (int)$summary['repair_count'] ?></strong><small>ครั้ง</small></div>
    <div class="vehicle-kpi"><span class="vehicle-kpi__label">จำนวนเงินที่ซ่อม</span><strong class="vehicle-kpi__value vehicle-kpi__value--money">฿<?= number_format((float)$summary['repair_total'], 2) ?></strong><small>ค่าใช้จ่ายสะสม</small></div>
    <div class="vehicle-kpi"><span class="vehicle-kpi__label">ซ่อมล่าสุด</span><strong class="vehicle-kpi__value vehicle-kpi__value--date"><?= $summary['last_repair_date'] ? vehicle_e(date('d/m/Y', strtotime($summary['last_repair_date']))) : '-' ?></strong><small>วันที่ส่งซ่อมล่าสุด</small></div>
</div>

<div class="vehicle-grid vehicle-repair-layout">
    <section class="vehicle-card vehicle-col-4">
        <div class="vehicle-card__header">
            <div>
                <h3><?= $editRepair ? 'แก้ไขรายการซ่อม' : 'เพิ่มรายการส่งซ่อม' ?></h3>
                <p>บันทึกข้อมูลการซ่อมของรถ <?= vehicle_e($vehicle['registration']) ?></p>
            </div>
        </div>
        <div class="vehicle-card__body">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= $editRepair ? (int)$editRepair['id'] : 0 ?>">
                <input type="hidden" name="vehicle_id" value="<?= (int)$vehicleId ?>">

                <div class="vehicle-field" style="margin-bottom:12px"><label>รถคันนี้</label><input class="vehicle-control" value="<?= vehicle_e($vehicle['registration']) ?>" disabled></div>
                <div class="vehicle-field" style="margin-bottom:12px"><label>วันที่ซ่อม <span class="vehicle-required">*</span></label><input class="vehicle-control" type="date" name="repair_date" required value="<?= vehicle_e($editRepair ? $editRepair['repair_date'] : date('Y-m-d')) ?>"></div>
                <div class="vehicle-field" style="margin-bottom:12px"><label>ร้าน / อู่ที่ซ่อม <span class="vehicle-required">*</span></label><input class="vehicle-control" name="shop_name" required value="<?= vehicle_e($editRepair ? $editRepair['shop_name'] : '') ?>" placeholder="เช่น อู่สมชายเซอร์วิส"></div>
                <div class="vehicle-field" style="margin-bottom:12px"><label>จำนวนเงินที่ซ่อม (บาท)</label><input class="vehicle-control" type="number" min="0" step="0.01" name="amount" value="<?= vehicle_e($editRepair ? $editRepair['amount'] : '0.00') ?>" placeholder="0.00"></div>
                <div class="vehicle-field" style="margin-bottom:12px"><label>รายละเอียดการซ่อม</label><textarea class="vehicle-control vehicle-textarea" name="detail" placeholder="เช่น เปลี่ยนน้ำมันเครื่อง เปลี่ยนผ้าเบรก ตรวจระบบช่วงล่าง"><?= vehicle_e($editRepair ? $editRepair['detail'] : '') ?></textarea></div>
                <div class="vehicle-field" style="margin-bottom:12px"><label>เลขที่ใบเสร็จ / เอกสาร</label><input class="vehicle-control" name="receipt_no" value="<?= vehicle_e($editRepair ? $editRepair['receipt_no'] : '') ?>"></div>
                <div class="vehicle-field" style="margin-bottom:12px"><label>เลขไมล์วันที่แจ่งซ่อม </label><input class="vehicle-control" type="number" min="0" name="odometer" value="<?= vehicle_e($editRepair && $editRepair['odometer'] !== null ? $editRepair['odometer'] : '') ?>" placeholder="เช่น 125000"></div>
                <div class="vehicle-field" style="margin-bottom:12px"><label>เช็คระยะ(กม.)</label><input class="vehicle-control" type="number" min="0" name="odometer" value="<?= vehicle_e($editRepair && $editRepair['odometer'] !== null ? $editRepair['odometer'] : '') ?>" placeholder="เช่น 125000"></div>

        </div>
        <div class="vehicle-field"><label>หมายเหตุ</label><textarea class="vehicle-control vehicle-textarea" name="notes"><?= vehicle_e($editRepair ? $editRepair['notes'] : '') ?></textarea></div>

        <button class="vehicle-btn vehicle-btn--primary vehicle-btn--wide" type="submit"><?= $editRepair ? 'บันทึกการแก้ไข' : 'บันทึกการส่งซ่อม' ?></button>
        <?php if ($editRepair): ?><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--wide" href="vehicle_repairs.php?vehicle_id=<?= (int)$vehicleId ?>">ยกเลิกแก้ไข</a><?php endif; ?>
        </form>
</div>
</section>

<section class="vehicle-card vehicle-col-8">
    <div class="vehicle-card__header">
        <div>
            <h2>ประวัติการส่งซ่อม</h2>
            <p>รถ <?= vehicle_e($vehicle['registration']) ?> · <?= (int)$summary['repair_count'] ?> ครั้ง · รวม ฿<?= number_format((float)$summary['repair_total'], 2) ?></p>
        </div>
    </div>
    <div class="vehicle-table-wrap">
        <table class="vehicle-table vehicle-repair-table" style="min-width:900px">
            <thead>
                <tr>
                    <th>#</th>
                    <th>วันที่ซ่อม</th>
                    <th>ร้าน / อู่</th>
                    <th>รายละเอียด</th>
                    <th>เลขไมล์</th>
                    <th>จำนวนเงิน</th>
                    <th>คำสั่ง</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1;
                if ($repairs && $repairs->num_rows): while ($r = $repairs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><strong><?= vehicle_e(date('d/m/Y', strtotime($r['repair_date']))) ?></strong><?php if ($r['receipt_no']): ?><br><small>เอกสาร: <?= vehicle_e($r['receipt_no']) ?></small><?php endif; ?></td>
                            <td><strong><?= vehicle_e($r['shop_name']) ?></strong></td>
                            <td><?= vehicle_e($r['detail'] ?: '-') ?><?php if ($r['notes']): ?><br><small><?= vehicle_e($r['notes']) ?></small><?php endif; ?></td>
                            <td><?= $r['odometer'] !== null ? number_format((int)$r['odometer']) . ' กม.' : '-' ?></td>
                            <td><strong>฿<?= number_format((float)$r['amount'], 2) ?></strong></td>
                            <td>
                                <div class="vehicle-actions">
                                    <a class="vehicle-btn vehicle-btn--warning vehicle-btn--small" href="vehicle_repairs.php?vehicle_id=<?= (int)$vehicleId ?>&edit=<?= (int)$r['id'] ?>">แก้ไข</a>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="vehicle_id" value="<?= (int)$vehicleId ?>">
                                        <button type="submit" class="vehicle-btn vehicle-btn--danger vehicle-btn--small" data-confirm="ยืนยันลบประวัติการซ่อมรายการนี้?">ลบ</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="vehicle-empty"><strong>ยังไม่มีประวัติการซ่อม</strong><span>กรอกข้อมูลทางด้านซ้ายเพื่อเพิ่มรายการแรก</span></div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
</div>
<?php vehicle_page_end(); ?>