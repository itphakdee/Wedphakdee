<?php
require_once __DIR__ . '/layout.php';
if (!$currentVehicleIsAdmin) { http_response_code(403); die('403 Forbidden: Admin เท่านั้น'); }

$tab = (isset($_GET['tab']) && $_GET['tab'] === 'drivers') ? 'drivers' : 'fleet';
$poroboReady = vehicle_column_exists('vehicle_fleet', 'porobo_expiry_date');

function vehicle_resource_date($value)
{
    $value = trim((string)$value);
    if ($value === '') return null;
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    vehicle_check_csrf();
    $kind = isset($_POST['kind']) ? $_POST['kind'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($kind === 'fleet') {
        if ($action === 'save') {
            $reg = trim((string)($_POST['registration'] ?? ''));
            $name = trim((string)($_POST['display_name'] ?? ''));
            $type = trim((string)($_POST['vehicle_type'] ?? 'รถโรงพยาบาล'));
            $brand = trim((string)($_POST['brand'] ?? ''));
            $model = trim((string)($_POST['model'] ?? ''));
            $status = (string)($_POST['status'] ?? 'active');
            $notes = trim((string)($_POST['notes'] ?? ''));
            if (!in_array($status, array('active','maintenance','inactive'), true)) $status = 'active';
            if ($reg === '') { vehicle_flash('danger','กรุณากรอกทะเบียนรถ'); vehicle_redirect('resources.php'); }

            if ($poroboReady) {
                $policy = trim((string)($_POST['porobo_policy_no'] ?? ''));
                $provider = trim((string)($_POST['porobo_provider'] ?? ''));
                $startDate = vehicle_resource_date($_POST['porobo_start_date'] ?? '');
                $expiryDate = vehicle_resource_date($_POST['porobo_expiry_date'] ?? '');
                $alertDays = (int)($_POST['porobo_alert_days'] ?? 30);
                if (!in_array($alertDays, array(7,15,30,45,60,90), true)) $alertDays = 30;
                $poroboNotes = trim((string)($_POST['porobo_notes'] ?? ''));

                if ($startDate && $expiryDate && strtotime($expiryDate) < strtotime($startDate)) {
                    vehicle_flash('danger','วันหมดอายุ พ.ร.บ. ต้องไม่น้อยกว่าวันเริ่มคุ้มครอง');
                    vehicle_redirect('resources.php' . ($id > 0 ? '?edit='.$id.'#porobo' : '#porobo'));
                }

                if ($id > 0) {
                    $stmt = $conn->prepare("UPDATE vehicle_fleet SET registration=?,display_name=?,vehicle_type=?,brand=?,model=?,status=?,notes=?,porobo_policy_no=?,porobo_provider=?,porobo_start_date=?,porobo_expiry_date=?,porobo_alert_days=?,porobo_notes=? WHERE id=?");
                    $stmt->bind_param('sssssssssssisi', $reg,$name,$type,$brand,$model,$status,$notes,$policy,$provider,$startDate,$expiryDate,$alertDays,$poroboNotes,$id);
                } else {
                    $stmt = $conn->prepare("INSERT INTO vehicle_fleet(registration,display_name,vehicle_type,brand,model,status,notes,porobo_policy_no,porobo_provider,porobo_start_date,porobo_expiry_date,porobo_alert_days,porobo_notes,sort_order) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,100)");
                    $stmt->bind_param('sssssssssssis', $reg,$name,$type,$brand,$model,$status,$notes,$policy,$provider,$startDate,$expiryDate,$alertDays,$poroboNotes);
                }
            } else {
                if ($id > 0) {
                    $stmt = $conn->prepare("UPDATE vehicle_fleet SET registration=?,display_name=?,vehicle_type=?,brand=?,model=?,status=?,notes=? WHERE id=?");
                    $stmt->bind_param('sssssssi',$reg,$name,$type,$brand,$model,$status,$notes,$id);
                } else {
                    $stmt = $conn->prepare("INSERT INTO vehicle_fleet(registration,display_name,vehicle_type,brand,model,status,notes,sort_order) VALUES(?,?,?,?,?,?,?,100)");
                    $stmt->bind_param('sssssss',$reg,$name,$type,$brand,$model,$status,$notes);
                }
            }

            if (!$stmt || !$stmt->execute()) {
                vehicle_flash('danger','บันทึกรถไม่สำเร็จ: ' . ($stmt ? $stmt->error : $conn->error));
            } else {
                vehicle_flash('success',$id>0?'แก้ไขข้อมูลรถและ พ.ร.บ. เรียบร้อยแล้ว':'เพิ่มรถเรียบร้อยแล้ว');
            }
            if ($stmt) $stmt->close();
            vehicle_redirect('resources.php');
        }

        if ($action === 'delete' && $id > 0) {
            $stmt=$conn->prepare("SELECT COUNT(*) FROM vehicle_requests WHERE vehicle_id=?");
            $stmt->bind_param('i',$id); $stmt->execute(); $stmt->bind_result($cnt); $stmt->fetch(); $stmt->close();
            if ((int)$cnt > 0) {
                $stmt=$conn->prepare("UPDATE vehicle_fleet SET status='inactive' WHERE id=?");
                $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
                vehicle_flash('warning','รถคันนี้มีประวัติใช้งาน จึงเปลี่ยนเป็น “ปิดใช้งาน” แทนการลบ');
            } else {
                $stmt=$conn->prepare("DELETE FROM vehicle_fleet WHERE id=?");
                $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
                vehicle_flash('success','ลบข้อมูลรถเรียบร้อยแล้ว');
            }
            vehicle_redirect('resources.php');
        }
    }

    if ($kind === 'driver') {
        if ($action === 'save') {
            $code=trim((string)($_POST['employee_code']??''));
            $name=trim((string)($_POST['fullname']??''));
            $phone=trim((string)($_POST['phone']??''));
            $license=trim((string)($_POST['license_no']??''));
            $status=(string)($_POST['status']??'active');
            $notes=trim((string)($_POST['notes']??''));
            if (!in_array($status,array('active','inactive'),true)) $status='active';
            if ($name==='') { vehicle_flash('danger','กรุณากรอกชื่อพนักงานขับ'); vehicle_redirect('resources.php?tab=drivers'); }
            if ($id>0) {
                $stmt=$conn->prepare("UPDATE vehicle_drivers SET employee_code=?,fullname=?,phone=?,license_no=?,status=?,notes=? WHERE id=?");
                $stmt->bind_param('ssssssi',$code,$name,$phone,$license,$status,$notes,$id);
            } else {
                $stmt=$conn->prepare("INSERT INTO vehicle_drivers(employee_code,fullname,phone,license_no,status,notes) VALUES(?,?,?,?,?,?)");
                $stmt->bind_param('ssssss',$code,$name,$phone,$license,$status,$notes);
            }
            if (!$stmt->execute()) vehicle_flash('danger','บันทึกพนักงานขับไม่สำเร็จ: '.$stmt->error);
            else vehicle_flash('success',$id>0?'แก้ไขพนักงานขับเรียบร้อยแล้ว':'เพิ่มพนักงานขับเรียบร้อยแล้ว');
            $stmt->close();
            vehicle_redirect('resources.php?tab=drivers');
        }
        if ($action==='delete'&&$id>0) {
            $stmt=$conn->prepare("SELECT COUNT(*) FROM vehicle_requests WHERE driver_id=?");
            $stmt->bind_param('i',$id);$stmt->execute();$stmt->bind_result($cnt);$stmt->fetch();$stmt->close();
            if((int)$cnt>0){
                $stmt=$conn->prepare("UPDATE vehicle_drivers SET status='inactive' WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();
                vehicle_flash('warning','พนักงานขับคนนี้มีประวัติงาน จึงเปลี่ยนเป็น “ปิดใช้งาน” แทนการลบ');
            } else {
                $stmt=$conn->prepare("DELETE FROM vehicle_drivers WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();
                vehicle_flash('success','ลบพนักงานขับเรียบร้อยแล้ว');
            }
            vehicle_redirect('resources.php?tab=drivers');
        }
    }
}

$editId=isset($_GET['edit'])?(int)$_GET['edit']:0;
$edit=null;
if($editId>0){
    $table=$tab==='drivers'?'vehicle_drivers':'vehicle_fleet';
    $stmt=$conn->prepare("SELECT * FROM `$table` WHERE id=? LIMIT 1");
    $stmt->bind_param('i',$editId);$stmt->execute();$rs=$stmt->get_result();$edit=$rs?$rs->fetch_assoc():null;$stmt->close();
}
$fleet=$conn->query("SELECT * FROM vehicle_fleet ORDER BY FIELD(status,'active','maintenance','inactive'),sort_order,registration");
$drivers=$conn->query("SELECT * FROM vehicle_drivers ORDER BY FIELD(status,'active','inactive'),fullname");

vehicle_page_start('จัดการรถและพนักงานขับ','เพิ่ม แก้ไข ทะเบียนรถ พนักงานขับ และกำหนดวันเริ่ม/หมดอายุ พ.ร.บ. เพื่อแจ้งเตือน LINE','resources');
?>
<div class="vehicle-resource-tabs">
    <a class="<?= $tab==='fleet'?'active':'' ?>" href="resources.php">ทะเบียนรถ</a>
    <a class="<?= $tab==='drivers'?'active':'' ?>" href="resources.php?tab=drivers">พนักงานขับ</a>
    <a href="porobo.php">แจ้งเตือน พ.ร.บ.</a>
    <a href="vehicle_repairs.php">ประวัติการส่งซ่อม</a>
    <a href="../repair_form/leave/supervisors.php">หัวหน้างานตามแผนก</a>
</div>

<?php if ($tab==='fleet' && !$poroboReady): ?>
<div class="vehicle-alert vehicle-alert--warning">ฟิลด์ พ.ร.บ. ยังไม่พร้อม กรุณา <a href="install.php"><strong>อัปเดตฐานข้อมูล</strong></a> ก่อน</div>
<?php endif; ?>

<div class="vehicle-grid">
<section class="vehicle-card vehicle-col-4">
    <div class="vehicle-card__header"><div><h3><?= $tab==='drivers'?($edit?'แก้ไขพนักงานขับ':'เพิ่มพนักงานขับ'):($edit?'แก้ไขทะเบียนรถ':'เพิ่มทะเบียนรถ') ?></h3><p>ข้อมูลจะแสดงใน Dropdown และระบบเตือน พ.ร.บ.</p></div></div>
    <div class="vehicle-card__body">
    <?php if($tab==='fleet'): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>">
            <input type="hidden" name="kind" value="fleet"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $edit?(int)$edit['id']:0 ?>">
            <div class="vehicle-field" style="margin-bottom:12px"><label>ทะเบียนรถ <span class="vehicle-required">*</span></label><input class="vehicle-control" name="registration" required value="<?= vehicle_e($edit?$edit['registration']:'') ?>" placeholder="เช่น กข 1130"></div>
            <div class="vehicle-field" style="margin-bottom:12px"><label>ชื่อเรียก / รายละเอียด</label><input class="vehicle-control" name="display_name" value="<?= vehicle_e($edit?$edit['display_name']:'') ?>" placeholder="เช่น รถตู้พยาบาล"></div>
            <div class="vehicle-field" style="margin-bottom:12px"><label>ประเภทรถ</label><input class="vehicle-control" name="vehicle_type" value="<?= vehicle_e($edit?$edit['vehicle_type']:'รถโรงพยาบาล') ?>"></div>
            <div class="vehicle-field" style="margin-bottom:12px"><label>ยี่ห้อ / รุ่น</label><div style="display:grid;grid-template-columns:1fr 1fr;gap:8px"><input class="vehicle-control" name="brand" value="<?= vehicle_e($edit?$edit['brand']:'') ?>" placeholder="ยี่ห้อ"><input class="vehicle-control" name="model" value="<?= vehicle_e($edit?$edit['model']:'') ?>" placeholder="รุ่น"></div></div>

            <?php if($poroboReady): ?>
            <div id="porobo" class="vehicle-porobo-form-block">
                <div class="vehicle-porobo-form-title"><strong>ข้อมูล พ.ร.บ. รถ</strong><span>ใช้สำหรับแจ้งเตือน LINE ก่อนหมดอายุ</span></div>
                <div class="vehicle-field"><label>เลขที่กรมธรรม์ / พ.ร.บ.</label><input class="vehicle-control" name="porobo_policy_no" value="<?= vehicle_e($edit?$edit['porobo_policy_no']:'') ?>"></div>
                <div class="vehicle-field"><label>บริษัท / ผู้รับประกัน</label><input class="vehicle-control" name="porobo_provider" value="<?= vehicle_e($edit?$edit['porobo_provider']:'') ?>"></div>
                <div class="vehicle-field"><label>วันเริ่มคุ้มครอง</label><input class="vehicle-control" type="date" name="porobo_start_date" value="<?= vehicle_e($edit?$edit['porobo_start_date']:'') ?>"></div>
                <div class="vehicle-field"><label>วันหมดอายุ พ.ร.บ.</label><input class="vehicle-control" type="date" name="porobo_expiry_date" value="<?= vehicle_e($edit?$edit['porobo_expiry_date']:'') ?>"></div>
                <div class="vehicle-field"><label>เริ่มแจ้งเตือนล่วงหน้า</label><select class="vehicle-control" name="porobo_alert_days"><?php $ad=$edit?(int)$edit['porobo_alert_days']:30; foreach(array(7,15,30,45,60,90) as $d): ?><option value="<?= $d ?>" <?= $ad===$d?'selected':'' ?>><?= $d ?> วัน</option><?php endforeach; ?></select></div>
                <div class="vehicle-field"><label>หมายเหตุ พ.ร.บ.</label><textarea class="vehicle-control vehicle-textarea" name="porobo_notes"><?= vehicle_e($edit?$edit['porobo_notes']:'') ?></textarea></div>
            </div>
            <?php endif; ?>

            <div class="vehicle-field" style="margin-bottom:12px"><label>สถานะ</label><select class="vehicle-control" name="status"><option value="active" <?= $edit&&$edit['status']==='active'?'selected':'' ?>>พร้อมใช้งาน</option><option value="maintenance" <?= $edit&&$edit['status']==='maintenance'?'selected':'' ?>>ซ่อมบำรุง</option><option value="inactive" <?= $edit&&$edit['status']==='inactive'?'selected':'' ?>>ปิดใช้งาน</option></select></div>
            <div class="vehicle-field"><label>หมายเหตุรถ</label><textarea class="vehicle-control vehicle-textarea" name="notes"><?= vehicle_e($edit?$edit['notes']:'') ?></textarea></div>
            <button class="vehicle-btn vehicle-btn--primary vehicle-btn--wide" type="submit"><?= $edit?'บันทึกการแก้ไข':'เพิ่มรถ' ?></button>
            <?php if($edit):?><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--wide" href="resources.php">ยกเลิกแก้ไข</a><?php endif;?>
        </form>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>"><input type="hidden" name="kind" value="driver"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $edit?(int)$edit['id']:0 ?>">
            <div class="vehicle-field" style="margin-bottom:12px"><label>รหัสพนักงาน</label><input class="vehicle-control" name="employee_code" value="<?= vehicle_e($edit?$edit['employee_code']:'') ?>"></div>
            <div class="vehicle-field" style="margin-bottom:12px"><label>ชื่อ–สกุล <span class="vehicle-required">*</span></label><input class="vehicle-control" name="fullname" required value="<?= vehicle_e($edit?$edit['fullname']:'') ?>"></div>
            <div class="vehicle-field" style="margin-bottom:12px"><label>เบอร์โทรศัพท์</label><input class="vehicle-control" name="phone" value="<?= vehicle_e($edit?$edit['phone']:'') ?>"></div>
            <div class="vehicle-field" style="margin-bottom:12px"><label>เลขใบขับขี่</label><input class="vehicle-control" name="license_no" value="<?= vehicle_e($edit?$edit['license_no']:'') ?>"></div>
            <div class="vehicle-field" style="margin-bottom:12px"><label>สถานะ</label><select class="vehicle-control" name="status"><option value="active" <?= $edit&&$edit['status']==='active'?'selected':'' ?>>ปฏิบัติงาน</option><option value="inactive" <?= $edit&&$edit['status']==='inactive'?'selected':'' ?>>ปิดใช้งาน</option></select></div>
            <div class="vehicle-field"><label>หมายเหตุ</label><textarea class="vehicle-control vehicle-textarea" name="notes"><?= vehicle_e($edit?$edit['notes']:'') ?></textarea></div>
            <button class="vehicle-btn vehicle-btn--primary vehicle-btn--wide" type="submit"><?= $edit?'บันทึกการแก้ไข':'เพิ่มพนักงานขับ' ?></button>
            <?php if($edit):?><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--wide" href="resources.php?tab=drivers">ยกเลิกแก้ไข</a><?php endif;?>
        </form>
    <?php endif; ?>
    </div>
</section>

<section class="vehicle-card vehicle-col-8">
    <div class="vehicle-card__header"><div><h2><?= $tab==='drivers'?'รายชื่อพนักงานขับ':'ทะเบียนรถโรงพยาบาล' ?></h2><p>เพิ่ม แก้ไข ปิดใช้งาน และติดตามข้อมูล พ.ร.บ. ของรถแต่ละคัน</p></div></div>
    <div class="vehicle-table-wrap"><table class="vehicle-table" style="min-width:760px">
        <?php if($tab==='fleet'): ?>
        <thead><tr><th>#</th><th>ทะเบียน</th><th>รายละเอียด</th><th>พ.ร.บ. หมดอายุ</th><th>สถานะ พ.ร.บ.</th><th>สถานะรถ</th><th>คำสั่ง</th></tr></thead>
        <tbody>
        <?php if($fleet):$i=1;while($r=$fleet->fetch_assoc()): $days=$poroboReady?vehicle_porobo_days_remaining($r['porobo_expiry_date']):null; $pm=vehicle_porobo_status_meta($days); ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><strong><?= vehicle_e($r['registration']) ?></strong></td>
            <td><?= vehicle_e($r['display_name']) ?><br><small><?= vehicle_e(trim($r['brand'].' '.$r['model'])) ?></small></td>
            <td><?= $poroboReady?vehicle_format_thai_date($r['porobo_expiry_date']):'-' ?></td>
            <td><?= $poroboReady?'<span class="vehicle-porobo-status '.vehicle_e($pm[1]).'">'.vehicle_e($pm[0]).'</span>':'-' ?></td>
            <td><?= vehicle_e($r['status']) ?></td>
            <td><div class="vehicle-actions">
                <a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" href="vehicle_repairs.php?vehicle_id=<?= (int)$r['id'] ?>">ประวัติการส่งซ่อม</a>
                <a class="vehicle-btn vehicle-btn--warning vehicle-btn--small" href="resources.php?edit=<?= (int)$r['id'] ?>#porobo">แก้ไข</a>
                <form method="post"><input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>"><input type="hidden" name="kind" value="fleet"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="vehicle-btn vehicle-btn--danger vehicle-btn--small" data-confirm="ยืนยันลบ/ปิดใช้งานรถคันนี้?">ลบ</button></form>
            </div></td>
        </tr>
        <?php endwhile;endif; ?>
        </tbody>
        <?php else: ?>
        <thead><tr><th>#</th><th>ชื่อ–สกุล</th><th>รหัส</th><th>เบอร์โทร</th><th>ใบขับขี่</th><th>สถานะ</th><th>คำสั่ง</th></tr></thead>
        <tbody>
        <?php if($drivers):$i=1;while($r=$drivers->fetch_assoc()): ?>
        <tr><td><?= $i++ ?></td><td><strong><?= vehicle_e($r['fullname']) ?></strong></td><td><?= vehicle_e($r['employee_code']) ?></td><td><?= vehicle_e($r['phone']) ?></td><td><?= vehicle_e($r['license_no']) ?></td><td><?= vehicle_e($r['status']) ?></td><td><div class="vehicle-actions"><a class="vehicle-btn vehicle-btn--warning vehicle-btn--small" href="resources.php?tab=drivers&edit=<?= (int)$r['id'] ?>">แก้ไข</a><form method="post"><input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>"><input type="hidden" name="kind" value="driver"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="vehicle-btn vehicle-btn--danger vehicle-btn--small" data-confirm="ยืนยันลบ/ปิดใช้งานพนักงานขับคนนี้?">ลบ</button></form></div></td></tr>
        <?php endwhile;endif; ?>
        </tbody>
        <?php endif; ?>
    </table></div>
</section>
</div>
<div class="vehicle-inline-note" style="margin-top:18px"><strong>การแจ้งเตือน พ.ร.บ.:</strong> กำหนดวันหมดอายุและจำนวนวันแจ้งล่วงหน้าในทะเบียนรถ จากนั้นระบบสามารถส่ง LINE เข้ากลุ่มผ่าน Google Apps Script ได้จากหน้า “แจ้งเตือน พ.ร.บ.”</div>
<?php vehicle_page_end(); ?>
