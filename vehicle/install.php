<?php
require_once __DIR__ . '/config_vehicle.php';

if (!$currentVehicleIsAdmin) {
    http_response_code(403);
    die('403 Forbidden: สำหรับผู้ดูแลระบบเท่านั้น');
}

$messages = array();
$error = '';

function vehicle_install_add_column($table, $column, $definition)
{
    global $conn, $messages;
    if (!vehicle_column_exists($table, $column)) {
        if ($conn->query("ALTER TABLE `" . $table . "` ADD COLUMN `" . $column . "` " . $definition)) {
            $messages[] = "เพิ่มฟิลด์ " . $table . "." . $column . " แล้ว";
        } else {
            throw new Exception("เพิ่มฟิลด์ " . $column . " ไม่สำเร็จ: " . $conn->error);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    vehicle_check_csrf();
    try {
        $conn->begin_transaction();

        // ตารางรถโรงพยาบาล
        $conn->query("CREATE TABLE IF NOT EXISTS vehicle_fleet (
            id INT NOT NULL AUTO_INCREMENT,
            registration VARCHAR(50) NOT NULL,
            display_name VARCHAR(150) NULL,
            vehicle_type VARCHAR(100) NULL,
            brand VARCHAR(100) NULL,
            model VARCHAR(100) NULL,
            status ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active',
            notes TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_vehicle_registration (registration),
            KEY idx_vehicle_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ฟิลด์ พ.ร.บ. รถ (รองรับฐานข้อมูลเดิม)
        $fleetColumns = array(
            'porobo_policy_no' => "VARCHAR(100) NULL AFTER model",
            'porobo_provider' => "VARCHAR(150) NULL AFTER porobo_policy_no",
            'porobo_start_date' => "DATE NULL AFTER porobo_provider",
            'porobo_expiry_date' => "DATE NULL AFTER porobo_start_date",
            'porobo_alert_days' => "INT NOT NULL DEFAULT 30 AFTER porobo_expiry_date",
            'porobo_notes' => "TEXT NULL AFTER porobo_alert_days"
        );
        foreach ($fleetColumns as $name => $def) {
            vehicle_install_add_column('vehicle_fleet', $name, $def);
        }

        // ตารางพนักงานขับรถ
        $conn->query("CREATE TABLE IF NOT EXISTS vehicle_drivers (
            id INT NOT NULL AUTO_INCREMENT,
            employee_code VARCHAR(50) NULL,
            fullname VARCHAR(150) NOT NULL,
            phone VARCHAR(50) NULL,
            license_no VARCHAR(100) NULL,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            notes TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_vehicle_driver_status (status),
            KEY idx_vehicle_driver_name (fullname)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ถ้ายังไม่มีตารางหัวหน้างานจากระบบลา ให้สร้างไว้เป็นตารางกลางสำหรับแผนก
        $conn->query("CREATE TABLE IF NOT EXISTS leave_supervisors (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ตารางคำขอเดิมอาจมีอยู่แล้ว: สร้างเมื่อยังไม่มี
        $conn->query("CREATE TABLE IF NOT EXISTS vehicle_requests (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NULL,
            fullname VARCHAR(150) NULL,
            subject VARCHAR(255) NULL,
            urgency VARCHAR(50) NULL,
            location VARCHAR(255) NULL,
            car VARCHAR(100) NULL,
            use_date DATE NULL,
            use_time TIME NULL,
            detail TEXT NULL,
            document VARCHAR(255) NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            companions TEXT NULL,
            PRIMARY KEY (id),
            KEY idx_vehicle_request_user (user_id),
            KEY idx_vehicle_request_date (use_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $columns = array(
            'request_no' => "VARCHAR(40) NULL AFTER id",
            'request_type' => "VARCHAR(20) NOT NULL DEFAULT 'general' AFTER request_no",
            'book_reference' => "VARCHAR(255) NULL AFTER request_type",
            'book_no' => "VARCHAR(100) NULL AFTER book_reference",
            'book_date' => "DATE NULL AFTER book_no",
            'vehicle_id' => "INT NULL AFTER car",
            'hospital_registration' => "VARCHAR(50) NULL AFTER vehicle_id",
            'private_registration' => "VARCHAR(50) NULL AFTER hospital_registration",
            'reason' => "TEXT NULL AFTER private_registration",
            'end_date' => "DATE NULL AFTER use_date",
            'end_time' => "TIME NULL AFTER use_time",
            'driver_id' => "INT NULL AFTER end_time",
            'odometer_out' => "DECIMAL(12,1) NULL AFTER driver_id",
            'odometer_return' => "DECIMAL(12,1) NULL AFTER odometer_out",
            'department_id' => "INT NULL AFTER fullname",
            'department_name' => "VARCHAR(150) NULL AFTER department_id",
            'operator_user_id' => "INT NULL AFTER user_id",
            'operator_name' => "VARCHAR(150) NULL AFTER operator_user_id",
            'supervisor_user_id' => "INT NULL AFTER department_name",
            'supervisor_name' => "VARCHAR(150) NULL AFTER supervisor_user_id",
            'supervisor_status' => "VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER supervisor_name",
            'supervisor_note' => "TEXT NULL AFTER supervisor_status",
            'supervisor_at' => "DATETIME NULL AFTER supervisor_note",
            'status' => "VARCHAR(30) NOT NULL DEFAULT 'pending_supervisor' AFTER supervisor_at",
            'admin_note' => "TEXT NULL AFTER status",
            'cancel_reason' => "TEXT NULL AFTER admin_note",
            'cancel_requested_at' => "DATETIME NULL AFTER cancel_reason",
            'cancelled_at' => "DATETIME NULL AFTER cancel_requested_at",
            'line_notify_status' => "VARCHAR(30) NULL AFTER cancelled_at",
            'line_notify_response' => "TEXT NULL AFTER line_notify_status",
            'updated_at' => "TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
        );
        foreach ($columns as $name => $def) {
            vehicle_install_add_column('vehicle_requests', $name, $def);
        }

        // ผู้ร่วมเดินทางแบบมีข้อมูลตำแหน่ง/ระดับ ณ เวลาที่ขอ
        $conn->query("CREATE TABLE IF NOT EXISTS vehicle_request_companions (
            id INT NOT NULL AUTO_INCREMENT,
            request_id INT NOT NULL,
            user_id INT NULL,
            person_name VARCHAR(150) NOT NULL,
            position_name VARCHAR(150) NULL,
            level_name VARCHAR(100) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_vehicle_companion_request (request_id),
            KEY idx_vehicle_companion_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ความพึงพอใจ
        $conn->query("CREATE TABLE IF NOT EXISTS vehicle_feedback (
            id INT NOT NULL AUTO_INCREMENT,
            request_id INT NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL,
            comment TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_vehicle_feedback_request (request_id),
            KEY idx_vehicle_feedback_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ประวัติการเปลี่ยนสถานะ
        $conn->query("CREATE TABLE IF NOT EXISTS vehicle_status_logs (
            id INT NOT NULL AUTO_INCREMENT,
            request_id INT NOT NULL,
            user_id INT NULL,
            action_key VARCHAR(60) NOT NULL,
            old_status VARCHAR(30) NULL,
            new_status VARCHAR(30) NULL,
            note TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_vehicle_log_request (request_id),
            KEY idx_vehicle_log_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Log การแจ้งเตือน พ.ร.บ. ทาง LINE
        $conn->query("CREATE TABLE IF NOT EXISTS vehicle_porobo_line_logs (
            id BIGINT NOT NULL AUTO_INCREMENT,
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
            PRIMARY KEY (id),
            KEY idx_porobo_vehicle (vehicle_id),
            KEY idx_porobo_expiry (expiry_date),
            KEY idx_porobo_alert (alert_key),
            KEY idx_porobo_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // รถเริ่มต้นตามที่ผู้ใช้กำหนด
        $fleet = array('บต 6681', 'นข 1169 ชย', 'กข 8547 ชย', 'นข 3854 ชย', 'กท 3246', 'กข 1130');
        $sort = 10;
        $stmt = $conn->prepare("INSERT INTO vehicle_fleet (registration,display_name,vehicle_type,status,sort_order)
                                VALUES (?,?,'รถโรงพยาบาล','active',?)
                                ON DUPLICATE KEY UPDATE display_name=VALUES(display_name),sort_order=VALUES(sort_order)");
        foreach ($fleet as $reg) {
            $display = 'รถโรงพยาบาล ' . $reg;
            $stmt->bind_param('ssi', $reg, $display, $sort);
            $stmt->execute();
            $sort += 10;
        }
        $stmt->close();

        // เติมเลขคำขอให้ข้อมูลเก่า
        $rs = $conn->query("SELECT id FROM vehicle_requests WHERE request_no IS NULL OR request_no='' ORDER BY id");
        if ($rs) {
            $stmtNo = $conn->prepare("UPDATE vehicle_requests SET request_no=? WHERE id=?");
            while ($r = $rs->fetch_assoc()) {
                $id = (int)$r['id'];
                $no = 'VR-' . date('Y') . '-' . str_pad($id, 5, '0', STR_PAD_LEFT);
                $stmtNo->bind_param('si', $no, $id);
                $stmtNo->execute();
            }
            $stmtNo->close();
        }

        $conn->commit();
        vehicle_flash('success', 'ติดตั้ง/อัปเดตฐานข้อมูลระบบยานพาหนะเรียบร้อยแล้ว');
        vehicle_redirect('index.php?page=dashboard');
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ติดตั้งระบบยานพาหนะ</title>
    <link rel="stylesheet" href="assets/css/vehicle.css?v=20260901">
</head>

<body class="vehicle-standalone">
    <div class="vehicle-install-card">
        <div class="vehicle-brand-mark">PDC</div>
        <p class="vehicle-eyebrow">PHAKDEE CHUMPHON HOSPITAL</p>
        <h1>ติดตั้งระบบบริหารยานพาหนะ</h1>
        <p>สร้าง/อัปเดตตารางรถ พนักงานขับ ผู้ร่วมเดินทาง ความพึงพอใจ พ.ร.บ. และระบบแจ้งเตือน LINE โดยไม่ลบข้อมูลเดิม</p>
        <?php if ($error !== ''): ?><div class="vehicle-alert vehicle-alert--danger"><?= vehicle_e($error) ?></div><?php endif; ?>
        <?php if ($messages): ?><div class="vehicle-alert vehicle-alert--info"><?= vehicle_e(implode(' • ', $messages)) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= vehicle_e(vehicle_csrf_token()) ?>">
            <button class="vehicle-btn vehicle-btn--primary vehicle-btn--wide" type="submit">ติดตั้ง / อัปเดตฐานข้อมูลตอนนี้</button>
        </form>
        <a class="vehicle-btn vehicle-btn--ghost vehicle-btn--wide" href="index.php">กลับระบบยานพาหนะ</a>
    </div>
</body>

</html>