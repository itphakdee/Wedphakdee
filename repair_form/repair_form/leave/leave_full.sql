-- =========================================================
-- ระบบบริหารการลางาน โรงพยาบาลภักดีชุมพล
-- ใช้ร่วมกับฐานข้อมูล login_db เดิม
-- =========================================================

CREATE TABLE IF NOT EXISTS leave_types_master (
    id INT NOT NULL AUTO_INCREMENT,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(120) NOT NULL,
    default_quota_days DECIMAL(7,2) NULL,
    description VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leave_type_code (code),
    UNIQUE KEY uq_leave_type_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO leave_types_master(code,name,default_quota_days,description,sort_order,is_active) VALUES
('PERSONAL','ลากิจ',45,'ค่าเริ่มต้นสามารถปรับตามระเบียบของหน่วยงานได้',10,1),
('VACATION','ลาพักผ่อน',10,'ค่าเริ่มต้นสามารถปรับตามสิทธิ์รายบุคคลได้',20,1),
('SICK','ลาป่วย',60,'สามารถแนบใบรับรองแพทย์ได้',30,1),
('STUDY','ลาศึกษา/อบรม',NULL,'จำนวนวันขึ้นอยู่กับคำสั่งหรือสิทธิ์ที่ได้รับ',40,1),
('MILITARY','ลาทหาร',60,'ค่าเริ่มต้นสามารถปรับตามระเบียบของหน่วยงานได้',50,1),
('CHILDBIRTH_HELP','ลาช่วยคลอด',15,'ค่าเริ่มต้นสามารถปรับตามระเบียบของหน่วยงานได้',60,1),
('RELIGION','ลาประกอบพิธีทางศาสนา',120,'จำนวนวันขึ้นอยู่กับหลักเกณฑ์ที่หน่วยงานกำหนด',70,1),
('SPOUSE','ลาติดตามคู่สมรส',NULL,'จำนวนวันขึ้นอยู่กับคำสั่งหรือสิทธิ์ที่ได้รับ',80,1)
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), sort_order=VALUES(sort_order), is_active=VALUES(is_active);

CREATE TABLE IF NOT EXISTS leave_supervisors (
    id INT NOT NULL AUTO_INCREMENT,
    department_id INT NULL,
    department_name VARCHAR(150) NOT NULL,
    supervisor_user_id INT NOT NULL,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leave_supervisor_department (department_name),
    KEY idx_leave_supervisor_user (supervisor_user_id),
    KEY idx_leave_supervisor_department_id (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leave_applications (
    id INT NOT NULL AUTO_INCREMENT,
    leave_no VARCHAR(40) NOT NULL,
    user_id INT NOT NULL,
    employee_code VARCHAR(50) NULL,
    employee_name VARCHAR(150) NOT NULL,
    position_name VARCHAR(150) NULL,
    department_id INT NULL,
    department_name VARCHAR(150) NULL,
    leave_type_id INT NOT NULL,
    leave_type_name VARCHAR(120) NOT NULL,
    fiscal_year INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    leave_days DECIMAL(7,2) NOT NULL DEFAULT 0,
    reason TEXT NOT NULL,
    contact_phone VARCHAR(50) NULL,
    medical_certificate VARCHAR(255) NULL,
    other_attachment VARCHAR(255) NULL,
    handover_user_id INT NOT NULL,
    handover_name VARCHAR(150) NOT NULL,
    handover_status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    handover_comment TEXT NULL,
    handover_at DATETIME NULL,
    supervisor_user_id INT NOT NULL,
    supervisor_name VARCHAR(150) NOT NULL,
    supervisor_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    supervisor_comment TEXT NULL,
    supervisor_at DATETIME NULL,
    status ENUM('pending_handover','pending_supervisor','approved','rejected','cancel_requested','cancelled') NOT NULL DEFAULT 'pending_handover',
    status_before_cancel VARCHAR(30) NULL,
    cancel_reason TEXT NULL,
    cancel_requested_at DATETIME NULL,
    cancel_decision_by INT NULL,
    cancel_decision_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leave_application_no (leave_no),
    KEY idx_leave_application_user (user_id),
    KEY idx_leave_application_fy (fiscal_year),
    KEY idx_leave_application_dates (start_date,end_date),
    KEY idx_leave_application_status (status),
    KEY idx_leave_application_handover (handover_user_id,handover_status),
    KEY idx_leave_application_supervisor (supervisor_user_id,supervisor_status),
    KEY idx_leave_application_department (department_id,department_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leave_balances (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    fiscal_year INT NOT NULL,
    leave_type_id INT NOT NULL,
    quota_days DECIMAL(7,2) NOT NULL DEFAULT 0,
    carried_days DECIMAL(7,2) NOT NULL DEFAULT 0,
    adjustment_days DECIMAL(7,2) NOT NULL DEFAULT 0,
    note VARCHAR(255) NULL,
    updated_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leave_balance (user_id,fiscal_year,leave_type_id),
    KEY idx_leave_balance_fy (fiscal_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leave_audit_logs (
    id BIGINT NOT NULL AUTO_INCREMENT,
    application_id INT NULL,
    actor_user_id INT NULL,
    action VARCHAR(80) NOT NULL,
    detail TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_leave_log_application (application_id),
    KEY idx_leave_log_actor (actor_user_id),
    KEY idx_leave_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
