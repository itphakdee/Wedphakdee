SET NAMES utf8mb4;

-- เพิ่มช่องราคาในทะเบียนเครื่องมือแพทย์ โดยไม่ลบข้อมูลเดิม
ALTER TABLE `medical_equipment`
  ADD COLUMN `price` DECIMAL(12,2) NULL COMMENT 'ราคาเครื่องมือแพทย์ (บาท)' AFTER `received_date`;
