<?php
include("config.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        $message = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } else {
        $stmt = $conn->prepare("SELECT id, fullname, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["fullname"] = $user["fullname"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["login_success"] = true;

                header("Location: dashboard.php");
                exit();
            } else {
                $message = "❌ รหัสผ่านไม่ถูกต้อง นะจ๊ะ++9998888";
            }
        } else {
            $message = "ไม่พบชื่อผู้ใช้นี้ในระบบ";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice Login</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stylelogin.css">
</head>

<body>

    <div class="bg-wrapper">
        <div class="grid">
            <img src="assets/images/bg1.jpg">
            <img src="assets/images/bg2.jpg">
            <img src="assets/images/bg3.jpg">
            <img src="assets/mages/bg4.jpg">
        </div>

        <div class="overlay"></div>

        <div class="login-box">

            <img src="assets/images/logo.png" class="logo">

            <h1  style="font-size:39px;">โรงพยาบาลภักดีชุมพล</h1>


            <div class="subtitle">
                <span></span>
                Backoffice Phakdee
                <span></span>
            </div>

            <p>ระบบบริหารจัดการภายในองค์กร</p>
            <form method="POST" action="">
                <div class="input-box">
                    <i class="bi bi-person-fill"></i>
                    <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
                </div>

                <div class="input-box">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="password" placeholder="รหัสผ่าน" required>
                    <i class="bi bi-eye"></i>
                </div>
                <?php if (!empty($message)) { ?>
                    <div class="error"><?php echo htmlspecialchars($message); ?></div>
                <?php } ?>

                <button class="login-btn">
                    <i class="bi bi-lock-fill"></i>

                    เข้าสู่ระบบ
                </button>
            </form>
            <div class="or">
                <div></div>
                หรือ
                <div></div>
            </div>

            <button class="register-btn">
                <i class="bi bi-person-plus"></i>
                <a href="register.php">สมัครสมาชิก</a>

            </button>

        </div>

        <div class="bottom-wave">


            <div class="features">

                <div>
                    <div class="circle">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>ปลอดภัย</h5>
                </div>

                <div>
                    <div class="circle">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5>รวดเร็ว</h5>
                </div>

                <div>
                    <div class="circle">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <h5>ใส่ใจบริการ</h5>
                </div>

                <div>
                    <div class="circle">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h5>พัฒนาต่อเนื่อง</h5>
                </div>

            </div>

        </div>
        <div class="modal fade" id="errorModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">⚠️ ข้อผิดพลาด</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            <?php if (!empty($message)) { ?>
                var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            <?php } ?>
        </script>
</div >

</body >
</html >
