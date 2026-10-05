<?php

/**
 * ==========================================================
 * LINE OA ระบบลางาน - โรงพยาบาลภักดีชุมพล
 * แยกออกจาก lineapi ของระบบแจ้งซ่อม / vehicle
 * ==========================================================
 *
 * หมายเหตุ:
 * - Channel Access Token เก็บที่ Google Apps Script > Script Properties เท่านั้น
 * - PHP เก็บเฉพาะ Apps Script URL + Shared Secret
 * - target_id ของผู้รับจะส่งจาก PHP ไป Apps Script เป็นรายบุคคล
 */

if (!defined('LEAVE_LINE_APPS_SCRIPT_URL')) {
    define(
        'LEAVE_LINE_APPS_SCRIPT_URL',
        'https://script.google.com/macros/s/AKfycbzBtyAtQEcFLgCM7cxga7m1l1aeX5JuMHQsSbOnWeRddA3qSnHeJFf3E7uF4R8IQKSeaQ/exec'
    );
}

if (!defined('LEAVE_LINE_APPS_SCRIPT_SHARED_SECRET')) {
    define(
        'LEAVE_LINE_APPS_SCRIPT_SHARED_SECRET',
        'PDCLeave_2026_Ln4Q8v2R7s9M'
    );
}

/**
 * LINE User ID สำรอง / ผู้ดูแลที่ต้องการรับสำเนาการแจ้งเตือน
 * รองรับหลาย ID คั่นด้วย comma
 *
 * U... = User ID
 * C... = Group ID
 * R... = Room ID
 */
if (!defined('LEAVE_LINE_DEFAULT_TARGETS')) {
    define(
        'LEAVE_LINE_DEFAULT_TARGETS',
        'Udcb9130b37083ee5b2aa3ecfe119e8e2'
    );
}

/**
 * URL สาธารณะของระบบลา
 * ใช้สร้างปุ่มใน LINE Flex Message
 * เปลี่ยน ngrok URL เมื่อ URL ใหม่เปลี่ยน
 */
if (!defined('LEAVE_PUBLIC_URL')) {
    define(
        'LEAVE_PUBLIC_URL',
        'https://unveiling-unroll-sherry.ngrok-free.dev/Wedphakdee/repair_form/leave'
    );
}

/**
 * Secret สำหรับเซ็น URL ปุ่มอนุมัติ/ไม่อนุมัติ
 * เก็บเฉพาะฝั่ง PHP
 */
if (!defined('LEAVE_LINE_ACTION_SECRET')) {
    define(
        'LEAVE_LINE_ACTION_SECRET',
        'PDCLeaveAction_2026_A7mQ4xR9K2v'
    );
}

if (!defined('LEAVE_LINE_CURL_SSL_VERIFY')) {
    // localhost/AppServ บางเครื่องไม่มี CA bundle
    // Production ที่ตั้ง CA ถูกต้อง แนะนำ true
    define('LEAVE_LINE_CURL_SSL_VERIFY', false);
}

if (!defined('LEAVE_LINE_CURL_CONNECT_TIMEOUT')) {
    define('LEAVE_LINE_CURL_CONNECT_TIMEOUT', 15);
}

if (!defined('LEAVE_LINE_CURL_TIMEOUT')) {
    define('LEAVE_LINE_CURL_TIMEOUT', 35);
}
