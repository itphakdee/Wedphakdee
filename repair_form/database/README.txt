# Wedphakdee - Database

ไฟล์ `login_db_updated.sql` เป็นฐานข้อมูลที่ปรับปรุงจากฐานข้อมูล `login_db` ที่ส่งมา

สิ่งที่เกี่ยวข้องกับระบบสิทธิ์:
- `users.role` รองรับ admin / technician / manager / user
- `user_permissions` เก็บสิทธิ์รายบุคคล
- `admin_permissions` เก็บรายการโมดูลและ action
- Admin สามารถเข้าศูนย์ Admin ได้ทุกระบบ
- การอนุมัติ/ไม่อนุมัติการจองห้องประชุมกำหนดให้ Admin เท่านั้น

การตั้งค่าเชื่อมต่ออยู่ที่:
`../config.php`
