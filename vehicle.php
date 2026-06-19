<?php
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>งานบริการยานพาหนะ</title>
<link rel="stylesheet" href="style.css">

<style>
.top-bar {
    background: linear-gradient(135deg, #00b4d8, #48cae4);
    color: white;
    padding: 18px;
    text-align: center;
    font-size: 22px;
    font-weight: bold;
}

.wrapper {
    width: 95%;
    max-width: 1100px;
    margin: 25px auto;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.menu-btn {
    display: inline-block;
    padding: 10px 15px;
    background: #2a9d8f;
    color: white;
    border-radius: 10px;
    text-decoration: none;
    margin: 5px;
}

.menu-btn:hover {
    background: #21867a;
}

.calendar {
    text-align: center;
    font-size: 18px;
    padding: 20px;
}

</style>
</head>

<body>

<div class="top-bar">🚗 งานบริการยานพาหนะ</div>

<div class="wrapper">

    <!-- เมนู -->
    <div class="card">
        <a class="menu-btn" href="?page=calendar">📅 ปฏิทินยานพาหนะ</a>
        <a class="menu-btn" href="?page=add">➕ เพิ่มข้อมูลขอใช้รถ</a>
        <a class="menu-btn" href="?page=list">📋 ทะเบียนใช้รถทั่วไป</a>
        <a class="menu-btn" href="dashboard.php">⬅ กลับ</a>
    </div>

<?php
$page = $_GET["page"] ?? "calendar";

if ($page == "calendar") {
?>

    <!-- ปฏิทิน -->
    <div class="card">
        <h2>📅 ปฏิทินยานพาหนะ</h2>

        <div class="calendar">
            <input type="date">
            <br><br>
            <button>เลือกวันที่</button>
        </div>
    </div>

<?php } elseif ($page == "add") { ?>

    <!-- เพิ่มข้อมูล -->
    <div class="card">
        <h2>➕ เพิ่มข้อมูลขอใช้รถ</h2>

        <form method="POST">
            <input type="text" name="fullname" placeholder="ชื่อผู้ขอ" required>
            <input type="text" name="car" placeholder="รถที่ใช้ / ทะเบียน" required>
            <input type="date" name="use_date" required>
            <textarea name="detail" placeholder="รายละเอียด"></textarea>
            <button type="submit">บันทึก</button>
        </form>
    </div>

<?php } elseif ($page == "list") { ?>

    <!-- รายการ -->
    <div class="card">
        <h2>📋 ทะเบียนใช้รถทั่วไป</h2>

        <table border="1" width="100%" cellpadding="10">
            <tr>
                <th>ลำดับ</th>
                <th>ชื่อ</th>
                <th>รถ</th>
                <th>วันที่</th>
                <th>รายละเอียด</th>
            </tr>

            <tr>
                <td colspan="5" align="center">ยังไม่มีข้อมูล</td>
            </tr>
        </table>
    </div>

<?php } ?>

</div>

</body>
</html>