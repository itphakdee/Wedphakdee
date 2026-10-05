USE login_db;

CREATE TABLE IF NOT EXISTS meeting_rooms (
  id INT NOT NULL AUTO_INCREMENT,
  room_name VARCHAR(150) NOT NULL,
  room_code VARCHAR(50) DEFAULT NULL,
  capacity INT NOT NULL DEFAULT 0,
  location VARCHAR(255) DEFAULT NULL,
  equipment TEXT NULL,
  color VARCHAR(20) DEFAULT '#0f766e',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_meeting_rooms_status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS meeting_bookings (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NULL,
  room_id INT NOT NULL,
  meeting_title VARCHAR(255) NOT NULL,
  requester_name VARCHAR(150) NOT NULL,
  department VARCHAR(150) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  meeting_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  attendees INT NOT NULL DEFAULT 1,
  detail TEXT NULL,
  meeting_platform VARCHAR(30) DEFAULT NULL,
  meeting_url VARCHAR(1000) DEFAULT NULL,
  status ENUM('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  approved_by VARCHAR(150) DEFAULT NULL,
  approved_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_meeting_room_date(room_id,meeting_date),
  KEY idx_meeting_status(status),
  KEY idx_meeting_user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO meeting_rooms(room_name,room_code,capacity,location,equipment,color,status)
SELECT 'ห้องประชุมภูไท','PHUTHAI',30,'อาคารอำนวยการ ชั้น 2','โปรเจคเตอร์, จอภาพ, ไมโครโฟน, เครื่องเสียง, Wi-Fi','#0f766e','active'
WHERE NOT EXISTS (SELECT 1 FROM meeting_rooms WHERE room_name='ห้องประชุมภูไท');

INSERT INTO meeting_rooms(room_name,room_code,capacity,location,equipment,color,status)
SELECT 'ห้องประชุมภูทยา','PHUTAYA',20,'อาคารผู้ป่วยนอก ชั้น 2','จอภาพ, ไมโครโฟน, เครื่องเสียง, Wi-Fi','#2563eb','active'
WHERE NOT EXISTS (SELECT 1 FROM meeting_rooms WHERE room_name='ห้องประชุมภูทยา');

INSERT INTO meeting_rooms(room_name,room_code,capacity,location,equipment,color,status)
SELECT 'ห้องประชุมพุทธา','PHUTTHA',15,'อาคารอำนวยการ ชั้น 1','โปรเจคเตอร์, จอภาพ, Wi-Fi','#7c3aed','active'
WHERE NOT EXISTS (SELECT 1 FROM meeting_rooms WHERE room_name='ห้องประชุมพุทธา');

INSERT INTO meeting_rooms(room_name,room_code,capacity,location,equipment,color,status)
SELECT 'ห้องประชุมหัวหน้าฝ่ายการ','HEAD',12,'อาคารอำนวยการ ชั้น 2','จอภาพ, ไมโครโฟน, Wi-Fi','#b45309','active'
WHERE NOT EXISTS (SELECT 1 FROM meeting_rooms WHERE room_name='ห้องประชุมหัวหน้าฝ่ายการ');

-- หากฐานข้อมูลเดิมมี meeting_bookings อยู่แล้วและยังไม่มีคอลัมน์ใหม่
-- แนะนำให้ใช้ install.php ซึ่งตรวจคอลัมน์ก่อน ALTER และรักษาข้อมูลเดิม
