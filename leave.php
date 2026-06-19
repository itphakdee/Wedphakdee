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
    <link rel="stylesheet" href="style.css">
    <style>
        .top-bar {
            background: #28a745;
            padding: 15px;
            text-align: center;
            color: white;
            font-size: 22px;
            font-weight: bold;
        }

        .page-wrapper {
            width: 95%;
            max-width: 1150px;
            margin: 30px auto;
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

        .btn-back {
            display: inline-block;
            padding: 10px 16px;
            background: #4a67ff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .btn-back:hover {
            background: #3349c9;
        }

        .message-box {
            background: #e8fff0;
            color: #0f7a36;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .error-box {
            background: #ffeaea;
            color: #b30000;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
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
    </style>
</head>
<body>

    <div class="top-bar">
        ระบบวันลา
    </div>

    <div class="page-wrapper">

        <a href="dashboard.php" class="btn-back">← กลับเมนูหลัก</a>

        <div class="card">
            <h2>ฟอร์มยื่นวันลา</h2>

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

                <button type="submit">บันทึกวันลา</button>
            </form>
        </div>

        <div class="card">
            <h2>รายการวันลาของฉัน</h2>

            <div class="table-box">
                <table>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อ</th>
                        <th>ประเภทการลา</th>
                        <th>วันที่เริ่ม</th>
                        <th>วันที่สิ้นสุด</th>
                        <th>เหตุผล</th>
                        <th>สถานะปัจจุบัน</th>
                        <th>แก้ไขสถานะ</th>
                        <th>วันที่บันทึก</th>
                    </tr>

                    <?php if ($leaves && $leaves->num_rows > 0) { ?>
                        <?php while($row = $leaves->fetch_assoc()) { ?>
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

    </div>

</body>
</html>