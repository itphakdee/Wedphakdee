<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();

}
$data = require_once '../../mock_data/mock_data.php';

include '../../components/header.php';
include '../../components/navbar.php';

/*=========================
Pagination
=========================*/

$limit = 20;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$start = ($page - 1) * $limit;

/*=========================
จำนวนข้อมูลทั้งหมด
=========================*/

$totalQuery = $conn->query("SELECT COUNT(*) total FROM properties");
$totalData = $totalQuery->fetch_assoc();

// ===========================
// รวมมูลค่าครุภัณฑ์
// ===========================

$sqlTotal = $conn->query("
SELECT
    SUM(price) AS total_price
FROM properties
");

$rowTotal = $sqlTotal->fetch_assoc();

$total_price = $rowTotal['total_price'] ?? 0;


// ===========================
// มูลค่าสิ่งก่อสร้าง
// ===========================

$sqlBuilding = $conn->query("
SELECT
    SUM(price) AS total_building
FROM properties
WHERE property_name='สิ่งก่อสร้าง'
");

$rowBuilding = $sqlBuilding->fetch_assoc();

$total_building = $rowBuilding['total_building'] ?? 0;

$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM properties");
$totalData = $totalQuery->fetch_assoc();

$total_records = $totalData['total'];
$total_pages = ceil($total_records / $limit);
// ===========================
// รวมทั้งหมด
// ===========================

$total_all = $total_price + $total_building;

$total_pages = ceil($total_records / $limit);

/*=========================
ดึงข้อมูล
=========================*/

$sql = "
SELECT *
FROM properties
ORDER BY id DESC
LIMIT $start,$limit
";

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">

    <title>งานทรัพย์สิน</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/property.css">
    <link rel="stylesheet" href="../../assets/css/property.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
        }

        .card {
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

        .pagination {
            margin-top: 20px;
        }

        .container-fluid {
            max-width: 100%;
        }

        .table {

            white-space: nowrap;

            font-size: 14px;

        }

        .table thead th {

            position: sticky;

            top: 0;

            background: #1565C0;

            color: #fff;

            z-index: 99;

            text-align: center;

            vertical-align: middle;

            padding: 14px;

        }

        .table td {

            white-space: nowrap;

            vertical-align: middle;

            padding: 10px;

        }

        .table tbody tr:hover {

            background: #edf6ff;

        }

        .table-responsive {

            max-height: 700px;

            overflow: auto;

            border-radius: 10px;

        }

        .table td:nth-child(4) {

            min-width: 220px;

        }

        .table td:nth-child(5) {

            min-width: 260px;

        }

        .table td:nth-child(3) {

            min-width: 170px;

        }

        .table td:nth-child(11) {

            text-align: right;

        }

        .table td:last-child {

            min-width: 220px;

        }

        .table-responsive {

            overflow: auto;

        }

        .table {

            white-space: nowrap;

        }

        .table td {

            white-space: nowrap;

        }

        .card {

            border: none;

        }

        .summary-card {

            border-radius: 15px;

        }

        .input-group-text {

            border-right: none;

        }

        .form-control {

            border-left: none;

        }

        .btn {

            border-radius: 10px;

        }
    </style>

</head>

<body>


    <div class="container-fluid mt-3">

        <?php include 'filter.php'; ?>

        <div class="card shadow">



            <div class=" m-2">
                <div class="mb-4">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <div class="input-group" style="max-width: 500px;">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search"></i>
                                </span>

                                <input type="text" id="searchInput" class="form-control mr-2"
                                    placeholder="ค้นหาเลขครุภัณฑ์ / ชื่อทรัพย์สิน">


                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive shadow rounded">

                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="propertyTable">
                        <thead>
                            <tr>
                                <th width="20">ลำดับ</th>
                                <th>ปีงบ</th>
                                <th>เลขครุภัณฑ์</th>
                                <th>วันที่รับเข้า</th>
                                <th>ประเภทครุภัณฑ์</th>
                                <th>ชื่อ</th>
                                <th>ประจำอยู่หน่วยงาน</th>
                                <th>ความเสี่ยง</th>
                                <th>การเบิกใช้</th>
                                <th>สถานะ</th>
                                <th>หน่วยงานขอยืม</th>
                                <th width="100">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">

                            <?php

                            if ($result->num_rows > 0) {

                                $no = $start + 1;

                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['budget_year']) ?></td>
                                        <td><?= htmlspecialchars($row['asset_no']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($row['purchase_date']) ?></td>
                                        <td><?= htmlspecialchars($row['property_name']) ?></td>
                                        <td><?= htmlspecialchars($row['property_type']) ?></td>
                                        <td><?= htmlspecialchars($row['department']) ?></td>
                                        <td><?= htmlspecialchars($row['department_unit']) ?></td>
                                        <td><?= htmlspecialchars($row['borrow_department']) ?></td>
                                        <td><?= htmlspecialchars($row['withdraw_status']) ?></td>
                                        <td><?= htmlspecialchars($row['withdraw_status']) ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-primary btn-sm btn-view-detail"
                                                data-bs-toggle="modal" data-bs-target="#equipmentDetailModal"
                                                data-id="<?= $row['id'] ?>"
                                                data-asset-no="<?= htmlspecialchars($row['asset_no']) ?>"
                                                data-receive-date="<?= htmlspecialchars($row['purchase_date']) ?>"
                                                data-property-name="<?= htmlspecialchars($row['property_name']) ?>"
                                                data-property-type="<?= htmlspecialchars($row['property_type']) ?>"
                                                data-budget-year="<?= htmlspecialchars($row['budget_year']) ?>"
                                                data-price="<?= number_format($row['price'] ?? 0, 2) ?>"
                                                data-department="<?= htmlspecialchars($row['department']) ?>"
                                                data-unit="<?= htmlspecialchars($row['department_unit']) ?>"
                                                data-building="<?= htmlspecialchars($row['building'] ?? '') ?>"
                                                data-model="<?= htmlspecialchars($row['model'] ?? '') ?>"
                                                data-brand="<?= htmlspecialchars($row['brand'] ?? '') ?>"
                                                data-detail="<?= htmlspecialchars($row['detail'] ?? '') ?>"
                                                data-floor="<?= htmlspecialchars($row['floor'] ?? '') ?>"
                                                data-room="<?= htmlspecialchars($row['room'] ?? '') ?>"
                                                data-size="<?= htmlspecialchars($row['size'] ?? '') ?>"
                                                data-color="<?= htmlspecialchars($row['color'] ?? '') ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="maintenance_detail.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"
                                                title="แก้ไข">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {

                                ?>

                                <tr>

                                    <td colspan="9" class="text-center">

                                        ไม่มีข้อมูล

                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>
                </div>

                <nav>

                    <ul class="pagination justify-content-center">

                        <?php if ($page > 1) { ?>

                            <li class="page-item">

                                <a class="page-link" href="?page=<?= $page - 1 ?>">

                                    « ก่อนหน้า

                                </a>

                            </li>

                        <?php } ?>

                        <?php

                        for ($i = 1; $i <= $total_pages; $i++) {

                            ?>

                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">

                                <a class="page-link" href="?page=<?= $i ?>">

                                    <?= $i ?>

                                </a>

                            </li>

                        <?php } ?>

                        <?php if ($page < $total_pages) { ?>

                            <li class="page-item">

                                <a class="page-link" href="?page=<?= $page + 1 ?>">

                                    ถัดไป »

                                </a>

                            </li>

                        <?php } ?>

                    </ul>

                </nav>

            </div>

        </div>

    </div>

    <div class="modal fade" id="equipmentDetailModal" tabindex="-1" aria-labelledby="equipmentDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-secondary" id="equipmentDetailModalLabel">รายละเอียดครุภัณฑ์
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-5 pb-4">
                    <div class="row g-4">

                        <div class="col-lg-9">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 90px;">รหัส
                                            :</div>
                                        <div id="modal-id" class="text-dark">N/A</div>
                                    </div>
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 90px;">
                                            ครุภัณฑ์ :</div>
                                        <div id="modal-property-name" class="text-dark">N/A</div>
                                    </div>
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 90px;">อาคาร
                                            :</div>
                                        <div id="modal-building" class="text-dark">N/A</div>
                                    </div>
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 90px;">โมเดล
                                            :</div>
                                        <div id="modal-model" class="text-dark">N/A</div>
                                    </div>
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 90px;">
                                            ยี่ห้อ :</div>
                                        <div id="modal-brand" class="text-dark">N/A</div>
                                    </div>
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 90px;">
                                            วันที่รับ :</div>
                                        <div id="modal-receive-date" class="text-dark">N/A</div>
                                    </div>
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 90px;">
                                            รายละเอียด :</div>
                                        <div id="modal-detail" class="text-dark">N/A</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 110px;">
                                            เลขครุภัณฑ์ :</div>
                                        <div id="modal-asset-no" class="text-break text-dark">N/A</div>
                                    </div>

                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="d-flex me-4 align-items-baseline">
                                            <div class="fw-bold text-end me-3 text-secondary" style="min-width: 110px;">
                                                ชั้น :</div>
                                            <div id="modal-floor" class="text-dark">N/A</div>
                                        </div>
                                        <div class="d-flex align-items-baseline">
                                            <div class="fw-bold text-end me-3 text-secondary" style="min-width: 50px;">
                                                ห้อง :</div>
                                            <div id="modal-room" class="text-dark">N/A</div>
                                        </div>
                                    </div>

                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 110px;">ขนาด
                                            :</div>
                                        <div id="modal-size" class="text-dark">N/A</div>
                                    </div>
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 110px;">สี :
                                        </div>
                                        <div id="modal-color" class="text-dark">N/A</div>
                                    </div>
                                    <div class="d-flex mb-3 align-items-baseline">
                                        <div class="fw-bold text-end me-3 text-secondary" style="min-width: 110px;">ราคา
                                            :</div>
                                        <div id="modal-price" class="text-dark fw-bold">N/A</div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-lg-3 d-flex justify-content-center align-items-start">
                            <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center text-muted bg-light"
                                style="width: 100%; max-width: 240px; aspect-ratio: 1/1;">
                                <i class="fas fa-image text-secondary fs-2 mb-2"></i>
                                <span class="small text-center text-secondary">กรุณาเพิ่มรูปภาพ</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top-0 bg-light justify-content-end py-3 px-4">
                    <button type="button" class="btn btn-secondary px-4 d-flex align-items-center"
                        data-bs-dismiss="modal" style="background-color: #6c757d; border-color: #6c757d;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-x-lg me-2" viewBox="0 0 16 16">
                            <path
                                d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
                        </svg>
                        ปิดหน้าต่าง
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/property.js"></script>
    <script src="../../assets/js/search.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            // ใช้ Event Delegation ($(document).on) เพื่อจับคลิกปุ่ม .btn-view-detail ได้ทั้งปุ่มเก่าและปุ่มใหม่จาก AJAX
            $(document).on('click', '.btn-view-detail', function () {
                console.log("🔥 ตรวจพบการคลิกปุ่มแสดงรายละเอียดสำเร็จ!");

                var $btn = $(this);

                // ฟังก์ชันช่วยเคลียร์ค่ากรณีข้อมูลในดิกชันนารีไม่มีค่า ให้ขึ้น N/A
                function clean(val) {
                    return (val === undefined || val === null || String(val).trim() === '') ? 'N/A' : val;
                }

                // ดึงค่าจาก data-attributes ของปุ่มที่ถูกคลิก
                var id = clean($btn.data('id'));
                var assetNo = clean($btn.data('asset-no'));
                var propertyName = clean($btn.data('property-name'));
                var propertyType = clean($btn.data('property-type'));
                var budgetYear = clean($btn.data('budget-year'));
                var price = clean($btn.data('price'));
                var department = clean($btn.data('department'));
                var unit = $btn.data('unit') ? String($btn.data('unit')).trim() : '';
                var receiveDate = clean($btn.data('receive-date'));

                var building = clean($btn.data('building'));
                var model = clean($btn.data('model'));
                var brand = clean($btn.data('brand'));
                var detail = clean($btn.data('detail'));
                var floor = clean($btn.data('floor'));
                var room = clean($btn.data('room'));
                var size = clean($btn.data('size'));
                var color = clean($btn.data('color'));

                var deptText = (department !== 'N/A') ? department + (unit ? ' (' + unit + ')' : '') : 'N/A';
                var priceText = (price !== 'N/A' && price !== '0.00' && price !== '0') ? price + ' บาท' : 'N/A';

                // นำค่าไปแสดงผลใน Modal ตาม ID ต่างๆ
                $('#modal-id').text(id);
                $('#modal-asset-no').text(assetNo);
                $('#modal-property-name').text(propertyName);
                $('#modal-property-type').text(propertyType);
                $('#modal-budget-year').text(budgetYear);
                $('#modal-price').text(priceText);
                $('#modal-receive-date').text(receiveDate);
                $('#modal-department').text(deptText);

                $('#modal-building').text(building);
                $('#modal-model').text(model);
                $('#modal-brand').text(brand);
                $('#modal-detail').text(detail);
                $('#modal-floor').text(floor);
                $('#modal-room').text(room);
                $('#modal-size').text(size);
                $('#modal-color').text(color);

                console.log("✅ ข้อมูลอัปเดตลงสู่หน้าต่าง Modal เรียบร้อย!");
            });
        });
    </script>

</body>

</html>