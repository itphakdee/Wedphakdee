<?php
include("config.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($fullname) || empty($username) || empty($password) || empty($confirm_password)) {
        $message = "กรุณากรอกข้อมูลให้ครบ";
    } elseif ($password !== $confirm_password) {
        $message = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (fullname, username, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fullname, $username, $hashed_password);

            if ($stmt->execute()) {
                $message = "สมัครสมาชิกสำเร็จ <a href='login.php'>เข้าสู่ระบบ</a>";
            } else {
                $message = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <form class="form-box" method="POST" action="">
            <h2>สมัครสมาชิก</h2>

            <?php if (!empty($message)) { ?>
                <div class="message"><?php echo $message; ?></div>
            <?php } ?>

            <input type="text" name="fullname" placeholder="ชื่อ-นามสกุล" required>
            <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" required>
            <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>

            <button type="submit">สมัครสมาชิก</button>

            <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        </form>
    </div>
</body>
</html>