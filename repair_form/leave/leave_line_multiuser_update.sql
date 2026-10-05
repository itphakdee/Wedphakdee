-- ==========================================================
-- LINE OA ระบบลางาน
-- โรงพยาบาลภักดีชุมพล
-- ==========================================================

CREATE TABLE IF NOT EXISTS leave_line_accounts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    line_user_id VARCHAR(120) NOT NULL,
    display_name VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leave_line_account_user (user_id),
    UNIQUE KEY uq_leave_line_account_target (line_user_id),
    KEY idx_leave_line_account_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leave_line_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id INT NULL,
    user_id INT NULL,
    target_id VARCHAR(120) NULL,
    event_name VARCHAR(100) NULL,
    message_summary VARCHAR(500) NULL,
    send_status ENUM('sent','failed','skipped') NOT NULL DEFAULT 'skipped',
    http_code INT NULL,
    response_text MEDIUMTEXT NULL,
    error_text TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_leave_line_log_application (application_id),
    KEY idx_leave_line_log_user (user_id),
    KEY idx_leave_line_log_event (event_name),
    KEY idx_leave_line_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;