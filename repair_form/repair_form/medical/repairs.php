<?php
require_once __DIR__ . '/layout.php';
medical_require_permission('view');

$ready = medical_schema_ready();
$u = medical_current_user();
$canAll = medical_can_view_all();
$scopeSql = $canAll ? '1=1' : 'user_id=' . (int)$u['id'];

$q = trim(isset($_GET['q']) ? $_GET['q'] : '');
$status = trim(isset($_GET['status']) ? $_GET['status'] : '');
$priority = trim(isset($_GET['priority']) ? $_GET['priority'] : '');
$page = max(1, (int)(isset($_GET['p']) ? $_GET['p'] : 1));
$perPage = 10;
$totalRows = 0;
$rows = array();
$pages = 1;

if ($ready) {
    $where = array($scopeSql);
    $params = array();
    $types = '';

    if ($q !== '') {
        $where[] = "(request_no LIKE CONCAT('%',?,'%') OR requester_name LIKE CONCAT('%',?,'%') OR department LIKE CONCAT('%',?,'%') OR equipment_name LIKE CONCAT('%',?,'%') OR asset_code LIKE CONCAT('%',?,'%') OR problem_detail LIKE CONCAT('%',?,'%'))";
        for ($i = 0; $i < 6; $i++) {
            $params[] = $q;
            $types .= 's';
        }
    }

    if ($status !== '') {
        $where[] = 'status=?';
        $params[] = $status;
        $types .= 's';
    }

    if ($priority !== '') {
        $where[] = 'priority=?';
        $params[] = $priority;
        $types .= 's';
    }

    $whereSql = implode(' AND ', $where);

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM medical_repair_requests WHERE $whereSql");
    if ($stmt) {
        if ($types !== '') medical_bind_params($stmt, $types, $params);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && ($x = $res->fetch_assoc())) $totalRows = (int)$x['c'];
        $stmt->close();
    }

    $pages = max(1, (int)ceil($totalRows / $perPage));
    if ($page > $pages) $page = $pages;
    $offset = ($page - 1) * $perPage;

    $stmt = $conn->prepare("SELECT * FROM medical_repair_requests WHERE $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset");
    if ($stmt) {
        if ($types !== '') medical_bind_params($stmt, $types, $params);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
    }
}

medical_page_start(
    'ทะเบียนงานแจ้งซ่อม',
    $canAll ? 'ค้นหา ติดตาม และบริหารรายการแจ้งซ่อมเครื่องมือแพทย์ทั้งหมด' : 'ค้นหาและติดตามรายการแจ้งซ่อมเครื่องมือแพทย์ของคุณ',
    'repairs'
);
?>
<?php if (!$ready): ?>
<div class="med-alert med-alert--warning">
    <strong>ระบบยังไม่ได้ติดตั้งฐานข้อมูล</strong>
    <?php if (medical_is_admin()): ?><a href="install.php">คลิกเพื่อติดตั้งระบบ</a><?php else: ?>กรุณาติดต่อผู้ดูแลระบบ<?php endif; ?>
</div>
<?php endif; ?>

<section class="med-card">
    <div class="med-card-head">
        <div class="med-section-no">01</div>
        <div>
            <h2>ทะเบียนงานแจ้งซ่อม</h2>
            <p><?= $canAll ? 'แสดงรายการแจ้งซ่อมทั้งหมดที่คุณมีสิทธิ์ดู' : 'แสดงเฉพาะรายการที่คุณเป็นผู้แจ้ง' ?></p>
        </div>
        <span class="med-card-tag"><?= (int)$totalRows ?> รายการ</span>
    </div>

    <form class="med-filters" method="get">
        <input name="q" value="<?= medical_e($q) ?>" placeholder="ค้นหาเลขที่งาน ผู้แจ้ง เครื่องมือ เลขครุภัณฑ์ หรืออาการ">
        <select name="status">
            <option value="">ทุกสถานะ</option>
            <?php foreach(array('pending'=>'รอตรวจสอบ','assigned'=>'มอบหมายแล้ว','in_progress'=>'กำลังซ่อม','waiting_parts'=>'รออะไหล่','completed'=>'เสร็จสิ้น','cancelled'=>'ยกเลิก') as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $status===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <select name="priority">
            <option value="">ทุกความเร่งด่วน</option>
            <option value="normal" <?= $priority==='normal'?'selected':'' ?>>ปกติ</option>
            <option value="urgent" <?= $priority==='urgent'?'selected':'' ?>>ด่วน</option>
            <option value="emergency" <?= $priority==='emergency'?'selected':'' ?>>ด่วนมาก</option>
        </select>
        <button class="med-btn med-btn-primary">ค้นหา</button>
        <a class="med-btn med-btn-light" href="repairs.php">ล้าง</a>
    </form>

    <div class="med-table-wrap">
        <table class="med-table">
            <thead>
                <tr>
                    <th>เลขที่งาน</th>
                    <th>เครื่องมือ</th>
                    <th>ผู้แจ้ง / หน่วยงาน</th>
                    <th>ความเร่งด่วน</th>
                    <th>สถานะ</th>
                    <th>ผู้รับผิดชอบ</th>
                    <th>วันที่แจ้ง</th>
                    <th>คำสั่ง</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="8" class="med-empty">ยังไม่มีรายการที่ตรงกับเงื่อนไข</td></tr>
            <?php endif; ?>
            <?php foreach($rows as $r): $sm=medical_status_meta($r['status']); $pm=medical_priority_meta($r['priority']); ?>
                <tr>
                    <td><strong><?= medical_e($r['request_no'] ?: '#'.$r['id']) ?></strong></td>
                    <td><strong><?= medical_e($r['equipment_name']) ?></strong><small><?= medical_e($r['asset_code'] ?: 'ไม่ระบุเลขครุภัณฑ์') ?></small></td>
                    <td><?= medical_e($r['requester_name']) ?><small><?= medical_e($r['department']) ?></small></td>
                    <td><span class="med-badge med-priority-<?= $pm['class'] ?>"><?= $pm['label'] ?></span></td>
                    <td><span class="med-badge med-status-<?= $sm['class'] ?>"><?= $sm['label'] ?></span></td>
                    <td><?= medical_e($r['technician_name'] ?: 'ยังไม่มอบหมาย') ?></td>
                    <td><?= medical_thai_datetime($r['created_at']) ?></td>
                    <td>
                        <div class="med-actions">
                            <a href="detail.php?id=<?= (int)$r['id'] ?>">รายละเอียด</a>
                            <?php if ((int)$r['user_id']===(int)$u['id'] && !in_array($r['status'],array('completed','cancelled'),true)): ?>
                                <a href="edit.php?id=<?= (int)$r['id'] ?>">แก้ไข</a>
                            <?php endif; ?>
                            <a href="print.php?id=<?= (int)$r['id'] ?>" target="_blank">พิมพ์</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="med-pagination">
        <?php if($page > 1): ?><a href="?<?= http_build_query(array('q'=>$q,'status'=>$status,'priority'=>$priority,'p'=>$page-1)) ?>">‹ ก่อนหน้า</a><?php endif; ?>
        <?php for($i=1;$i<=$pages;$i++): ?>
            <?php if($i==1 || $i==$pages || abs($i-$page)<=2): ?>
                <a class="<?= $i===$page?'active':'' ?>" href="?<?= http_build_query(array('q'=>$q,'status'=>$status,'priority'=>$priority,'p'=>$i)) ?>"><?= $i ?></a>
            <?php elseif(abs($i-$page)===3): ?>
                <span>…</span>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if($page < $pages): ?><a href="?<?= http_build_query(array('q'=>$q,'status'=>$status,'priority'=>$priority,'p'=>$page+1)) ?>">ถัดไป ›</a><?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<?php medical_page_end(); ?>
