<?php
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// ดึงรายชื่อช่างจากฐานข้อมูล
$technicians = [];
$sql = "SELECT id, name FROM technicians WHERE status='ใช้งาน'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $technicians[] = $row;
    }
}

// ตัวแปรเก็บข้อความแจ้ง
$message = "";

// บันทึกข้อมูลเมื่อฟอร์มถูกส่ง
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_repair"])) {
    $sender_name = trim($_POST["sender_name"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $repair_system = trim($_POST["repair_system"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $technician_id = intval($_POST["technician_id"] ?? 0);
    $details = trim($_POST["details"] ?? "");

    if (
        empty($sender_name) || empty($department) || empty($repair_system) ||
        empty($location) || $technician_id <= 0 || empty($details)
    ) {
        $message = "⚠️ กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO repair_jobs 
            (user_id, sender_name, department, repair_system, location, technician_id, details, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'รอดำเนินการ')
        ");

        if ($stmt) {
            $user_id = $_SESSION["user_id"];
            $stmt->bind_param("isssssi", $user_id, $sender_name, $department, $repair_system, $location, $technician_id, $details);

            if ($stmt->execute()) {
                $message = "✅ บันทึกการแจ้งซ่อมเรียบร้อยแล้ว";
                // ล้างฟอร์ม
                $_POST = [];
            } else {
                $message = "❌ เกิดข้อผิดพลาด: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "❌ เตรียมคำสั่ง SQL ล้มเหลว";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ระบบวันลา</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Tahoma, sans-serif;
            background: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #0051ffff;
            padding: 15px 20px;
            color: white;
            font-size: 22px;
            font-weight: bold;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .container-wrapper {
            display: flex;
            width: 100%;
            margin-top: 60px;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #003d99, #0051ff);
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            left: 0;
            top: 60px;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #48cae4;
            padding-left: 25px;
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: #48cae4;
        }

        .menu-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
            margin: 10px 0;
        }

        .sidebar-title {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 15px 20px 8px;
            letter-spacing: 1px;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px 20px;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        input,
        select,
        textarea,
        button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: Tahoma, sans-serif;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #1f7d35;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

        }

        @media (max-width: 600px) {
            .container-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <div class="top-bar">
        🏢 ระบบแจ้งซ่อมและบริหารงาน
    </div>

    <div class="container-wrapper">
        <?php
        $activePage = "repair";
        $basePath = "";
        require __DIR__ . "/components/sidebar.php";
        ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="card">
                <h2>🔧 แจ้งรายละเอียดการซ่อม</h2>

                <?php if (!empty($message)): ?>
                    <div class="msg-box <?php
                                        if (strpos($message, '✅') !== false) echo 'msg-success';
                                        elseif (strpos($message, '⚠️') !== false) echo 'msg-warning';
                                        else echo 'msg-error';
                                        ?>">
                        <?= htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form action="lineapi/save_repair.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ส่ง:</label>
                        <input type="text" name="sender_name" class="form-control" required value="<?= htmlspecialchars($_POST['sender_name'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">แผนก:</label>
                        <select name="department" class="form-select" required>
                            <option value="">-- เลือกแผนก --</option>
                            <option value="IT" <?= ($_POST['department'] ?? '') === 'IT' ? 'selected' : ''; ?>>IT</option>
                            <option value="บริหาร" <?= ($_POST['department'] ?? '') === 'บริหาร' ? 'selected' : ''; ?>>บริหาร</option>
                            <option value="OPD" <?= ($_POST['department'] ?? '') === 'OPD' ? 'selected' : ''; ?>>OPD</option>
                            <option value="IPD" <?= ($_POST['department'] ?? '') === 'IPD' ? 'selected' : ''; ?>>IPD</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ระบบที่ต้องการแจ้งซ่อม:</label>
                        <select name="repair_system" class="form-select" required>
                            <option value="">-- เลือกระบบ --</option>
                            <option value="ระบบไฟฟ้า" <?= ($_POST['repair_system'] ?? '') === 'ระบบไฟฟ้า' ? 'selected' : ''; ?>>ระบบไฟฟ้า</option>
                            <option value="ระบบคอมพิวเตอร์" <?= ($_POST['repair_system'] ?? '') === 'ระบบคอมพิวเตอร์' ? 'selected' : ''; ?>>ระบบคอมพิวเตอร์</option>
                            <option value="ระบบจัดการเครื่องมือแพทย์" <?= ($_POST['repair_system'] ?? '') === 'ระบบจัดการเครื่องมือแพทย์' ? 'selected' : ''; ?>>ระบบจัดการเครื่องมือแพทย์</option>
                            <option value="ระบบทั่วไป" <?= ($_POST['repair_system'] ?? '') === 'ระบบทั่วไป' ? 'selected' : ''; ?>>ระบบทั่วไป</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">สถานที่พบเจอปัญหา:</label>
                        <select name="location" class="form-select" required>
                            <option value="">-- เลือกสถานที่ --</option>
                            <option value="ผู้ป่วยนอก" <?= ($_POST['location'] ?? '') === 'ผู้ป่วยนอก' ? 'selected' : ''; ?>>ผู้ป่วยนอก</option>
                            <option value="ผู้ป่วยใน" <?= ($_POST['location'] ?? '') === 'ผู้ป่วยใน' ? 'selected' : ''; ?>>ผู้ป่วยใน</option>
                            <option value="บริหาร" <?= ($_POST['location'] ?? '') === 'บริหาร' ? 'selected' : ''; ?>>บริหาร</option>
                            <option value="ตึก10เตียง" <?= ($_POST['location'] ?? '') === 'ตึก10เตียง' ? 'selected' : ''; ?>>ตึก10เตียง</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เลือกช่าง:</label>
                        <select name="technician_id" class="form-select" required>
                            <option value="">-- เลือกช่าง --</option>
                            <?php foreach ($technicians as $tech): ?>
                                <option value="<?= $tech['id']; ?>" <?= ($_POST['technician_id'] ?? '') == $tech['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($tech['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รายละเอียดแจ้งซ่อม:</label>
                        <textarea name="details" class="form-control" rows="5" required><?= htmlspecialchars($_POST['details'] ?? ''); ?></textarea>
                    </div>

                    <input type="hidden" name="save_repair" value="1">
                    <button type="submit" class="btn-submit">✅ ส่งข้อมูลและแจ้งเตือน Line</button>
                    <a href="dashboard.php" class="btn-back">← กลับไปหน้าแรก</a>
                </form>
            </div>
        </main>
    </div>

    <style>
        #logoutModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        #logoutModal.active {
            display: flex;
        }

        #logoutModal .modal-box {
            width: min(500px, calc(100% - 40px));
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.25);
        }

        #logoutModal .modal-header {
            padding: 18px 20px;
            background: #198754;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #logoutModal .modal-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        #logoutModal .modal-close {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }

        #logoutModal .modal-body {
            padding: 20px;
            color: #333;
            font-size: 15px;
            line-height: 1.6;
        }

        #logoutModal .modal-footer {
            padding: 16px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        #logoutModal .btn-cancel,
        #logoutModal .btn-confirm {
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            cursor: pointer;
            font-weight: 600;
        }

        #logoutModal .btn-cancel {
            background: #6c757d;
            color: #fff;
        }

        #logoutModal .btn-confirm {
            background: #dc3545;
            color: #fff;
        }
    </style>

    <div id="logoutModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">ยืนยันออกจากระบบ</div>
                <div><button class="modal-close" type="button" id="logoutModalClose">&times;</button></div>
            </div>
            <div class="modal-body">คุณต้องการออกจากระบบหรือไม่?</div>
            <div class="modal-footer">
                <button class="btn-cancel" type="button" id="logoutModalCancel">ยกเลิก</button>
                <button class="btn-confirm" type="button" id="logoutModalConfirm">ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var logoutModal = document.getElementById('logoutModal');
            var logoutHref = '';
            var links = document.querySelectorAll('.logout-link');
            var closeBtn = document.getElementById('logoutModalClose');
            var cancelBtn = document.getElementById('logoutModalCancel');
            var confirmBtn = document.getElementById('logoutModalConfirm');

            links.forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    logoutHref = this.href;
                    logoutModal.classList.add('active');
                });
            });

            var hideModal = function() {
                logoutModal.classList.remove('active');
            };

            closeBtn.addEventListener('click', hideModal);
            cancelBtn.addEventListener('click', hideModal);
            logoutModal.addEventListener('click', function(event) {
                if (event.target === logoutModal) {
                    hideModal();
                }
            });
            confirmBtn.addEventListener('click', function() {
                if (logoutHref) {
                    window.location.href = logoutHref;
                }
            });
        });
    </script>
</body>

</html>

</html>
