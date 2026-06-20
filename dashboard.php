<?php
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check if just logged in
$show_login_success = isset($_SESSION["login_success"]) ? $_SESSION["login_success"] : false;
if ($show_login_success) {
    unset($_SESSION["login_success"]);
}

// ฟั่งชี่นแจ้งเตือนไลน์ 


// บันทึกข้อมูลแจ้งซ่อม
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_repair"])) {
    $topic = trim($_POST["topic"]);
    $reporter_name = trim($_POST["reporter_name"]);
    $detail = trim($_POST["detail"]);

    if (empty($topic) || empty($reporter_name) || empty($detail)) {
        $message = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } else {
        $technician_id = $_POST["technician_id"];

        $stmt = $conn->prepare("
    INSERT INTO repairs 
    (user_id, topic, reporter_name, detail, technician_id) 
    VALUES (?, ?, ?, ?, ?)
");

        $stmt->bind_param("isssi", $user_id, $topic, $reporter_name, $detail, $technician_id);
        $stmt->bind_param("isss", $user_id, $topic, $reporter_name, $detail);

        if ($stmt->execute()) {
            $message = "บันทึกการแจ้งซ่อมเรียบร้อยแล้ว";
        } else {
            $message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }

        $stmt->close();
    }
}
// ดึงประเภทงานซ่อมจากฐานข้อมูล
$categories = [];

$sql = "SELECT id, category_name FROM repair_categories WHERE status='ใช้งาน' ORDER BY id ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
// ดึงรายชื่อช่าง
$technicians = [];

$sqlTech = "SELECT id, name FROM technicians WHERE status='ใช้งาน'";
$resultTech = $conn->query($sqlTech);

if ($resultTech->num_rows > 0) {
    while ($row = $resultTech->fetch_assoc()) {
        $technicians[] = $row;
    }
}
// อัปเดตสถานะการแจ้งซ่อม
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    $repair_id = intval($_POST["repair_id"]);
    $status = trim($_POST["status"]);

    $allowed_status = ["รอดำเนินการ", "ซ่อมเสร็จแล้ว", "ยกเลิก"];

    if (in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE repairs SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $repair_id, $user_id);

        if ($stmt->execute()) {
            $message = "อัปเดตสถานะเรียบร้อยแล้ว";
        } else {
            $message = "ไม่สามารถอัปเดตสถานะได้";
        }

        $stmt->close();
    } else {
        $message = "สถานะไม่ถูกต้อง";
    }
}

// ดึงรายการแจ้งซ่อมของผู้ใช้
$stmt = $conn->prepare("
    SELECT r.*, t.name AS technician_name
    FROM repairs r
    LEFT JOIN technicians t ON r.technician_id = t.id
    WHERE r.user_id = ?
    ORDER BY r.id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$repairs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เมนูหลัก - ระบบแจ้งซ่อม</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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

        .container {
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

        .card h2 {
            margin-bottom: 15px;
            color: #333;
        }

        .user-info {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .user-info strong {
            color: #0051ff;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .top-bar {
                font-size: 18px;
            }
        }

        @media (max-width: 600px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
                margin-top: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .top-bar {
                font-size: 16px;
                padding: 12px 15px;
            }
        }
    </style>
</head>

<body>
    <div class="top-bar">
        🏢 ระบบแจ้งซ่อมและบริหารงาน
    </div>

    <div class="container">
        <?php
        $activePage = "dashboard";
        $basePath = "";
        require __DIR__ . "/components/sidebar.php";
        ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="card">
                <h2>👋 ยินดีต้อนรับ</h2>
                <div class="user-info">
                    <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($_SESSION["fullname"]); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION["username"]); ?></p>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal for Login Success -->
    <div class="modal fade" id="loginSuccessModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">✅ ยินดีต้อนรับ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>สวัสดี <strong><?php echo htmlspecialchars($_SESSION["fullname"]); ?></strong></p>
                    <p>ยินดีต้อนรับเข้าสู่ระบบแจ้งซ่อมและบริหารงาน</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">เข้าใช้งาน</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($show_login_success) { ?>
            var loginModal = new bootstrap.Modal(document.getElementById('loginSuccessModal'));
            loginModal.show();
        <?php } ?>
    </script>

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
