-- ============================================================
-- Personnel Career / Occupational Information Update
-- Database: login_db
-- โรงพยาบาลภักดีชุมพล
-- แนะนำให้ใช้หน้า personnel/install.php เพื่ออัปเดตอัตโนมัติ
-- ============================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `personnel_career_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_personnel_career_option` (`option_type`,`option_name`),
  KEY `idx_personnel_career_type_status` (`option_type`,`status`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `personnel`
  ADD COLUMN `work_group_id` int(11) DEFAULT NULL AFTER `national_id`,
  ADD COLUMN `division_id` int(11) DEFAULT NULL AFTER `work_group_id`,
  ADD COLUMN `appointment_date` date DEFAULT NULL AFTER `division_id`,
  ADD COLUMN `position_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `appointment_date`,
  ADD COLUMN `professional_license_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `position_number`,
  ADD COLUMN `license_issue_date` date DEFAULT NULL AFTER `professional_license_no`,
  ADD COLUMN `position_id` int(11) DEFAULT NULL AFTER `license_issue_date`,
  ADD COLUMN `level_id` int(11) DEFAULT NULL AFTER `position_name`,
  ADD COLUMN `current_status_id` int(11) DEFAULT NULL AFTER `level_id`,
  ADD COLUMN `civil_service_group_id` int(11) DEFAULT NULL AFTER `current_status_id`,
  ADD COLUMN `civil_service_type_id` int(11) DEFAULT NULL AFTER `civil_service_group_id`,
  ADD COLUMN `personnel_group_id` int(11) DEFAULT NULL AFTER `civil_service_type_id`,
  ADD COLUMN `affiliation` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `personnel_group_id`,
  ADD COLUMN `salary` decimal(12,2) DEFAULT NULL AFTER `affiliation`,
  ADD COLUMN `position_allowance` decimal(12,2) DEFAULT NULL AFTER `salary`;

-- ตัวเลือกเริ่มต้นจะถูก Seed อัตโนมัติเมื่อกด personnel/install.php


-- ============================================================
-- รายการกลุ่มงานและหน่วยงาน โรงพยาบาลภักดีชุมพล
-- ============================================================

INSERT IGNORE INTO `personnel_career_options` (`option_type`,`option_name`,`sort_order`,`status`) VALUES
('work_group','กลุ่มงานบริหารทั่วไป',10,'active'),
('work_group','กลุ่มงานเทคนิคการแพทย์',20,'active'),
('work_group','กลุ่มงานทันตกรรม',30,'active'),
('work_group','กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',40,'active'),
('work_group','กลุ่มงานการแพทย์',50,'active'),
('work_group','กลุ่มงานโภชนศาสตร์',60,'active'),
('work_group','กลุ่มงานทางรังสีวิทยา',70,'active'),
('work_group','กลุ่มงานเวชกรรมฟื้นฟู',80,'active'),
('work_group','งานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์',90,'active'),
('work_group','กลุ่มงานบริการด้านปฐมภูมิและองค์รวม',100,'active'),
('work_group','กลุ่มงานการพยาบาล',110,'active'),
('work_group','กลุ่มอำนวยการ',120,'active'),
('work_group','กลุ่มงานการแพทย์แผนไทย',130,'active');

CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ใช้งาน',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_department_name` (`department_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `departments` (`department_name`,`status`) VALUES
('บริหารกลุ่มการพยาบาล','ใช้งาน'),
('งานอุบัติเหตุฉุกเฉิน','ใช้งาน'),
('งานการพยาบาลผู้ป่วยใน','ใช้งาน'),
('งานผู้ป่วยนอก','ใช้งาน'),
('งานสุขภาพจิตและยาเสพติด','ใช้งาน'),
('งานการพยาบาลหน่วยควบคุมการติดเชื้อและงานจ่ายกลาง','ใช้งาน'),
('งานโภชนศาสตร์','ใช้งาน'),
('ฝ่ายบริหารงานทั่วไป','ใช้งาน'),
('งานการเงิน','ใช้งาน'),
('งานพัสดุ','ใช้งาน'),
('งานธุรการ','ใช้งาน'),
('งานซ่อมบำรุง','ใช้งาน'),
('งานยานพาหนะ','ใช้งาน'),
('งานภูมิทัศน์','ใช้งาน'),
('งานซักฟอก','ใช้งาน'),
('งานรักษาความปลอดภัย','ใช้งาน'),
('งานทำความสะอาด','ใช้งาน'),
('งานเวชปฏิบัติทั่วไป','ใช้งาน'),
('งานรังสี','ใช้งาน'),
('งานเทคนิคการแพทย์','ใช้งาน'),
('งานแพทย์แผนไทย','ใช้งาน'),
('ฝ่ายแผนงานและประเมินผล','ใช้งาน'),
('งานศูนย์คอมพิวเตอร์','ใช้งาน'),
('งานศูนย์ประกันสุขภาพ','ใช้งาน'),
('งานเวชระเบียน','ใช้งาน'),
('ฝ่ายเวชปฏิบัติครอบครัว','ใช้งาน'),
('กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค','ใช้งาน'),
('ฝ่ายเวชกรรมฟื้นฟู','ใช้งาน'),
('ฝ่ายทันตสาธารณสุข','ใช้งาน'),
('งานสุขศึกษาและประชาสัมพันธ์','ใช้งาน'),
('การแพทย์','ใช้งาน'),
('งานผู้ป่วยนอก คลีนิค NCD','ใช้งาน'),
('กองช่าง','ใช้งาน'),
('งานห้องคลอด','ใช้งาน'),
('เครื่องมือแพทย์','ใช้งาน')
ON DUPLICATE KEY UPDATE `status`='ใช้งาน';
