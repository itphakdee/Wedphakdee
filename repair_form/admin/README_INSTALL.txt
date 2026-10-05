ติดตั้งระบบ Admin

ตำแหน่งโฟลเดอร์:
C:\AppServ\www\Wedphakdee\repair_form\admin\

URL:
http://localhost/Wedphakdee/repair_form/admin/

ฐานข้อมูล:
login_db

SQL:
admin_permissions.sql

ไฟล์ config:
admin/config_admin.php จะเรียก
C:\AppServ\www\Wedphakdee\config.php
ผ่าน:
require_once __DIR__ . '/../../config.php';

สิทธิ์:
เฉพาะ users.role = 'admin' เท่านั้นที่เข้า Admin Control Center ได้


COMPUTER PERMISSIONS
- computer.view = ดูรายการ
- computer.create = เพิ่มงาน
- computer.edit = แก้ไข
- computer.delete = ลบ
- computer.receive = รับงาน
- computer.manage = จัดการ

Role user จะไม่แสดงคอลัมน์ 'จัดการ' ในรายการแจ้งซ่อมคอมพิวเตอร์
และจะถูกปฏิเสธ edit/delete/receive ผ่าน URL โดยตรงด้วย
Admin สามารถจัดการได้ทั้งหมด
