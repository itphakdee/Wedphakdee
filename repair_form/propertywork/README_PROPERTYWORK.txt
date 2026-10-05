ระบบทะเบียนครุภัณฑ์ โรงพยาบาลภักดีชุมพล

หน้าหลัก:
http://localhost/Wedphakdee/repair_form/propertywork/index.php

การติดตั้ง/อัปเดตฐานข้อมูล:
1. Login เข้าระบบ
2. เปิด http://localhost/Wedphakdee/repair_form/propertywork/install.php
3. กด "ติดตั้ง / อัปเดตฐานข้อมูล"
4. กลับไปหน้า index.php

คุณสมบัติ:
- Dashboard สรุปจำนวนครุภัณฑ์ มูลค่ารวม พร้อมใช้งาน ชำรุด/ส่งซ่อม
- แสดงรายการครุภัณฑ์จากฐานข้อมูลพร้อมราคา
- ค้นหาและกรองตามสถานะ ประเภท หน่วยงาน
- Pagination 1 2 3 / ก่อนหน้า / ถัดไป
- เพิ่ม แก้ไข ดูรายละเอียด ลบครุภัณฑ์
- อัปโหลดรูปครุภัณฑ์
- ส่งออก CSV และพิมพ์รายละเอียด
- CSRF และ Prepared Statement สำหรับงานบันทึก/แก้ไข/ลบ
- Responsive รองรับมือถือ

ฐานข้อมูล: login_db ตาราง properties
ไฟล์ SQL: propertywork.sql
