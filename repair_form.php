
<?php
$conn = new mysqli("localhost", "root", "12345678", "login_db");
$technician_id = $_POST['technician_id'];
$sql = "SELECT id, name FROM technicians";
$result = $conn->query($sql);
$stmt = $conn->prepare("
INSERT INTO repair_jobs (sender_name, department, details, technician_id)
VALUES (?, ?, ?, ?)
");

$stmt->bind_param("sssi", $sender_name, $department, $details, $technician_id);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แจ้งซ่อมออนไลน์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>แจ้งรายละเอียดการซ่อม</h4>
        </div>
        <div class="card-body">
            <form action="lineapi/save_repair.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">ชื่อผู้ส่ง:</label>
                    <input type="text" name="sender_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">แผนก:</label>
                    <select name="department" class="form-select">
                        <option value="IT">IT</option>
                        <option value="บริหาร">บริหาร</option>
                        <option value="OPD">OPD</option>
                        <option value="IPD">IPD</option>
                    </select>
                </div>
                 <div class="mb-3">
                    <label class="form-label">ระบบที่ต้องการแจ้งซ่อม:</label>
                    <select name="department" class="form-select">
                        <option value="IT">ระบบไฟฟ้า</option>
                        <option value="บริหาร">ระบบคอมพิวเตอร์</option>
                        <option value="OPD">ระบบจัดการเครื่องมือแพทย์</option>
                        <option value="IPD">ระบบทั่วไป</option>
                    </select>
                </div>
                    <div class="mb-3">
                    <label class="form-label">สถานที่พบเจอปัญหา:</label>
                    <select name="department" class="form-select">
                        <option value="ผู้ป่วยนอก">ผู้ป่วยนอก</option>
                        <option value="ผู้ป่วยใน">ผู้ป่วยใน</option>
                        <option value="บริหาร">บริหาร</option>
                        <option value="ตึก10เตียง">ตึก10เตียง</option>
                    </select>
                </div>
                  <div class="mb-3">
                    <label class="form-label">เลือกช่าง:</label>
                    
                    <select name="department" class="form-select">
                      <?php while($row = $result->fetch_assoc()): ?>
        <option value="<?= $row['id']; ?>">
            <?= $row['name']; ?>
        </option>
    <?php endwhile; ?>
                    </select>
                </div>
                  
              
                
            

 


</select>
                <div class="mb-3">
                    <label class="form-label">รายละเอียดแจ้งซ่อม:</label>
                    <textarea name="details" class="form-control" rows="4" required></textarea>
                       <a href="dashboard.php" class="btn-back">กลับ</a>
                </div>
                <button type="submit" class="btn btn-success w-100">ส่งข้อมูลและแจ้งเตือน Line</button>
            </form>
        </div>
    </div>
</body>
</html>