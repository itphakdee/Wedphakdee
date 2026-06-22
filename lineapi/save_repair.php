<?php
// --- 1. ตั้งค่าเชื่อมต่อฐานข้อมูล (เหมือนเดิม) ---
$host = 'localhost';
$db   = 'login_db'; // เปลี่ยนชื่อฐานข้อมูลของคุณ
$user = 'root';              // User ฐานข้อมูล
$pass = '12345678';    
// --- 2. ตั้งค่า LINE API ---
$accessToken = "gHWAS2WB2oCsAdNNk9wz+kRePjgphps9OZkCExPlzjHNiBXFkFc8QEVYBXxdExm7Wube+RNDt2HGLcsKtAO8lDGrkT0nLuWPqCPakcRtFjmbctzhP9579F0JopMv2gJfLikVqL97txHCxlaivPoyCAdB04t89/1O/w1cDnyilFU="; 
$groupId     = "Ce213eaf090cda6b4de630f90c70e7657"; // ต้องเป็นไอดีกลุ่มที่ขึ้นต้นด้วยตัว c...

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $sender_name = $_POST['sender_name'];
        $department  = $_POST['department'];
        $details     = $_POST['details'];
        $location    = $_POST['location'];
        $technician_id = $_POST['technician_id'];

        // บันทึกลงตาราง 
        $sql = "INSERT INTO repair_jobs (sender_name, department, details, location , technician_id) VALUES (?, ?, ?, ? ,? )";
        // echo "<script>alert(" . json_encode($sql) . ");</script>";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sender_name, $department, $details, $location ,$technician_id]);

        // --- 3. เตรียมข้อความแจ้งเตือนกลุ่ม ---
        $messageText = "🆘 [แจ้งซ่อมใหม่]\n" .
                       "--------------------------\n" .
                       "👤 ผู้แจ้ง: $sender_name\n" .
                       "🏢 แผนก: $department\n" .
                       "📝 รายละเอียด: $details\n" .
                       "--------------------------\n" .
                       "⏰ เวลา: " . date("Y-m-d H:i:s");

        $url = 'https://api.line.me/v2/bot/message/push';
        $data = [
            'to' => $groupId, // เปลี่ยนจาก userId เป็น groupId
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $messageText
                ]
            ]
        ];

        $post = json_encode($data);
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ];

        // --- 4. ส่งด้วย CURL ---
        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // $result = curl_exec($ch);
        // curl_close($ch);

        echo "<script>alert('ส่งแจ้งเตือนเข้ากลุ่มเรียบร้อย!'); window.location='../repair_form/computer/indexrepairlist.php';</script>";
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}       
?>