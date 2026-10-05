<?php
require_once __DIR__ . '/config_leave.php';

if (!leave_is_admin()) {
    http_response_code(403);
    die('403 Forbidden: เฉพาะผู้ดูแลระบบเท่านั้นที่ติดตั้งฐานข้อมูลได้');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    leave_require_csrf();
    $sql = file_get_contents(__DIR__ . '/leave_full.sql');
    if ($sql === false) {
        $error = 'ไม่พบไฟล์ leave_full.sql';
    } elseif (!$conn->multi_query($sql)) {
        $error = 'ติดตั้งฐานข้อมูลไม่สำเร็จ: ' . $conn->error;
    } else {
        do {
            if ($result = $conn->store_result()) $result->free();
        } while ($conn->more_results() && $conn->next_result());

        // ให้ผู้ใช้ active เข้าเมนูลาและเพิ่ม/แก้ไขใบลาของตนเองได้ โดยไม่ให้สิทธิ์ delete/manage
        if (leave_table_exists('user_permissions')) {
            $conn->query("INSERT IGNORE INTO user_permissions(user_id,permission_key) SELECT id,'leave.view' FROM users WHERE status='active'");
            $conn->query("INSERT IGNORE INTO user_permissions(user_id,permission_key) SELECT id,'leave.create' FROM users WHERE status='active'");
            $conn->query("INSERT IGNORE INTO user_permissions(user_id,permission_key) SELECT id,'leave.edit' FROM users WHERE status='active'");
        }

        // สร้าง mapping หัวหน้าจาก users.supervisor_id ที่มีอยู่
        if (leave_column_exists('users', 'supervisor_id')) {
            $conn->query("INSERT INTO leave_supervisors(department_id,department_name,supervisor_user_id,created_by)
                          SELECT DISTINCT u.department_id, COALESCE(NULLIF(u.department,''), CONCAT('หน่วยงาน-',u.department_id)), u.supervisor_id, " . (int)$_SESSION['user_id'] . "
                          FROM users u
                          JOIN users s ON s.id=u.supervisor_id AND s.status='active'
                          WHERE u.status='active' AND u.supervisor_id IS NOT NULL AND u.supervisor_id>0
                            AND (u.department IS NOT NULL OR u.department_id IS NOT NULL)
                          ON DUPLICATE KEY UPDATE supervisor_user_id=VALUES(supervisor_user_id), department_id=VALUES(department_id)");
        }

        $message = 'ติดตั้ง / อัปเดตฐานข้อมูลระบบลางานเรียบร้อยแล้ว';
    }
}
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ติดตั้งระบบลางาน</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            background: #f4f8f8;
            color: #17343d;
            margin: 0
        }

        .box {
            max-width: 760px;
            margin: 70px auto;
            background: #fff;
            border: 1px solid #d8e6e6;
            border-radius: 24px;
            padding: 34px;
            box-shadow: 0 20px 50px rgba(15, 76, 78, .12)
        }

        h1 {
            margin: 0 0 10px
        }

        .notice {
            padding: 15px 18px;
            border-radius: 14px;
            margin: 18px 0
        }

        .ok {
            background: #e9f8f1;
            color: #14603e
        }

        .err {
            background: #fff0f0;
            color: #9d2b2b
        }

        .btn {
            display: inline-block;
            border: 0;
            border-radius: 12px;
            padding: 13px 20px;
            background: #0c6b6e;
            color: white;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer
        }

        .ghost {
            background: #eef5f5;
            color: #174b52;
            margin-left: 8px
        }

        code {
            background: #eef4f5;
            padding: 2px 6px;
            border-radius: 6px
        }
    </style>
</head>

<body>
    <div class="box">
        <div style="font-size:12px;letter-spacing:.15em;color:#168486;font-weight:700">PHAKDEE CHUMPHON HOSPITAL</div>
        <h1>ติดตั้งระบบบริหารการลางาน</h1>
        <p>สร้างตารางใบลา, ประเภทการลา, หัวหน้างาน, ยอดวันลา และประวัติการทำรายการ โดยไม่ลบตารางเดิม</p>
        <?php if ($message): ?><div class="notice ok"><?= leave_e($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice err"><?= leave_e($error) ?></div><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf_token" value="<?= leave_e(leave_csrf_token()) ?>"><button class="btn" type="submit">ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้</button><a class="btn ghost" href="index.php">กลับระบบลางาน</a></form>
        <p style="margin-top:22px;color:#6c7d82;font-size:13px">ไฟล์ SQL: <code>repair_form/leave/leave_full.sql</code></p>
    </div>
</body>

</html>