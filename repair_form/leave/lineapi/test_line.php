<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/line_sender.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

$targetId = trim((string)($_POST['target_id'] ?? 'Udcb9130b37083ee5b2aa3ecfe119e8e2'));
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bubble = array(
        'type' => 'bubble',
        'body' => array(
            'type' => 'box',
            'layout' => 'vertical',
            'spacing' => 'md',
            'contents' => array(
                array('type'=>'text','text'=>'🏥 ระบบการลางาน','weight'=>'bold','size'=>'xl','color'=>'#0A5966'),
                array('type'=>'text','text'=>'โรงพยาบาลภักดีชุมพล','size'=>'sm','color'=>'#6D7C82'),
                array('type'=>'separator','margin'=>'md'),
                array('type'=>'text','text'=>'✅ PHP → Apps Script → LINE Messaging API ทำงานเรียบร้อย','wrap'=>true,'margin'=>'md')
            )
        )
    );

    $result = leave_line_send_messages(
        $targetId,
        array(leave_line_flex_message('ทดสอบ LINE OA ระบบลางาน', $bubble)),
        'leave_php_test'
    );
}

function test_h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ทดสอบ LINE OA ระบบลา</title>
<style>
body{font-family:Tahoma,Arial,sans-serif;background:#eef5f6;margin:0;padding:35px;color:#173640}
.card{max-width:860px;margin:auto;background:#fff;border-radius:24px;padding:30px;box-shadow:0 16px 45px rgba(10,80,90,.12)}
input{width:100%;padding:13px;border:1px solid #ccdfe4;border-radius:12px;box-sizing:border-box}
button{width:100%;margin-top:12px;padding:13px;border:0;border-radius:12px;background:#0e877d;color:#fff;font-weight:700;cursor:pointer}
.ok{color:#087657}.bad{color:#c93345}
pre{background:#f4f7f8;padding:18px;border-radius:14px;white-space:pre-wrap;word-break:break-word}
</style>
</head>
<body>
<div class="card">
<h1>ทดสอบ LINE OA ระบบลางาน</h1>
<p>แยกจาก LINE ของระบบแจ้งซ่อม / vehicle</p>
<form method="post">
<label>LINE User ID / Group ID</label>
<input name="target_id" value="<?= test_h($targetId) ?>" required>
<button>ส่งข้อความทดสอบ</button>
</form>
<?php if ($result !== null): ?>
<h3 class="<?= !empty($result['ok']) ? 'ok' : 'bad' ?>">
<?= !empty($result['ok']) ? '✅ ส่งสำเร็จ' : '❌ ส่งไม่สำเร็จ' ?>
</h3>
<pre><?= test_h(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
<?php endif; ?>
<p><a href="../line_accounts.php">← จัดการ LINE OA</a></p>
</div>
</body>
</html>
