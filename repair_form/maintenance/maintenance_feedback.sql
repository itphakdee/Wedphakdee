-- ระบบ Feedback งานซ่อมบำรุง
-- ใช้กับฐานข้อมูล login_db หลังจากมีตาราง maintenance_requests แล้ว
SET NAMES utf8mb4;
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
