โรงพยาบาลภักดีชุมพล - ระบบยานพาหนะ + LINE Messaging API
================================================================

เส้นทางการทำงาน
PHP ระบบยานพาหนะ
  -> Google Apps Script Web App
  -> LINE Messaging API
  -> LINE Group / User

ไฟล์ตั้งค่าฝั่ง PHP
Wedphakdee/lineapi/line_apps_script_config.php

Public URL ที่ระบบใช้แนบในข้อความ LINE
https://unveiling-unroll-sherry.ngrok-free.dev/Wedphakdee/vehicle

หาก ngrok เปลี่ยน URL ให้แก้ค่า:
VEHICLE_PUBLIC_BASE_URL

Google Apps Script
------------------
ใช้ไฟล์:
Wedphakdee/lineapi/apps_script/Code.gs

Script Properties ต้องมี:
LINE_CHANNEL_ACCESS_TOKEN = Channel Access Token ของ LINE Messaging API
LINE_TARGET_ID            = Group ID / User ID / Room ID
WEBHOOK_SECRET            = ต้องตรงกับ LINE_APPS_SCRIPT_SHARED_SECRET ฝั่ง PHP

การ Deploy Apps Script:
1. Deploy > Manage deployments
2. Edit deployment
3. New version
4. Execute as: Me
5. Who has access: Anyone
6. Deploy

ทดสอบ Gateway
--------------
Local:
http://localhost/Wedphakdee/lineapi/test_line.php

Public/ngrok:
https://unveiling-unroll-sherry.ngrok-free.dev/Wedphakdee/lineapi/test_line.php

เหตุการณ์ที่ส่ง LINE
--------------------
1. สร้างคำขอใช้รถใหม่
2. หัวหน้างานรับรอง / ไม่รับรอง
3. Admin เปลี่ยนสถานะ / จัดรถ / จัดพนักงานขับ
4. ผู้ใช้แจ้งยกเลิก
5. เพิ่ม / แก้ไข / ลบประวัติส่งซ่อมรถ

หมายเหตุ
-------
- ระบบบันทึกข้อมูลลง MySQL ก่อนส่ง LINE
- LINE ล่มหรือ Apps Script ตอบกลับผิดพลาด ข้อมูลคำขอจะไม่หาย
- ผลการส่งล่าสุดของคำขอเก็บใน vehicle_requests.line_notify_status
  และ vehicle_requests.line_notify_response
- อย่าเก็บ Channel Access Token ใน PHP หรือแชร์ Token ในภาพ/แชต
