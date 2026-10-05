LINE OA Gateway - ระบบลางาน (แยกจากระบบแจ้งซ่อม)

1. สร้าง Google Apps Script Project ใหม่สำหรับระบบลา
2. วาง Code.gs จากโฟลเดอร์นี้
3. ใน setupLeaveConfig():
   - ใส่ Channel Access Token ใน NEW_TOKEN ครั้งแรก
   - ตั้ง DEFAULT_TARGET_ID ได้ถ้าต้องการทดสอบส่งเข้ากลุ่ม
   - เปลี่ยน SECRET ให้ตรงกับ PHP
4. Run setupLeaveConfig()
5. Run checkLeaveConfig()
6. Deploy > Web app
   Execute as: Me
   Who has access: Anyone
7. Copy URL ที่ลงท้าย /exec
8. ใส่ URL ลง repair_form/leave/lineapi/line_apps_script_config.php
9. ให้ LEAVE_LINE_APPS_SCRIPT_SHARED_SECRET ตรงกับ LEAVE_WEBHOOK_SECRET

ระบบนี้รองรับ body.target_id จาก PHP จึงส่งตรงไป LINE User ID ของหัวหน้า/ผู้ลา/ผู้รับมอบงานได้
