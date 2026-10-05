<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. ต้อง login ก่อน
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


// 2. ตั้งเวลา timeout
$timeout = 1800; // 30 นาที

// 3. เช็ค last_activity
if (isset($_SESSION["last_activity"])) {

    $inactiveTime = time() - $_SESSION["last_activity"];

    if ($inactiveTime > $timeout) {

        // ล้าง session
        session_unset();
        session_destroy();

        // เด้งกลับ login พร้อม flag
        header("Location: login.php?timeout=1");
        exit();
    }
}

// 4. อัปเดตเวลาใช้งานล่าสุด
$_SESSION["last_activity"] = time();
