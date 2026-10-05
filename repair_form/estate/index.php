<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}

$sql = "SELECT * FROM estate ORDER BY id DESC";
$result = $conn->query($sql);

$total = $conn->query("SELECT COUNT(*) total FROM estate")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="utf-8">

    <title>ทะเบียนที่ดิน</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <style>
        body {

            background: #f4f6f9;

        }

        .card {

            border: none;

            border-radius: 10px;

        }

        .card-header {

            background: #0d6efd;

            color: white;

            font-size: 22px;

            font-weight: bold;

        }

        .table thead th {

            background: #fce7c2;

            text-align: center;

            vertical-align: middle;

            white-space: nowrap;

        }

        .table td {

            vertical-align: middle;

        }

        .btn {

            border-radius: 8px;

        }

        .dataTables_filter {

            display: none;

        }
    </style>

</head>

<body>

    <div class="container-fluid mt-3">

        <div class="card shadow">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <i class="fas fa-map-marked-alt"></i>

                    ทะเบียนที่ดิน

                </div>

                <a href="add.php" class="btn btn-light">

                    <i class="fas fa-plus"></i>

                    เพิ่มข้อมูล

                </a>

            </div>

            <div class="card-body">

                <div class="row mb-3">

                    <div class="col-md-8">

                        <input

                            type="text"

                            id="search"

                            class="form-control"

                            placeholder="ค้นหาเลขระวาง / เลขที่ดิน / เลขโฉนด">

                    </div>

                    <div class="col-md-2">

                        <button class="btn btn-primary w-100" id="btnSearch">

                            <i class="fas fa-search"></i>

                            ค้นหา

                        </button>

                    </div>

                    <div class="col-md-2">

                        <a href="../../repair_form/propertywork/index.php"

                            class="btn btn-secondary w-100">

                            <i class="fas fa-arrow-left"></i>

                            กลับ

                        </a>

                    </div>

                </div>

                <div class="alert alert-info">

                    ข้อมูลทั้งหมด

                    <b><?= number_format($total) ?></b>

                    รายการ

                </div>

                <div class="table-responsive">

                    <table

                        id="estateTable"

                        class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th width="60">ลำดับ</th>

                                <th>เลขระวาง</th>

                                <th>เลขที่ดิน</th>

                                <th>เลขที่โฉนดเอกสารสิทธิ์</th>

                                <th>จำนวนไร่</th>

                                <th>จำนวนงาน</th>

                                <th>จำนวนตารางวา</th>

                                <th>เขตสำนักงานที่ดิน</th>

                                <th width="220">คำสั่ง</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $no = 1;

                            while ($row = $result->fetch_assoc()) {

                            ?>

                                <tr>

                                    <td class="text-center"><?= $no++ ?></td>

                                    <td><?= $row['survey_no'] ?></td>

                                    <td><?= $row['land_no'] ?></td>

                                    <td><?= $row['deed_no'] ?></td>

                                    <td class="text-center"><?= $row['rai'] ?></td>

                                    <td class="text-center"><?= $row['ngan'] ?></td>

                                    <td class="text-center"><?= $row['square_wa'] ?></td>

                                    <td><?= $row['land_office'] ?></td>

                                    <td class="text-center">

                                        <div class="btn-group">

                                            <a

                                                href="detail.php?id=<?= $row['id'] ?>"

                                                class="btn btn-info btn-sm">

                                                รายละเอียด

                                            </a>

                                            <button

                                                class="btn btn-info btn-sm dropdown-toggle dropdown-toggle-split"

                                                data-bs-toggle="dropdown">

                                            </button>

                                            <ul class="dropdown-menu">

                                                <li>

                                                    <a

                                                        class="dropdown-item"

                                                        href="edit.php?id=<?= $row['id'] ?>">

                                                        <i class="fas fa-edit"></i>

                                                        แก้ไข

                                                    </a>

                                                </li>

                                                <li>

                                                    <a

                                                        class="dropdown-item"

                                                        href="print_estate.php?id=<?= $row['id'] ?>"

                                                        target="_blank">

                                                        <i class="fas fa-print"></i>

                                                        พิมพ์

                                                    </a>

                                                </li>

                                                <li>

                                                    <hr class="dropdown-divider">

                                                </li>

                                                <li>

                                                    <a

                                                        class="dropdown-item text-danger"

                                                        onclick="return confirm('ยืนยันการลบข้อมูล?')"

                                                        href="delete.php?id=<?= $row['id'] ?>">

                                                        <i class="fas fa-trash"></i>

                                                        ลบ

                                                    </a>

                                                </li>

                                            </ul>

                                        </div>

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

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(function() {

            var table = $("#estateTable").DataTable({

                pageLength: 10,

                ordering: true,

                language: {

                    url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/th.json"

                }

            });

            $("#btnSearch").click(function() {

                table.search($("#search").val()).draw();

            });

            $("#search").keyup(function(e) {

                if (e.keyCode == 13) {

                    table.search($(this).val()).draw();

                }

            });

        });
    </script>

</body>

</html>