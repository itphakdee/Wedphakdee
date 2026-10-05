SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `medical_equipment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(100) DEFAULT NULL,
  `display_name` varchar(255) NOT NULL,
  `equipment_name` varchar(200) NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medical_equipment_asset` (`asset_code`),
  KEY `idx_medical_equipment_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medical_technicians` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(150) NOT NULL,
  `specialty` varchar(200) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medical_technician_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medical_repair_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_no` varchar(40) DEFAULT NULL,
  `user_id` int NOT NULL,
  `requester_name` varchar(150) NOT NULL,
  `department` varchar(150) NOT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `equipment_id` int DEFAULT NULL,
  `asset_code` varchar(100) DEFAULT NULL,
  `equipment_name` varchar(200) NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `problem_type` varchar(50) NOT NULL,
  `problem_detail` text NOT NULL,
  `priority` enum('normal','urgent','emergency') NOT NULL DEFAULT 'normal',
  `technician_id` int DEFAULT NULL,
  `technician_name` varchar(150) DEFAULT NULL,
  `status` enum('pending','assigned','in_progress','waiting_parts','completed','cancelled') NOT NULL DEFAULT 'pending',
  `technician_note` text DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `accepted_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `line_notify_status` varchar(20) DEFAULT NULL,
  `line_notify_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medical_request_no` (`request_no`),
  KEY `idx_medical_request_user` (`user_id`),
  KEY `idx_medical_request_status` (`status`),
  KEY `idx_medical_request_priority` (`priority`),
  KEY `idx_medical_request_equipment` (`equipment_id`),
  KEY `idx_medical_request_technician` (`technician_id`),
  KEY `idx_medical_request_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medical_status_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action_key` varchar(60) NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medical_log_request` (`request_id`),
  KEY `idx_medical_log_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medical_feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medical_feedback_request` (`request_id`),
  KEY `idx_medical_feedback_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
