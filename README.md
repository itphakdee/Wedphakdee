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
