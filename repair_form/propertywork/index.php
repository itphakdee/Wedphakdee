<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

$sql = "
SELECT *
FROM properties
ORDER BY id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">

    <title>งานทรัพย์สิน</title>

    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .table th {
            background: #0d6efd;
            color: #fff;
            text-align: center;
        }

        .table td {
            vertical-align: middle;
        }

        .btn {
            border-radius: 8px;
        }
    </style>

</head>

<body>

    <div class="container mt-4">

        <div class="card shadow">

            <div class="card-header bg-primary text-white">

                <h3 class="mb-0">
                    🏢 งานทรัพย์สิน
                </h3>

            </div>

            <div class="card-body">

                <div class="mb-3">

                    <a href="add.php" class="btn btn-success">

                        ➕ เพิ่มทรัพย์สิน

                    </a>

                    <a href="../..//repair_form/home_repair.php" class="btn btn-secondary">
                        🔙 กลับ
                    </a>
                </div>

                <table class="table table-bordered table-hover">

                    <thead>

                        <tr>

                            <th width="70">ID</th>

                            <th>เลขครุภัณฑ์</th>



                            <th>ประเภทครุภัณฑ์ </th>
                            <th>ชื่อทรัพย์สิน</th>
                            <th>แผนก</th>

                            <th>สถานที่</th>


                            <th>วันที่รับเข้า</th>
                            <th>ราคา</th>
                            <th width="250">จัดการ</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        if ($result->num_rows > 0) {

                            while ($row = $result->fetch_assoc()) {
                        ?>

                                <tr>

                                    <td><?= $row['id']; ?></td>

                                    <td><?= $row['asset_no']; ?></td>

                                    <td><?= $row['property_name']; ?></td>

                                    <td><?= $row['property_type']; ?></td>

                                    <td><?= $row['location']; ?></td>

                                    <td><?= $row['department']; ?></td>
                                    <td><?= $row['purchase_date']; ?></td>



                                    <td>

                                        <?= number_format($row['price'], 2); ?>

                                    </td>

                                    <td>

                                        <a href="detail.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">

                                            รายละเอียด

                                        </a>

                                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">

                                            แก้ไข

                                        </a>

                                        <a href="delete.php?id=<?= $row['id']; ?>"

                                            onclick="return confirm('ยืนยันการลบข้อมูล ?')"

                                            class="btn btn-danger btn-sm">

                                            ลบ

                                        </a>

                                        <a href="print_property.php?id=<?= $row['id']; ?>"

                                            target="_blank"

                                            class="btn btn-primary btn-sm">

                                            พิมพ์
                                        </a>

                                    </td>

                                </tr>

                            <?php
                            }
                        } else {
                            ?>

                            <tr>

                                <td colspan="8" class="text-center">

                                    ยังไม่มีข้อมูล

                                </td>

                            </tr>

                        <?php
                        }
                        ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</body>

</html>