<?php
include("../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}
// ================= USERS =================
$get_user = "SELECT fullname
        FROM users
        WHERE status = 'active'
        ORDER BY fullname";

$stmt_users = $conn->prepare($get_user);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
// ดึงรายชื่อผู้ใช้


// ✅ บันทึกข้อมูลขอใช้รถ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_vehicle"])) {
    $fullname = trim($_POST["fullname"]);
    $members = $_POST['members'] ?? []; // รับค่าผู้ร่วมเดินทางเป็น Array
    $member_str = implode(', ', $members);
    $car = trim($_POST["car"]);
    $use_date = $_POST["use_date"];
    $detail = trim($_POST["detail"]);

    if (empty($fullname) || empty($car) || empty($use_date)) {
        $message = "กรุณากรอกข้อมูลให้ครบ";
    } else {

        $stmt = $conn->prepare("INSERT INTO vehicle_requests (user_id, fullname, companions, car, use_date, detail) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $_SESSION["user_id"], $fullname, $member_str, $car, $use_date, $detail);

        if ($stmt->execute()) {
            $message = "บันทึกข้อมูลเรียบร้อยแล้ว " . $member_str;
        } else {
            $message = "เกิดข้อผิดพลาด " . $member_str;
        }
        $stmt->close();
    }
}

// ดึงข้อมูลมาแสดง
$result_requests = $conn->query("SELECT * FROM vehicle_requests ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>งานบริการยานพาหนะ</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
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
            padding: 0;
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

        /*input,
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
        }*/

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

        .bootstrap-select {
            width: 100% !important;
        }

        .bootstrap-select>.dropdown-toggle {
            width: 100%;
            height: calc(1.5em + .75rem + 2px);

            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: .375rem;
            box-shadow: none;
        }

        
        .bootstrap-select>.dropdown-toggle:focus,
        .bootstrap-select.show>.dropdown-toggle {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
        }

        
        .bootstrap-select .filter-option {
            display: flex;
            align-items: center;
            height: 100%;
        }

        .form-control {
            background: #eef4f6;
            border: 1px solid #b6e0fe;
            border-radius: 20px;
        }

        .bootstrap-select>.dropdown-toggle {
            background: #eef4f6 !important;
            border: 1px solid #b6e0fe !important;
            border-radius: 20px !important;
            height: 52px;
            padding: 0 20px;
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
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="save_vehicle" value="1">

                            <input type="text" name="fullname" placeholder="ชื่อผู้ขอ" required>
                            <label>ผู้ร่วมเดินทาง (เลือกได้หลายคนโดยกด Ctrl ค้างไว้)</label>
                            <select class="selectpicker" name="members[]" multiple data-live-search="true"
                                title="เลือกผู้ร่วมเดินทาง" data-width="100%">
                                <?php while ($user = $result_users->fetch_assoc()): ?>
                                    <option value="<?= $user['fullname']; ?>">
                                        <?= htmlspecialchars($user['fullname']); ?>
                                    </option>
                                <?php endwhile; ?>
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
                            <?php if ($result_requests && $result_requests->num_rows > 0) { ?>
                                <?php while ($row = $result_requests->fetch_assoc()) { ?>
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

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php require __DIR__ . "/../components/dialog.php"; ?>
    <script>
        $(function () {
            $('#members').select2({
                placeholder: 'เลือกผู้ร่วมเดินทาง',
                closeOnSelect: false,
                templateResult: function (data) {
                    if (!data.id) {
                        return data.text;
                    }

                    return $('<span class="wrap">' + data.text + '</span>');
                }
            });
        });
    </script>
</body>

</html>