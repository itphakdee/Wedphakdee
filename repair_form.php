
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

    if (empty($sender_name) || empty($department) || empty($repair_system) || 
        empty($location) || $technician_id <= 0 || empty($details)) {
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
    <link rel="stylesheet" href="style.css">
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
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
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
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
            background: rgba(255,255,255,0.1);
            border-left-color: #48cae4;
            padding-left: 25px;
        }

        .sidebar-menu a.active {
            background: rgba(255,255,255,0.15);
            border-left-color: #48cae4;
        }

        .menu-divider {
            height: 1px;
            background: rgba(255,255,255,0.2);
            margin: 10px 0;
        }

        .sidebar-title {
            color: rgba(255,255,255,0.7);
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
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        input, select, textarea, button {
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

        .message-box {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }

        .error-box {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }

        .table-box {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background: #28a745;
            color: white;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .status-form {
            display: flex;
            gap: 8px;
            align-items: center;
            min-width: 220px;
        }

        .status-form select {
            margin: 0;
            min-width: 140px;
        }

        .status-form button {
            margin: 0;
            width: auto;
            white-space: nowrap;
            padding: 10px 14px;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 13px;
            color: white;
        }

        .pending {
            background: #f0ad4e;
        }

        .approved {
            background: #28a745;
        }

        .cancel {
            background: #dc3545;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .status-form {
                flex-direction: column;
                align-items: stretch;
            }

            .status-form button {
                width: 100%;
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
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li class="sidebar-title">📋 เมนูหลัก</li>
                <li><a href="dashboard.php">🏠 หน้าแรก</a></li>
                <li><a href="leave.php">📅 วันลา</a></li>
                <li><a href="e_document.php">📄 หนังสือราชการ</a></li>
                <li><a href="vehicle/index.php">🚗 ยานพาหนะ</a></li>
                <li><a href="repair_form.php" class="active">🔧 แจ้งซ่อม</a></li>

                <li class="menu-divider"></li>
                <li class="sidebar-title">⚙️ ตั้งค่า</li>
                <li><a href="logout.php">🚪 ออกจากระบบ</a></li>
            </ul>
        </aside>

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
                            <?php foreach($technicians as $tech): ?>
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
</body>

</html>