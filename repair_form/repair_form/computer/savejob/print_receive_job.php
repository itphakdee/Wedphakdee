<?php
include("../../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

$sql = "
SELECT
    rr.*,
    r.sender_name,
    r.department,
    r.repair_system,
    r.location,
    r.details,
    r.priority

FROM repair_receive_jobs rr

LEFT JOIN repair_jobs r
ON rr.repair_job_id = r.id

WHERE rr.id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die($conn->error);
}

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

    <meta charset="utf-8">

    <title>ใบรับงานซ่อม</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Tahoma;
            margin: 40px;
        }

        table {
            width: 100%;
        }

        th {
            width: 220px;
            background: #f2f2f2;
        }

        .signature {

            margin-top: 80px;

            display: flex;

            justify-content: space-between;

        }

        .signature div {

            width: 250px;

            text-align: center;

        }

        @media print {

            button {
                display: none;
            }

        }
    </style>

</head>

<body>

    <div class="container">

        <h2 class="text-center">
            ใบรับงานซ่อม
        </h2>

        <hr>

        <table class="table table-bordered">

            <tr>

                <th>เลขที่งาน</th>

                <td><?= $row['repair_job_id'] ?></td>

            </tr>

            <tr>

                <th>ผู้แจ้ง</th>

                <td><?= htmlspecialchars($row['sender_name']) ?></td>

            </tr>

            <tr>

                <th>แผนก</th>

                <td><?= htmlspecialchars($row['department']) ?></td>

            </tr>

            <tr>

                <th>ระบบที่แจ้งซ่อม</th>

                <td><?= htmlspecialchars($row['repair_system']) ?></td>

            </tr>

            <tr>

                <th>สถานที่</th>

                <td><?= htmlspecialchars($row['location']) ?></td>

            </tr>

            <tr>

                <th>รายละเอียดที่แจ้ง</th>

                <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>

            </tr>

            <tr>

                <th>ช่างผู้รับงาน</th>

                <td><?= htmlspecialchars($row['technician_name']) ?></td>

            </tr>

            <tr>

                <th>สถานะงาน</th>

                <td><?= htmlspecialchars($row['status']) ?></td>

            </tr>

            <tr>

                <th>ประเภทการซ่อม</th>

                <td><?= htmlspecialchars($row['repair_type']) ?></td>

            </tr>

            <tr>

                <th>อุปกรณ์ที่เปลี่ยน</th>

                <td><?= htmlspecialchars($row['device_name']) ?></td>

            </tr>

            <tr>

                <th>ราคา</th>

                <td><?= number_format($row['price'], 2) ?> บาท</td>

            </tr>

            <tr>

                <th>จำนวน</th>

                <td><?= $row['qty'] ?></td>

            </tr>

            <tr>

                <th>รวมเงิน</th>

                <td><b><?= number_format($row['total_price'], 2) ?> บาท</b></td>

            </tr>

            <tr>

                <th>หมายเหตุ</th>

                <td><?= nl2br(htmlspecialchars($row['technician_note'])) ?></td>

            </tr>

            <tr>

                <th>วันที่รับงาน</th>

                <td><?= $row['created_at'] ?></td>

            </tr>

        </table>

        <div class="signature">

            <div>

                ............................................

                <br>

                ผู้แจ้ง

            </div>

            <div>

                ............................................

                <br>

                ช่างผู้รับงาน

            </div>

            <div>

                ............................................

                <br>

                ผู้อนุมัติ

            </div>

        </div>

        <div class="text-center mt-5">

            <button class="btn btn-primary" onclick="window.print()">

                🖨 พิมพ์ใบรับงาน

            </button>

            <button class="btn btn-secondary" onclick="window.close()">

                ปิด

            </button>

        </div>

    </div>

</body>

</html>