อัปเดตระบบตรวจแผนก -> หัวหน้าแผนก
โรงพยาบาลภักดีชุมพล

การทำงาน:
1. ระบบอ่าน user_id ที่ Login อยู่
2. ตรวจแผนกจาก personnel ก่อน แล้ว fallback users
3. ถ้ามี department_id จะใช้รหัสแผนกจับคู่กับ departments
4. ดึงหัวหน้าจาก leave_supervisors เฉพาะแผนกเดียวกัน
5. ไม่มี fallback ไป Admin/หัวหน้าแผนกอื่น
6. ฝั่ง save/update ตรวจซ้ำอีกครั้ง ป้องกันแก้ hidden supervisor_user_id
7. หน้า supervisors.php กรองรายชื่อหัวหน้าตามแผนกที่เลือก และตรวจซ้ำฝั่ง PHP

ไม่ต้องสร้างตารางใหม่ หากติดตั้งระบบลาก่อนหน้าแล้ว
หน้า Admin กำหนดหัวหน้า:
http://localhost/Wedphakdee/repair_form/leave/supervisors.php

หน้าใบลา:
http://localhost/Wedphakdee/repair_form/leave/add.php
