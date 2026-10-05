-- LINE OA ระบบลางาน โรงพยาบาลภักดีชุมพล
-- ใช้ร่วมกับฐาน login_db เดิม

CREATE TABLE IF NOT EXISTS leave_line_accounts (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    line_user_id VARCHAR(100) NOT NULL,
    display_name VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    updated_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leave_line_user (user_id),
    UNIQUE KEY uq_leave_line_id (line_user_id),
    KEY idx_leave_line_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leave_line_logs (
    id BIGINT NOT NULL AUTO_INCREMENT,
    application_id INT NULL,
    event_key VARCHAR(80) NOT NULL,
    target_user_id INT NULL,
    target_id VARCHAR(120) NULL,
    is_success TINYINT(1) NOT NULL DEFAULT 0,
    http_code INT NOT NULL DEFAULT 0,
    response_text TEXT NULL,
    error_text TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_leave_line_log_application (application_id),
    KEY idx_leave_line_log_user (target_user_id),
    KEY idx_leave_line_log_event (event_key),
    KEY idx_leave_line_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
