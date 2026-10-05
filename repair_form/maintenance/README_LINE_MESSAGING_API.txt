โรงพยาบาลภักดีชุมพล - งานซ่อมบำรุง + LINE Messaging API

การทำงาน
PHP maintenance/save.php
  -> lineapi/line_apps_script_config.php
  -> Google Apps Script Web App (/exec)
  -> LINE Messaging API Push
  -> LINE Group/User

สิ่งที่ต้องมี
1. lineapi/line_apps_script_config.php ต้องมี LINE_APPS_SCRIPT_URL และ LINE_APPS_SCRIPT_SHARED_SECRET
2. Apps Script Script Properties ต้องมี LINE_CHANNEL_ACCESS_TOKEN, LINE_TARGET_ID, WEBHOOK_SECRET
3. WEBHOOK_SECRET ต้องตรงกับ LINE_APPS_SCRIPT_SHARED_SECRET
4. Deploy Apps Script เป็น Web App และใช้ /exec

หลังวาง PATCH
- Admin เปิด repair_form/maintenance/install.php แล้วกดติดตั้ง/อัปเดตฐานข้อมูล 1 ครั้ง
- ทดสอบ lineapi/test_line.php ให้ HTTP 200 และ ok=true
- จากนั้นส่งฟอร์ม repair_form/maintenance/index.php#new-request

ระบบจะบันทึกฐานข้อมูลก่อน แล้วค่อยส่ง LINE ดังนั้น LINE ล่ม งานที่แจ้งจะไม่หาย
