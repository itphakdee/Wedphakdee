-- ============================================================
-- Hospital Personnel Module - schema update
-- Database: login_db
-- ใช้เมื่อต้องการ Import ผ่าน phpMyAdmin (สำหรับฐานข้อมูลเดิม)
-- แนะนำให้สำรองฐานข้อมูลก่อน Import
-- ============================================================

SET NAMES utf8mb4;

-- ตาราง personnel เดิมของโครงการต้องมีอยู่แล้ว
ALTER TABLE `personnel`
  ADD COLUMN `user_id` int(11) DEFAULT NULL AFTER `id`,
  ADD COLUMN `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `employee_code`,
  ADD COLUMN `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `prefix`,
  ADD COLUMN `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `first_name`,
  ADD COLUMN `first_name_en` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `fullname`,
  ADD COLUMN `nickname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `first_name_en`,
  ADD COLUMN `birth_date` date DEFAULT NULL AFTER `nickname`,
  ADD COLUMN `gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `birth_date`,
  ADD COLUMN `national_id` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `gender`,
  ADD UNIQUE KEY `uq_personnel_username` (`username`),
  ADD UNIQUE KEY `uq_personnel_national_id` (`national_id`),
  ADD KEY `idx_personnel_user_id` (`user_id`);

CREATE TABLE IF NOT EXISTS `personnel_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnel_id` int(11) DEFAULT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `detail` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_personnel_audit_personnel` (`personnel_id`),
  KEY `idx_personnel_audit_actor` (`actor_user_id`),
  KEY `idx_personnel_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- หมายเหตุ
-- บุคลากรใหม่จะถูกสร้างบัญชีในตาราง users อัตโนมัติ
-- รหัสผ่านเริ่มต้น = 123456 และบันทึกเป็น password_hash() ไม่ใช่ข้อความธรรมดา
