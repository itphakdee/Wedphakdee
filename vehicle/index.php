<?php
require_once __DIR__ . '/layout.php';

if (!vehicle_table_exists('vehicle_fleet') || !vehicle_column_exists('vehicle_requests', 'request_no')) {
    if ($currentVehicleIsAdmin) {
        vehicle_redirect('install.php');
    }
    die('ระบบยานพาหนะยังไม่ได้ติดตั้ง กรุณาติดต่อผู้ดูแลระบบ');
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowedPages = array('dashboard','add','list','calendar');
if (!in_array($page, $allowedPages, true)) {
    $page = 'dashboard';
}

$uid = (int)$_SESSION['user_id'];
$scopeSql = $currentVehicleIsAdmin ? '1=1' : ('vr.user_id=' . $uid);

if ($page === 'dashboard') {
    $stats = array('total'=>0,'pending'=>0,'month'=>0,'completed'=>0,'today'=>0,'fleet'=>0);
    $queries = array(
        'total' => "SELECT COUNT(*) c FROM vehicle_requests vr WHERE $scopeSql",
        'pending' => "SELECT COUNT(*) c FROM vehicle_requests vr WHERE $scopeSql AND vr.status IN ('pending_supervisor','pending_admin','approved','assigned','cancel_requested')",
        'month' => "SELECT COUNT(*) c FROM vehicle_requests vr WHERE $scopeSql AND DATE_FORMAT(vr.use_date,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')",
        'completed' => "SELECT COUNT(*) c FROM vehicle_requests vr WHERE $scopeSql AND vr.status='completed'",
        'today' => "SELECT COUNT(*) c FROM vehicle_requests vr WHERE $scopeSql AND vr.use_date=CURDATE()"
    );
    foreach ($queries as $k=>$sql) {
        $rs=$conn->query($sql); if($rs && ($r=$rs->fetch_assoc())) $stats[$k]=(int)$r['c'];
    }
    $rs=$conn->query("SELECT COUNT(*) c FROM vehicle_fleet WHERE status='active'"); if($rs&&($r=$rs->fetch_assoc()))$stats['fleet']=(int)$r['c'];

    $recent = $conn->query("SELECT vr.*,vf.registration AS fleet_registration,vd.fullname AS driver_name
                            FROM vehicle_requests vr
                            LEFT JOIN vehicle_fleet vf ON vf.id=vr.vehicle_id
                            LEFT JOIN vehicle_drivers vd ON vd.id=vr.driver_id
                            WHERE $scopeSql ORDER BY vr.id DESC LIMIT 8");

    $usage = $conn->query("SELECT COALESCE(NULLIF(vr.hospital_registration,''),vf.registration,'รถส่วนตัว') label,COUNT(*) total
                           FROM vehicle_requests vr
                           LEFT JOIN vehicle_fleet vf ON vf.id=vr.vehicle_id
                           WHERE $scopeSql AND vr.use_date>=DATE_SUB(CURDATE(),INTERVAL 90 DAY)
                           GROUP BY label ORDER BY total DESC LIMIT 6");

    vehicle_page_start('ภาพรวมงานบริการยานพาหนะ','ติดตามคำขอใช้รถ สถานะการรับรอง รถที่ใช้งาน และภารกิจประจำวันในหน้าจอเดียว','dashboard');
    ?>
    <div class="vehicle-kpi-grid">
        <div class="vehicle-kpi"><div class="vehicle-kpi__icon">🚐</div><div class="vehicle-kpi__label">คำขอทั้งหมด</div><div class="vehicle-kpi__value"><?= $stats['total'] ?></div><div class="vehicle-kpi__sub"><?= $currentVehicleIsAdmin?'ทุกหน่วยงาน':'เฉพาะรายการของฉัน' ?></div></div>
        <div class="vehicle-kpi"><div class="vehicle-kpi__icon">⏳</div><div class="vehicle-kpi__label">อยู่ระหว่างดำเนินการ</div><div class="vehicle-kpi__value"><?= $stats['pending'] ?></div><div class="vehicle-kpi__sub">รอรับรอง / รอจัดรถ / รอใช้งาน</div></div>
        <div class="vehicle-kpi"><div class="vehicle-kpi__icon">📅</div><div class="vehicle-kpi__label">ภารกิจเดือนนี้</div><div class="vehicle-kpi__value"><?= $stats['month'] ?></div><div class="vehicle-kpi__sub">วันนี้มี <?= $stats['today'] ?> รายการ</div></div>
        <div class="vehicle-kpi"><div class="vehicle-kpi__icon">✅</div><div class="vehicle-kpi__label">เสร็จสิ้น</div><div class="vehicle-kpi__value"><?= $stats['completed'] ?></div><div class="vehicle-kpi__sub">รถพร้อมใช้งาน <?= $stats['fleet'] ?> คัน</div></div>
    </div>

    <div class="vehicle-grid">
        <section class="vehicle-card vehicle-col-8">
            <div class="vehicle-card__header"><div><h2>รายการล่าสุด</h2><p>คำขอใช้รถที่มีการบันทึกล่าสุดในระบบ</p></div><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" href="index.php?page=list">ดูทั้งหมด</a></div>
            <div class="vehicle-table-wrap"><table class="vehicle-table" style="min-width:760px"><thead><tr><th>เลขที่</th><th>ผู้ร้องขอ</th><th>ประเภท</th><th>รถ</th><th>วันใช้งาน</th><th>สถานะ</th><th></th></tr></thead><tbody>
            <?php if($recent && $recent->num_rows): while($row=$recent->fetch_assoc()): $sm=vehicle_status_meta($row['status']); $tm=vehicle_request_type_meta($row['request_type']); ?>
                <tr><td><strong><?= vehicle_e($row['request_no']?$row['request_no']:'#'.$row['id']) ?></strong></td><td><?= vehicle_e($row['fullname']) ?><br><small><?= vehicle_e($row['department_name']) ?></small></td><td><span class="vehicle-type-pill <?= $row['request_type']==='refer'?'vehicle-type-pill--refer':'' ?>"><?= vehicle_e($tm[0]) ?></span></td><td><?= vehicle_e($row['private_registration']?$row['private_registration']:($row['hospital_registration']?$row['hospital_registration']:$row['fleet_registration'])) ?></td><td><?= vehicle_format_thai_date($row['use_date']) ?><br><small><?= vehicle_e(substr((string)$row['use_time'],0,5)) ?> น.</small></td><td><span class="vehicle-badge vehicle-badge--<?= vehicle_e($sm[1]) ?>"><?= vehicle_e($sm[0]) ?></span></td><td><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" href="detail.php?id=<?= (int)$row['id'] ?>">รายละเอียด</a></td></tr>
            <?php endwhile; else: ?><tr><td colspan="7"><div class="vehicle-empty"><div class="vehicle-empty__icon">🚐</div><strong>ยังไม่มีรายการขอใช้รถ</strong><span>เริ่มต้นโดยกด “ขอใช้รถ” ด้านบน</span></div></td></tr><?php endif; ?>
            </tbody></table></div>
        </section>
        <section class="vehicle-card vehicle-col-4">
            <div class="vehicle-card__header"><div><h3>สถิติรถที่ถูกเลือก</h3><p>ย้อนหลัง 90 วัน</p></div></div>
            <div class="vehicle-card__body">
                <?php $max=1;$usageRows=array();if($usage){while($u=$usage->fetch_assoc()){$usageRows[]=$u;$max=max($max,(int)$u['total']);}} ?>
                <?php if($usageRows): foreach($usageRows as $u): $pct=round(((int)$u['total']/$max)*100); ?>
                    <div style="margin-bottom:16px"><div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:6px"><strong><?= vehicle_e($u['label']) ?></strong><span><?= (int)$u['total'] ?> ครั้ง</span></div><div class="vehicle-stat-bar"><span style="width:<?= $pct ?>%"></span></div></div>
                <?php endforeach; else: ?><div class="vehicle-empty"><strong>ยังไม่มีข้อมูลสถิติ</strong></div><?php endif; ?>
            </div>
        </section>
    </div>
    <?php
    vehicle_page_end();
    exit;
}

if ($page === 'add') {
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    if (!in_array($type,array('general','refer'),true)) $type='';
    vehicle_page_start('เพิ่มข้อมูลการใช้รถ','ยื่นคำขอใช้รถของโรงพยาบาลหรือรถยนต์ส่วนตัว พร้อมส่งแจ้งเตือนผ่าน LINE Messaging API','add');

    if ($type === '') {
        ?>
        <section class="vehicle-card"><div class="vehicle-card__header"><div><h2>เลือกประเภทการขอใช้รถ</h2><p>เลือกให้ตรงกับลักษณะภารกิจ ระบบจะใช้แบบฟอร์มและสถานะที่เหมาะสม</p></div></div><div class="vehicle-card__body">
            <div class="vehicle-choice-grid">
                <a class="vehicle-choice" href="index.php?page=add&type=general"><div class="vehicle-choice__icon">🚗</div><div><h3>ประชุม / ภารกิจทั่วไป</h3><p>สำหรับประชุม อบรม รับส่งเอกสาร ติดต่อราชการ และภารกิจทั่วไป</p></div><div class="vehicle-choice__arrow">→</div></a>
                <a class="vehicle-choice vehicle-choice--refer" href="index.php?page=add&type=refer"><div class="vehicle-choice__icon">🚑</div><div><h3>ฉุกเฉิน (REFER)</h3><p>สำหรับภารกิจรับ–ส่งต่อผู้ป่วย หรือภารกิจเร่งด่วนของโรงพยาบาล</p></div><div class="vehicle-choice__arrow">→</div></a>
            </div>
        </div></section>
        <?php vehicle_page_end(); exit;
    }

    $fleet = $conn->query("SELECT * FROM vehicle_fleet WHERE status='active' ORDER BY sort_order,registration");
    $drivers = $conn->query("SELECT * FROM vehicle_drivers WHERE status='active' ORDER BY fullname");
    $people = $conn->query("SELECT u.id,u.fullname,
                                   COALESCE((SELECT p.position_name FROM personnel p WHERE p.user_id=u.id ORDER BY p.id DESC LIMIT 1),'') position_name,
                                   '' level_name
                            FROM users u WHERE u.status='active' AND u.id<>$uid ORDER BY u.fullname");
    if ($people === false) {
        error_log('Vehicle companion users query failed: ' . $conn->error);
    }
    $supervisorName = isset($currentVehicleSupervisor['fullname']) ? $currentVehicleSupervisor['fullname'] : '';
    $deptName = $currentVehicleDept['name'];
    $typeMeta=vehicle_request_type_meta($type);
    ?>
    <section class="vehicle-card">
      <div class="vehicle-card__header"><div><h2><?= vehicle_e($typeMeta[0]) ?></h2><p>กรอกข้อมูลให้ครบถ้วน ข้อมูลผู้ร้องขอ ผู้ทำรายการ แผนก และหัวหน้างานจะตรวจจากบัญชีที่เข้าสู่ระบบ</p></div><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" href="index.php?page=add">← เปลี่ยนประเภท</a></div>
      <div class="vehicle-card__body">
      <?php if($deptName===''): ?><div class="vehicle-alert vehicle-alert--warning">บัญชีนี้ยังไม่มีข้อมูลแผนก กรุณาให้ Admin กำหนดแผนกในข้อมูลผู้ใช้ก่อน เพื่อให้ระบบเลือกหัวหน้างานได้ถูกต้อง</div><?php elseif($supervisorName===''): ?><div class="vehicle-alert vehicle-alert--warning">พบแผนก “<?= vehicle_e($deptName) ?>” แต่ยังไม่ได้กำหนดหัวหน้างาน ระบบจะส่งรายการไปให้เจ้าหน้าที่ดำเนินการโดยตรง</div><?php endif; ?>
      <form id="vehicleRequestForm" action="save.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>">
        <input type="hidden" name="request_type" value="<?= vehicle_e($type) ?>">

        <div class="vehicle-section"><div class="vehicle-section__head"><span class="vehicle-section__num">01</span><div><strong>ข้อมูลหนังสือและความเร่งด่วน</strong><small>ข้อมูลอ้างอิงของคำขอใช้รถ</small></div></div><div class="vehicle-section__body"><div class="vehicle-form-grid">
          <div class="vehicle-field vehicle-field--4"><label>ตามหนังสือ</label><input class="vehicle-control" name="book_reference" placeholder="เช่น หนังสือเชิญ / คำสั่ง / บันทึกข้อความ"></div>
          <div class="vehicle-field vehicle-field--4"><label>เลขที่หนังสือ</label><input class="vehicle-control" name="book_no" placeholder="เช่น ชย 0033/..."></div>
          <div class="vehicle-field vehicle-field--4"><label>ลงวันที่</label><input class="vehicle-control" type="date" name="book_date"></div>
          <div class="vehicle-field vehicle-field--4"><label>ความเร่งด่วน <span class="vehicle-required">*</span></label><select class="vehicle-control vehicle-select" name="urgency" required><option value="ปกติ">ปกติ</option><option value="ด่วน">ด่วน</option><option value="ด่วนมาก" <?= $type==='refer'?'selected':'' ?>>ด่วนมาก</option></select></div>
          <div class="vehicle-field vehicle-field--8"><label>เอกสารอ้างอิง / แนบหนังสือ</label><div class="vehicle-doc-upload"><span>📎</span><input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png"></div><small class="vehicle-help">รองรับ PDF, JPG, PNG ไม่เกิน 8 MB</small></div>
        </div></div></div>

        <div class="vehicle-section"><div class="vehicle-section__head"><span class="vehicle-section__num">02</span><div><strong>รถและรายละเอียดภารกิจ</strong><small>เลือกรถโรงพยาบาล หรือกรอกทะเบียนรถยนต์ส่วนตัวอย่างใดอย่างหนึ่ง</small></div></div><div class="vehicle-section__body"><div class="vehicle-form-grid">
          <div class="vehicle-field"><label>รถโรงพยาบาลทะเบียน</label><select id="vehicle_id" class="vehicle-control vehicle-select" name="vehicle_id"><option value="">-- เลือกรถโรงพยาบาล --</option><?php if($fleet):while($v=$fleet->fetch_assoc()):?><option value="<?= (int)$v['id'] ?>"><?= vehicle_e($v['registration']) ?><?= $v['display_name']?' · '.vehicle_e($v['display_name']):'' ?></option><?php endwhile;endif;?></select></div>
          <div class="vehicle-field"><label>รถยนต์ส่วนตัวทะเบียน</label><input id="private_registration" class="vehicle-control" name="private_registration" placeholder="กรอกเมื่อใช้รถยนต์ส่วนตัว"><small class="vehicle-help">* หากใช้รถยนต์ส่วนตัว กรุณากรอกเลขทะเบียนรถของท่าน</small></div>
          <div class="vehicle-field vehicle-field--12"><label>เหตุผลขอใช้รถ <span class="vehicle-required">*</span></label><textarea class="vehicle-control vehicle-textarea" name="reason" required placeholder="ระบุวัตถุประสงค์ของการเดินทาง"></textarea></div>
          <div class="vehicle-field vehicle-field--12"><label>รายละเอียด</label><textarea class="vehicle-control vehicle-textarea" name="detail" placeholder="รายละเอียดเพิ่มเติม เช่น หน่วยงานที่ติดต่อ จำนวนผู้ร่วมเดินทาง หรือข้อมูลประกอบ"></textarea></div>
          <div class="vehicle-field vehicle-field--12"><label>สถานที่ไป <span class="vehicle-required">*</span></label><input class="vehicle-control" name="location" required placeholder="ระบุสถานที่ / หน่วยงาน / จังหวัด"></div>
          <div class="vehicle-field vehicle-field--3"><label>วันที่เริ่มใช้ <span class="vehicle-required">*</span></label><input class="vehicle-control" type="date" name="use_date" required min="<?= date('Y-m-d') ?>"></div>
          <div class="vehicle-field vehicle-field--3"><label>เวลาเริ่ม <span class="vehicle-required">*</span></label><input class="vehicle-control" type="time" name="use_time" required></div>
          <div class="vehicle-field vehicle-field--3"><label>วันที่สิ้นสุด</label><input class="vehicle-control" type="date" name="end_date"></div>
          <div class="vehicle-field vehicle-field--3"><label>เวลาสิ้นสุด</label><input class="vehicle-control" type="time" name="end_time"></div>
          <div class="vehicle-field vehicle-field--12"><label>พนักงานขับ</label><select class="vehicle-control vehicle-select" name="driver_id"><option value="">-- ยังไม่ระบุ / ให้เจ้าหน้าที่จัดพนักงานขับ --</option><?php if($drivers):while($d=$drivers->fetch_assoc()):?><option value="<?= (int)$d['id'] ?>"><?= vehicle_e($d['fullname']) ?><?= $d['phone']?' · '.vehicle_e($d['phone']):'' ?></option><?php endwhile;endif;?></select><small class="vehicle-help">Admin สามารถเปลี่ยนพนักงานขับภายหลังได้</small></div>
        </div></div></div>

        <div class="vehicle-section"><div class="vehicle-section__head"><span class="vehicle-section__num">03</span><div><strong>ผู้ร้องขอ ผู้ทำรายการ และผู้รับรอง</strong><small>ดึงจากข้อมูลบัญชีผู้ใช้และแผนกโดยอัตโนมัติ</small></div></div><div class="vehicle-section__body"><div class="vehicle-form-grid">
          <div class="vehicle-field"><label>ผู้ร้องขอ</label><input class="vehicle-control" readonly value="<?= vehicle_e($currentVehicleUser['fullname']) ?>"></div>
          <div class="vehicle-field"><label>ผู้ทำรายการ</label><input class="vehicle-control" readonly value="<?= vehicle_e($currentVehicleUser['fullname']) ?>"></div>
          <div class="vehicle-field"><label>แผนก / หน่วยงาน</label><input class="vehicle-control" readonly value="<?= vehicle_e($deptName!==''?$deptName:'ยังไม่ระบุแผนก') ?>"></div>
          <div class="vehicle-field"><label>หัวหน้างานรับรอง</label><input class="vehicle-control" readonly value="<?= vehicle_e($supervisorName!==''?$supervisorName:'ยังไม่ได้กำหนดหัวหน้างาน') ?>"><small class="vehicle-help">ระบบตรวจจากแผนกของ User แล้วดึงหัวหน้าแผนกนั้นอัตโนมัติ</small></div>
        </div></div></div>

        <div class="vehicle-section vehicle-section--dropdown"><div class="vehicle-section__head"><span class="vehicle-section__num">04</span><div><strong>ผู้ร่วมเดินทาง</strong><small>เลือกได้หลายคน โดยแสดงชื่อ–สกุล ตำแหน่ง และระดับ</small></div></div><div class="vehicle-section__body">
          <div class="vehicle-multiselect" data-multiselect><button type="button" class="vehicle-multiselect__button"><span>เลือกผู้ร่วมเดินทาง</span><span class="vehicle-selected-count">ยังไม่ได้เลือก</span></button><div class="vehicle-multiselect__panel"><input class="vehicle-multiselect__search" type="search" placeholder="ค้นหาชื่อ / ตำแหน่ง"><div class="vehicle-person" style="background:#f5f8f9;font-weight:800"><span></span><span>ชื่อ–สกุล</span><span>ตำแหน่ง</span><span>ระดับ</span></div><?php if($people):while($p=$people->fetch_assoc()):?><label class="vehicle-person"><input type="checkbox" name="members[]" value="<?= (int)$p['id'] ?>"><strong><?= vehicle_e($p['fullname']) ?></strong><span><?= vehicle_e($p['position_name']?$p['position_name']:'-') ?></span><span><?= vehicle_e($p['level_name']?$p['level_name']:'-') ?></span></label><?php endwhile;endif;?></div></div>
        </div></div>

        <div class="vehicle-inline-note"><strong>LINE Messaging API:</strong> หลังบันทึก ระบบจะบันทึกข้อมูลลงฐานข้อมูลก่อน แล้วจึงส่งแจ้งเตือนผ่าน Google Apps Script ไปยัง LINE หาก LINE ขัดข้อง คำขอใช้รถจะยังถูกเก็บไว้ในระบบ</div>
        <div class="vehicle-form-actions" style="margin-top:18px"><a class="vehicle-btn vehicle-btn--ghost" href="index.php?page=add">ยกเลิก</a><button class="vehicle-btn vehicle-btn--primary" type="submit">💾 บันทึกคำขอและแจ้งเตือน LINE</button></div>
      </form>
      </div>
    </section>
    <?php vehicle_page_end(); exit;
}

if ($page === 'list') {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $month = isset($_GET['month']) ? $_GET['month'] : '';
    $statusAllowed = array('','pending_supervisor','pending_admin','approved','assigned','in_use','completed','cancel_requested','cancelled','rejected');
    $typeAllowed = array('','general','refer');
    if(!in_array($status,$statusAllowed,true))$status='';
    if(!in_array($type,$typeAllowed,true))$type='';
    if($month!==''&&!preg_match('/^\d{4}-\d{2}$/',$month))$month='';
    $where=array($scopeSql);
    if($q!==''){$esc=$conn->real_escape_string($q);$where[]="(vr.request_no LIKE '%$esc%' OR vr.fullname LIKE '%$esc%' OR vr.location LIKE '%$esc%' OR vr.hospital_registration LIKE '%$esc%' OR vr.private_registration LIKE '%$esc%' OR vr.reason LIKE '%$esc%')";}
    if($status!=='')$where[]="vr.status='".$conn->real_escape_string($status)."'";
    if($type!=='')$where[]="vr.request_type='".$conn->real_escape_string($type)."'";
    if($month!=='')$where[]="DATE_FORMAT(vr.use_date,'%Y-%m')='".$conn->real_escape_string($month)."'";
    $whereSql=implode(' AND ',$where);
    $perPage=10;$pageno=max(1,isset($_GET['p'])?(int)$_GET['p']:1);
    $rs=$conn->query("SELECT COUNT(*) c FROM vehicle_requests vr WHERE $whereSql");$total=0;if($rs&&($r=$rs->fetch_assoc()))$total=(int)$r['c'];
    $pages=max(1,(int)ceil($total/$perPage));if($pageno>$pages)$pageno=$pages;$offset=($pageno-1)*$perPage;
    $rows=$conn->query("SELECT vr.*,vf.registration AS fleet_registration,vd.fullname AS driver_name,
                               fb.rating AS feedback_rating
                        FROM vehicle_requests vr
                        LEFT JOIN vehicle_fleet vf ON vf.id=vr.vehicle_id
                        LEFT JOIN vehicle_drivers vd ON vd.id=vr.driver_id
                        LEFT JOIN vehicle_feedback fb ON fb.request_id=vr.id
                        WHERE $whereSql ORDER BY vr.id DESC LIMIT $offset,$perPage");
    vehicle_page_start('ทะเบียนใช้รถ','ค้นหาและติดตามคำขอใช้รถทั้งหมด พร้อมรายละเอียด ความพึงพอใจ การแก้ไข แจ้งยกเลิก และพิมพ์เอกสาร','list');
    ?>
    <section class="vehicle-card">
      <div class="vehicle-card__header"><div><h2><?= $currentVehicleIsAdmin?'ทะเบียนคำขอใช้รถทุกหน่วยงาน':'ทะเบียนคำขอใช้รถของฉัน' ?></h2><p>พบ <?= number_format($total) ?> รายการ · User จะเห็นเฉพาะคำขอของตนเอง ส่วน Admin เห็นทั้งหมด</p></div><a class="vehicle-btn vehicle-btn--primary" href="index.php?page=add">＋ เพิ่มคำขอ</a></div>
      <div class="vehicle-card__body" style="padding-bottom:10px"><form class="vehicle-filter" method="get"><input type="hidden" name="page" value="list"><div class="vehicle-field vehicle-search-box"><label>ค้นหา</label><span>⌕</span><input class="vehicle-control" name="q" value="<?= vehicle_e($q) ?>" placeholder="เลขที่คำขอ / ชื่อ / สถานที่ / ทะเบียนรถ"></div><div class="vehicle-field"><label>สถานะ</label><select class="vehicle-control" name="status"><option value="">ทุกสถานะ</option><?php foreach($statusAllowed as $s){if($s==='')continue;$m=vehicle_status_meta($s);?><option value="<?= vehicle_e($s) ?>" <?= $status===$s?'selected':'' ?>><?= vehicle_e($m[0]) ?></option><?php } ?></select></div><div class="vehicle-field"><label>ประเภท</label><select class="vehicle-control" name="type"><option value="">ทุกประเภท</option><option value="general" <?= $type==='general'?'selected':'' ?>>ทั่วไป</option><option value="refer" <?= $type==='refer'?'selected':'' ?>>REFER</option></select></div><div class="vehicle-field"><label>เดือนใช้งาน</label><input class="vehicle-control" type="month" name="month" value="<?= vehicle_e($month) ?>"></div><button class="vehicle-btn vehicle-btn--navy" type="submit">ค้นหา</button></form></div>
      <div class="vehicle-table-wrap"><table class="vehicle-table"><thead><tr><th>ลำดับ</th><th>คำขอ / ผู้ร้องขอ</th><th>สถานะ</th><th>ประเภท</th><th>รถ / พนักงานขับ</th><th>สถานที่ / วันเวลา</th><th>ความพึงพอใจ</th><th>คำสั่ง</th></tr></thead><tbody>
      <?php if($rows&&$rows->num_rows):$seq=$offset+1;while($row=$rows->fetch_assoc()):$sm=vehicle_status_meta($row['status']);$tm=vehicle_request_type_meta($row['request_type']);$canEdit=$currentVehicleIsAdmin||((int)$row['user_id']===$uid&&in_array($row['status'],array('pending_supervisor','pending_admin'),true)); ?>
        <tr><td><?= $seq++ ?></td><td><strong><?= vehicle_e($row['request_no']?$row['request_no']:'#'.$row['id']) ?></strong><br><?= vehicle_e($row['fullname']) ?><br><small><?= vehicle_e($row['department_name']) ?></small></td><td><span class="vehicle-badge vehicle-badge--<?= vehicle_e($sm[1]) ?>"><?= vehicle_e($sm[0]) ?></span><br><small>รับรอง: <?= vehicle_e($row['supervisor_name']?$row['supervisor_name']:'-') ?></small></td><td><span class="vehicle-type-pill <?= $row['request_type']==='refer'?'vehicle-type-pill--refer':'' ?>"><?= vehicle_e($tm[0]) ?></span><br><small><?= vehicle_e($row['urgency']) ?></small></td><td><strong><?= vehicle_e($row['private_registration']?$row['private_registration']:($row['hospital_registration']?$row['hospital_registration']:$row['fleet_registration'])) ?></strong><br><small><?= vehicle_e($row['driver_name']?$row['driver_name']:'ยังไม่ระบุพนักงานขับ') ?></small></td><td><?= vehicle_e($row['location']) ?><br><small><?= vehicle_format_thai_date($row['use_date']) ?> · <?= vehicle_e(substr((string)$row['use_time'],0,5)) ?> น.</small></td><td><?= $row['feedback_rating']?'<strong>'.(int)$row['feedback_rating'].'/5</strong>':'<span style="color:#94a5af">ยังไม่มี</span>' ?></td><td><div class="vehicle-actions"><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" href="detail.php?id=<?= (int)$row['id'] ?>">รายละเอียด</a><?php if($canEdit):?><a class="vehicle-btn vehicle-btn--warning vehicle-btn--small" href="edit.php?id=<?= (int)$row['id'] ?>">แก้ไข</a><?php endif; ?><a class="vehicle-btn vehicle-btn--ghost vehicle-btn--small" target="_blank" href="print.php?id=<?= (int)$row['id'] ?>">พิมพ์</a><?php if((int)$row['user_id']===$uid&&$row['status']==='completed'):?><a class="vehicle-btn vehicle-btn--success vehicle-btn--small" href="feedback.php?id=<?= (int)$row['id'] ?>">ความพึงพอใจ</a><?php endif; ?></div></td></tr>
      <?php endwhile;else:?><tr><td colspan="8"><div class="vehicle-empty"><div class="vehicle-empty__icon">🔎</div><strong>ไม่พบรายการ</strong><span>ลองเปลี่ยนคำค้นหาหรือตัวกรอง</span></div></td></tr><?php endif; ?>
      </tbody></table></div>
      <?php if($pages>1):$base=$_GET;unset($base['p']);?><div class="vehicle-pagination"><?php if($pageno>1):$base['p']=$pageno-1;?><a href="?<?= vehicle_e(http_build_query($base)) ?>">‹ ก่อนหน้า</a><?php else:?><span class="disabled">‹ ก่อนหน้า</span><?php endif; ?><?php $start=max(1,$pageno-2);$end=min($pages,$pageno+2);for($i=$start;$i<=$end;$i++):$base['p']=$i;?><a class="<?= $i===$pageno?'active':'' ?>" href="?<?= vehicle_e(http_build_query($base)) ?>"><?= $i ?></a><?php endfor; ?><?php if($pageno<$pages):$base['p']=$pageno+1;?><a href="?<?= vehicle_e(http_build_query($base)) ?>">ถัดไป ›</a><?php else:?><span class="disabled">ถัดไป ›</span><?php endif; ?></div><?php endif; ?>
    </section>
    <?php vehicle_page_end(); exit;
}

if ($page === 'calendar') {
    $month=isset($_GET['month'])?$_GET['month']:date('Y-m');if(!preg_match('/^\d{4}-\d{2}$/',$month))$month=date('Y-m');
    $start=$month.'-01';$end=date('Y-m-t',strtotime($start));
    $events=array();
    $rs=$conn->query("SELECT vr.id,vr.request_no,vr.request_type,vr.use_date,vr.use_time,vr.end_date,vr.end_time,vr.location,vr.hospital_registration,vr.private_registration,vf.registration fleet_registration,vr.fullname,vr.status
                      FROM vehicle_requests vr LEFT JOIN vehicle_fleet vf ON vf.id=vr.vehicle_id
                      WHERE $scopeSql AND vr.use_date BETWEEN '".$conn->real_escape_string($start)."' AND '".$conn->real_escape_string($end)."' AND vr.status NOT IN ('cancelled','rejected') ORDER BY vr.use_date,vr.use_time");
    if($rs){while($r=$rs->fetch_assoc()){$events[$r['use_date']][]=$r;}}
    $firstWeekday=(int)date('N',strtotime($start));$days=(int)date('t',strtotime($start));
    $prev=date('Y-m',strtotime($start.' -1 month'));$next=date('Y-m',strtotime($start.' +1 month'));
    $months=array(1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม');
    vehicle_page_start('ปฏิทินยานพาหนะ','ดูวันและเวลาที่มีการใช้งานรถ เพื่อช่วยวางแผนการเดินทางและหลีกเลี่ยงการจองซ้ำ','calendar');
    ?>
    <section class="vehicle-card"><div class="vehicle-card__body"><div class="vehicle-calendar-head"><a class="vehicle-btn vehicle-btn--ghost" href="index.php?page=calendar&month=<?= vehicle_e($prev) ?>">← เดือนก่อน</a><div class="vehicle-calendar-title"><?= $months[(int)date('n',strtotime($start))] ?> <?= (int)date('Y',strtotime($start))+543 ?></div><a class="vehicle-btn vehicle-btn--ghost" href="index.php?page=calendar&month=<?= vehicle_e($next) ?>">เดือนถัดไป →</a></div>
    <div class="vehicle-calendar"><?php foreach(array('จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์','อาทิตย์') as $wd):?><div class="vehicle-calendar__weekday"><?= $wd ?></div><?php endforeach;?>
    <?php for($blank=1;$blank<$firstWeekday;$blank++):?><div class="vehicle-calendar__day muted"></div><?php endfor; ?>
    <?php for($d=1;$d<=$days;$d++):$date=$month.'-'.str_pad($d,2,'0',STR_PAD_LEFT);$isToday=$date===date('Y-m-d');?><div class="vehicle-calendar__day"><div class="vehicle-calendar__date <?= $isToday?'today':'' ?>"><span><?= $d ?></span><span><?= isset($events[$date])?count($events[$date]).' งาน':'' ?></span></div><?php if(isset($events[$date])):foreach($events[$date] as $ev):$reg=$ev['private_registration']?$ev['private_registration']:($ev['hospital_registration']?$ev['hospital_registration']:$ev['fleet_registration']);?><a title="<?= vehicle_e($ev['location'].' · '.$reg) ?>" class="vehicle-calendar__event <?= $ev['request_type']==='refer'?'refer':'' ?>" href="detail.php?id=<?= (int)$ev['id'] ?>"><?= vehicle_e(substr((string)$ev['use_time'],0,5).' '.$reg.' · '.$ev['location']) ?></a><?php endforeach;endif;?></div><?php endfor;?>
    <?php $cells=($firstWeekday-1)+$days;while($cells%7!==0){$cells++;?><div class="vehicle-calendar__day muted"></div><?php } ?></div>
    <div class="vehicle-inline-note" style="margin-top:15px">Admin เห็นตารางใช้งานรถทุกหน่วยงาน ส่วน User เห็นเฉพาะคำขอของตนเอง คลิกรายการในปฏิทินเพื่อดูรายละเอียด</div>
    </div></section>
    <?php vehicle_page_end(); exit;
}
