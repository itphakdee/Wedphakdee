<?php
include("../../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../login.php");
    exit();
}

/* ===============================
   KPI
================================ */

$totalJob = $conn->query("
SELECT COUNT(*) total
FROM repair_receive_jobs
")->fetch_assoc()['total'];

$working = $conn->query("
SELECT COUNT(*) total
FROM repair_receive_jobs
WHERE status='กำลังดำเนินการ'
")->fetch_assoc()['total'];

$finish = $conn->query("
SELECT COUNT(*) total
FROM repair_receive_jobs
WHERE status='เสร็จสิ้น'
")->fetch_assoc()['total'];

$selfRepair = $conn->query("
SELECT COUNT(*) total
FROM repair_receive_jobs
WHERE repair_type='ซ่อมเอง'
")->fetch_assoc()['total'];

$outRepair = $conn->query("
SELECT COUNT(*) total
FROM repair_receive_jobs
WHERE repair_type='ส่งซ่อมข้างนอก'
")->fetch_assoc()['total'];

$totalPrice = $conn->query("
SELECT IFNULL(SUM(total_price),0) total
FROM repair_receive_jobs
")->fetch_assoc()['total'];

?>
<!doctype html>
<html lang="th">

<head>

    <meta charset="utf-8">

    <title>Dashboard งานซ่อม</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="dashboard.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

    <div class="container-fluid py-4">

        <h2 class="mb-4">

            📊 Dashboard งานซ่อมคอมพิวเตอร์

        </h2>

        <div class="mb-3">

            <a href="../indexrepairlist.php" class="btn btn-secondary">

                ← กลับหน้ารายการแจ้งซ่อม

            </a>

        </div>

        <div class="row g-3">

            <div class="col-lg-2 col-md-4">

                <div class="kpi bg-primary">

                    <h2><?= number_format($totalJob) ?></h2>

                    <p>งานทั้งหมด</p>

                </div>

            </div>

            <div class="col-lg-2 col-md-4">

                <div class="kpi bg-warning">

                    <h2><?= number_format($working) ?></h2>

                    <p>กำลังดำเนินการ</p>

                </div>

            </div>

            <div class="col-lg-2 col-md-4">

                <div class="kpi bg-success">

                    <h2><?= number_format($finish) ?></h2>

                    <p>เสร็จสิ้น</p>

                </div>

            </div>

            <div class="col-lg-2 col-md-4">

                <div class="kpi bg-info">

                    <h2><?= number_format($selfRepair) ?></h2>

                    <p>ซ่อมเอง</p>

                </div>

            </div>

            <div class="col-lg-2 col-md-4">

                <div class="kpi bg-danger">

                    <h2><?= number_format($outRepair) ?></h2>

                    <p>ส่งซ่อมนอก</p>

                </div>

            </div>

            <div class="col-lg-2 col-md-4">

                <div class="kpi bg-dark">

                    <h3><?= number_format($totalPrice, 2) ?></h3>

                    <p>ค่าใช้จ่ายรวม</p>

                </div>

            </div>

        </div>

        <hr>

        <div class="row mt-3">

            <div class="col-lg-6">

                <div class="card shadow">

                    <div class="card-header">

                        สถานะงาน

                    </div>

                    <div class="card-body">

                        <canvas id="statusChart"></canvas>

                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="card shadow">

                    <div class="card-header">

                        ประเภทการซ่อม

                    </div>

                    <div class="card-body">

                        <canvas id="repairChart"></canvas>

                    </div>

                </div>

            </div>

        </div>

        <div class="row mt-4">

            <div class="col-lg-12">

                <div class="card shadow">

                    <div class="card-header">

                        จำนวนงานรายเดือน

                    </div>

                    <div class="card-body">

                        <canvas id="monthChart"></canvas>

                    </div>

                </div>

            </div>

        </div>

        <div class="row mt-4">

            <div class="col-lg-6">

                <div class="card shadow">

                    <div class="card-header">

                        Top Technician

                    </div>

                    <div class="card-body">

                        <canvas id="technicianChart"></canvas>

                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="card shadow">

                    <div class="card-header">

                        ค่าใช้จ่ายรายเดือน

                    </div>

                    <div class="card-body">

                        <canvas id="costChart"></canvas>

                    </div>

                </div>

            </div>

        </div>

        <div class="row mt-4">

            <div class="col-lg-12">

                <div class="card shadow">

                    <div class="card-header">

                        10 งานล่าสุด

                    </div>

                    <div class="card-body">

                        <div id="latestTable">

                            กำลังโหลดข้อมูล...

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="dashboard.js"></script>

</body>

</html>