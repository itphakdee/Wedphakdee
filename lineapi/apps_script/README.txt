โรงพยาบาลภักดีชุมพล - LINE Messaging API
========================================

1) Google Apps Script
- เปิด Apps Script โปรเจกต์เดิม
- แทน Code.gs ด้วยไฟล์ Code.gs ในโฟลเดอร์นี้
- ถ้า Script Properties เดิมมี Token/Target/Secret อยู่แล้ว ไม่ต้อง Run setupConfig ใหม่
- Run checkConfig ต้องเห็น TOKEN=true, TARGET_TYPE=GROUP, SECRET=true
- Run testSendLine ต้องได้ HTTP Status 200

2) Deploy Web App ใหม่หลังแก้ Code.gs
- Deploy > Manage deployments
- Edit deployment
- Version: New version
- Execute as: Me
- Who has access: Anyone
- Deploy

3) PHP
ไฟล์ line_apps_script_config.php มี URL /exec และ WEBHOOK_SECRET แล้ว
เปิดทดสอบ:
http://localhost/Wedphakdee/lineapi/test_line.php

ถ้าสำเร็จควรได้ HTTP 200 และ JSON ที่มี:
"ok":true
"line_status":200

4) ทดสอบหน้าแจ้งซ่อม
http://localhost/Wedphakdee/repair_form/computer/repair_form.php

5) ความปลอดภัย
Channel Access Token ที่เคยแสดงในแชตหรือรูปควร Reissue ใหม่ก่อนใช้งานจริง
Token ควรเก็บใน Script Properties เท่านั้น ไม่ควรเก็บในไฟล์ PHP
