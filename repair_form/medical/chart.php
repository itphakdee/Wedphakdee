<?php

// mock_data.php already loaded by index.php
// use the same array variable passed through the parent file
?>
<script>
    console.log("CHART.PHP LOADED 1");
    window.repairData = <?= json_encode(array_values($data['repairType'] ?? [])); ?>;
    window.repairLabel = <?= json_encode(array_keys($data['repairType'] ?? [])); ?>;

    window.statusData = <?= json_encode(array_values($data['repairStatus'] ?? [])); ?>;
    window.statusLabel = <?= json_encode(array_keys($data['repairStatus'] ?? [])); ?>;

    window.inhouseData = <?= json_encode(array_values($data['inhouseOutside'] ?? [])); ?>;
    window.inhouseLabel = <?= json_encode(array_keys($data['inhouseOutside'] ?? [])); ?>;
    window.pmData = <?= json_encode(array_values($data['monthlyPM'] ?? [])); ?>;
    window.pmLabel = <?= json_encode(array_keys($data['monthlyPM'] ?? [])); ?>;

    window.monthlyRepairData = <?= json_encode(array_values($data['monthlyRepair'] ?? [])); ?>;
    window.monthlyRepairLabel = <?= json_encode(array_keys($data['monthlyRepair'] ?? [])); ?>;
    console.log("CHART.PHP LOADED 2");
</script>
<header>
    <style>
        .card-chart {
            height: 320px;
        }

        .card-chart canvas {
            height: 250px !important;
        }
    </style>
</header>

<body>
   <div class="mb-4 mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="" method="GET" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="fiscal_year" class="col-form-label fw-bold text-secondary">ประจำปีงบประมาณ :</label>
                    </div>
                    <div class="col-auto">
                        <select class="form-select" id="fiscal_year" name="fiscal_year" style="min-width: 200px;">
                            <?php
                            // ตัวอย่างการใช้ PHP วนลูปสร้างปีงบประมาณ (พ.ศ.)
                            $current_year = 2569; // กำหนดปีปัจจุบันตามในรูป
                            for ($year = $current_year; $year >= $current_year - 5; $year--) {
                                echo "<option value=\"$year\">$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary px-4">แสดง</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="row mt-4">

        <div class="col-lg-6 mb-4 d-flex">
            <div class="card shadow-sm border-success w-100 h-100">
                <div class="card-header bg-success text-white">
                    จำนวนงานซ่อมแบ่งตามประเภท
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="height:320px;">
                    <canvas id="repairTypeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4 d-flex">
            <div class="card shadow-sm border-success w-100 h-100">
                <div class="card-header bg-success text-white">
                    จำนวนงานซ่อมแยกตามแผนก
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="height:320px;">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4 d-flex">
            <div class="card shadow-sm border-success w-100 h-100s">
                <div class="card-header bg-success text-white">
                    จำนวนงานซ่อมรายเดือน
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="height:320px;">
                    <canvas id="monthlyRepairChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4 d-flex">
            <div class="card shadow-sm border-success w-100 h-100">
                <div class="card-header bg-success text-white">
                    งาน PM รายเดือน
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="height:320px;">
                    <canvas id="pmChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4 d-flex">
            <div class="card shadow-sm border-success w-100 h-100">
                <div class="card-header bg-success text-white">
                    สถานะงานซ่อม
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="height:320px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4 d-flex">
            <div class="card shadow-sm border-success w-100 h-100">
                <div class="card-header bg-success text-white">
                    ซ่อมเอง / ส่งซ่อม
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="height:320px;">
                    <canvas id="inhouseChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</body>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/medical.js"></script>