<?php
/**
 * ทดสอบ PHP -> Google Apps Script -> LINE Messaging API
 * เปิด: http://localhost/Wedphakdee/lineapi/test_line.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/line_apps_script_config.php';

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$data = array(
    'secret' => LINE_APPS_SCRIPT_SHARED_SECRET,
    'event' => 'php_test',
    'message' => "🔧 ทดสอบ PHP → Apps Script → LINE\nโรงพยาบาลภักดีชุมพล\nเวลา: " . date('d/m/Y H:i:s')
);

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$httpCode = 0;
$response = '';
$error = '';
$errno = 0;
$effectiveUrl = '';

if (!function_exists('curl_init')) {
    $error = 'PHP cURL extension ยังไม่ได้เปิดใช้งาน';
} else {
    $ch = curl_init(LINE_APPS_SCRIPT_URL);
    curl_setopt_array($ch, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json'
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => defined('LINE_CURL_CONNECT_TIMEOUT') ? LINE_CURL_CONNECT_TIMEOUT : 15,
        CURLOPT_TIMEOUT => defined('LINE_CURL_TIMEOUT') ? LINE_CURL_TIMEOUT : 35,
        CURLOPT_SSL_VERIFYPEER => defined('LINE_CURL_SSL_VERIFY') ? (bool)LINE_CURL_SSL_VERIFY : false,
        CURLOPT_SSL_VERIFYHOST => (defined('LINE_CURL_SSL_VERIFY') && LINE_CURL_SSL_VERIFY) ? 2 : 0,
        CURLOPT_USERAGENT => 'PhakdeeChumphonRepairTest/1.0'
    ));

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if ($raw !== false) {
        $response = (string)$raw;
    }
}

$decoded = json_decode($response, true);
$isSuccess = $httpCode >= 200 && $httpCode < 300 && is_array($decoded) && !empty($decoded['ok']);
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ทดสอบ LINE Gateway</title>
<style>
body{font-family:Tahoma,Arial,sans-serif;background:#f3f7fa;color:#17324d;margin:0;padding:32px}
.card{max-width:900px;margin:auto;background:#fff;border:1px solid #dbe6ee;border-radius:18px;box-shadow:0 12px 32px rgba(25,63,90,.10);overflow:hidden}
.head{padding:24px 28px;background:linear-gradient(135deg,#0d5a74,#148f82);color:#fff}.head h1{margin:0 0 8px;font-size:26px}.body{padding:28px}.row{padding:12px 0;border-bottom:1px solid #edf2f5}.label{font-weight:700}.ok{color:#0a7d51}.bad{color:#bd2f2f}.warn{color:#9c6500}pre{white-space:pre-wrap;word-break:break-word;background:#f6f8fa;border-radius:12px;padding:16px;border:1px solid #e3e8ec}.btn{display:inline-block;margin-top:18px;padding:12px 18px;border-radius:10px;background:#0d6efd;color:#fff;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="head">
    <h1>ทดสอบ LINE Messaging API</h1>
    <div>PHP → Google Apps Script → LINE Group</div>
  </div>
  <div class="body">
    <div class="row"><span class="label">ผล:</span> <strong class="<?= $isSuccess ? 'ok' : 'bad' ?>"><?= $isSuccess ? '✅ สำเร็จ' : '❌ ไม่สำเร็จ' ?></strong></div>
    <div class="row"><span class="label">HTTP CODE:</span> <?= h($httpCode) ?></div>
    <div class="row"><span class="label">cURL ERROR NUMBER:</span> <?= h($errno) ?></div>
    <div class="row"><span class="label">cURL ERROR:</span> <?= h($error !== '' ? $error : 'ไม่มี Error') ?></div>
    <div class="row"><span class="label">Effective URL:</span><br><?= h($effectiveUrl) ?></div>
    <div class="row"><span class="label">Apps Script Response:</span><pre><?= h($response !== '' ? $response : 'ไม่มี Response') ?></pre></div>
    <?php if (is_array($decoded) && isset($decoded['line_status'])): ?>
      <div class="row"><span class="label">LINE HTTP:</span> <?= h($decoded['line_status']) ?></div>
    <?php endif; ?>
    <a class="btn" href="test_line.php">ทดสอบอีกครั้ง</a>
  </div>
</div>
</body>
</html>
