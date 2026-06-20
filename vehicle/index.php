<?php
include("../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}
// ================= USERS =================
$users = [];

$sqlUsers = "SELECT id, fullname FROM users";

$resultUsers = mysqli_query($conn, $sqlUsers);

if ($resultUsers) {

    while ($row = mysqli_fetch_assoc($resultUsers)) {

        $users[] = $row;
    }
} else {

    echo mysqli_error($conn);
}
$message = "";
// ดึงรายชื่อผู้ใช้


// ✅ บันทึกข้อมูลขอใช้รถ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_vehicle"])) {
    $fullname = trim($_POST["fullname"]);
    $companions = $_POST["companions"]; // รับค่าชื่อผู้ร่วมเดินทาง
    $car = trim($_POST["car"]);
    $use_date = $_POST["use_date"];
    $detail = trim($_POST["detail"]);

    if (empty($fullname) || empty($car) || empty($use_date)) {
        $message = "กรุณากรอกข้อมูลให้ครบ";
    } else {
        // เพิ่ม companions ลงใน SQL Statement
        $stmt = $conn->prepare("INSERT INTO vehicle_requests (user_id, fullname, companions, car, use_date, detail) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $_SESSION["user_id"], $fullname, $companions, $car, $use_date, $detail);

        if ($stmt->execute()) {
            $message = "บันทึกข้อมูลเรียบร้อยแล้ว";
        } else {
            $message = "เกิดข้อผิดพลาด";
        }
        $stmt->close();
    }
}
// ผู้ร่วมเดินทาง
$companions = "";

if (!empty($_POST["companions"])) {
    $companions = implode(", ", $_POST["companions"]);
}

// ดึงข้อมูลมาแสดง
$result = $conn->query("SELECT * FROM vehicle_requests ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>งานบริการยานพาหนะ</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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

        .wrapper {
            width: 100%;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .menu-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #2a9d8f;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            margin: 5px 5px 5px 0;
        }

        .msg {
            background: #d4edda;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            color: #155724;
            border: 1px solid #c3e6cb;
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
            background: #2a9d8f;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #1e7566;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background: #2a9d8f;
            color: white;
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

    <div class="top-bar">🏢 ระบบแจ้งซ่อมและบริหารงาน</div>

    <div class="container-wrapper">
        <?php
        $activePage = "vehicle";
        $basePath = "../";
        require __DIR__ . "/../components/sidebar.php";
        ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="wrapper">

                <!-- เมนู -->
                <div class="card">
                    <a class="menu-btn" href="?page=calendar">📅 ปฏิทิน</a>
                    <a class="menu-btn" href="?page=add">➕ เพิ่มข้อมูล</a>
                    <a class="menu-btn" href="?page=list">📋 รายการ</a>
                    <a class="menu-btn" href="../dashboard.php">⬅ กลับ</a>
                </div>

                <?php
                $page = $_GET["page"] ?? "add";

                // ================== เพิ่มข้อมูล ==================
                if ($page == "add") {
                ?>

                    <div class="card">
                        <h2>➕ เพิ่มข้อมูลขอใช้รถ</h2>

                        <?php if (!empty($message)) { ?>
                            <div class="msg"><?php echo $message = "";

                                                // ✅ แก้ไขส่วนบันทึกข้อมูลขอใช้รถ
                                                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_vehicle"])) {
                                                    $fullname = trim($_POST["fullname"]);
                                                    $subject = trim($_POST["subject"]);
                                                    $car = trim($_POST["car"]);
                                                    $use_date = $_POST["use_date"];
                                                    $use_time = $_POST["use_time"];
                                                    $location = trim($_POST["location"]);
                                                    $urgency = $_POST["urgency"];
                                                    $detail = trim($_POST["detail"]);

                                                    // ✅ จัดการผู้ร่วมเดินทาง (แปลง Array เป็น String)
                                                    $companions_text = "";
                                                    if (!empty($_POST["companions"])) {
                                                        $companions_text = implode(", ", $_POST["companions"]);
                                                    }

                                                    // ... (ส่วนของการ Upload File เหมือนเดิม) ...

                                                    $stmt = $conn->prepare("
        INSERT INTO vehicle_requests 
        (user_id, fullname, subject, car, use_date, use_time, location, urgency, companions, detail, document)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

                                                    $stmt->bind_param(
                                                        "issssssssss",
                                                        $_SESSION["user_id"],
                                                        $fullname,
                                                        $subject,
                                                        $car,
                                                        $use_date,
                                                        $use_time,
                                                        $location,
                                                        $urgency,
                                                        $companions_text, // บันทึกรายชื่อที่รวมกันแล้ว
                                                        $detail,
                                                        $document_name
                                                    );

                                                    if ($stmt->execute()) {
                                                        $message = "บันทึกข้อมูลเรียบร้อยแล้ว";
                                                    }
                                                    // ...


                                                } ?></div>
                        <?php } ?>
                        <?php if (!empty($message)) { ?>
                            <script>
                                alert("ส่งคำขอเรียบร้อยแล้ว");
                            </script>
                        <?php } ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="save_vehicle" value="1">

                            <input type="text" name="fullname" placeholder="ชื่อผู้ขอ" required>
                            <label>ผู้ร่วมเดินทาง (เลือกได้หลายคนโดยกด Ctrl ค้างไว้)</label>
                            <select name="companions[]" multiple class="form-control" style="height: 150px;" required>
                                <?php foreach ($users as $user) { ?>
                                    <option value="<?php echo htmlspecialchars($user["fullname"]); ?>">
                                        <?php echo htmlspecialchars($user["fullname"]); ?>
                                    </option>
                                <?php } ?>
                            </select>

                            <input type="text" name="subject" placeholder="หัวข้อเรื่อง" required>

                            <input type="text" name="car" placeholder="ประเภทรถ" required>

                            <input type="date" name="use_date" required>

                            <input type="time" name="use_time" required>

                            <input type="text" name="location" placeholder="สถานที่ไป" required>

                            <select name="urgency" required>
                                <option value="">-- เลือกความเร่งด่วน --</option>
                                <option value="ปกติ">ปกติ</option>
                                <option value="ด่วน">ด่วน</option>
                                <option value="ด่วนมาก">ด่วนมาก</option>
                            </select>

                            <textarea name="detail" placeholder="รายละเอียดเพิ่มเติม"></textarea>

                            <label>แนบหนังสืออ้างถึง</label>
                            <input type="file" name="document">

                            <button type="submit">บันทึก</button>
                        </form>
                    </div>

                <?php } ?>

                <!-- ================== รายการ ================== -->
                <?php if ($page == "list") { ?>

                    <div class="card">
                        <h2>📋 รายการใช้รถ</h2>

                        <table border="1" width="100%" cellpadding="10">
                            <tr>
                                <th>ID</th>
                                <th>ชื่อผู้ขอ</th>
                                <th>ผู้ร่วมเดินทาง</th>
                                <th>รถ</th>
                                <th>วันที่</th>
                                <th>รายละเอียด</th>
                            </tr>
                            <?php if ($result && $result->num_rows > 0) { ?>
                                <?php while ($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $row["id"]; ?></td>
                                        <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["companions"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["car"]); ?></td>
                                        <td><?php echo $row["use_date"]; ?></td>
                                        <td><?php echo htmlspecialchars($row["detail"]); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </table>
                    </div>

                <?php } ?>

                <!-- ================== ปฏิทิน ================== -->
                <?php if ($page == "calendar") { ?>

                    <div class="card">
                        <h2>📅 ปฏิทินยานพาหนะ</h2>

                        <input type="date">
                        <br><br>
                        <button>เลือกวันที่</button>
                    </div>

                <?php } ?>

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
