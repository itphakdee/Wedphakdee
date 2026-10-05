-- เพิ่มฟิลด์วันที่เข้ารับสำหรับระบบศูนย์เครื่องมือแพทย์
-- ใช้กับฐานข้อมูล login_db ที่ติดตั้ง medical_equipment แล้ว
ALTER TABLE `medical_equipment`
  ADD COLUMN `received_date` DATE NULL COMMENT 'วันที่เข้ารับเครื่องมือแพทย์' AFTER `equipment_name`;
