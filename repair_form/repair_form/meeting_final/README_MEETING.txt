ระบบห้องประชุม โรงพยาบาลภักดีชุมพล
===================================

ตำแหน่งระบบ:
http://localhost/Wedphakdee/repair_form/meeting_final/index.php

ติดตั้งครั้งแรก:
1) Login ด้วย Admin
2) เปิด http://localhost/Wedphakdee/repair_form/meeting_final/install.php
3) กด "ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้"
4) กลับไปหน้า Dashboard

ห้องประชุมเริ่มต้น:
- ห้องประชุมภูไท
- ห้องประชุมภูทยา
- ห้องประชุมพุทธา
- ห้องประชุมหัวหน้าฝ่ายการ

สถานะหน้า Dashboard:
- มีการประชุม = มีรายการ approved ที่อยู่ในช่วงเวลาปัจจุบัน
- ติดจองแล้ว = มีรายการ pending/approved ถัดไปในวันนี้
- ว่าง = ไม่มีรายการประชุมปัจจุบันและไม่มีรายการถัดไปในวันนี้

ระบบประชุมออนไลน์:
เลือกแพลตฟอร์มและบันทึกลิงก์ในหน้าจอง
รองรับ Zoom, Google Meet, Microsoft Teams, Cisco Webex, LINE Meeting และ HTTPS URL อื่น
ลิงก์เข้าประชุมจะเปิดได้เมื่อรายการได้รับอนุมัติแล้ว

ไฟล์สำคัญ:
index.php              Dashboard
booking.php            จองห้อง
bookings.php           รายการจอง + ค้นหา + pagination
booking_detail.php     รายละเอียด
booking_edit.php       แก้ไข
booking_update.php     บันทึกแก้ไข
status_update.php      อนุมัติ/ไม่อนุมัติ/ยกเลิก
meeting_link.php       เปิดลิงก์ประชุมออนไลน์
rooms.php              จัดการห้อง
room_add.php           เพิ่มห้อง
room_edit.php          แก้ไขห้อง
install.php            ติดตั้ง/อัปเดตฐานข้อมูล
meeting_system.sql     โครงสร้างฐานข้อมูล
assets/meeting.css     ดีไซน์
assets/meeting.js      ตรวจฟอร์ม

หมายเหตุ:
- install.php ไม่ลบข้อมูลการจองเดิม
- ห้องเริ่มต้นจะเพิ่มเฉพาะห้องที่ยังไม่มี
- ผู้จองแก้ไข/ยกเลิกรายการของตัวเองได้เมื่อระบบมี user_id
- Admin เป็นผู้อนุมัติ/ไม่อนุมัติ
