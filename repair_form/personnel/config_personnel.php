<?php
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');

$permissionHelper = __DIR__ . '/../admin/permissions_helper.php';
if (is_file($permissionHelper)) {
    require_once $permissionHelper;
}

function ph($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function personnel_require_login()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ../../login.php');
        exit;
    }
}

function personnel_is_admin()
{
    $role = strtolower((string)($_SESSION['role'] ?? ''));
    if ($role === 'admin') return true;
    if (function_exists('is_admin_user')) return (bool)is_admin_user();
    return false;
}

function personnel_can($action)
{
    if (empty($_SESSION['user_id'])) return false;
    if (personnel_is_admin()) return true;
    if ($action === 'view') {
        if (function_exists('has_permission')) return has_permission('personnel.view');
        return true;
    }
    $key = 'personnel.' . $action;
    if (function_exists('can_manage_action')) return can_manage_action($key);
    if (function_exists('has_permission')) return has_permission($key);
    return false;
}

function personnel_require_permission($action)
{
    personnel_require_login();
    if (!personnel_can($action)) {
        http_response_code(403);
        die('403 Forbidden: บัญชีนี้ไม่มีสิทธิ์ใช้งานส่วนข้อมูลบุคลากร');
    }
}

function personnel_csrf_token()
{
    if (empty($_SESSION['personnel_csrf'])) $_SESSION['personnel_csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['personnel_csrf'];
}

function personnel_verify_csrf($token)
{
    $sessionToken = $_SESSION['personnel_csrf'] ?? '';
    return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function personnel_flash($type, $message)
{
    $_SESSION['personnel_flash'] = ['type'=>$type, 'message'=>$message];
}

function personnel_pull_flash()
{
    $flash = $_SESSION['personnel_flash'] ?? null;
    unset($_SESSION['personnel_flash']);
    return $flash;
}

function personnel_column_exists($table, $column)
{
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    $result = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn->real_escape_string($column) . "'");
    return $result && $result->num_rows > 0;
}

function personnel_table_exists($table)
{
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $result = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
    return $result && $result->num_rows > 0;
}

function personnel_index_exists($table, $index)
{
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $index = $conn->real_escape_string($index);
    $result = $conn->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
    return $result && $result->num_rows > 0;
}

function personnel_schema_ready()
{
    $requiredColumns = [
        'username','first_name','last_name','national_id','user_id',
        'work_group_id','division_id','appointment_date','position_number',
        'professional_license_no','license_issue_date','position_id','level_id',
        'current_status_id','civil_service_group_id','civil_service_type_id',
        'personnel_group_id','affiliation','salary','position_allowance'
    ];
    if (!personnel_table_exists('personnel') || !personnel_table_exists('users') || !personnel_table_exists('personnel_career_options') || !personnel_table_exists('departments')) return false;
    foreach ($requiredColumns as $column) {
        if (!personnel_column_exists('personnel', $column)) return false;
    }
    return true;
}

function personnel_career_option_types()
{
    return [
        'work_group' => 'กลุ่มงาน',
        'division' => 'ฝ่าย / แผนก',
        'position' => 'ตำแหน่ง',
        'level' => 'ระดับ',
        'current_status' => 'สถานะปัจจุบัน',
        'civil_group' => 'กลุ่มข้าราชการ',
        'civil_type' => 'ประเภทข้าราชการ',
        'personnel_group' => 'กลุ่มบุคลากร',
    ];
}

function personnel_seed_career_options()
{
    global $conn;
    if (!personnel_table_exists('personnel_career_options')) return 0;
    $seed = [
        'work_group' => [
            'กลุ่มงานบริหารทั่วไป',
            'กลุ่มงานเทคนิคการแพทย์',
            'กลุ่มงานทันตกรรม',
            'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',
            'กลุ่มงานการแพทย์',
            'กลุ่มงานโภชนศาสตร์',
            'กลุ่มงานทางรังสีวิทยา',
            'กลุ่มงานเวชกรรมฟื้นฟู',
            'งานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์',
            'กลุ่มงานบริการด้านปฐมภูมิและองค์รวม',
            'กลุ่มงานการพยาบาล',
            'กลุ่มอำนวยการ',
            'กลุ่มงานการแพทย์แผนไทย',
        ],
        'division' => ['บริหาร','การพยาบาล','การแพทย์','สนับสนุนบริการสุขภาพ','ยุทธศาสตร์และสารสนเทศ','การเงินและบัญชี','พัสดุ','ทรัพยากรบุคคล'],
        'position' => [
            'แพทย์','ทันตแพทย์','เภสัชกร','พยาบาลวิชาชีพ','นักเทคนิคการแพทย์','นักรังสีการแพทย์',
            'นักกายภาพบำบัด','นักวิชาการสาธารณสุข','นักวิชาการคอมพิวเตอร์','นักจัดการงานทั่วไป',
            'เจ้าพนักงานธุรการ','เจ้าพนักงานการเงินและบัญชี','เจ้าพนักงานพัสดุ','นายช่างเทคนิค','ผู้ช่วยพยาบาล','พนักงานบริการ'
        ],
        'level' => ['ไม่มีระดับ','ปฏิบัติงาน','ชำนาญงาน','อาวุโส','ปฏิบัติการ','ชำนาญการ','ชำนาญการพิเศษ','เชี่ยวชาญ','ทรงคุณวุฒิ'],
        'current_status' => ['ปฏิบัติงาน','ลาศึกษาต่อ','ช่วยราชการ','พักราชการ','ลาออก','เกษียณอายุราชการ','พ้นสภาพ'],
        'civil_group' => ['ข้าราชการ','พนักงานราชการ','พนักงานกระทรวงสาธารณสุข','ลูกจ้างประจำ','ลูกจ้างชั่วคราว','จ้างเหมาบริการ'],
        'civil_type' => ['ทั่วไป','วิชาการ','อำนวยการ','บริหาร','วิชาชีพเฉพาะ','ทักษะพิเศษ','อื่นๆ'],
        'personnel_group' => ['ข้าราชการ','พนักงานราชการ','พนักงานกระทรวงสาธารณสุข','ลูกจ้างประจำ','ลูกจ้างชั่วคราว','จ้างเหมาบริการ'],
    ];
    $stmt = $conn->prepare('INSERT IGNORE INTO personnel_career_options (option_type, option_name, sort_order, status) VALUES (?, ?, ?, \'active\')');
    if (!$stmt) return 0;
    $count = 0;
    foreach ($seed as $type => $names) {
        foreach ($names as $i => $name) {
            $sort = ($i + 1) * 10;
            $stmt->bind_param('ssi', $type, $name, $sort);
            if ($stmt->execute() && $stmt->affected_rows > 0) $count++;
        }
    }
    $stmt->close();
    return $count;
}


function personnel_sync_official_work_groups()
{
    global $conn;
    if (!personnel_table_exists('personnel_career_options')) return ['activated'=>0,'deactivated'=>0,'remapped'=>0];

    // รายการกลุ่มงานที่ใช้งานจริงของโรงพยาบาลภักดีชุมพล
    // เก็บไว้เพียงชุดเดียวเพื่อป้องกันชื่อซ้ำ/ชื่อใกล้เคียงชนกันใน Dropdown
    $official = [
        'กลุ่มงานบริหารทั่วไป',
        'กลุ่มงานเทคนิคการแพทย์',
        'กลุ่มงานทันตกรรม',
        'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',
        'กลุ่มงานการแพทย์',
        'กลุ่มงานโภชนศาสตร์',
        'กลุ่มงานทางรังสีวิทยา',
        'กลุ่มงานเวชกรรมฟื้นฟู',
        'งานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์',
        'กลุ่มงานบริการด้านปฐมภูมิและองค์รวม',
        'กลุ่มงานการพยาบาล',
        'กลุ่มอำนวยการ',
        'กลุ่มงานการแพทย์แผนไทย',
    ];

    // ชื่อเก่าที่มีความหมายเดียวกัน ให้ย้ายบุคลากรไปยังชื่อมาตรฐานก่อนปิดรายการเก่า
    $aliases = [
        'กลุ่มงานรังสีวิทยา' => 'กลุ่มงานทางรังสีวิทยา',
        'กลุ่มงานเวชศาสตร์ฟื้นฟู' => 'กลุ่มงานเวชกรรมฟื้นฟู',
        'กลุ่มงานประกันสุขภาพ ยุทธศาสตร์และสารสนเทศทางการแพทย์' => 'งานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์',
        'กลุ่มงานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์' => 'งานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์',
    ];

    $activated = 0;
    $deactivated = 0;
    $remapped = 0;

    // บังคับให้รายการมาตรฐานมีอยู่ ใช้งาน และเรียงตามลำดับที่กำหนด
    $upsert = $conn->prepare("INSERT INTO personnel_career_options (option_type, option_name, sort_order, status) VALUES ('work_group', ?, ?, 'active') ON DUPLICATE KEY UPDATE sort_order=VALUES(sort_order), status='active'");
    if ($upsert) {
        foreach ($official as $i => $name) {
            $sort = ($i + 1) * 10;
            $upsert->bind_param('si', $name, $sort);
            if ($upsert->execute() && $upsert->affected_rows > 0) $activated++;
        }
        $upsert->close();
    }

    // ย้าย reference ของบุคลากรจากชื่อซ้ำ/ชื่อเดิม ไปยังชื่อมาตรฐาน
    if (personnel_table_exists('personnel')) {
        $findId = $conn->prepare("SELECT id FROM personnel_career_options WHERE option_type='work_group' AND option_name=? LIMIT 1");
        $move = $conn->prepare('UPDATE personnel SET work_group_id=? WHERE work_group_id=?');
        if ($findId && $move) {
            foreach ($aliases as $oldName => $newName) {
                $findId->bind_param('s', $newName);
                $findId->execute();
                $newRow = $findId->get_result()->fetch_assoc();

                $findId->bind_param('s', $oldName);
                $findId->execute();
                $oldRow = $findId->get_result()->fetch_assoc();

                if ($newRow && $oldRow && (int)$newRow['id'] !== (int)$oldRow['id']) {
                    $newId = (int)$newRow['id'];
                    $oldId = (int)$oldRow['id'];
                    $move->bind_param('ii', $newId, $oldId);
                    if ($move->execute()) $remapped += max(0, (int)$move->affected_rows);
                }
            }
        }
        if ($findId) $findId->close();
        if ($move) $move->close();
    }

    // ปิดชื่อกลุ่มงานอื่นทั้งหมดที่ไม่อยู่ในรายชื่อมาตรฐาน
    $escaped = [];
    foreach ($official as $name) $escaped[] = "'" . $conn->real_escape_string($name) . "'";
    $sql = "UPDATE personnel_career_options SET status='inactive' WHERE option_type='work_group' AND option_name NOT IN (" . implode(',', $escaped) . ") AND status<>'inactive'";
    if ($conn->query($sql)) $deactivated = max(0, (int)$conn->affected_rows);

    return ['activated'=>$activated,'deactivated'=>$deactivated,'remapped'=>$remapped];
}


function personnel_seed_departments()
{
    global $conn;
    if (!personnel_table_exists('departments')) return 0;
    $names = [
        'บริหารกลุ่มการพยาบาล',
        'งานอุบัติเหตุฉุกเฉิน',
        'งานการพยาบาลผู้ป่วยใน',
        'งานผู้ป่วยนอก',
        'งานสุขภาพจิตและยาเสพติด',
        'งานการพยาบาลหน่วยควบคุมการติดเชื้อและงานจ่ายกลาง',
        'งานโภชนศาสตร์',
        'ฝ่ายบริหารงานทั่วไป',
        'งานการเงิน',
        'งานพัสดุ',
        'งานธุรการ',
        'งานซ่อมบำรุง',
        'งานยานพาหนะ',
        'งานภูมิทัศน์',
        'งานซักฟอก',
        'งานรักษาความปลอดภัย',
        'งานทำความสะอาด',
        'งานเวชปฏิบัติทั่วไป',
        'งานรังสี',
        'งานเทคนิคการแพทย์',
        'งานแพทย์แผนไทย',
        'ฝ่ายแผนงานและประเมินผล',
        'งานศูนย์คอมพิวเตอร์',
        'งานศูนย์ประกันสุขภาพ',
        'งานเวชระเบียน',
        'ฝ่ายเวชปฏิบัติครอบครัว',
        'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',
        'ฝ่ายเวชกรรมฟื้นฟู',
        'ฝ่ายทันตสาธารณสุข',
        'งานสุขศึกษาและประชาสัมพันธ์',
        'การแพทย์',
        'งานผู้ป่วยนอก คลีนิค NCD',
        'กองช่าง',
        'งานห้องคลอด',
        'เครื่องมือแพทย์'
    ];
    $find = $conn->prepare('SELECT id FROM departments WHERE department_name=? LIMIT 1');
    $insert = $conn->prepare("INSERT INTO departments (department_name, status) VALUES (?, 'ใช้งาน')");
    $activate = $conn->prepare("UPDATE departments SET status='ใช้งาน' WHERE id=?");
    if (!$find || !$insert || !$activate) return 0;
    $count = 0;
    foreach ($names as $name) {
        $find->bind_param('s', $name);
        $find->execute();
        $row = $find->get_result()->fetch_assoc();
        if ($row) {
            $id = (int)$row['id'];
            $activate->bind_param('i', $id);
            $activate->execute();
            continue;
        }
        $insert->bind_param('s', $name);
        if ($insert->execute() && $insert->affected_rows > 0) $count++;
    }
    $find->close();
    $insert->close();
    $activate->close();
    return $count;
}

function personnel_install_schema()
{
    global $conn;
    $messages = [];

    if (!personnel_table_exists('personnel')) {
        $sql = "CREATE TABLE `personnel` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `employee_code` varchar(50) DEFAULT NULL,
          `username` varchar(50) DEFAULT NULL,
          `prefix` varchar(30) DEFAULT NULL,
          `first_name` varchar(100) DEFAULT NULL,
          `last_name` varchar(100) DEFAULT NULL,
          `fullname` varchar(150) NOT NULL,
          `first_name_en` varchar(150) DEFAULT NULL,
          `nickname` varchar(100) DEFAULT NULL,
          `birth_date` date DEFAULT NULL,
          `gender` varchar(20) DEFAULT NULL,
          `national_id` varchar(13) DEFAULT NULL,
          `work_group_id` int(11) DEFAULT NULL,
          `division_id` int(11) DEFAULT NULL,
          `appointment_date` date DEFAULT NULL,
          `position_number` varchar(100) DEFAULT NULL,
          `professional_license_no` varchar(100) DEFAULT NULL,
          `license_issue_date` date DEFAULT NULL,
          `position_id` int(11) DEFAULT NULL,
          `position_name` varchar(150) DEFAULT NULL,
          `level_id` int(11) DEFAULT NULL,
          `current_status_id` int(11) DEFAULT NULL,
          `civil_service_group_id` int(11) DEFAULT NULL,
          `civil_service_type_id` int(11) DEFAULT NULL,
          `personnel_group_id` int(11) DEFAULT NULL,
          `affiliation` varchar(150) DEFAULT NULL,
          `salary` decimal(12,2) DEFAULT NULL,
          `position_allowance` decimal(12,2) DEFAULT NULL,
          `department` varchar(150) DEFAULT NULL,
          `department_id` int(11) DEFAULT NULL,
          `phone` varchar(50) DEFAULT NULL,
          `email` varchar(150) DEFAULT NULL,
          `room_location` varchar(150) DEFAULT NULL,
          `photo` varchar(255) DEFAULT NULL,
          `status` enum('active','inactive') NOT NULL DEFAULT 'active',
          `notes` text,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$conn->query($sql)) throw new Exception('สร้างตาราง personnel ไม่สำเร็จ: ' . $conn->error);
        $messages[] = 'สร้างตาราง personnel แล้ว';
    }

    $columns = [
        'user_id' => "ALTER TABLE `personnel` ADD COLUMN `user_id` int(11) DEFAULT NULL AFTER `id`",
        'username' => "ALTER TABLE `personnel` ADD COLUMN `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `employee_code`",
        'first_name' => "ALTER TABLE `personnel` ADD COLUMN `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `prefix`",
        'last_name' => "ALTER TABLE `personnel` ADD COLUMN `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `first_name`",
        'first_name_en' => "ALTER TABLE `personnel` ADD COLUMN `first_name_en` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `fullname`",
        'nickname' => "ALTER TABLE `personnel` ADD COLUMN `nickname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `first_name_en`",
        'birth_date' => "ALTER TABLE `personnel` ADD COLUMN `birth_date` date DEFAULT NULL AFTER `nickname`",
        'gender' => "ALTER TABLE `personnel` ADD COLUMN `gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `birth_date`",
        'national_id' => "ALTER TABLE `personnel` ADD COLUMN `national_id` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `gender`",
        'work_group_id' => "ALTER TABLE `personnel` ADD COLUMN `work_group_id` int(11) DEFAULT NULL AFTER `national_id`",
        'division_id' => "ALTER TABLE `personnel` ADD COLUMN `division_id` int(11) DEFAULT NULL AFTER `work_group_id`",
        'appointment_date' => "ALTER TABLE `personnel` ADD COLUMN `appointment_date` date DEFAULT NULL AFTER `division_id`",
        'position_number' => "ALTER TABLE `personnel` ADD COLUMN `position_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `appointment_date`",
        'professional_license_no' => "ALTER TABLE `personnel` ADD COLUMN `professional_license_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `position_number`",
        'license_issue_date' => "ALTER TABLE `personnel` ADD COLUMN `license_issue_date` date DEFAULT NULL AFTER `professional_license_no`",
        'position_id' => "ALTER TABLE `personnel` ADD COLUMN `position_id` int(11) DEFAULT NULL AFTER `license_issue_date`",
        'level_id' => "ALTER TABLE `personnel` ADD COLUMN `level_id` int(11) DEFAULT NULL AFTER `position_name`",
        'current_status_id' => "ALTER TABLE `personnel` ADD COLUMN `current_status_id` int(11) DEFAULT NULL AFTER `level_id`",
        'civil_service_group_id' => "ALTER TABLE `personnel` ADD COLUMN `civil_service_group_id` int(11) DEFAULT NULL AFTER `current_status_id`",
        'civil_service_type_id' => "ALTER TABLE `personnel` ADD COLUMN `civil_service_type_id` int(11) DEFAULT NULL AFTER `civil_service_group_id`",
        'personnel_group_id' => "ALTER TABLE `personnel` ADD COLUMN `personnel_group_id` int(11) DEFAULT NULL AFTER `civil_service_type_id`",
        'affiliation' => "ALTER TABLE `personnel` ADD COLUMN `affiliation` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `personnel_group_id`",
        'salary' => "ALTER TABLE `personnel` ADD COLUMN `salary` decimal(12,2) DEFAULT NULL AFTER `affiliation`",
        'position_allowance' => "ALTER TABLE `personnel` ADD COLUMN `position_allowance` decimal(12,2) DEFAULT NULL AFTER `salary`",
    ];
    foreach ($columns as $name => $sql) {
        if (!personnel_column_exists('personnel', $name)) {
            if (!$conn->query($sql)) throw new Exception('เพิ่มคอลัมน์ ' . $name . ' ไม่สำเร็จ: ' . $conn->error);
            $messages[] = 'เพิ่มคอลัมน์ ' . $name . ' แล้ว';
        }
    }


    if (!personnel_table_exists('departments')) {
        $sql = "CREATE TABLE `departments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `department_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
          `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ใช้งาน',
          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_department_name` (`department_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$conn->query($sql)) throw new Exception('สร้างตาราง departments ไม่สำเร็จ: ' . $conn->error);
        $messages[] = 'สร้างตารางหน่วยงานแล้ว';
    }
    $departmentSeeded = personnel_seed_departments();
    if ($departmentSeeded > 0) $messages[] = 'เพิ่มหน่วยงานเริ่มต้น ' . $departmentSeeded . ' รายการ';

    if (!personnel_table_exists('personnel_career_options')) {
        $sql = "CREATE TABLE `personnel_career_options` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `option_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
          `option_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
          `sort_order` int(11) NOT NULL DEFAULT 0,
          `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_personnel_career_option` (`option_type`,`option_name`),
          KEY `idx_personnel_career_type_status` (`option_type`,`status`,`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$conn->query($sql)) throw new Exception('สร้างตารางตัวเลือกข้อมูลอาชีพไม่สำเร็จ: ' . $conn->error);
        $messages[] = 'สร้างตารางตัวเลือกข้อมูลอาชีพแล้ว';
    }
    $seeded = personnel_seed_career_options();
    if ($seeded > 0) $messages[] = 'เพิ่มตัวเลือกข้อมูลอาชีพเริ่มต้น ' . $seeded . ' รายการ';

    $workGroupSync = personnel_sync_official_work_groups();
    if ($workGroupSync['deactivated'] > 0) $messages[] = 'ปิดกลุ่มงานซ้ำ/ไม่ใช้งาน ' . $workGroupSync['deactivated'] . ' รายการ';
    if ($workGroupSync['remapped'] > 0) $messages[] = 'ย้ายบุคลากรจากชื่อกลุ่มงานซ้ำไปชื่อมาตรฐาน ' . $workGroupSync['remapped'] . ' รายการ';

    if (!personnel_index_exists('personnel', 'uq_personnel_username')) {
        if ($conn->query("ALTER TABLE `personnel` ADD UNIQUE KEY `uq_personnel_username` (`username`)")) $messages[] = 'เพิ่ม Unique Username แล้ว';
    }
    if (!personnel_index_exists('personnel', 'uq_personnel_national_id')) {
        if ($conn->query("ALTER TABLE `personnel` ADD UNIQUE KEY `uq_personnel_national_id` (`national_id`)")) $messages[] = 'เพิ่ม Unique เลขบัตรประชาชนแล้ว';
    }
    foreach ([
        'idx_personnel_user_id'=>'user_id','idx_personnel_work_group'=>'work_group_id','idx_personnel_division'=>'division_id',
        'idx_personnel_position'=>'position_id','idx_personnel_level'=>'level_id','idx_personnel_current_status'=>'current_status_id',
        'idx_personnel_personnel_group'=>'personnel_group_id'
    ] as $index => $column) {
        if (!personnel_index_exists('personnel', $index) && $conn->query("ALTER TABLE `personnel` ADD KEY `{$index}` (`{$column}`)")) {
            $messages[] = 'เพิ่ม Index ' . $column . ' แล้ว';
        }
    }

    if (!personnel_table_exists('personnel_audit_logs')) {
        $sql = "CREATE TABLE `personnel_audit_logs` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `personnel_id` int(11) DEFAULT NULL,
          `actor_user_id` int(11) DEFAULT NULL,
          `action` varchar(50) NOT NULL,
          `detail` text DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_personnel_audit_personnel` (`personnel_id`),
          KEY `idx_personnel_audit_actor` (`actor_user_id`),
          KEY `idx_personnel_audit_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$conn->query($sql)) throw new Exception('สร้างตาราง personnel_audit_logs ไม่สำเร็จ: ' . $conn->error);
        $messages[] = 'สร้างตารางบันทึกประวัติการจัดการแล้ว';
    }
    return $messages;
}

function personnel_log_action($personnelId, $action, $detail = '')
{
    global $conn;
    if (!personnel_table_exists('personnel_audit_logs')) return;
    $actor = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $stmt = $conn->prepare('INSERT INTO personnel_audit_logs (personnel_id, actor_user_id, action, detail) VALUES (?, ?, ?, ?)');
    if ($stmt) {
        $pid = $personnelId ? (int)$personnelId : null;
        $stmt->bind_param('iiss', $pid, $actor, $action, $detail);
        $stmt->execute(); $stmt->close();
    }
}

function personnel_bind_params($stmt, $types, &$params)
{
    if ($types === '' || !$params) return true;
    $args = [$types];
    foreach ($params as $key => $value) $args[] = &$params[$key];
    return call_user_func_array([$stmt, 'bind_param'], $args);
}

function personnel_clean_national_id($value) { return preg_replace('/\D+/', '', (string)$value); }
function personnel_validate_national_id($value) { return preg_match('/^\d{13}$/', (string)$value) === 1; }
function personnel_mask_national_id($value)
{
    $value = personnel_clean_national_id($value);
    if (strlen($value) !== 13) return '-';
    return substr($value,0,1).'-'.substr($value,1,4).'-XXXXX-'.substr($value,10,2).'-'.substr($value,12,1);
}
function personnel_format_national_id($value)
{
    $value = personnel_clean_national_id($value);
    if (strlen($value) !== 13) return $value ?: '-';
    return substr($value,0,1).'-'.substr($value,1,4).'-'.substr($value,5,5).'-'.substr($value,10,2).'-'.substr($value,12,1);
}
function personnel_status_meta($status)
{
    if ($status === 'inactive') return ['label'=>'พ้นสภาพ / ไม่ปฏิบัติงาน','class'=>'inactive'];
    return ['label'=>'ปฏิบัติงาน','class'=>'active'];
}
function personnel_gender_options() { return ['ชาย','หญิง','ไม่ระบุ']; }
function personnel_prefix_options() { return ['นาย','นาง','นางสาว','นพ.','พญ.','ทพ.','ทพญ.','ภก.','ภญ.','พว.','อื่นๆ']; }

function personnel_departments()
{
    global $conn;
    $items = [];
    if (personnel_table_exists('departments')) {
        $result = $conn->query("SELECT id, department_name FROM departments WHERE status='ใช้งาน' ORDER BY department_name ASC");
        if ($result) while ($row = $result->fetch_assoc()) $items[] = $row;
    }
    return $items;
}

function personnel_department_name($departmentId)
{
    global $conn;
    $id = (int)$departmentId;
    if ($id <= 0 || !personnel_table_exists('departments')) return null;
    $stmt = $conn->prepare("SELECT department_name FROM departments WHERE id=? AND status='ใช้งาน' LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('i', $id); $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc(); $stmt->close();
    return $row ? $row['department_name'] : null;
}

function personnel_career_options($type, $activeOnly = true)
{
    global $conn;
    $types = personnel_career_option_types();
    if (!isset($types[$type]) || !personnel_table_exists('personnel_career_options')) return [];
    $sql = 'SELECT id, option_type, option_name, sort_order, status FROM personnel_career_options WHERE option_type=?';
    if ($activeOnly) $sql .= " AND status='active'";
    $sql .= ' ORDER BY sort_order ASC, option_name ASC';
    $stmt = $conn->prepare($sql); $stmt->bind_param('s', $type); $stmt->execute();
    $result = $stmt->get_result(); $items = [];
    while ($row = $result->fetch_assoc()) $items[] = $row;
    $stmt->close(); return $items;
}

function personnel_career_option_name($id, $type = '')
{
    global $conn;
    $id = (int)$id;
    if ($id <= 0 || !personnel_table_exists('personnel_career_options')) return null;
    if ($type !== '') {
        $stmt = $conn->prepare('SELECT option_name FROM personnel_career_options WHERE id=? AND option_type=? LIMIT 1');
        $stmt->bind_param('is', $id, $type);
    } else {
        $stmt = $conn->prepare('SELECT option_name FROM personnel_career_options WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $id);
    }
    $stmt->execute(); $row = $stmt->get_result()->fetch_assoc(); $stmt->close();
    return $row ? $row['option_name'] : null;
}

function personnel_validate_career_option($id, $type)
{
    return personnel_career_option_name((int)$id, $type) !== null;
}

function personnel_money_value($value)
{
    if ($value === '' || $value === null) return null;
    $value = str_replace(',', '', trim((string)$value));
    return is_numeric($value) ? round((float)$value, 2) : null;
}

function personnel_user_account($userId)
{
    global $conn;
    if (!$userId) return null;
    $stmt = $conn->prepare('SELECT id, fullname, username, email, department, status, role, created_at FROM users WHERE id = ? LIMIT 1');
    $id = (int)$userId; $stmt->bind_param('i', $id); $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $row ?: null;
}
