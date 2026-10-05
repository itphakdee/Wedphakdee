-- เพิ่มระบบประวัติการส่งซ่อมรถ โรงพยาบาลภักดีชุมพล
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS vehicle_repairs (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  repair_date DATE NOT NULL,
  shop_name VARCHAR(200) NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  detail TEXT NULL,
  receipt_no VARCHAR(100) NULL,
  odometer INT NULL,
  notes TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_vehicle_repair_vehicle(vehicle_id),
  KEY idx_vehicle_repair_date(repair_date),
  KEY idx_vehicle_repair_shop(shop_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
