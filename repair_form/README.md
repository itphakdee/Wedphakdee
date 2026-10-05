# Wedphakdee

โครงสร้างหลักของโปรเจกต์:

```text
assets/
  bootstrap/  ไฟล์ Bootstrap แบบ local
  css/        ไฟล์ CSS กลางของระบบ
  images/     รูปภาพที่ใช้ในหน้าเว็บ

components/   ส่วนย่อยที่ include ในหน้าอื่น เช่น sidebar และ dialog/modal
lineapi/      endpoint หรือ script ที่เกี่ยวกับ LINE API นะจ๊ะ
uploads/      ไฟล์ที่ผู้ใช้อัปโหลด
vehicle/      โมดูลยานพาหนะ
```

หน้า PHP หลัก เช่น `login.php`, `dashboard.php`, `leave.php`, `repair_form.php` มีหน้าหลักใหม่ๆเพื่มในนี้ด้่วยนะน้อง
ยังอยู่ที่ root เพื่อให้ URL เดิมบน XAMPP ใช้งานต่อได้.


## Admin และสิทธิ์ผู้ใช้งาน

Admin อยู่ที่ `repair_form/admin/` และเข้าถึงได้เฉพาะบัญชีที่ `users.role = 'admin'` และ `status = 'active'`

ระบบสิทธิ์รายบุคคลเก็บใน `user_permissions` และรายการสิทธิ์มาตรฐานเก็บใน `admin_permissions`

### ห้องประชุม
- `meeting.view` ดูระบบ
- `meeting.create` จองห้อง
- `meeting.manage` จัดการห้อง/ยกเลิก
- `meeting.approve` แสดงใน Admin แต่การอนุมัติ/ไม่อนุมัติการจองจริงบังคับ **Admin เท่านั้น**
- ถ้าเป็น User จะไม่เห็นปุ่มอนุมัติ และถ้าพยายาม POST ตรงไปยัง `status_update.php` จะได้รับ HTTP 403

ฐานข้อมูลฉบับปรับปรุงอยู่ที่ `database/login_db_updated.sql`
