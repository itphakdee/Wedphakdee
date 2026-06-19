
<?php
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
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
    <link rel="stylesheet" href="style.css">
    <style>
        .menu-bar {
            background: #0051ffff;
            padding: 15px;
            text-align: center;
            color: white;
            font-size: 22px;
            font-weight: bold;
        }

        .dashboard-wrapper {
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

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            resize: vertical;
            font-family: Tahoma, sans-serif;
        }

        input, button, select {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        button {
            background: #4a67ff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #3349c9;
        }

        .logout-btn,
        .menu-btn {
            display: inline-block;
            padding: 10px 18px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 10px;
            margin-right: 10px;
        }

        .logout-btn {
            background: #dc3545;
        }

        .logout-btn:hover {
            background: #b52a37;
        }

        .menu-btn {
            background: #28a745;
        }

        .menu-btn:hover {
            background: #1f7d35;
        }

        .msg-box {
            background: #e8fff0;
            color: #0f7a36;
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
            background: #4a67ff;
            color: white;
        }

        .status-form {
            display: flex;
            gap: 8px;
            align-items: center;
            min-width: 220px;
        }

        .status-form select {
            margin: 0;
            min-width: 150px;
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
            background: linear-gradient(135deg, #d8002bff, #48cae4);
        }

        .done {
           background: linear-gradient(135deg, #00b4d8, #48cae4);
        }

        .cancel {
            background: #dc3545;
        }

        .welcome-text {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
  
    <div class="menu-bar">
        เมนูหลัก - ระบบแจ้งซ่อม
    </div>

    <div class="dashboard-wrapper">

        <div class="card">
            <h2>ยินดีต้อนรับ</h2>
            <p class="welcome-text"><strong>ชื่อ:</strong> <?php echo htmlspecialchars($_SESSION["fullname"]); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION["username"]); ?></p>

            <a class="menu-btn" href="leave.php">📅 วันลา</a>
            <a class="menu-btn" href="e_document.php">📄 หนังสือราชการ</a>
            <a class="menu-btn" href="vehicle/index.php">🚗 งานบริการยานพาหนะ</a>
            <a class="menu-btn" href="repair_form.php">หน้าแจ้งซ่อม</a>
            <a class="logout-btn" href="logout.php">ออกจากระบบ</a>
            
        </div>
        

</select>

              
</select>


</body>
</html>