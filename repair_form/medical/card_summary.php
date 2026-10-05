<?php
// ถ้ายังไม่ได้ include mock_data
// require_once '../../mock_data/mock_data.php';

$summary = [
    "total"      => 6,
    "waiting"    => 4,
    "receive"    => 0,
    "repairing"  => 0,
    "finish"     => 1,
    "outsource"  => 1,
    "dispose"    => 0,
    "main"       => 0,
    "cancel"     => 0,
];
?>

<!-- งานซ่อมทั้งหมด -->
<div class="card shadow-sm border-0 mb-4">

    <div class="card-body bg-primary text-white rounded">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <small>งานซ่อมทั้งหมด</small>

                <h1 class="fw-bold mb-0">
                    <?= $summary['total']; ?>
                </h1>

                <small>รายการ</small>

            </div>

            <div>

                <i class="fa-solid fa-folder-open fa-3x opacity-75"></i>

            </div>

        </div>

    </div>

</div>


<div class="row g-3">

    <!-- รอซ่อม -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <small>รอซ่อม</small>
                        <h2><?= $summary['waiting']; ?></h2>
                        <small>รายการ</small>
                    </div>
                    <i class="fa-solid fa-envelope fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- รับเรื่อง -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <small>รับเรื่อง</small>
                        <h2><?= $summary['receive']; ?></h2>
                        <small>รายการ</small>
                    </div>
                    <i class="fa-solid fa-envelope-open fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- กำลังซ่อม -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <small>กำลังซ่อม</small>
                        <h2><?= $summary['repairing']; ?></h2>
                        <small>รายการ</small>
                    </div>
                    <i class="fa-solid fa-screwdriver-wrench fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ซ่อมเสร็จ -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <small>ซ่อมเสร็จแล้ว</small>
                        <h2><?= $summary['finish']; ?></h2>
                        <small>รายการ</small>
                    </div>
                    <i class="fa-solid fa-check fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่งซ่อมภายนอก -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <small>ส่งซ่อมภายนอก</small>
                        <h2><?= $summary['outsource']; ?></h2>
                        <small>รายการ</small>
                    </div>
                    <i class="fa-solid fa-truck fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- จำหน่าย -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm text-white h-100"
             style="background:#ff8b3d;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <small>จำหน่าย</small>
                        <h2><?= $summary['dispose']; ?></h2>
                        <small>รายการ</small>
                    </div>
                    <i class="fa-solid fa-dollar-sign fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- แจ้งยกเลิก -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm text-white h-100"
             style="background:#ff6b81;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <small>แจ้งยกเลิก</small>
                        <h2><?= $summary['main']; ?></h2>
                        <small>รายการ</small>
                    </div>
                    <i class="fa-solid fa-xmark fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ยกเลิก -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <small>ยกเลิก</small>
                        <h2><?= $summary['cancel']; ?></h2>
                        <small>รายการ</small>
                    </div>
                    <i class="fa-solid fa-xmark fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

</div>