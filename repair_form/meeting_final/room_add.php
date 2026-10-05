<?php
require_once __DIR__ . '/config_meeting.php';
require_once __DIR__ . '/../admin/permissions_helper.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
require_permission('meeting.manage');
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>เพิ่มห้องประชุม</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/meeting.css?v=20260831">
</head>

<body class="meeting-body">
    <header class="mh-topbar">
        <div class="mh-brand">
            <div class="mh-brand-mark">PDC</div>
            <div>
                <div class="mh-brand-title">เพิ่มห้องประชุม</div>
                <div class="mh-brand-sub">โรงพยาบาลภักดีชุมพล</div>
            </div>
        </div><a class="mh-btn mh-btn-light" href="rooms.php">← กลับ</a>
    </header>
    <main class="mh-wrap">
        <section class="mh-page-title">
            <div><span class="mh-kicker">NEW MEETING ROOM</span>
                <h1>เพิ่มข้อมูลห้องประชุม</h1>
                <p>กำหนดข้อมูลพื้นฐานและอุปกรณ์ประจำห้อง</p>
            </div>
        </section>
        <section class="mh-form-card">
            <form method="post" action="room_save.php"><input type="hidden" name="csrf_token" value="<?= meeting_e(meeting_csrf_token()) ?>">
                <div class="mh-form-section">
                    <div class="mh-form-section-title"><span>01</span>
                        <div>
                            <h2>ข้อมูลห้อง</h2>
                            <p>ข้อมูลที่แสดงใน Dashboard และหน้าจอง</p>
                        </div>
                    </div>
                    <div class="mh-form-grid">
                        <div class="mh-field mh-col-6"><label>ชื่อห้องประชุม <b>*</b></label><input name="room_name" required placeholder="เช่น ห้องประชุมภูไท"></div>
                        <div class="mh-field mh-col-3"><label>รหัสห้อง</label><input name="room_code" maxlength="50" placeholder="เช่น PHT-01"></div>
                        <div class="mh-field mh-col-3"><label>ความจุ <b>*</b></label><input type="number" name="capacity" min="1" value="20" required></div>
                        <div class="mh-field mh-col-6"><label>สถานที่</label><input name="location" placeholder="อาคาร / ชั้น / จุดสังเกต"></div>
                        <div class="mh-field mh-col-3"><label>สีประจำห้อง</label><input class="mh-color-input" type="color" name="color" value="#0f766e"></div>
                        <div class="mh-field mh-col-3"><label>สถานะ</label><select name="status">
                                <option value="active">เปิดใช้งาน</option>
                                <option value="inactive">ปิดใช้งาน</option>
                            </select></div>
                        <div class="mh-field mh-col-12"><label>อุปกรณ์ประจำห้อง</label><textarea name="equipment" rows="4" placeholder="Projector, TV, Microphone, Conference Camera, Wi-Fi"></textarea></div>
                    </div>
                </div>
                <div class="mh-form-actions"><a class="mh-btn mh-btn-outline" href="rooms.php">ยกเลิก</a><button class="mh-btn mh-btn-primary" type="submit">บันทึกห้องประชุม</button></div>
            </form>
        </section>
    </main>
</body>

</html>