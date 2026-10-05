<?php
require_once __DIR__ . '/config_vehicle.php';
if (!$currentVehicleIsAdmin) { http_response_code(403); die('403 Forbidden: Admin เท่านั้น'); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') vehicle_redirect('porobo.php');
vehicle_check_csrf();

$action = (string)($_POST['action'] ?? 'scan');
if ($action === 'single') {
    $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
    if ($vehicleId <= 0) {
        vehicle_flash('danger','ไม่พบรถที่ต้องการส่งแจ้งเตือน');
        vehicle_redirect('porobo.php');
    }
    $summary = vehicle_porobo_scan_and_send(true, $vehicleId);
    if ($summary['sent'] > 0) vehicle_flash('success','ส่งแจ้งเตือน พ.ร.บ. เข้า LINE เรียบร้อยแล้ว');
    elseif ($summary['failed'] > 0) vehicle_flash('danger','ส่ง LINE ไม่สำเร็จ กรุณาตรวจ Apps Script / LINE Messaging API');
    else vehicle_flash('warning','ยังไม่มีข้อมูลวันหมดอายุ พ.ร.บ. สำหรับรถคันนี้');
    vehicle_redirect('porobo.php');
}

$summary = vehicle_porobo_scan_and_send(false, 0);
if (!empty($summary['error'])) {
    vehicle_flash('danger',$summary['error']);
} elseif ($summary['sent'] > 0 && $summary['failed'] === 0) {
    vehicle_flash('success','ตรวจสอบ พ.ร.บ. แล้ว ส่ง LINE ใหม่ '.$summary['sent'].' รายการ และข้ามรายการที่ยังไม่ถึงกำหนด/เคยส่งแล้ว '.$summary['skipped'].' รายการ');
} elseif ($summary['failed'] > 0) {
    vehicle_flash('warning','ตรวจสอบแล้ว ส่งสำเร็จ '.$summary['sent'].' รายการ ส่งไม่สำเร็จ '.$summary['failed'].' รายการ กรุณาทดสอบ LINE');
} else {
    vehicle_flash('info','ตรวจสอบแล้ว ยังไม่มีแจ้งเตือน พ.ร.บ. ใหม่ที่ต้องส่ง (ข้าม '.$summary['skipped'].' รายการ)');
}
vehicle_redirect('porobo.php');
