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

    <title>รายละเอียดงานรับซ่อม</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container mt-4">

        <div class="card shadow">

            <div class="card-header bg-primary text-white">

                <h4>รายละเอียดงานรับซ่อม</h4>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <tr>
                        <th width="250">เลขที่งาน</th>
                        <td><?= $row['repair_job_id'] ?></td>
                    </tr>

                    <tr>
                        <th>ชื่อผู้แจ้ง</th>
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
                        <th>ความเร่งด่วน</th>
                        <td>

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

                        </td>

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
                        <th>ราคา/หน่วย</th>
                        <td><?= number_format($row['price'], 2) ?> บาท</td>
                    </tr>

                    <tr>
                        <th>จำนวน</th>
                        <td><?= $row['qty'] ?></td>
                    </tr>

                    <tr>
                        <th>รวมเงิน</th>
                        <td class="text-danger fw-bold">
                            <?= number_format($row['total_price'], 2) ?> บาท
                        </td>
                    </tr>

                    <tr>
                        <th>หมายเหตุช่าง</th>
                        <td><?= nl2br(htmlspecialchars($row['technician_note'])) ?></td>
                    </tr>

                    <tr>
                        <th>วันที่รับงาน</th>
                        <td><?= $row['created_at'] ?></td>
                    </tr>

                </table>

                <div class="text-center">

                    <a href="receive_job_list.php" class="btn btn-secondary">
                        ย้อนกลับ
                    </a>

                    <a href="print_receive_job.php?id=<?= $row['id'] ?>" class="btn btn-success" target="_blank">

                        🖨 พิมพ์ใบรับงาน

                    </a>

                </div>

            </div>

        </div>

    </div>

</body>

</html>