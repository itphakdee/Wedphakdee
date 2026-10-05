<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

require_permission('meeting.view');

$canCreate = has_permission('meeting.create');
$canManage = has_permission('meeting.manage');
$isAdmin = is_admin_user();
$schemaReady = meeting_schema_ready($conn);

$today = date('Y-m-d');
$nowTime = date('H:i:s');
$thisMonth = date('Y-m');

$totalRooms = 0;
$availableRooms = 0;
$busyRooms = 0;
$reservedRooms = 0;
$todayBookingsCount = 0;
$monthBookingsCount = 0;
$pendingCount = 0;
$roomRows = array();
$todayRows = array();
$upcomingRows = array();

if ($schemaReady) {
    $totalRooms = meeting_scalar($conn, "SELECT COUNT(*) FROM meeting_rooms WHERE status='active'");
    $todayBookingsCount = meeting_scalar($conn, "SELECT COUNT(*) FROM meeting_bookings WHERE meeting_date=CURDATE() AND status IN ('pending','approved')");
    $monthBookingsCount = meeting_scalar($conn, "SELECT COUNT(*) FROM meeting_bookings WHERE DATE_FORMAT(meeting_date,'%Y-%m')='" . $conn->real_escape_string($thisMonth) . "' AND status IN ('pending','approved')");
    $pendingCount = meeting_scalar($conn, "SELECT COUNT(*) FROM meeting_bookings WHERE status='pending'");

    $roomResult = $conn->query("
        SELECT
            r.id, r.room_name, r.room_code, r.capacity, r.location, r.equipment, r.color,
            (
                SELECT b.id
                FROM meeting_bookings b
                WHERE b.room_id=r.id
                  AND b.meeting_date=CURDATE()
                  AND b.status='approved'
                  AND b.start_time <= CURTIME()
                  AND b.end_time > CURTIME()
                ORDER BY b.start_time
                LIMIT 1
            ) AS current_booking_id,
            (
                SELECT b.meeting_title
                FROM meeting_bookings b
                WHERE b.room_id=r.id
                  AND b.meeting_date=CURDATE()
                  AND b.status='approved'
                  AND b.start_time <= CURTIME()
                  AND b.end_time > CURTIME()
                ORDER BY b.start_time
                LIMIT 1
            ) AS current_title,
            (
                SELECT CONCAT(TIME_FORMAT(b.start_time,'%H:%i'),' - ',TIME_FORMAT(b.end_time,'%H:%i'))
                FROM meeting_bookings b
                WHERE b.room_id=r.id
                  AND b.meeting_date=CURDATE()
                  AND b.status='approved'
                  AND b.start_time <= CURTIME()
                  AND b.end_time > CURTIME()
                ORDER BY b.start_time
                LIMIT 1
            ) AS current_time_text,
            (
                SELECT b.id
                FROM meeting_bookings b
                WHERE b.room_id=r.id
                  AND b.meeting_date=CURDATE()
                  AND b.status IN ('pending','approved')
                  AND b.start_time > CURTIME()
                ORDER BY b.start_time
                LIMIT 1
            ) AS next_booking_id,
            (
                SELECT CONCAT(TIME_FORMAT(b.start_time,'%H:%i'),' น. · ',b.meeting_title)
                FROM meeting_bookings b
                WHERE b.room_id=r.id
                  AND b.meeting_date=CURDATE()
                  AND b.status IN ('pending','approved')
                  AND b.start_time > CURTIME()
                ORDER BY b.start_time
                LIMIT 1
            ) AS next_text
        FROM meeting_rooms r
        WHERE r.status='active'
        ORDER BY r.id
    ");

    if ($roomResult) {
        while ($row = $roomResult->fetch_assoc()) {
            if (!empty($row['current_booking_id'])) {
                $row['live_status'] = 'busy';
                $busyRooms++;
            } elseif (!empty($row['next_booking_id'])) {
                $row['live_status'] = 'reserved';
                $reservedRooms++;
            } else {
                $row['live_status'] = 'available';
                $availableRooms++;
            }
            $roomRows[] = $row;
        }
    }

    $todayResult = $conn->query("
        SELECT b.id, b.room_id, b.meeting_title, b.requester_name, b.department,
               b.start_time, b.end_time, b.status, b.meeting_platform, b.meeting_url,
               r.room_name, r.room_code
        FROM meeting_bookings b
        INNER JOIN meeting_rooms r ON r.id=b.room_id
        WHERE b.meeting_date=CURDATE()
          AND b.status IN ('pending','approved')
        ORDER BY b.start_time ASC
        LIMIT 20
    ");
    if ($todayResult) {
        while ($row = $todayResult->fetch_assoc()) {
            $todayRows[] = $row;
        }
    }

    $upcomingResult = $conn->query("
        SELECT b.id, b.meeting_title, b.meeting_date, b.start_time, b.end_time,
               b.status, b.meeting_platform, b.meeting_url, r.room_name
        FROM meeting_bookings b
        INNER JOIN meeting_rooms r ON r.id=b.room_id
        WHERE b.status IN ('pending','approved')
          AND TIMESTAMP(b.meeting_date,b.end_time) >= NOW()
        ORDER BY b.meeting_date ASC, b.start_time ASC
        LIMIT 8
    ");
    if ($upcomingResult) {
        while ($row = $upcomingResult->fetch_assoc()) {
            $upcomingRows[] = $row;
        }
    }
}

$userName = isset($_SESSION['fullname']) && $_SESSION['fullname'] !== ''
    ? $_SESSION['fullname']
    : (isset($_SESSION['username']) ? $_SESSION['username'] : 'ผู้ใช้งาน');
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ระบบจองห้องประชุม | โรงพยาบาลภักดีชุมพล</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/meeting.css?v=20260831">
</head>
<body class="meeting-body">
<header class="mh-topbar">
    <div class="mh-brand">
        <div class="mh-brand-mark">PDC</div>
        <div>
            <div class="mh-brand-title">ระบบจองห้องประชุม</div>
            <div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล · PHAKDEE CHUMPHON HOSPITAL</div>
        </div>
    </div>
    <div class="mh-top-actions">
        <div class="mh-user">
            <span class="mh-user-dot"></span>
            <span><?= meeting_e($userName) ?></span>
        </div>
        <a class="mh-btn mh-btn-light" href="../../dashboard.php">← หน้าหลัก</a>
    </div>
</header>

<nav class="mh-nav">
    <div class="mh-nav-inner">
        <a class="active" href="index.php">Dashboard</a>
        <?php if ($canCreate): ?><a href="booking.php">จองห้องประชุม</a><?php endif; ?>
        <a href="bookings.php">รายการจอง</a>
        <?php if ($canManage): ?><a href="rooms.php">จัดการห้องประชุม</a><?php endif; ?>
    </div>
</nav>

<main class="mh-wrap">
    <?php if (!$schemaReady): ?>
        <div class="mh-alert mh-alert-warning">
            <div>
                <strong>ระบบฐานข้อมูลห้องประชุมยังไม่ได้อัปเดต</strong>
                <span>กรุณาติดตั้ง/อัปเดตฐานข้อมูลก่อนใช้งานฟังก์ชันใหม่</span>
            </div>
            <?php if ($isAdmin): ?><a class="mh-btn mh-btn-primary" href="install.php">ติดตั้งฐานข้อมูล</a><?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="mh-hero">
        <div class="mh-hero-copy">
            <span class="mh-kicker">MEETING ROOM MANAGEMENT</span>
            <h1>Dashboard ห้องประชุม</h1>
            <p>ตรวจสอบสถานะห้องแบบปัจจุบัน จองห้อง ติดตามรายการ และเข้าสู่ห้องประชุมออนไลน์จากหน้าจอเดียว</p>
            <div class="mh-hero-actions">
                <?php if ($canCreate): ?><a class="mh-btn mh-btn-primary" href="booking.php">＋ จองห้องประชุม</a><?php endif; ?>
                <a class="mh-btn mh-btn-outline" href="bookings.php">ดูตารางการจอง</a>
            </div>
        </div>
        <div class="mh-date-card">
            <span>วันที่ระบบ</span>
            <strong><?= date('d/m/') . (date('Y') + 543) ?></strong>
            <small><?= date('H:i') ?> น.</small>
        </div>
    </section>

    <section class="mh-stats">
        <article class="mh-stat">
            <span class="mh-stat-icon">🏢</span>
            <div><small>ห้องประชุมทั้งหมด</small><strong><?= number_format($totalRooms) ?></strong><span>ห้อง</span></div>
        </article>
        <article class="mh-stat mh-stat-green">
            <span class="mh-stat-icon">✓</span>
            <div><small>ว่างในขณะนี้</small><strong><?= number_format($availableRooms) ?></strong><span>ห้อง</span></div>
        </article>
        <article class="mh-stat mh-stat-red">
            <span class="mh-stat-icon">●</span>
            <div><small>กำลังมีการประชุม</small><strong><?= number_format($busyRooms) ?></strong><span>ห้อง</span></div>
        </article>
        <article class="mh-stat mh-stat-amber">
            <span class="mh-stat-icon">◷</span>
            <div><small>ติดจองถัดไปวันนี้</small><strong><?= number_format($reservedRooms) ?></strong><span>ห้อง</span></div>
        </article>
        <article class="mh-stat mh-stat-blue">
            <span class="mh-stat-icon">📅</span>
            <div><small>รายการประชุมวันนี้</small><strong><?= number_format($todayBookingsCount) ?></strong><span>รายการ</span></div>
        </article>
        <article class="mh-stat mh-stat-purple">
            <span class="mh-stat-icon">⌛</span>
            <div><small>รออนุมัติ</small><strong><?= number_format($pendingCount) ?></strong><span>รายการ</span></div>
        </article>
    </section>

    <section class="mh-section">
        <div class="mh-section-head">
            <div>
                <span class="mh-kicker">LIVE ROOM STATUS</span>
                <h2>สถานะห้องประชุม</h2>
                <p>สถานะคำนวณจากรายการจองและเวลาปัจจุบันโดยอัตโนมัติ</p>
            </div>
            <?php if ($canManage): ?><a class="mh-link" href="rooms.php">จัดการห้อง →</a><?php endif; ?>
        </div>

        <div class="mh-room-grid">
            <?php if ($roomRows): ?>
                <?php foreach ($roomRows as $room): ?>
                    <?php
                    $live = $room['live_status'];
                    $statusText = $live === 'busy' ? 'มีการประชุม' : ($live === 'reserved' ? 'ติดจองแล้ว' : 'ว่าง');
                    ?>
                    <article class="mh-room-card mh-room-<?= meeting_e($live) ?>" style="--room-accent:<?= meeting_e($room['color'] ? $room['color'] : '#0f766e') ?>">
                        <div class="mh-room-card-top">
                            <div class="mh-room-icon">▦</div>
                            <span class="mh-live-badge"><?= meeting_e($statusText) ?></span>
                        </div>
                        <div class="mh-room-code"><?= meeting_e($room['room_code']) ?></div>
                        <h3><?= meeting_e($room['room_name']) ?></h3>
                        <div class="mh-room-meta">
                            <span>👥 <?= (int)$room['capacity'] ?> คน</span>
                            <span>📍 <?= meeting_e($room['location'] ? $room['location'] : 'อาคารโรงพยาบาล') ?></span>
                        </div>

                        <div class="mh-room-info-box">
                            <?php if ($live === 'busy'): ?>
                                <small>กำลังประชุม</small>
                                <strong><?= meeting_e($room['current_title']) ?></strong>
                                <span><?= meeting_e($room['current_time_text']) ?> น.</span>
                            <?php elseif ($live === 'reserved'): ?>
                                <small>รายการถัดไป</small>
                                <strong><?= meeting_e($room['next_text']) ?></strong>
                                <span>ห้องยังว่างก่อนถึงเวลาจอง</span>
                            <?php else: ?>
                                <small>สถานะปัจจุบัน</small>
                                <strong>พร้อมใช้งาน</strong>
                                <span>ยังไม่มีรายการจองถัดไปในวันนี้</span>
                            <?php endif; ?>
                        </div>

                        <div class="mh-room-actions">
                            <?php if ($canCreate): ?><a href="booking.php?room_id=<?= (int)$room['id'] ?>">จองห้องนี้</a><?php endif; ?>
                            <?php if (!empty($room['current_booking_id'])): ?><a class="secondary" href="booking_detail.php?id=<?= (int)$room['current_booking_id'] ?>">รายละเอียด</a><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="mh-empty">ยังไม่มีข้อมูลห้องประชุม กรุณาติดตั้งฐานข้อมูลหรือเพิ่มห้องประชุม</div>
            <?php endif; ?>
        </div>
    </section>

    <div class="mh-dashboard-grid">
        <section class="mh-panel">
            <div class="mh-panel-head">
                <div>
                    <span class="mh-kicker">TODAY SCHEDULE</span>
                    <h2>ตารางประชุมวันนี้</h2>
                </div>
                <span class="mh-count-pill"><?= count($todayRows) ?> รายการ</span>
            </div>
            <?php if ($todayRows): ?>
                <div class="mh-timeline">
                    <?php foreach ($todayRows as $b): ?>
                        <article class="mh-timeline-item">
                            <div class="mh-time">
                                <strong><?= substr($b['start_time'], 0, 5) ?></strong>
                                <span><?= substr($b['end_time'], 0, 5) ?></span>
                            </div>
                            <div class="mh-timeline-body">
                                <div class="mh-row-between">
                                    <strong><?= meeting_e($b['meeting_title']) ?></strong>
                                    <span class="mh-status <?= meeting_status_class($b['status']) ?>"><?= meeting_e(meeting_status_label($b['status'])) ?></span>
                                </div>
                                <p><?= meeting_e($b['room_name']) ?> · <?= meeting_e($b['requester_name']) ?></p>
                                <div class="mh-inline-actions">
                                    <a href="booking_detail.php?id=<?= (int)$b['id'] ?>">รายละเอียด</a>
                                    <?php if (!empty($b['meeting_url']) && $b['status'] === 'approved'): ?>
                                        <a class="join" href="meeting_link.php?id=<?= (int)$b['id'] ?>" target="_blank" rel="noopener">เข้าห้อง <?= meeting_e(meeting_platform_label($b['meeting_platform'])) ?> ↗</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="mh-empty mh-empty-compact">วันนี้ยังไม่มีรายการประชุม</div>
            <?php endif; ?>
        </section>

        <aside class="mh-panel">
            <div class="mh-panel-head">
                <div>
                    <span class="mh-kicker">ONLINE MEETING</span>
                    <h2>บริการประชุมออนไลน์</h2>
                </div>
            </div>
            <p class="mh-muted">เปิดเว็บไซต์ผู้ให้บริการ หรือใช้ลิงก์ที่บันทึกไว้ในรายการจอง</p>
            <div class="mh-platform-grid">
                <?php foreach (meeting_platforms() as $key => $platform): ?>
                    <?php if ($key === 'other' || $platform['home'] === '') continue; ?>
                    <a href="<?= meeting_e($platform['home']) ?>" target="_blank" rel="noopener noreferrer">
                        <span><?= $platform['icon'] ?></span>
                        <strong><?= meeting_e($platform['label']) ?></strong>
                        <small>เปิดเว็บไซต์ ↗</small>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>

    <section class="mh-panel mh-upcoming">
        <div class="mh-panel-head">
            <div>
                <span class="mh-kicker">UPCOMING</span>
                <h2>การประชุมที่กำลังจะมาถึง</h2>
            </div>
            <a class="mh-link" href="bookings.php">ดูทั้งหมด →</a>
        </div>
        <div class="mh-table-wrap">
            <table class="mh-table">
                <thead>
                    <tr>
                        <th>วัน / เวลา</th>
                        <th>ห้องประชุม</th>
                        <th>หัวข้อ</th>
                        <th>สถานะ</th>
                        <th>ประชุมออนไลน์</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($upcomingRows): ?>
                    <?php foreach ($upcomingRows as $b): ?>
                        <tr>
                            <td><strong><?= meeting_format_thai_date($b['meeting_date']) ?></strong><small><?= substr($b['start_time'],0,5) ?> - <?= substr($b['end_time'],0,5) ?> น.</small></td>
                            <td><?= meeting_e($b['room_name']) ?></td>
                            <td><a class="mh-title-link" href="booking_detail.php?id=<?= (int)$b['id'] ?>"><?= meeting_e($b['meeting_title']) ?></a></td>
                            <td><span class="mh-status <?= meeting_status_class($b['status']) ?>"><?= meeting_e(meeting_status_label($b['status'])) ?></span></td>
                            <td>
                                <?php if (!empty($b['meeting_url']) && $b['status']==='approved'): ?>
                                    <a class="mh-mini-btn" href="meeting_link.php?id=<?= (int)$b['id'] ?>" target="_blank" rel="noopener">เปิด <?= meeting_e(meeting_platform_label($b['meeting_platform'])) ?> ↗</a>
                                <?php else: ?>
                                    <span class="mh-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5"><div class="mh-empty mh-empty-compact">ยังไม่มีรายการประชุมที่กำลังจะมาถึง</div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="mh-footer">
        <span>ระบบห้องประชุม · โรงพยาบาลภักดีชุมพล</span>
        <span>เดือนนี้ <?= number_format($monthBookingsCount) ?> รายการ</span>
    </footer>
</main>
<script src="assets/meeting.js?v=20260831"></script>
</body>
</html>
