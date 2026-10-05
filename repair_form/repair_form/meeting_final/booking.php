<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
require_permission('meeting.create');

if (!meeting_schema_ready($conn)) {
    header('Location: install.php?from=booking');
    exit;
}

$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';

$rooms = $conn->query("SELECT id, room_name, room_code, capacity, location, equipment FROM meeting_rooms WHERE status='active' ORDER BY id");
$platforms = meeting_platforms();
$userName = !empty($_SESSION['fullname']) ? $_SESSION['fullname'] : (!empty($_SESSION['username']) ? $_SESSION['username'] : '');
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>จองห้องประชุม | โรงพยาบาลภักดีชุมพล</title>
<link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/meeting.css?v=20260831">
</head>
<body class="meeting-body">
<header class="mh-topbar">
    <div class="mh-brand">
        <div class="mh-brand-mark">PDC</div>
        <div><div class="mh-brand-title">ระบบจองห้องประชุม</div><div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล</div></div>
    </div>
    <a class="mh-btn mh-btn-light" href="index.php">← Dashboard</a>
</header>
<nav class="mh-nav"><div class="mh-nav-inner"><a href="index.php">Dashboard</a><a class="active" href="booking.php">จองห้องประชุม</a><a href="bookings.php">รายการจอง</a></div></nav>

<main class="mh-wrap">
    <section class="mh-page-title">
        <div>
            <span class="mh-kicker">MEETING RESERVATION</span>
            <h1>จองห้องประชุม</h1>
            <p>กรอกข้อมูลให้ครบถ้วน ระบบจะตรวจสอบเวลาซ้ำและความจุของห้องก่อนบันทึก</p>
        </div>
        <a class="mh-btn mh-btn-outline" href="index.php">← กลับ</a>
    </section>

    <?php if ($message !== ''): ?>
        <div class="mh-alert <?= $status === 'success' ? 'mh-alert-success' : 'mh-alert-danger' ?>">
            <strong><?= meeting_e($message) ?></strong>
        </div>
    <?php endif; ?>

    <section class="mh-form-card">
        <form method="post" action="booking_save.php" id="bookingForm">
            <input type="hidden" name="csrf_token" value="<?= meeting_e(meeting_csrf_token()) ?>">

            <div class="mh-form-section">
                <div class="mh-form-section-title"><span>01</span><div><h2>ข้อมูลการประชุม</h2><p>ห้อง หัวข้อ ผู้ขอ และจำนวนผู้เข้าร่วม</p></div></div>
                <div class="mh-form-grid">
                    <div class="mh-field mh-col-6">
                        <label for="room_id">ห้องประชุม <b>*</b></label>
                        <select name="room_id" id="room_id" required>
                            <option value="">— เลือกห้องประชุม —</option>
                            <?php if ($rooms): while ($r=$rooms->fetch_assoc()): ?>
                                <option value="<?= (int)$r['id'] ?>"
                                    data-capacity="<?= (int)$r['capacity'] ?>"
                                    data-location="<?= meeting_e($r['location']) ?>"
                                    <?= $roomId === (int)$r['id'] ? 'selected' : '' ?>>
                                    <?= meeting_e($r['room_name']) ?> · <?= (int)$r['capacity'] ?> คน
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                        <small id="roomInfo">ระบบจะแสดงความจุเมื่อเลือกห้อง</small>
                    </div>

                    <div class="mh-field mh-col-6">
                        <label for="meeting_title">หัวข้อการประชุม <b>*</b></label>
                        <input id="meeting_title" name="meeting_title" maxlength="255" required placeholder="เช่น ประชุมคณะกรรมการบริหารโรงพยาบาล">
                    </div>

                    <div class="mh-field mh-col-4">
                        <label for="requester_name">ชื่อผู้ขอใช้บริการ <b>*</b></label>
                        <input id="requester_name" name="requester_name" value="<?= meeting_e($userName) ?>" required>
                    </div>
                    <div class="mh-field mh-col-4">
                        <label for="department">หน่วยงาน / แผนก</label>
                        <input id="department" name="department" placeholder="ระบุหน่วยงาน">
                    </div>
                    <div class="mh-field mh-col-4">
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input id="phone" name="phone" maxlength="50" placeholder="เบอร์ติดต่อภายใน/มือถือ">
                    </div>

                    <div class="mh-field mh-col-4">
                        <label for="attendees">จำนวนผู้เข้าร่วม <b>*</b></label>
                        <input id="attendees" type="number" name="attendees" min="1" value="1" required>
                        <small id="capacityMessage"></small>
                    </div>
                    <div class="mh-field mh-col-4">
                        <label for="meeting_date">วันที่ประชุม <b>*</b></label>
                        <input id="meeting_date" type="date" name="meeting_date" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mh-field mh-col-2">
                        <label for="start_time">เวลาเริ่ม <b>*</b></label>
                        <input id="start_time" type="time" name="start_time" required>
                    </div>
                    <div class="mh-field mh-col-2">
                        <label for="end_time">สิ้นสุด <b>*</b></label>
                        <input id="end_time" type="time" name="end_time" required>
                    </div>

                    <div class="mh-field mh-col-12">
                        <label for="detail">รายละเอียด / วาระการประชุม</label>
                        <textarea id="detail" name="detail" rows="4" placeholder="รายละเอียดเพิ่มเติม วัตถุประสงค์ หรืออุปกรณ์ที่ต้องการ"></textarea>
                    </div>
                </div>
            </div>

            <div class="mh-form-section">
                <div class="mh-form-section-title"><span>02</span><div><h2>ห้องประชุมออนไลน์</h2><p>เลือกผู้ให้บริการและฝากลิงก์ เพื่อให้ผู้ใช้กดเข้าประชุมจากระบบได้ทันที</p></div></div>
                <div class="mh-form-grid">
                    <div class="mh-field mh-col-4">
                        <label for="meeting_platform">แพลตฟอร์มประชุม</label>
                        <select name="meeting_platform" id="meeting_platform">
                            <option value="">— ไม่ใช้ห้องประชุมออนไลน์ —</option>
                            <?php foreach ($platforms as $key=>$p): ?>
                                <option value="<?= meeting_e($key) ?>"><?= $p['icon'] ?> <?= meeting_e($p['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mh-field mh-col-8">
                        <label for="meeting_url">ลิงก์เข้าร่วมประชุม</label>
                        <input type="url" name="meeting_url" id="meeting_url" maxlength="1000" placeholder="https://...">
                        <small>รองรับ Zoom, Google Meet, Microsoft Teams, Cisco Webex, LINE Meeting และลิงก์ HTTPS อื่น</small>
                    </div>
                </div>

                <div class="mh-platform-strip">
                    <?php foreach ($platforms as $key=>$p): if ($key==='other' || !$p['home']) continue; ?>
                        <a href="<?= meeting_e($p['home']) ?>" target="_blank" rel="noopener noreferrer"><span><?= $p['icon'] ?></span><?= meeting_e($p['label']) ?> ↗</a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mh-form-actions">
                <a class="mh-btn mh-btn-outline" href="index.php">ยกเลิก</a>
                <button class="mh-btn mh-btn-primary" type="submit">บันทึกคำขอจองห้อง</button>
            </div>
        </form>
    </section>
</main>
<script src="assets/meeting.js?v=20260831"></script>
</body>
</html>
