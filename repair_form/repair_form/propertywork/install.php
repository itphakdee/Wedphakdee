<?php
require_once '../../config.php';
require_once __DIR__ . '/property_helpers.php';
property_require_login();

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!property_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $error = 'คำขอไม่ถูกต้อง';
    } else {
        $create = "CREATE TABLE IF NOT EXISTS properties (
          id INT(11) NOT NULL AUTO_INCREMENT,
          asset_no VARCHAR(100) NOT NULL,
          budget_year VARCHAR(10) DEFAULT NULL,
          property_name VARCHAR(255) NOT NULL,
          property_type VARCHAR(100) NOT NULL,
          category VARCHAR(100) DEFAULT NULL,
          brand VARCHAR(100) DEFAULT NULL,
          model VARCHAR(100) DEFAULT NULL,
          serial_no VARCHAR(150) DEFAULT NULL,
          department VARCHAR(100) DEFAULT NULL,
          department_unit VARCHAR(255) DEFAULT NULL,
          location VARCHAR(255) DEFAULT NULL,
          responsible_person VARCHAR(150) DEFAULT NULL,
          purchase_date DATE DEFAULT NULL,
          warranty_date DATE DEFAULT NULL,
          vendor VARCHAR(255) DEFAULT NULL,
          price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
          risk_level VARCHAR(100) DEFAULT NULL,
          withdraw_status VARCHAR(100) DEFAULT NULL,
          borrow_department VARCHAR(255) DEFAULT NULL,
          status ENUM('ใช้งาน','ชำรุด','ส่งซ่อม','จำหน่าย') NOT NULL DEFAULT 'ใช้งาน',
          image VARCHAR(255) DEFAULT NULL,
          note TEXT DEFAULT NULL,
          qr_code VARCHAR(255) DEFAULT NULL,
          created_by INT(11) DEFAULT NULL,
          created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id),
          KEY idx_properties_asset_no (asset_no),
          KEY idx_properties_status (status),
          KEY idx_properties_department (department),
          KEY idx_properties_type (property_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if (!$conn->query($create)) {
            $error = $conn->error;
        } else {
            $columns = array(
                'budget_year' => "VARCHAR(10) DEFAULT NULL AFTER asset_no",
                'department_unit' => "VARCHAR(255) DEFAULT NULL AFTER department",
                'risk_level' => "VARCHAR(100) DEFAULT NULL AFTER price",
                'withdraw_status' => "VARCHAR(100) DEFAULT NULL AFTER risk_level",
                'borrow_department' => "VARCHAR(255) DEFAULT NULL AFTER withdraw_status",
                'qr_code' => "VARCHAR(255) DEFAULT NULL AFTER note",
                'created_by' => "INT(11) DEFAULT NULL AFTER qr_code",
                'created_at' => "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER created_by",
                'updated_at' => "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
            );
            foreach ($columns as $name => $definition) {
                $check = $conn->query("SHOW COLUMNS FROM properties LIKE '" . $conn->real_escape_string($name) . "'");
                if ($check && $check->num_rows === 0) {
                    if (!$conn->query("ALTER TABLE properties ADD COLUMN {$name} {$definition}")) {
                        $error .= ($error ? ' | ' : '') . $conn->error;
                    }
                }
            }
            if ($error === '') {
                $message = 'ติดตั้ง / อัปเดตฐานข้อมูลทะเบียนครุภัณฑ์เรียบร้อยแล้ว';
            }
        }
    }
}
property_csrf_token();
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ติดตั้งระบบครุภัณฑ์</title><link href="assets/css/property.css?v=20260831" rel="stylesheet"></head><body class="property-body"><div class="install-box"><span class="eyebrow">PROPERTY DATABASE INSTALLER</span><h1>ติดตั้งฐานข้อมูลทะเบียนครุภัณฑ์</h1><p>ใช้หน้านี้ครั้งแรก หรือเมื่อต้องการตรวจสอบโครงสร้างฐานข้อมูล ระบบจะไม่ลบข้อมูลเดิมในตาราง <code>properties</code></p><?php if($message):?><div class="property-alert alert-success-property">✓ <?php echo property_e($message); ?></div><?php endif;?><?php if($error):?><div class="property-alert alert-danger-property">! <?php echo property_e($error); ?></div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?php echo property_e(property_csrf_token()); ?>"><button class="btn-property btn-primary-property" type="submit">ติดตั้ง / อัปเดตฐานข้อมูล</button> <a class="btn-property btn-light-property" href="index.php">กลับหน้าทะเบียน</a></form></div></body></html>
