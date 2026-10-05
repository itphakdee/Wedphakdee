-- อัปเดตระบบแจ้งเตือน พ.ร.บ. รถ
SET NAMES utf8mb4;

ALTER TABLE vehicle_fleet
  ADD COLUMN IF NOT EXISTS porobo_policy_no VARCHAR(100) NULL AFTER model,
  ADD COLUMN IF NOT EXISTS porobo_provider VARCHAR(150) NULL AFTER porobo_policy_no,
  ADD COLUMN IF NOT EXISTS porobo_start_date DATE NULL AFTER porobo_provider,
  ADD COLUMN IF NOT EXISTS porobo_expiry_date DATE NULL AFTER porobo_start_date,
  ADD COLUMN IF NOT EXISTS porobo_alert_days INT NOT NULL DEFAULT 30 AFTER porobo_expiry_date,
  ADD COLUMN IF NOT EXISTS porobo_notes TEXT NULL AFTER porobo_alert_days;

CREATE TABLE IF NOT EXISTS vehicle_porobo_line_logs (
  id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  expiry_date DATE NULL,
  alert_key VARCHAR(80) NOT NULL,
  days_remaining INT NULL,
  event_name VARCHAR(80) NULL,
  send_status ENUM('success','failed') NOT NULL DEFAULT 'failed',
  http_code INT NULL,
  response_text MEDIUMTEXT NULL,
  error_text TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_porobo_vehicle(vehicle_id),
  KEY idx_porobo_expiry(expiry_date),
  KEY idx_porobo_alert(alert_key),
  KEY idx_porobo_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
