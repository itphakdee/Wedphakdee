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
                $message = "❌ รหัสผ่านไม่ถูกต้อง นะจ๊ะ";
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
    <title>backoffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, rgb(114, 241, 158), #ffffff, #17c46eff);
            margin: 0;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .box {
            width: 100%;
            max-width: 380px;
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 0 18px rgba(0, 128, 0, 0.12);
        }

        h2 {
            text-align: center;
            color: #1b4332;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid rgb(7, 15, 9);
            border-radius: 8px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2d6a4f;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 8px;
        }

        button:hover {
            background: #1b4332;
        }

        a {
            text-decoration: none;
            color: #2d6a4f;
        }

        .error {
            color: #c1121f;
            background: #ffe5e5;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        p {
            text-align: center;
            margin-top: 15px;
        }
        .logo-box {
    text-align: center;
    margin-bottom: 15px;
}

.logo-box img {
    width: 100px;      /* ปรับขนาดได้ */
    height: auto;
    border-radius: 12px;
}.logo-box img {
    transition: 0.3s;
}
.logo-box img:hover {
    transform: scale(1.05);
} 
.brand {
    font-size: 26px;
    font-weight: bold;
    background: linear-gradient(90deg, #0a7a3d, #6ee7a1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
        
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="logo-box">
    <img src="images/1.png" alt="logo">
</div>
 
             <h2>โรงพยาบาลภักดีชุมพล</h2>
                    <p class="brand">ฺBackofficePhakdee</p>

            <?php if (!empty($message)) { ?>
                <div class="error"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <form method="POST" action="">
                <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
                <input type="password" name="password" placeholder="รหัสผ่าน" required>
                <button type="submit">เข้าสู่ระบบ</button>
            </form>

            <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
     
        </div>
    </div>

    <!-- Modal for Error Message -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (!empty($message)) { ?>
            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        <?php } ?>
    </script>
</body>
</html>