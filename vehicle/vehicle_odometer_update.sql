-- เพิ่มเลขไมล์ขาไป/ขากลับให้ระบบยานพาหนะ
-- โรงพยาบาลภักดีชุมพล

ALTER TABLE vehicle_requests
  ADD COLUMN IF NOT EXISTS odometer_out DECIMAL(12,1) NULL AFTER driver_id,
  ADD COLUMN IF NOT EXISTS odometer_return DECIMAL(12,1) NULL AFTER odometer_out;
