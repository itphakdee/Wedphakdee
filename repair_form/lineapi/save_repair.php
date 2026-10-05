<?php
/**
 * Compatibility handler สำหรับลิงก์เก่า
 * ให้ส่ง POST ต่อไปยัง handler จริงใน repair_form/computer/save_repair.php
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/../repair_form/computer/save_repair.php';
    exit;
}

header('Location: ../repair_form/computer/repair_form.php');
exit;
