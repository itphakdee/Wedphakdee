USE login_db;

/*
ลบข้อมูลห้องที่ถูกเพิ่มซ้ำจากชุดก่อนหน้า
เก็บ 4 ห้องเดิม ID 1-4 ตามข้อมูลที่มีอยู่ในระบบของคุณ
*/
DELETE FROM meeting_rooms
WHERE id IN (5,6,7,8,9,10);

/*
ตรวจสอบข้อมูลหลังลบ
*/
SELECT id, room_name, room_code, capacity, location, equipment, color, status
FROM meeting_rooms
ORDER BY id;
