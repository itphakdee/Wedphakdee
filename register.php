<?php
include("config.php");

$message = "";
$register_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($fullname) || empty($username) || empty($password) || empty($confirm_password)) {
        $message = "กรุณากรอกข้อมูลให้ครบ";
        $register_status = "fail";
    } elseif ($password !== $confirm_password) {
        $message = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
        $register_status = "fail";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
            $register_status = "fail";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (fullname, username, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fullname, $username, $hashed_password);

            if ($stmt->execute()) {
                $message = "สมัครสมาชิกสำเร็จ";
                $register_status = "success";
            } else {
                $message = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
                $register_status = "fail";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <form class="form-box p-4 shadow rounded bg-white" method="POST" action="">
            <h2 class="text-center mb-4">สมัครสมาชิก</h2>

            <input type="text" name="fullname" placeholder="ชื่อ-นามสกุล" required>
            <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" required>
            <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>

            <button class="btn btn-success w-100" type="submit">สมัครสมาชิก</button>

            <p class="text-center mt-3">มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        </form>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header <?php echo ($register_status === 'success') ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                    <h5 class="modal-title"><?php echo ($register_status === 'success') ? 'สำเร็จ' : 'ล้มเหลว'; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn <?php echo ($register_status === 'success') ? 'btn-success' : 'btn-danger'; ?>" data-bs-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($register_status)) { ?>
        <script>
            var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();

            <?php if ($register_status === 'success') { ?>
                document.getElementById('registerModal').addEventListener('hidden.bs.modal', function() {
                    window.location.href = 'login.php';
                });
            <?php } ?>
        </script>
    <?php } ?>
</body>

</html>
