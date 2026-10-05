<?php
session_start();
require_once 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($username) || empty($password)) {

        $message = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";

    } else {

        $stmt = $conn->prepare("
            SELECT
                id,
                fullname,
                username,
                password,
                role,
                status
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            //ตรวจสอบสถานะบัญชี
            if (isset($user["status"]) && $user["status"] !== "active") {
                $message = "บัญชีนี้ถูกระงับการใช้งาน";
            }

            //ตรวจสอบรหัสผ่าน
            elseif (password_verify($password, $user["password"])) {

                // ป้องกัน Session Fixation
                session_regenerate_id(true);

                // SESSION หลัก
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["fullname"] = $user["fullname"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                // ใช้ทำ timeout
                $_SESSION["last_activity"] = time();

                $_SESSION["login_success"] = true;

                // log เวลา login
                $_SESSION["login_time"] = date("Y-m-d H:i:s");

                header("Location: dashboard.php");
                exit();

            } else {
                $message = "❌ รหัสผ่านไม่ถูกต้อง";
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
<title>Backoffice Phakdee - เข้าสู่ระบบ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700;800&family=Mitr:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/stylelogins.css">
</head>
<body>

  <!-- ===== Sky + clouds (ตัวอย่าง) ===== -->
  <div class="sky" aria-hidden="true"></div>

  <svg class="cloud c1" viewBox="0 0 200 90" aria-hidden="true">
    <path d="M30 70 C10 70 0 55 8 42 C2 28 18 14 34 18 C40 4 64 0 76 12 C92 6 112 14 114 32 C132 30 146 44 140 58 C148 70 138 82 124 80 L30 80 Z" fill="#ffffff"/>
  </svg>
  <svg class="cloud c2" viewBox="0 0 160 70" aria-hidden="true">
    <path d="M24 56 C8 56 0 44 8 33 C4 21 18 10 32 14 C38 2 58 -2 68 8 C82 2 100 10 100 25 C115 23 126 35 120 47 C126 57 118 66 106 64 L24 64 Z" fill="#ffffff"/>
  </svg>
  <svg class="cloud c3" viewBox="0 0 220 100" aria-hidden="true">
    <path d="M34 78 C12 78 0 60 10 46 C2 30 20 14 38 18 C46 2 74 -2 88 12 C106 4 130 14 132 34 C152 32 168 48 160 64 C170 78 158 92 142 90 L34 90 Z" fill="#ffffff"/>
  </svg>
  <svg class="cloud c4" viewBox="0 0 140 60" aria-hidden="true">
    <path d="M20 48 C6 48 0 38 6 28 C2 18 14 8 26 12 C32 2 48 -2 56 6 C68 0 84 8 84 21 C98 19 108 30 102 40 C108 48 100 56 90 54 L20 54 Z" fill="#ffffff"/>
  </svg>

  <!-- ===== Background photo collage (replace src with real hospital photos) ===== -->
  <div class="bg-collage" aria-hidden="true">
    <div class="bg-tile"><img src="assets/images/bg1.jpg" alt=""></div>
    <div class="bg-tile"><img src="assets/images/bg2.jpg" alt=""></div>
    <div class="bg-tile"><img src="assets/images/bg3.jpg" alt=""></div>
    <div class="bg-tile"><img src="assets/images/bg4.jpg" alt=""></div>
  </div>
  <div class="bg-overlay" aria-hidden="true"></div>
  <div class="light-rays" aria-hidden="true"></div>
  <div class="sun-glow" aria-hidden="true"></div>

  <svg class="sparkle s1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0l1.8 8.2L22 10l-8.2 1.8L12 20l-1.8-8.2L2 10l8.2-1.8L12 0Z"/></svg>
  <svg class="sparkle s2" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0l1.8 8.2L22 10l-8.2 1.8L12 20l-1.8-8.2L2 10l8.2-1.8L12 0Z"/></svg>
  <svg class="sparkle s3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0l1.8 8.2L22 10l-8.2 1.8L12 20l-1.8-8.2L2 10l8.2-1.8L12 0Z"/></svg>
  <svg class="sparkle s4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0l1.8 8.2L22 10l-8.2 1.8L12 20l-1.8-8.2L2 10l8.2-1.8L12 0Z"/></svg>

  <!-- decorative leaf branches hanging from the top (ตัวอย่าง) -->
  <svg class="leaf-branch b1" viewBox="0 0 260 300" aria-hidden="true">
    <!-- main stem -->
    <path d="M40 0 C50 40 70 70 90 110 C105 140 100 175 85 205" stroke="#3d6b3f" stroke-width="5" fill="none" stroke-linecap="round"/>
    <!-- side twig -->
    <path d="M70 95 C85 100 100 95 112 80" stroke="#3d6b3f" stroke-width="3" fill="none" stroke-linecap="round"/>
    <!-- leaves along stem -->
    <g>
      <path d="M40 0 C20 10 8 35 15 55 C35 50 48 25 40 0Z" fill="#3aa757"/>
      <path d="M40 0 C60 8 75 28 70 50 C50 48 35 25 40 0Z" fill="#57c46e"/>
      <path d="M58 35 C38 38 22 58 25 80 C47 80 62 58 58 35Z" fill="#2c8c4a"/>
      <path d="M58 35 C78 40 90 62 85 84 C63 82 50 58 58 35Z" fill="#57c46e"/>
      <path d="M90 110 C68 110 50 128 48 150 C70 152 88 132 90 110Z" fill="#3aa757"/>
      <path d="M90 110 C112 113 128 134 124 156 C102 156 88 134 90 110Z" fill="#2c8c4a"/>
      <path d="M112 80 C128 70 148 72 160 88 C146 100 124 98 112 80Z" fill="#57c46e"/>
      <path d="M85 205 C65 208 50 228 50 250 C72 250 88 230 85 205Z" fill="#2c8c4a"/>
      <path d="M85 205 C105 210 118 232 114 254 C92 252 80 230 85 205Z" fill="#3aa757"/>
    </g>
  </svg>

  <svg class="leaf-branch b2" viewBox="0 0 190 230" aria-hidden="true">
    <path d="M95 0 C92 30 100 55 115 78 C128 98 126 122 112 145" stroke="#3d6b3f" stroke-width="4" fill="none" stroke-linecap="round"/>
    <g>
      <path d="M95 0 C78 10 68 30 74 50 C92 48 102 22 95 0Z" fill="#57c46e"/>
      <path d="M95 0 C112 10 120 32 113 52 C95 50 86 22 95 0Z" fill="#3aa757"/>
      <path d="M112 55 C92 56 78 74 80 95 C100 95 114 76 112 55Z" fill="#2c8c4a"/>
      <path d="M112 55 C132 58 144 78 140 98 C120 96 108 76 112 55Z" fill="#57c46e"/>
      <path d="M112 145 C94 148 82 165 84 185 C104 184 116 166 112 145Z" fill="#3aa757"/>
      <path d="M112 145 C130 150 140 168 136 188 C118 186 108 166 112 145Z" fill="#2c8c4a"/>
    </g>
  </svg>

  <svg class="leaf-branch b3" viewBox="0 0 240 280" aria-hidden="true">
    <path d="M30 0 C45 35 65 60 82 95 C95 122 92 152 78 178" stroke="#3d6b3f" stroke-width="5" fill="none" stroke-linecap="round"/>
    <path d="M62 85 C78 92 96 88 108 72" stroke="#3d6b3f" stroke-width="3" fill="none" stroke-linecap="round"/>
    <g>
      <path d="M30 0 C12 12 2 35 10 56 C30 52 42 25 30 0Z" fill="#2c8c4a"/>
      <path d="M30 0 C48 10 60 32 54 54 C34 50 22 24 30 0Z" fill="#57c46e"/>
      <path d="M50 40 C30 44 16 64 20 86 C42 84 56 62 50 40Z" fill="#3aa757"/>
      <path d="M50 40 C70 46 80 68 74 88 C54 84 42 62 50 40Z" fill="#2c8c4a"/>
      <path d="M82 95 C60 96 44 114 44 136 C66 138 82 118 82 95Z" fill="#57c46e"/>
      <path d="M82 95 C104 98 118 118 114 140 C92 140 80 118 82 95Z" fill="#3aa757"/>
      <path d="M108 72 C124 64 144 66 154 82 C142 94 120 92 108 72Z" fill="#2c8c4a"/>
      <path d="M78 178 C58 182 44 200 46 222 C68 222 82 202 78 178Z" fill="#3aa757"/>
    </g>
  </svg>

  <!-- decorative bottom wave -->
  <svg class="wave-bottom" viewBox="0 0 1200 160" preserveAspectRatio="none" style="width:100%; height:140px;" aria-hidden="true">
    <path d="M0,90 C200,140 350,40 600,70 C850,100 1000,40 1200,80 L1200,160 L0,160 Z" fill="#1f6b3a" opacity="0.85"/>
    <path d="M0,110 C220,150 380,70 620,100 C860,130 1020,70 1200,110 L1200,160 L0,160 Z" fill="#2c8c4a" opacity="0.6"/>
  </svg>

  <div class="login-wrap">

    <!-- ===== Logo (ตัวอย่าง: วาดด้วย SVG แทนโลโก้จริงของโรงพยาบาล) ===== -->
   <div class="logo-badge">
  <img src="assets/images/logo.png" alt="โลโก้" style="width:85%; height:85%; object-fit:contain;">
</div>

    <div class="login-card">
      <h1 class="org-name">โรงพยาบาลภักดีชุมพล</h1>
      <div class="app-name">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M2 12h4l2-6 4 12 3-9 2 5h5" stroke="#2c8c4a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Backoffice </span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M2 12h4l2-6 4 12 3-9 2 5h5" stroke="#2c8c4a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <p class="subtitle">ระบบบริหารจัดการภายในองค์กร</p>

      <form method="POST" action="" >
        <div class="field">
          <span class="icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5Zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5Z"/></svg>
          </span>
                    <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
        </div>

        <div class="field">
          <span class="icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a4 4 0 0 0-4 4v3H7a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-1V6a4 4 0 0 0-4-4Zm-2 7V6a2 2 0 1 1 4 0v3Z"/></svg>
          </span>
                    <input type="password" id="pwd" name="password" placeholder="รหัสผ่าน" required>
          <span class="eye" id="toggleEye" title="แสดง/ซ่อนรหัสผ่าน">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
          </span>
        </div>
    <?php if (!empty($message)) { ?>
                    <div class="error"><?php echo htmlspecialchars($message); ?></div>
                <?php } ?>

        <button type="submit" class="btn-login">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a4 4 0 0 0-4 4v3H7a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-1V6a4 4 0 0 0-4-4Zm-2 7V6a2 2 0 1 1 4 0v3Z"/></svg>
          เข้าสู่ระบบ
        </button>
      </form>

      <div class="divider">หรือ</div>
     <a href="register.php">
      <button type="button" class="btn-signup">
   
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
        สมัครสมาชิก
      </button>
     </a>
    </div>

    <div class="features">
      <div class="feature">
        <div class="circle">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4Z"/><path d="M12 8v4M12 16h.01"/></svg>
        </div>
        <span>ปลอดภัย</span>
      </div>
      <div class="feature">
        <div class="circle">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20v-1a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v1"/><path d="M16 14a4 4 0 0 1 4 4v2"/></svg>
        </div>
        <span>รวดเร็ว</span>
      </div>
      <div class="feature">
        <div class="circle">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h4l2-6 4 12 3-9 2 5h5"/></svg>
        </div>
        <span>ใส่ใจบริการ</span>
      </div>
      <div class="feature">
        <div class="circle">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l5-5 4 4 8-8"/><path d="M15 8h5v5"/></svg>
        </div>
        <span>พัฒนาต่อเนื่อง</span>
      </div>
    </div>
 
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            <?php if (!empty($message)) { ?>
                var errorModalElement = document.getElementById('errorModal');
                if (errorModalElement && window.bootstrap) {
                    var errorModal = new bootstrap.Modal(errorModalElement);
                    errorModal.show();
                }
            <?php } ?>
        </script>
  <script src="assets/js/script.js"></script>

</body>
</html>
