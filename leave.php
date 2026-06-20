<?php
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$user_id = $_SESSION["user_id"];

// บันทึกข้อมูลวันลา
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_leave"])) {
    $fullname = trim($_POST["fullname"]);
    $leave_type = trim($_POST["leave_type"]);
    $start_date = trim($_POST["start_date"]);
    $end_date = trim($_POST["end_date"]);
    $reason = trim($_POST["reason"]);

    if (empty($fullname) || empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
        $message = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif ($end_date < $start_date) {
        $message = "วันที่สิ้นสุดลาต้องไม่น้อยกว่าวันที่เริ่มลา";
    } else {
        $stmt = $conn->prepare("INSERT INTO leave_requests (user_id, fullname, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $fullname, $leave_type, $start_date, $end_date, $reason);

        if ($stmt->execute()) {
            $message = "บันทึกข้อมูลวันลาเรียบร้อยแล้ว";
        } else {
            $message = "เกิดข้อผิดพลาดในการบันทึกวันลา";
        }

        $stmt->close();
    }
}

// อัปเดตสถานะวันลา
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    $leave_id = intval($_POST["leave_id"]);
    $status = trim($_POST["status"]);

    $allowed_status = ["รอดำเนินการ", "อนุมัติ", "ยกเลิก"];

    if (in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $leave_id, $user_id);

        if ($stmt->execute()) {
            $message = "อัปเดตสถานะวันลาเรียบร้อยแล้ว";
        } else {
            $message = "ไม่สามารถอัปเดตสถานะวันลาได้";
        }

        $stmt->close();
    } else {
        $message = "สถานะไม่ถูกต้อง";
    }
}

// ดึงข้อมูลวันลาของผู้ใช้
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$leaves = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ระบบวันลา</title>
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

        table th,
        table td {
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
        <?php
        $activePage = "leave";
        $basePath = "";
        require __DIR__ . "/components/sidebar.php";
        ?>

        <!-- Main Content -->
        <main class="main-content">

            <div class="card">
                <h2>📋 ฟอร์มยื่นวันลา</h2>

                <?php if (!empty($message)) { ?>
                    <?php
                    $is_success = ($message == "บันทึกข้อมูลวันลาเรียบร้อยแล้ว" || $message == "อัปเดตสถานะวันลาเรียบร้อยแล้ว");
                    ?>
                    <div class="<?php echo $is_success ? 'message-box' : 'error-box'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php } ?>

                <form method="POST" action="">
                    <input type="hidden" name="save_leave" value="1">

                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($_SESSION["fullname"]); ?>" placeholder="ชื่อผู้ลา" required>

                    <select name="leave_type" required>
                        <option value="">-- เลือกประเภทการลา --</option>
                        <option value="ลาป่วย">ลาป่วย</option>
                        <option value="ลากิจ">ลากิจ</option>
                        <option value="ลาพักร้อน">ลาพักร้อน</option>
                        <option value="ลาคลอด">ลาคลอด</option>
                        <option value="ลาอื่น ๆ">ลาอื่น ๆ</option>
                    </select>

                    <div class="grid-2">
                        <div>
                            <label>วันที่เริ่มลา</label>
                            <input type="date" name="start_date" required>
                        </div>

                        <div>
                            <label>วันที่สิ้นสุดลา</label>
                            <input type="date" name="end_date" required>
                        </div>
                    </div>

                    <textarea name="reason" placeholder="เหตุผลการลา" required></textarea>

                    <button type="submit">✅ บันทึกวันลา</button>
                </form>
            </div>

            <div class="card">
                <h2>📋 รายการวันลาของฉัน</h2>

                <div class="table-box">
                    <table>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ชื่อ</th>
                            <th>ประเภทการลา</th>
                            <th>วันที่เริ่ม</th>
                            <th>วันที่สิ้นสุด</th>
                            <th>เหตุผล</th>
                            <th>สถานะ</th>
                            <th>แก้ไขสถานะ</th>
                            <th>วันที่บันทึก</th>
                        </tr>

                        <?php if ($leaves && $leaves->num_rows > 0) { ?>
                            <?php while ($row = $leaves->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row["id"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["leave_type"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["start_date"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["end_date"]); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row["reason"])); ?></td>
                                    <td>
                                        <?php
                                        $status_class = "pending";
                                        if ($row["status"] == "อนุมัติ") {
                                            $status_class = "approved";
                                        } elseif ($row["status"] == "ยกเลิก") {
                                            $status_class = "cancel";
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($row["status"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="status-form">
                                            <input type="hidden" name="update_status" value="1">
                                            <input type="hidden" name="leave_id" value="<?php echo $row["id"]; ?>">

                                            <select name="status" required>
                                                <option value="รอดำเนินการ" <?php if ($row["status"] == "รอดำเนินการ") echo "selected"; ?>>
                                                    รอดำเนินการ
                                                </option>
                                                <option value="อนุมัติ" <?php if ($row["status"] == "อนุมัติ") echo "selected"; ?>>
                                                    อนุมัติ
                                                </option>
                                                <option value="ยกเลิก" <?php if ($row["status"] == "ยกเลิก") echo "selected"; ?>>
                                                    ยกเลิก
                                                </option>
                                            </select>

                                            <button type="submit">บันทึก</button>
                                        </form>
                                    </td>
                                    <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="9" style="text-align:center;">ยังไม่มีข้อมูลวันลา</td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

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
