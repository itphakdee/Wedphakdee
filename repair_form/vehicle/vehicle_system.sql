-- ระบบบริหารยานพาหนะ โรงพยาบาลภักดีชุมพล
-- แนะนำใช้ install.php เพื่ออัปเดตตาราง vehicle_requests เดิมโดยอัตโนมัติ
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS vehicle_fleet (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  registration VARCHAR(50) NOT NULL UNIQUE,
  display_name VARCHAR(150) NULL,
  vehicle_type VARCHAR(100) NULL,
  brand VARCHAR(100) NULL,
  model VARCHAR(100) NULL,
  status ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active',
  notes TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO vehicle_fleet(registration,display_name,vehicle_type,status,sort_order) VALUES
('บต 6681','รถโรงพยาบาล บต 6681','รถโรงพยาบาล','active',10),
('นข 1169 ชย','รถโรงพยาบาล นข 1169 ชย','รถโรงพยาบาล','active',20),
('กข 8547 ชย','รถโรงพยาบาล กข 8547 ชย','รถโรงพยาบาล','active',30),
('นข 3854 ชย','รถโรงพยาบาล นข 3854 ชย','รถโรงพยาบาล','active',40),
('กท 3246','รถโรงพยาบาล กท 3246','รถโรงพยาบาล','active',50),
('กข 1130','รถโรงพยาบาล กข 1130','รถโรงพยาบาล','active',60)
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name),sort_order=VALUES(sort_order);

CREATE TABLE IF NOT EXISTS vehicle_drivers (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  employee_code VARCHAR(50) NULL,
  fullname VARCHAR(150) NOT NULL,
  phone VARCHAR(50) NULL,
  license_no VARCHAR(100) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vehicle_request_companions (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  user_id INT NULL,
  person_name VARCHAR(150) NOT NULL,
  position_name VARCHAR(150) NULL,
  level_name VARCHAR(100) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_vehicle_companion_request(request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vehicle_feedback (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL UNIQUE,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vehicle_status_logs (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  user_id INT NULL,
  action_key VARCHAR(60) NOT NULL,
  old_status VARCHAR(30) NULL,
  new_status VARCHAR(30) NULL,
  note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_vehicle_log_request(request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ขยายตาราง vehicle_requests เดิม (MySQL 8 / MariaDB รุ่นใหม่)
ALTER TABLE vehicle_requests
  ADD COLUMN IF NOT EXISTS request_no VARCHAR(40) NULL AFTER id,
  ADD COLUMN IF NOT EXISTS request_type VARCHAR(20) NOT NULL DEFAULT 'general' AFTER request_no,
  ADD COLUMN IF NOT EXISTS book_reference VARCHAR(255) NULL AFTER request_type,
  ADD COLUMN IF NOT EXISTS book_no VARCHAR(100) NULL AFTER book_reference,
  ADD COLUMN IF NOT EXISTS book_date DATE NULL AFTER book_no,
  ADD COLUMN IF NOT EXISTS operator_user_id INT NULL AFTER user_id,
  ADD COLUMN IF NOT EXISTS operator_name VARCHAR(150) NULL AFTER operator_user_id,
  ADD COLUMN IF NOT EXISTS department_id INT NULL AFTER fullname,
  ADD COLUMN IF NOT EXISTS department_name VARCHAR(150) NULL AFTER department_id,
  ADD COLUMN IF NOT EXISTS vehicle_id INT NULL AFTER car,
  ADD COLUMN IF NOT EXISTS hospital_registration VARCHAR(50) NULL AFTER vehicle_id,
  ADD COLUMN IF NOT EXISTS private_registration VARCHAR(50) NULL AFTER hospital_registration,
  ADD COLUMN IF NOT EXISTS reason TEXT NULL AFTER private_registration,
  ADD COLUMN IF NOT EXISTS end_date DATE NULL AFTER use_date,
  ADD COLUMN IF NOT EXISTS end_time TIME NULL AFTER use_time,
  ADD COLUMN IF NOT EXISTS driver_id INT NULL AFTER end_time,
  ADD COLUMN IF NOT EXISTS supervisor_user_id INT NULL AFTER department_name,
  ADD COLUMN IF NOT EXISTS supervisor_name VARCHAR(150) NULL AFTER supervisor_user_id,
  ADD COLUMN IF NOT EXISTS supervisor_status VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER supervisor_name,
  ADD COLUMN IF NOT EXISTS supervisor_note TEXT NULL AFTER supervisor_status,
  ADD COLUMN IF NOT EXISTS supervisor_at DATETIME NULL AFTER supervisor_note,
  ADD COLUMN IF NOT EXISTS status VARCHAR(30) NOT NULL DEFAULT 'pending_supervisor' AFTER supervisor_at,
  ADD COLUMN IF NOT EXISTS admin_note TEXT NULL AFTER status,
  ADD COLUMN IF NOT EXISTS cancel_reason TEXT NULL AFTER admin_note,
  ADD COLUMN IF NOT EXISTS cancel_requested_at DATETIME NULL AFTER cancel_reason,
  ADD COLUMN IF NOT EXISTS cancelled_at DATETIME NULL AFTER cancel_requested_at,
  ADD COLUMN IF NOT EXISTS line_notify_status VARCHAR(30) NULL AFTER cancelled_at,
  ADD COLUMN IF NOT EXISTS line_notify_response TEXT NULL AFTER line_notify_status,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE TABLE IF NOT EXISTS leave_supervisors (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  department_id INT NULL,
  department_name VARCHAR(150) NOT NULL,
  supervisor_user_id INT NOT NULL,
  created_by INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_leave_supervisor_department(department_name),
  KEY idx_leave_supervisor_department_id(department_id),
  KEY idx_leave_supervisor_user(supervisor_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
