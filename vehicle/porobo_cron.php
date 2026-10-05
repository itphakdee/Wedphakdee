<?php
define('VEHICLE_CRON_MODE', true);
require_once __DIR__ . '/config_vehicle.php';
require_once __DIR__ . '/../lineapi/line_apps_script_config.php';
header('Content-Type: application/json; charset=utf-8');

$provided = isset($_GET['token']) ? (string)$_GET['token'] : '';
if ($provided === '' && isset($_SERVER['HTTP_X_VEHICLE_CRON_TOKEN'])) $provided = (string)$_SERVER['HTTP_X_VEHICLE_CRON_TOKEN'];
$expected = defined('VEHICLE_POROBO_CRON_TOKEN') ? (string)VEHICLE_POROBO_CRON_TOKEN : '';

if ($expected === '' || !hash_equals($expected, $provided)) {
    http_response_code(403);
    echo json_encode(array('ok'=>false,'error'=>'Forbidden'), JSON_UNESCAPED_UNICODE);
    exit;
}

$summary = vehicle_porobo_scan_and_send(false, 0);
echo json_encode(array('ok'=>empty($summary['error']),'timestamp'=>date('c'),'summary'=>$summary), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
