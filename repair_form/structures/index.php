<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}


/*=========================================
    Dashboard
==========================================*/

// จำนวนอาคารทั้งหมด
$sqlTotal = "SELECT COUNT(*) AS total FROM buildings";
$rowTotal = $conn->query($sqlTotal)->fetch_assoc();
$total_building = $rowTotal['total'];

// มูลค่ารวม
$sqlAmount = "SELECT SUM(amount) AS total_amount FROM buildings";
$rowAmount = $conn->query($sqlAmount)->fetch_assoc();
$total_amount = $rowAmount['total_amount'];

if ($total_amount == "") {
    $total_amount = 0;
}

// งบประมาณ
$sqlBudget = "
SELECT budget_type,
COUNT(*) total
FROM buildings
GROUP BY budget_type
";
$budget = $conn->query($sqlBudget);

// อายุเฉลี่ย
$sqlAge = "
SELECT AVG(age) avg_age
FROM buildings
";
$rowAge = $conn->query($sqlAge)->fetch_assoc();

$avg_age = round($rowAge['avg_age']);

// รายการอาคาร
$sql = "
SELECT *
FROM buildings
ORDER BY id DESC
";

$result = $conn->query($sql);
$total = $result->fetch_assoc();
?>
<!doctype html>

<html lang="th">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>

        ระบบอาคารและสิ่งปลูกสร้าง

    </title>

    <link
        href="../../assets/bootstrap/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --blue: #17BD08;
            --green: #198754;
            --orange: #ff7a00;
            --purple: #6f42c1;
            --light: #f4f7fb;
            --text: #263238;
            --border: #dfe5ec;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            background: #f3f6fa;
            color: var(--text);
            font-family: "Sarabun", "Noto Sans Thai", Tahoma, sans-serif;
            font-size: 15px;
        }

        .page-wrap {
            padding: 18px
        }

        .main-card {
            background: #fff;
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        }

        .page-header {
            padding: 18px 22px;
            background: var(--blue);
            color: #fff;
            border-bottom: 0;
        }

        .page-header h1 {
            margin: 0;
            font-size: 27px;
            font-weight: 700;
        }

        .page-header .subtitle {
            color: #fff;
            margin-top: 4px;
            font-size: 14px;
        }

        .header-icon {
            width: auto;
            height: auto;
            border-radius: 0;
            background: transparent;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            margin-right: 10px;
        }

        .stat-card {
            border: 0;
            border-radius: 14px;
            padding: 20px;
            min-height: 122px;
            color: #fff;
            box-shadow: 0 6px 15px rgba(0, 0, 0, .14);
        }

        .stat-card:nth-child(1) {
            background: var(--blue)
        }

        .stat-card:nth-child(2) {
            background: var(--green)
        }

        .stat-card:nth-child(3) {
            background: var(--orange)
        }

        .stat-card:nth-child(4) {
            background: var(--purple)
        }

        .stat-blue {
            background: var(--blue)
        }

        .stat-green {
            background: var(--green)
        }

        .stat-orange {
            background: var(--orange)
        }

        .stat-purple {
            background: var(--purple)
        }

        .stat-label {
            color: #fff;
            font-size: 14px;
            margin-bottom: 7px
        }

        .stat-value {
            font-size: 30px;
            font-weight: 700;
            line-height: 1.15;
            color: #fff
        }

        .stat-value small {
            color: #fff !important
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .20);
            color: rgba(255, 255, 255, .55);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .toolbar {
            background: #fff;
            border: 0;
            border-radius: 0;
            padding: 16px 10px;
            margin-bottom: 12px;
        }

        .toolbar .form-control,
        .toolbar .form-select {
            border-color: #cfd6df;
            border-radius: 4px;
            min-height: 34px;
        }

        .toolbar .input-group-text {
            background: #f1f3f5;
            border-color: #cfd6df;
            color: #52606d;
        }

        .action-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 8px;
        }

        .action-bar .btn {
            border-radius: 3px;
            font-weight: 600;
            border: 0;
        }

        .action-bar .btn-primary {
            background: #198754
        }

        .action-bar .btn-outline-primary {
            background: #0d6efd;
            color: #fff
        }

        .action-bar .btn-outline-secondary {
            background: #198754;
            color: #fff
        }

        .action-bar .btn-outline-secondary:last-child {
            background: #ffc107;
            color: #212529
        }

        .table-card {
            background: #fff;
            border: 0;
            border-radius: 0;
            overflow: hidden;
        }

        #buildingTable {
            margin: 0 !important;
            border: 0;
        }

        #buildingTable thead th {
            background: #2167b6 !important;
            color: #fff !important;
            border-color: #d7e0ea !important;
            font-weight: 700;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            padding: 12px 10px;
        }

        #buildingTable tbody td {
            border-color: #dfe5ec;
            vertical-align: middle;
            padding: 11px 10px;
            color: #374151;
        }

        #buildingTable tbody tr:nth-child(even) {
            background: #f8fafc
        }

        #buildingTable tbody tr:hover {
            background: #eef5ff
        }

        .row-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 27px;
            height: 27px;
            padding: 0 8px;
            border-radius: 6px;
            background: #0d6efd;
            color: #fff;
            font-weight: 700;
        }

        .building-name {
            font-weight: 600;
            color: #1f2937
        }

        .amount {
            font-weight: 600;
            color: #198754
        }

        .neutral-badge {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
            border: 0;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 4px
        }

        .action-buttons .btn {
            width: 31px;
            height: 30px;
            padding: 0;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            color: #fff;
        }

        .action-buttons .btn:nth-child(1) {
            background: #17a2b8
        }

        .action-buttons .btn:nth-child(2) {
            background: #ffc107;
            color: #212529
        }

        .action-buttons .btn:nth-child(3) {
            background: #0d6efd
        }

        .action-buttons .btn:nth-child(4) {
            background: #dc3545
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cfd6df;
            border-radius: 4px;
            padding: 6px 9px;
            margin-left: 6px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cfd6df;
            border-radius: 4px;
            padding: 4px 25px 4px 7px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 4px !important;
        }

        .dataTables_wrapper .dt-buttons {
            display: none
        }

        @media(max-width:768px) {
            .page-wrap {
                padding: 8px
            }

            .page-header {
                padding: 16px
            }

            .page-header h1 {
                font-size: 21px
            }

            .stat-value {
                font-size: 25px
            }

            .action-bar .btn {
                width: 100%
            }
        }

        @media print {
            body {
                background: #fff
            }

            .no-print,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_paginate,
            .dataTables_wrapper .dataTables_info {
                display: none !important
            }

            .main-card,
            .table-card,
            .stat-card,
            .toolbar {
                box-shadow: none
            }

            .page-wrap {
                padding: 0
            }
        }
    </style>

</head>


<body>
    <div class="page-wrap">
        <div class="main-card">

            <div class="page-header">
                <div class="d-flex align-items-center">
                    <div class="header-icon"><i class="fas fa-building"></i></div>
                    <div>
                        <h1>ระบบทะเบียนอาคารและสิ่งปลูกสร้าง</h1>
                        <div class="subtitle">โรงพยาบาลภักดีชุมพล</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="stat-card stat-blue">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">จำนวนอาคาร</div>
                                    <div class="stat-value"><?= number_format($total_building) ?></div>
                                </div>
                                <div class="stat-icon"><i class="fa fa-building"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="stat-card stat-green">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">มูลค่ารวม</div>
                                    <div class="stat-value"><?= number_format($total_amount, 2) ?></div>
                                </div>
                                <div class="stat-icon"><i class="fa fa-coins"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="stat-card stat-orange">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">อายุเฉลี่ย</div>
                                    <div class="stat-value"><?= $avg_age ?> <small class="fs-6 fw-normal text-muted">ปี</small></div>
                                </div>
                                <div class="stat-icon"><i class="fa fa-clock"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="stat-card stat-purple">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">ประเภทงบประมาณ</div>
                                    <div class="stat-value"><?= $budget->num_rows ?></div>
                                </div>
                                <div class="stat-icon"><i class="fa fa-wallet"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="toolbar no-print">
                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">ค้นหาอาคาร</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาชื่ออาคาร...">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label fw-semibold">งบประมาณ</label>
                            <select id="budgetFilter" class="form-select">
                                <option value="">ทุกงบประมาณ</option>
                                <?php
                                $budgetList = $conn->query("
                            SELECT DISTINCT budget_type
                            FROM buildings
                            ORDER BY budget_type
                        ");
                                while ($b = $budgetList->fetch_assoc()) {
                                ?>
                                    <option value="<?= htmlspecialchars($b['budget_type']) ?>">
                                        <?= htmlspecialchars($b['budget_type']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label fw-semibold">อายุอาคาร</label>
                            <select id="ageFilter" class="form-select">
                                <option value="">ทุกอายุ</option>
                                <option value="10">ต่ำกว่า 10 ปี</option>
                                <option value="20">10–20 ปี</option>
                                <option value="30">20–30 ปี</option>
                                <option value="100">มากกว่า 30 ปี</option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-4 text-lg-end">
                            <button id="btnSearch" class="btn btn-primary btn-clean me-1">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                            <button type="button" onclick="location.reload()" class="btn btn-outline-secondary btn-clean">
                                <i class="fas fa-rotate-right"></i> รีเฟรช
                            </button>
                        </div>

                    </div>
                </div>

                <div class="action-bar no-print">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> เพิ่มอาคาร
                    </a>
                    <a href="import_excel.php" class="btn btn-outline-primary">
                        <i class="fas fa-file-import"></i> Import Excel
                    </a>
                    <a href="export_excel.php" class="btn btn-outline-secondary">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="fas fa-print"></i> พิมพ์
                    </button>
                    <a href="../../repair_form/propertywork/index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> กลับ
                    </a>
                </div>

                <div class="table-card">
                    <div class="table-responsive">

                        <table

                            id="buildingTable"

                            class="table table-bordered table-hover table-striped">

                            <thead>

                                <tr>

                                    <th width="60">

                                        ลำดับ

                                    </th>

                                    <th>

                                        ชื่ออาคาร

                                    </th>

                                    <th width="180">

                                        จำนวนเงิน

                                    </th>

                                    <th width="150">

                                        วันที่เริ่มสร้าง

                                    </th>

                                    <th width="100">

                                        อายุ

                                    </th>

                                    <th width="180">

                                        งบประมาณ

                                    </th>

                                    <th width="240">

                                        จัดการ

                                    </th>

                                </tr>

                            </thead>

                            <tbody>
                                <?php

                                $no = 1;

                                if ($result->num_rows > 0) {

                                    while ($row = $result->fetch_assoc()) {

                                ?>

                                        <tr>

                                            <td class="text-center">

                                                <span class="row-number"><?= $no++ ?></span>

                                            </td>

                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars($row['building_name']) ?>

                                                </strong>

                                            </td>

                                            <td class="text-end">

                                                <span class="amount"><?= number_format($row['amount'], 2) ?></span>

                                            </td>

                                            <td class="text-center">

                                                <?= date("d/m/Y", strtotime($row['start_date'])) ?>

                                            </td>

                                            <td class="text-center">

                                                <?php

                                                echo '<span class="neutral-badge">' . htmlspecialchars($row['age']) . ' ปี</span>';

                                                ?>

                                            </td>

                                            <td class="text-center">

                                                <?php

                                                echo '<span class="neutral-badge">' . htmlspecialchars($row['budget_type']) . '</span>';

                                                ?>

                                            </td>

                                            <td class="text-center">

                                                <div class="action-buttons">

                                                    <a

                                                        href="detail.php?id=<?= $row['id'] ?>"

                                                        class="btn btn-sm">

                                                        <i class="fas fa-eye"></i>

                                                    </a>

                                                    <a

                                                        href="edit.php?id=<?= $row['id'] ?>"

                                                        class="btn btn-sm">

                                                        <i class="fas fa-edit"></i>

                                                    </a>

                                                    <a

                                                        href="print_structure.php?id=<?= $row['id'] ?>"

                                                        target="_blank"

                                                        class="btn btn-sm">

                                                        <i class="fas fa-print"></i>

                                                    </a>

                                                    <a

                                                        href="delete.php?id=<?= $row['id'] ?>"

                                                        onclick="return confirm('ยืนยันการลบข้อมูล ?')"

                                                        class="btn btn-sm">

                                                        <i class="fas fa-trash"></i>

                                                    </a>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php

                                    }
                                } else {

                                    ?>

                                    <tr>

                                        <td colspan="7" class="text-center">

                                            ไม่มีข้อมูล

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
    </div>
    <!-- Bootstrap -->

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- JQuery -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {

            var table = $("#buildingTable").DataTable({

                responsive: true,

                autoWidth: false,

                pageLength: 15,

                lengthMenu: [
                    [15, 25, 50, 100, -1],
                    [15, 25, 50, 100, "ทั้งหมด"]
                ],

                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/th.json"
                },

                dom: 'frtip',

                order: [
                    [0, 'desc']
                ]

            });


            // =============================
            // Search
            // =============================

            $("#searchInput").keyup(function() {

                table.search($(this).val()).draw();

            });


            // =============================
            // Budget Filter
            // =============================

            $("#budgetFilter").change(function() {

                table.column(5).search($(this).val()).draw();

            });


            // =============================
            // Age Filter
            // =============================

            $("#ageFilter").change(function() {

                var value = $(this).val();

                if (value == "") {

                    table.column(4).search("").draw();

                } else if (value == "10") {

                    table.column(4).search("0|1|2|3|4|5|6|7|8|9").draw();

                } else if (value == "20") {

                    table.column(4).search("1[0-9]|20").draw();

                } else if (value == "30") {

                    table.column(4).search("2[0-9]|30").draw();

                } else {

                    table.column(4).search("3[1-9]|4[0-9]|5[0-9]").draw();

                }

            });

        });
    </script>
    <?php
    /* ใส่ก่อน </body> ของ index.php */
    if (isset($_GET['status'], $_GET['message'])):
    ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: <?= json_encode($_GET['status'] === 'success' ? 'success' : 'error') ?>,
                    title: <?= json_encode($_GET['status'] === 'success' ? 'บันทึกข้อมูลสำเร็จ' : 'เกิดข้อผิดพลาด') ?>,
                    text: <?= json_encode($_GET['message'], JSON_UNESCAPED_UNICODE) ?>,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#198754'
                });

                if (window.history.replaceState) {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    url.searchParams.delete('message');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>