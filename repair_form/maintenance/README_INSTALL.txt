ระบบงานซ่อมบำรุง - โรงพยาบาลภักดีชุมพล
============================================================

URL หลัก
http://localhost/Wedphakdee/repair_form/maintenance/index.php

ฟังก์ชันที่จัดทำ
- แจ้งซ่อม: ชื่อผู้ส่ง, แผนก, ประปา/ไฟฟ้า/แอร์, เลือกช่าง, รายละเอียด
- เพิ่มสถานที่/จุดที่พบปัญหา และระดับความเร่งด่วน
- Dashboard สรุปจำนวนงานทั้งหมด/รอรับ/กำลังทำ/เสร็จสิ้น
- ค้นหาและกรองตามสถานะและประเภทระบบ
- ดูรายละเอียดงาน
- แก้ไขรายการ
- ช่าง/ผู้ดูแลเปลี่ยนสถานะและบันทึกหมายเหตุ
- ลบรายการตามสิทธิ์
- รองรับสิทธิ์ maintenance.view/create/edit/delete/manage
- Responsive รองรับมือถือ
- CSRF protection และ Prepared Statements

ฐานข้อมูล
1) วิธีง่ายที่สุด: เปิด
   http://localhost/Wedphakdee/repair_form/maintenance/install.php
   แล้วกด "ติดตั้งฐานข้อมูลตอนนี้" ด้วยบัญชี Admin

2) หรือ Import ไฟล์
   repair_form/maintenance/maintenance.sql
   เข้า database login_db ผ่าน phpMyAdmin

ตารางที่ระบบใช้
- maintenance_requests : ตารางใหม่สำหรับงานซ่อมบำรุง
- departments          : ใช้รายชื่อแผนกเดิมของระบบ
- technicians          : ใช้รายชื่อช่างเดิมของระบบ
- users                : ใช้ข้อมูลผู้ล็อกอิน

หมายเหตุ
- รายชื่อช่างจะกรองตามคอลัมน์ technicians.department เช่น ประปา, ไฟฟ้า, แอร์
- หากผู้ใช้ทั่วไปเข้าไม่ได้ ให้ Admin เปิดสิทธิ์โมดูล "งานซ่อมบำรุง" จากหน้าจัดการสิทธิ์

=== ระบบ Feedback (เพิ่มในเวอร์ชันนี้) ===
- ตารางใหม่: maintenance_feedback
- ผู้แจ้งงานให้ Feedback ได้เฉพาะรายการที่ maintenance_requests.user_id ตรงกับ user_id ที่ล็อกอิน
- ให้ Feedback ครั้งแรกได้เมื่อสถานะงานเป็น completed (เสร็จสิ้น)
- คะแนนที่เลือกได้: 5 ดีมาก / 3 พอใช้ / 1 ปรับปรุง
- หลังส่งแล้ว ผู้แจ้งงานคนเดิมสามารถแก้ไขคะแนนและความคิดเห็นได้
- ผู้ใช้คนอื่นดู Feedback ได้จาก detail.php แต่ไม่สามารถบันทึกหรือแก้ไข Feedback ของงานนั้น
- feedback_save.php ตรวจ CSRF + ตรวจเจ้าของงานฝั่ง Server ทุกครั้ง

วิธีอัปเดตฐานข้อมูลเดิม:
1) ล็อกอิน Admin
2) เปิด repair_form/maintenance/install.php
3) กด "ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้"
หรือ Import ไฟล์ maintenance_feedback.sql ผ่าน phpMyAdmin

