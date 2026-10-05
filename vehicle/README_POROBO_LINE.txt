ระบบแจ้งเตือน พ.ร.บ. รถ + LINE Group
โรงพยาบาลภักดีชุมพล

สิ่งที่เพิ่ม
- หน้า approvals.php แสดงรถใกล้หมดอายุ พ.ร.บ.
- สรุป หมดอายุ / ภายใน 7 วัน / 8-30 วัน / ปกติ / ยังไม่กำหนด
- กำหนดเลขที่ พ.ร.บ., บริษัท, วันเริ่ม, วันหมดอายุ, วันแจ้งล่วงหน้า ได้ที่ resources.php
- ส่ง LINE เข้ากลุ่มผ่าน vehicle_send_line -> Google Apps Script -> LINE Messaging API
- ปุ่มส่ง LINE ตอนนี้รายคัน
- ปุ่มตรวจและส่ง LINE ตามกำหนดทั้งระบบ
- ป้องกันการส่งระดับเดิมซ้ำในรอบวันหมดอายุเดียวกัน
- หน้า porobo_test.php สำหรับทดสอบ LINE Group
- porobo_cron.php สำหรับ Scheduler อัตโนมัติ
- porobo_scheduler.gs.txt ตัวอย่าง Apps Script Trigger ทุกวันประมาณ 08:00 น.

ขั้นตอนติดตั้ง
1. นำ PATCH ไปทับ Wedphakdee เดิม
2. Login Admin
3. เปิด http://localhost/Wedphakdee/vehicle/install.php
4. กด “ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้” 1 ครั้ง
5. เปิด http://localhost/Wedphakdee/vehicle/resources.php
6. กรอกข้อมูล พ.ร.บ. ของรถแต่ละคัน
7. เปิด http://localhost/Wedphakdee/vehicle/porobo.php
8. กด “ตรวจและส่ง LINE ตามกำหนด”
9. ทดสอบ LINE ที่ http://localhost/Wedphakdee/vehicle/porobo_test.php

เกณฑ์ LINE
- เข้าช่วงแจ้งล่วงหน้าตามค่ารถแต่ละคัน เช่น 30 วัน
- 7 วัน
- 1 วัน
- วันครบกำหนด
- หมดอายุแล้ว
ระบบไม่ส่ง alert_key เดิมซ้ำ ถ้าเคยส่งสำเร็จแล้ว

ตั้งเวลาอัตโนมัติ
- เปิด vehicle/porobo_scheduler.gs.txt
- เปลี่ยน PUBLIC_BASE_URL เป็น URL สาธารณะ เช่น ngrok ของคุณ
- TOKEN ต้องตรงกับ VEHICLE_POROBO_CRON_TOKEN ใน lineapi/line_apps_script_config.php
- Run createVehiclePoroboDailyTrigger() 1 ครั้ง

หมายเหตุ
- LINE Group ใช้ Group ID ที่ตั้งอยู่ใน Script Properties ของ Apps Script เดิม
- PHP ไม่เก็บ Channel Access Token
