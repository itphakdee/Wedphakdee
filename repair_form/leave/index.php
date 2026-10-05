<?php
require_once __DIR__ . '/layout.php';

if (!leave_table_exists('leave_applications')) {
  if (leave_is_admin()) leave_redirect('install.php');
  die('ระบบลางานยังไม่ได้ติดตั้งฐานข้อมูล กรุณาติดต่อผู้ดูแลระบบ');
}

$user = leave_current_user();
$uid = (int)$user['id'];
$fy = isset($_GET['fy']) ? (int)$_GET['fy'] : leave_fiscal_year();
if ($fy < 2500 || $fy > 2700) $fy = leave_fiscal_year();

$types = array();
$res = $conn->query("SELECT * FROM leave_types_master WHERE is_active=1 ORDER BY sort_order,id");
while ($res && $row = $res->fetch_assoc()) $types[] = $row;

$balances = array();
foreach ($types as $type) {
  $quota = leave_quota($uid, $fy, $type);
  $used = leave_used_days($uid, $fy, (int)$type['id']);
  $remaining = $quota === null ? null : max(0, $quota - $used);
  $balances[] = array('type' => $type, 'quota' => $quota, 'used' => $used, 'remaining' => $remaining);
}

$stats = array('total' => 0, 'approved' => 0, 'pending' => 0, 'days' => 0);
$stmt = $conn->prepare("SELECT COUNT(*) total,
    SUM(status='approved') approved,
    SUM(status IN ('pending_handover','pending_supervisor','cancel_requested')) pending,
    COALESCE(SUM(CASE WHEN status='approved' THEN leave_days ELSE 0 END),0) days
    FROM leave_applications WHERE user_id=? AND fiscal_year=?");
$stmt->bind_param('ii', $uid, $fy);
$stmt->execute();
$statsRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($statsRow) $stats = $statsRow;

$stmt = $conn->prepare("SELECT COUNT(*) c FROM leave_applications WHERE handover_user_id=? AND handover_status='pending' AND status='pending_handover'");
$stmt->bind_param('i', $uid);
$stmt->execute();
$handoverCount = (int)$stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

if (leave_is_admin()) {
  $approvalCount = (int)$conn->query("SELECT COUNT(*) c FROM leave_applications WHERE status IN ('pending_supervisor','cancel_requested')")->fetch_assoc()['c'];
} else {
  $stmt = $conn->prepare("SELECT COUNT(*) c FROM leave_applications WHERE supervisor_user_id=? AND status IN ('pending_supervisor','cancel_requested')");
  $stmt->bind_param('i', $uid);
  $stmt->execute();
  $approvalCount = (int)$stmt->get_result()->fetch_assoc()['c'];
  $stmt->close();
}

$summary = array();
$stmt = $conn->prepare("SELECT leave_type_name, COUNT(*) times_count, COALESCE(SUM(leave_days),0) days_count
                      FROM leave_applications WHERE user_id=? AND fiscal_year=? AND status='approved'
                      GROUP BY leave_type_name ORDER BY days_count DESC, leave_type_name");
$stmt->bind_param('ii', $uid, $fy);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $summary[] = $r;
$stmt->close();

// รายการข้อมูลการลา
$keyword = trim(isset($_GET['keyword']) ? $_GET['keyword'] : '');
$status = trim(isset($_GET['status']) ? $_GET['status'] : '');
$typeId = (int)(isset($_GET['type']) ? $_GET['type'] : 0);
$scope = (leave_is_admin() && isset($_GET['scope']) && $_GET['scope'] === 'mine') ? 'mine' : (leave_is_admin() ? 'all' : 'mine');
$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 10;
$where = array('fiscal_year=' . (int)$fy);
if ($scope === 'mine') $where[] = 'user_id=' . $uid;
if ($keyword !== '') {
  $k = $conn->real_escape_string($keyword);
  $where[] = "(leave_no LIKE '%{$k}%' OR employee_name LIKE '%{$k}%' OR reason LIKE '%{$k}%' OR department_name LIKE '%{$k}%')";
}
if ($status !== '') $where[] = "status='" . $conn->real_escape_string($status) . "'";
if ($typeId > 0) $where[] = 'leave_type_id=' . $typeId;
$whereSql = implode(' AND ', $where);
$totalRows = (int)$conn->query("SELECT COUNT(*) c FROM leave_applications WHERE {$whereSql}")->fetch_assoc()['c'];
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;
$rows = array();
$res = $conn->query("SELECT * FROM leave_applications WHERE {$whereSql} ORDER BY id DESC LIMIT {$offset},{$perPage}");
while ($res && $r = $res->fetch_assoc()) $rows[] = $r;

$queryBase = $_GET;
unset($queryBase['page']);
function leave_page_url($p, $base)
{
  $base['page'] = $p;
  return 'index.php?' . http_build_query($base);
}

leave_page_start('ระบบบริหารการลางาน', 'สรุปสิทธิ์วันลา เวิร์กโฟลว์รับมอบงาน การเห็นชอบของหัวหน้างาน และข้อมูลการลาประจำปีงบประมาณ', 'dashboard');
?>

<section class="leave-hero-grid">
  <div class="leave-stat leave-stat--blue">
    <div class="leave-stat__label">รายการลาปีงบ <?= (int)$fy ?></div>
    <div class="leave-stat__value"><?= number_format((int)$stats['total']) ?></div>
    <div class="leave-stat__note">รายการของคุณทั้งหมด</div>
  </div>
  <div class="leave-stat leave-stat--green">
    <div class="leave-stat__label">วันลาที่อนุมัติแล้ว</div>
    <div class="leave-stat__value"><?= leave_format_days($stats['days']) ?></div>
    <div class="leave-stat__note"><?= number_format((int)$stats['approved']) ?> ครั้ง</div>
  </div>
  <div class="leave-stat leave-stat--amber">
    <div class="leave-stat__label">รายการรอดำเนินการ</div>
    <div class="leave-stat__value"><?= number_format((int)$stats['pending']) ?></div>
    <div class="leave-stat__note">รอรับมอบงาน / รอเห็นชอบ</div>
  </div>
  <div class="leave-stat leave-stat--purple">
    <div class="leave-stat__label">งานที่รอคุณดำเนินการ</div>
    <div class="leave-stat__value"><?= number_format($handoverCount + $approvalCount) ?></div>
    <div class="leave-stat__note">รับมอบ <?= number_format($handoverCount) ?> · อนุมัติ <?= number_format($approvalCount) ?></div>
  </div>
</section>

<section class="leave-card">
  <div class="leave-card__head">
    <div>
      <h2>สิทธิ์วันลาคงเหลือ ปีงบประมาณ <?= (int)$fy ?></h2>
      <p>คำนวณจากโควตาที่กำหนด ลบเฉพาะรายการที่อนุมัติแล้ว</p>
    </div>
    <form method="get" class="no-print"><select class="leave-select" name="fy" onchange="this.form.submit()"><?php for ($y = leave_fiscal_year() + 1; $y >= leave_fiscal_year() - 4; $y--): ?><option value="<?= $y ?>" <?= $fy === $y ? 'selected' : '' ?>>ปีงบ <?= $y ?></option><?php endfor; ?></select></form>
  </div>
  <div class="leave-card__body">
    <div class="leave-balance-grid">
      <?php foreach ($balances as $b): $quota = $b['quota'];
        $used = $b['used'];
        $remain = $b['remaining'];
        $pct = ($quota !== null && $quota > 0) ? min(100, ($used / $quota) * 100) : 0; ?>
        <div class="leave-balance">
          <div class="leave-balance__top">
            <div><strong><?= leave_e($b['type']['name']) ?></strong><small>ใช้แล้ว <?= leave_format_days($used) ?> วัน</small></div>
            <div class="leave-balance__remain"><?= $remain === null ? '—' : leave_format_days($remain) ?></div>
          </div><small><?= $quota === null ? 'ตามสิทธิ์/คำสั่ง' : 'คงเหลือจาก ' . leave_format_days($quota) . ' วัน' ?></small><?php if ($quota !== null): ?><div class="leave-progress"><span style="width:<?= $pct ?>%"></span></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="leave-grid-equal">
  <section class="leave-card">
    <div class="leave-card__head">
      <div>
        <h3>สรุปครั้งและวันการลา</h3>
        <p>เฉพาะรายการอนุมัติ ปีงบประมาณ <?= (int)$fy ?></p>
      </div>
    </div>
    <div class="leave-card__body">
      <?php if (!$summary): ?><div class="leave-empty">ยังไม่มีรายการลาที่อนุมัติในปีงบประมาณนี้</div><?php else: ?><div class="leave-summary-list"><?php foreach ($summary as $s): ?><div class="leave-summary-row"><span><?= leave_e($s['leave_type_name']) ?></span><b><?= number_format($s['times_count']) ?> ครั้ง</b><span><?= leave_format_days($s['days_count']) ?> วัน</span></div><?php endforeach; ?></div><?php endif; ?>
    </div>
  </section>
  <section class="leave-card">
    <div class="leave-card__head">
      <div>
        <h3>งานที่ต้องดำเนินการ</h3>
        <p>รายการที่ส่งถึงบัญชีของคุณ</p>
      </div>
    </div>
    <div class="leave-card__body">
      <div class="leave-mini-list">
        <div class="leave-mini-item">
          <div><strong>รับมอบงานแทนเพื่อนร่วมงาน</strong><span>ยืนยันว่ารับผิดชอบงานในช่วงที่เพื่อนลา</span></div><a class="leave-btn leave-btn--secondary" href="handovers.php"><?= number_format($handoverCount) ?> รายการ</a>
        </div>
        <div class="leave-mini-item">
          <div><strong>หัวหน้างานเห็นชอบ</strong><span>พิจารณาใบลาและคำขอยกเลิก</span></div><a class="leave-btn leave-btn--secondary" href="approvals.php"><?= number_format($approvalCount) ?> รายการ</a>
        </div>
        <div class="leave-mini-item">
          <div><strong>ปฏิทินการลาของหน่วยงาน</strong><span>ดูผู้ลารายวันและกรองตามหน่วยงาน</span></div><a class="leave-btn leave-btn--outline" href="calendar.php">เปิดปฏิทิน</a>
        </div>
      </div>
    </div>
  </section>
</div>

<section class="leave-card" id="records">
  <div class="leave-card__head">
    <div>
      <h2>ข้อมูลการลา</h2>
      <p>รายการใบลาในปีงบประมาณ <?= (int)$fy ?> · <?= leave_is_admin() && $scope === 'all' ? 'ทุกบุคลากร' : 'เฉพาะของคุณ' ?></p>
    </div><a class="leave-btn leave-btn--primary no-print" href="add.php">＋ เพิ่มใบลา</a>
  </div>
  <div class="leave-card__body">
    <form class="leave-filter no-print" method="get">
      <input type="hidden" name="fy" value="<?= $fy ?>">
      <?php if (leave_is_admin()): ?><div class="leave-field"><label>ขอบเขต</label><select class="leave-select" name="scope">
            <option value="all" <?= $scope === 'all' ? 'selected' : '' ?>>ทุกบุคลากร</option>
            <option value="mine" <?= $scope === 'mine' ? 'selected' : '' ?>>เฉพาะของฉัน</option>
          </select></div><?php endif; ?>
      <div class="leave-field keyword"><label>ค้นหา</label><input class="leave-input" name="keyword" value="<?= leave_e($keyword) ?>" placeholder="เลขที่ใบลา / ชื่อ / เหตุผล / หน่วยงาน"></div>
      <div class="leave-field"><label>สถานะ</label><select class="leave-select" name="status">
          <option value="">ทุกสถานะ</option><?php foreach (array('pending_handover', 'pending_supervisor', 'approved', 'rejected', 'cancel_requested', 'cancelled') as $st): $si = leave_status_info($st); ?><option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= leave_e($si[0]) ?></option><?php endforeach; ?>
        </select></div>
      <div class="leave-field"><label>ประเภทการลา</label><select class="leave-select" name="type">
          <option value="0">ทุกประเภท</option><?php foreach ($types as $t): ?><option value="<?= $t['id'] ?>" <?= $typeId === (int)$t['id'] ? 'selected' : '' ?>><?= leave_e($t['name']) ?></option><?php endforeach; ?>
        </select></div>
      <button class="leave-btn leave-btn--secondary" type="submit">ค้นหา</button><a class="leave-btn leave-btn--outline" href="index.php?fy=<?= $fy ?>#records">ล้าง</a>
    </form>
  </div>
  <div class="leave-table-wrap">
    <table class="leave-table">
      <thead>
        <tr>
          <th>ลำดับ</th>
          <th>สถานะ</th>
          <th>ปีงบ</th>
          <th>สถานะเห็นชอบ</th>
          <th>ประเภทการลา</th>
          <th>เหตุผลการลา</th>
          <th>วันเริ่มลา</th>
          <th>ลาถึงวันที่</th>
          <th>จำนวนวันลา</th>
          <th>คำสั่ง</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?><tr>
            <td colspan="10">
              <div class="leave-empty">ไม่พบข้อมูลการลา</div>
            </td>
          </tr><?php endif; ?>
        <?php foreach ($rows as $i => $r): $si = leave_status_info($r['status']);
          $hi = leave_supervisor_info($r['supervisor_status']); ?>
          <tr>
            <td><?= ($offset + $i + 1) ?></td>
            <td><span class="leave-badge leave-badge--<?= $si[1] ?>"><?= leave_e($si[0]) ?></span><?php if (leave_is_admin() && $scope === 'all'): ?><br><small><?= leave_e($r['employee_name']) ?></small><?php endif; ?></td>
            <td><?= leave_e($r['fiscal_year']) ?></td>
            <td><span class="leave-badge leave-badge--<?= $hi[1] ?>"><?= leave_e($hi[0]) ?></span></td>
            <td><strong><?= leave_e($r['leave_type_name']) ?></strong></td>
            <td class="reason"><?= leave_e($r['reason']) ?></td>
            <td><?= leave_date_thai($r['start_date']) ?></td>
            <td><?= leave_date_thai($r['end_date']) ?></td>
            <td><?= leave_format_days($r['leave_days']) ?> วัน</td>
            <td>
              <div class="leave-actions"><a class="leave-btn leave-btn--sm leave-btn--outline" href="detail.php?id=<?= $r['id'] ?>">รายละเอียด</a><?php if ($r['medical_certificate']): ?><a class="leave-btn leave-btn--sm leave-btn--secondary" href="download.php?id=<?= $r['id'] ?>&type=medical">ใบรับรองแพทย์</a><?php endif; ?><?php if (leave_can_edit_application($r)): ?><a class="leave-btn leave-btn--sm leave-btn--secondary" href="edit.php?id=<?= $r['id'] ?>">แก้ไขข้อมูล</a><?php endif; ?><?php if ((int)$r['user_id'] === $uid && !in_array($r['status'], array('cancelled', 'rejected', 'cancel_requested'), true)): ?><a class="leave-btn leave-btn--sm leave-btn--danger" href="detail.php?id=<?= $r['id'] ?>#cancel">แจ้งยกเลิก</a><?php endif; ?></div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($totalPages > 1): ?><div class="leave-pagination no-print"><?php if ($page > 1): ?><a href="<?= leave_e(leave_page_url($page - 1, $queryBase)) ?>#records">‹ ก่อนหน้า</a><?php endif; ?><?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?><a class="<?= $p === $page ? 'active' : '' ?>" href="<?= leave_e(leave_page_url($p, $queryBase)) ?>#records"><?= $p ?></a><?php endfor; ?><?php if ($page < $totalPages): ?><a href="<?= leave_e(leave_page_url($page + 1, $queryBase)) ?>#records">ถัดไป ›</a><?php endif; ?></div><?php endif; ?>
</section>

<?php leave_page_end(); ?>