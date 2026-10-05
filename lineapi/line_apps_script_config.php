<?php

/**
 * LINE Gateway configuration
 * โรงพยาบาลภักดีชุมพล
 *
 * หมายเหตุ:
 * - LINE Channel Access Token เก็บไว้ที่ Google Apps Script > Script Properties
 * - ไฟล์ PHP นี้เก็บเฉพาะ URL ของ Web App และ shared secret
 */

if (!defined('LINE_APPS_SCRIPT_URL')) {
    define(
        'LINE_APPS_SCRIPT_URL',
        'https://script.google.com/macros/s/AKfycbyt-7zldGI2Y5LpWvSLhOBXZVcVN-JW3PzrQggye5IMiFAT3Ai8-S80h6hZ0YunKA7yVA/exec'
    );
}

// ต้องตรงกับ WEBHOOK_SECRET ใน Script Properties ของ Apps Script
if (!defined('LINE_APPS_SCRIPT_SHARED_SECRET')) {
    define(
        'LINE_APPS_SCRIPT_SHARED_SECRET',
        'PDCRepair_2026_x7K9m2Q8v4'
    );
}

/**
 * AppServ/Windows บางเครื่องไม่มี CA certificate สำหรับ cURL
 * จึงตั้ง false เพื่อให้ localhost ทดสอบกับ Google Apps Script ได้ก่อน
 * เมื่อขึ้น Production ที่มี CA bundle ถูกต้อง แนะนำให้เปลี่ยนเป็น true
 */
if (!defined('LINE_CURL_SSL_VERIFY')) {
    define('LINE_CURL_SSL_VERIFY', false);
}

if (!defined('LINE_CURL_CONNECT_TIMEOUT')) {
    define('LINE_CURL_CONNECT_TIMEOUT', 15);
}

if (!defined('LINE_CURL_TIMEOUT')) {
    define('LINE_CURL_TIMEOUT', 35);
}


// Secret สำหรับเรียกตรวจ พ.ร.บ. อัตโนมัติจาก Scheduler / Apps Script
if (!defined('VEHICLE_POROBO_CRON_TOKEN')) {
    define('VEHICLE_POROBO_CRON_TOKEN', 'PDC_POROBO_2026_7mQ9x4K2v8R');
}
