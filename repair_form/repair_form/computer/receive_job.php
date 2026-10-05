<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../admin/permissions_helper.php";
if (!is_admin_user()) {
    http_response_code(403);
    exit("403 Forbidden: การรับงานซ่อมคอมพิวเตอร์อนุญาตเฉพาะ Admin");
}

$id = $_GET['id'] ?? 0;

$sql = "
SELECT
    r.*,
    t.name AS technician_name
FROM repair_jobs r
LEFT JOIN technicians t
ON r.technician_id = t.id
WHERE r.id = ?
";






$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("ไม่พบข้อมูล");
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รับงานซ่อม</title>
    <input type="hidden" name="id" value="<?= $row['id'] ?>">
    <input type="hidden" name="technician_id" value="<?= $_SESSION['user_id'] ?>">
    <input type="hidden" name="technician_name" value="<?= $_SESSION['fullname'] ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <div class="container mt-4">

        <div class="card shadow">

            <div class="card-header bg-success text-white">
                📥 รับงานซ่อม
            </div>

            <div class="card-body">

                <form action="savejob/save_receive_job.php" method="POST">

                    <input type="hidden" name="id" value="<?= $row['id'] ?>">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">ชื่อผู้ส่ง</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['sender_name']) ?>"
                                readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">แผนก</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['department']) ?>"
                                readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">ระบบที่แจ้งซ่อม</label>
                            <input type="text" class="form-control"
                                value="<?= htmlspecialchars($row['repair_system']) ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">สถานที่</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['location']) ?>"
                                readonly>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">รายละเอียด</label>
                            <textarea class="form-control" rows="4"
                                readonly><?= htmlspecialchars($row['details']) ?></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">ช่างผู้รับงาน</label>
                            <input type="text" class="form-control"
                                value="<?= htmlspecialchars($_SESSION['fullname']) ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">สถานะงาน</label>

                            <select name="status" class="form-select">

                                <option value="กำลังดำเนินการ">
                                    กำลังดำเนินการ
                                </option>

                                <option value="เสร็จสิ้น">
                                    เสร็จสิ้น
                                </option>

                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานะความเร่งด่วน</label><br>

                            <?php
                            switch ($row['priority']) {
                                case 'normal':
                                    echo '<span class="badge bg-success">ปกติ</span>';
                                    break;

                                case 'urgent':
                                    echo '<span class="badge bg-warning text-dark">ด่วน</span>';
                                    break;

                                case 'emergency':
                                    echo '<span class="badge bg-danger">ด่วนมาก</span>';
                                    break;

                                default:
                                    echo '<span class="badge bg-secondary">ไม่ระบุ</span>';
                            }
                            ?>
                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">ประเภทการซ่อม</label>

                                <select name="repair_type" class="form-select" required>

                                    <option value="">-- เลือกประเภท --</option>

                                    <option value="ซ่อมเอง">
                                        ซ่อมเอง
                                    </option>

                                    <option value="ส่งซ่อมข้างนอก">
                                        ส่งซ่อมข้างนอก
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">อุปกรณ์ที่เปลี่ยน</label>

                                <input type="text" name="device_name" class="form-control" placeholder="">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">ราคา / หน่วย</label>

                                <input type="number" name="price" id="price" class="form-control" value="0">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">จำนวน</label>

                                <input type="number" name="qty" id="qty" class="form-control" value="1">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">รวมเงิน</label>

                                <input type="number" name="total_price" id="total_price" class="form-control" readonly>
                            </div>

                        </div>
                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                หมายเหตุช่าง
                            </label>

                            <textarea name="technician_note" class="form-control" rows="4"></textarea>

                        </div>

                    </div>

                    <button type="submit" class="btn btn-success">

                        💾 บันทึกรับงาน

                    </button>

                    <a href="indexrepairlist.php" class="btn btn-secondary">

                        กลับ

                    </a>

                </form>

            </div>

        </div>

    </div>
    <script>

        function calculateTotal() {

            let price =
                parseFloat(document.getElementById('price').value) || 0;

            let qty =
                parseInt(document.getElementById('qty').value) || 0;

            document.getElementById('total_price').value =
                (price * qty).toFixed(2);
        }

        document.getElementById('price')
            .addEventListener('input', calculateTotal);

        document.getElementById('qty')
            .addEventListener('input', calculateTotal);

        calculateTotal();

    </script>
</body>

</html>