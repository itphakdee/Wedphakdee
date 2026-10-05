ระบบบริหารยานพาหนะ โรงพยาบาลภักดีชุมพล
============================================================
URL หลัก
http://localhost/Wedphakdee/vehicle/index.php

ติดตั้ง / อัปเดตฐานข้อมูล (Admin)
http://localhost/Wedphakdee/vehicle/install.php

ฟังก์ชันหลัก
- หน้าเลือกประเภทคำขอ: ประชุม/ภารกิจทั่วไป และ ฉุกเฉิน (REFER)
- ฟอร์มตามหนังสือ / เลขที่หนังสือ / ลงวันที่ / ความเร่งด่วน
- เลือกรถโรงพยาบาล หรือระบุทะเบียนรถยนต์ส่วนตัว
- รถตั้งต้น: บต 6681, นข 1169 ชย, กข 8547 ชย, นข 3854 ชย, กท 3246, กข 1130
- เหตุผล สถานที่ วันที่ เวลาเริ่ม/สิ้นสุด
- Dropdown พนักงานขับจากฐานข้อมูล vehicle_drivers
- ผู้ร้องขอและผู้ทำรายการดึงจาก users ตาม session
- ตรวจแผนกจาก personnel/users แล้วดึงหัวหน้างานจาก leave_supervisors
- ผู้ร่วมเดินทางเลือกหลายคน พร้อม Snapshot ชื่อ ตำแหน่ง ระดับ
- LINE Messaging API ผ่าน Google Apps Script หลังบันทึกฐานข้อมูลสำเร็จ
- Dashboard / ทะเบียนใช้รถ / ค้นหา / Pagination / ปฏิทินยานพาหนะ
- Admin เห็นรายการทุกคน, User เห็นทะเบียนเฉพาะคำขอตนเอง
- หน้าแยกหัวหน้างานสำหรับรับรองคำขอ
- แก้ไข / แจ้งยกเลิก / พิมพ์ / ความพึงพอใจ
- Admin เพิ่ม แก้ไข ลบ/ปิดใช้งาน รถและพนักงานขับ
- Audit status log, CSRF, Prepared Statements

ตารางฐานข้อมูล
- vehicle_requests (ขยายจากของเดิมโดยไม่ลบข้อมูล)
- vehicle_fleet
- vehicle_drivers
- vehicle_request_companions
- vehicle_feedback
- vehicle_status_logs
- leave_supervisors (ใช้ร่วมกับระบบลาสำหรับหัวหน้าแผนก)

LINE Messaging API
ใช้ไฟล์เดิม:
Wedphakdee/lineapi/line_apps_script_config.php
และ Google Apps Script Gateway เดิมที่รับ JSON fields: secret, event, message
ระบบรถจะส่ง event เช่น vehicle_request_created, vehicle_status_update

หมายเหตุ
แนะนำติดตั้งผ่าน install.php เพราะจะตรวจ Column ก่อน ALTER และรักษาข้อมูล vehicle_requests เดิมไว้
