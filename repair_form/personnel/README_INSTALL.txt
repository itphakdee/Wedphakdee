ระบบข้อมูลบุคลากรโรงพยาบาล
====================================

ตำแหน่งระบบ:
/Wedphakdee/repair_form/personnel/

ไฟล์หลัก:
- index.php               รายการบุคลากร / ค้นหา / สรุปจำนวน
- add.php                 เพิ่มบุคลากร
- store.php               บันทึกบุคลากร + สร้างบัญชี users
- detail.php              รายละเอียดบุคลากร
- edit.php                แก้ไขข้อมูล + หน้าจัดการรหัสผ่าน
- update.php              บันทึกการแก้ไขและ Sync กับ users
- password_update.php     เปลี่ยน/รีเซ็ตรหัสผ่านโดย Admin
- delete.php              ลบบุคลากร
- install.php             ติดตั้ง/อัปเดตฐานข้อมูล
- assets/personnel.css    ดีไซน์ระบบ
- personnel.sql           SQL สำหรับ Import ด้วยตนเอง

วิธีติดตั้งที่แนะนำ:
1. สำรองฐานข้อมูล login_db ก่อน
2. นำโฟลเดอร์ personnel ไปไว้ใน repair_form/personnel/
3. Login ด้วยบัญชี Admin
4. เปิด http://localhost/Wedphakdee/repair_form/personnel/install.php
5. กด "ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้"
6. เข้า http://localhost/Wedphakdee/repair_form/personnel/index.php

บัญชีของบุคลากรใหม่:
- Username: กำหนดจากแบบฟอร์ม
- Password เริ่มต้น: 123456
- Role: user
- Status: Sync ตามสถานะบุคลากร
- รหัสผ่านเก็บแบบ password_hash() ในตาราง users

Admin สามารถ:
- เพิ่ม / แก้ไข / ลบข้อมูลบุคลากรตามสิทธิ์
- เปลี่ยนรหัสผ่านเอง
- รีเซ็ตรหัสผ่านกลับเป็น 123456

ข้อมูลที่เพิ่มใน personnel:
- user_id
- username
- first_name
- last_name
- first_name_en
- nickname
- birth_date
- gender
- national_id

มีตาราง personnel_audit_logs สำหรับบันทึกกิจกรรมสำคัญ เช่น เพิ่ม แก้ไข เปลี่ยนรหัสผ่าน และลบ
