-- =========================================================
-- ระบบงานซ่อมบำรุง โรงพยาบาลภักดีชุมพล
-- Database: login_db
-- รองรับ PHP + MySQL/MariaDB (AppServ)
-- =========================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `maintenance_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL COMMENT 'ผู้ใช้งานที่สร้างรายการ',
  `sender_name` varchar(150) NOT NULL COMMENT 'ชื่อผู้ส่ง/ผู้แจ้ง',
  `department` varchar(150) NOT NULL COMMENT 'แผนกผู้แจ้ง',
  `system_type` enum('ประปา','ไฟฟ้า','แอร์') NOT NULL COMMENT 'ระบบที่ต้องการแจ้งซ่อม',
  `technician_id` int DEFAULT NULL COMMENT 'รหัสช่างจากตาราง technicians',
  `technician_name` varchar(150) DEFAULT NULL COMMENT 'ชื่อช่าง ณ วันที่มอบหมายงาน',
  `details` text NOT NULL COMMENT 'รายละเอียดแจ้งซ่อม',
  `location` varchar(255) DEFAULT NULL COMMENT 'สถานที่/จุดที่พบปัญหา',
  `priority` enum('normal','urgent','emergency') NOT NULL DEFAULT 'normal' COMMENT 'ระดับความเร่งด่วน',
  `status` enum('pending','assigned','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'สถานะงาน',
  `technician_note` text DEFAULT NULL COMMENT 'หมายเหตุจากช่าง/ผู้ดูแล',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_maintenance_status` (`status`),
  KEY `idx_maintenance_system` (`system_type`),
  KEY `idx_maintenance_technician` (`technician_id`),
  KEY `idx_maintenance_created_at` (`created_at`),
  KEY `idx_maintenance_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------
-- Feedback งานซ่อมบำรุง
-- ผู้แจ้ง 1 คนให้ Feedback ได้ 1 ครั้งต่อ 1 งาน และแก้ไขได้
-- คะแนนที่ระบบยอมรับ: 5 = ดีมาก, 3 = พอใช้, 1 = ปรับปรุง
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `maintenance_feedback` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int unsigned NOT NULL COMMENT 'อ้างอิง maintenance_requests.id',
  `user_id` int NOT NULL COMMENT 'บัญชีผู้แจ้งงานที่ให้ Feedback',
  `score` tinyint unsigned NOT NULL COMMENT '5=ดีมาก, 3=พอใช้, 1=ปรับปรุง',
  `feedback_text` text DEFAULT NULL COMMENT 'ความคิดเห็นเพิ่มเติม',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_maintenance_feedback_request` (`request_id`),
  KEY `idx_maintenance_feedback_user` (`user_id`),
  KEY `idx_maintenance_feedback_score` (`score`),
  CONSTRAINT `fk_maintenance_feedback_request`
    FOREIGN KEY (`request_id`) REFERENCES `maintenance_requests` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- สิทธิ์มาตรฐาน (จะเพิ่มเฉพาะรายการที่ยังไม่มี)
INSERT INTO `admin_permissions` (`module_key`,`module_name`,`action_key`,`action_name`,`status`)
SELECT 'maintenance','งานซ่อมบำรุง','view','ดู','active'
WHERE NOT EXISTS (SELECT 1 FROM `admin_permissions` WHERE `module_key`='maintenance' AND `action_key`='view');

INSERT INTO `admin_permissions` (`module_key`,`module_name`,`action_key`,`action_name`,`status`)
SELECT 'maintenance','งานซ่อมบำรุง','create','เพิ่ม','active'
WHERE NOT EXISTS (SELECT 1 FROM `admin_permissions` WHERE `module_key`='maintenance' AND `action_key`='create');

INSERT INTO `admin_permissions` (`module_key`,`module_name`,`action_key`,`action_name`,`status`)
SELECT 'maintenance','งานซ่อมบำรุง','edit','แก้ไข','active'
WHERE NOT EXISTS (SELECT 1 FROM `admin_permissions` WHERE `module_key`='maintenance' AND `action_key`='edit');

INSERT INTO `admin_permissions` (`module_key`,`module_name`,`action_key`,`action_name`,`status`)
SELECT 'maintenance','งานซ่อมบำรุง','delete','ลบ','active'
WHERE NOT EXISTS (SELECT 1 FROM `admin_permissions` WHERE `module_key`='maintenance' AND `action_key`='delete');

INSERT INTO `admin_permissions` (`module_key`,`module_name`,`action_key`,`action_name`,`status`)
SELECT 'maintenance','งานซ่อมบำรุง','manage','จัดการ','active'
WHERE NOT EXISTS (SELECT 1 FROM `admin_permissions` WHERE `module_key`='maintenance' AND `action_key`='manage');
