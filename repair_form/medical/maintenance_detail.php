<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลครุภัณฑ์และการบำรุงรักษา</title>

    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/medical.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .nav-link.active {
            background-color: #6c757d !important;
            color: #000 !important;
        }

        .nav-link:not(.active) {
            background-color: rgba(255, 255, 255, 0.5) !important;
            color: #495057 !important;
        }

        .modal-xl {
            max-width: 95%;
        }

        .table-warning th {
            background: #ffe7bc !important;
            vertical-align: middle;
            font-weight: 600;
        }

        .modal-body {
            padding: 25px;
        }

        .form-label {
            margin-top: 8px;
        }

        .btn-success,
        .btn-danger,
        .btn-primary {
            border-radius: 6px;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <div class="container mt-4">
        <div class="card shadow-sm border-danger p-4" style="border-width: 2px;">

            <div class="card mb-4 border-0 bg-light">
                <div class="card-header text-center fw-bold bg-secondary bg-opacity-10 py-3">
                    ข้อมูลครุภัณฑ์เครื่องมือการแพทย์
                </div>
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="border d-flex align-items-center justify-content-center bg-white mx-auto shadow-sm"
                                style="width: 180px; height: 180px; border-radius: 8px;">
                                <i class="fas fa-microscope text-secondary" style="font-size: 4.5rem;"></i>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <td width="30%" class="fw-bold text-end">รหัส :</td>
                                            <td width="70%">56</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">ครุภัณฑ์ :</td>
                                            <td>เครื่องดึงคอและหลัง</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">อาคาร :</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">โมเดล :</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">ยี่ห้อ :</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">วันที่รับ :</td>
                                            <td>20 ก.พ. 2563</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">รายละเอียด :</td>
                                            <td>-</td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <td width="30%" class="fw-bold text-end">เลขครุภัณฑ์ :</td>
                                            <td width="70%">6530-004-1103/2/63</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">ชั้น :</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">ห้อง :</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">ขนาด :</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">สี :</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-end">ราคา :</td>
                                            <td>374500.00</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs bg-secondary bg-opacity-10 border-bottom-0 rounded-top px-2 pt-2"
                id="maintenanceTab" role="tablist" style="flex-wrap: wrap;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-dark" id="pm-tab" data-bs-toggle="tab"
                        data-bs-target="#pm-content" type="button" role="tab" aria-controls="pm-content"
                        aria-selected="true"
                        style="font-size: 0.95rem; padding: 0.5rem 0.75rem; color: #000 !important;">ตรวจเช็ค(PM)</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark" id="cal-tab" data-bs-toggle="tab" data-bs-target="#cal-content"
                        type="button" role="tab" aria-controls="cal-content" aria-selected="false"
                        style="font-size: 0.95rem; padding: 0.5rem 0.75rem; color: #495057 !important; background-color: rgba(255,255,255,0.5);">สอบเทียบ(CAL)</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark" id="cm-tab" data-bs-toggle="tab" data-bs-target="#cm-content"
                        type="button" role="tab" aria-controls="cm-content" aria-selected="false"
                        style="font-size: 0.95rem; padding: 0.5rem 0.75rem; color: #495057 !important; background-color: rgba(255,255,255,0.5);">ประวัติซ่อม(CM)</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark" id="plan-pm-tab" data-bs-toggle="tab"
                        data-bs-target="#plan-pm-content" type="button" role="tab" aria-controls="plan-pm-content"
                        aria-selected="false"
                        style="font-size: 0.95rem; padding: 0.5rem 0.75rem; color: #495057 !important; background-color: rgba(255,255,255,0.5);">แผนบำรุง</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark" id="plan-cal-tab" data-bs-toggle="tab"
                        data-bs-target="#plan-cal-content" type="button" role="tab" aria-controls="plan-cal-content"
                        aria-selected="false"
                        style="font-size: 0.95rem; padding: 0.5rem 0.75rem; color: #495057 !important; background-color: rgba(255,255,255,0.5);">แผนสอบ</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark" id="list-pm-tab" data-bs-toggle="tab"
                        data-bs-target="#list-pm-content" type="button" role="tab" aria-controls="list-pm-content"
                        aria-selected="false"
                        style="font-size: 0.95rem; padding: 0.5rem 0.75rem; color: #495057 !important; background-color: rgba(255,255,255,0.5);">รายการบำรุง</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark" id="list-cal-tab" data-bs-toggle="tab"
                        data-bs-target="#list-cal-content" type="button" role="tab" aria-controls="list-cal-content"
                        aria-selected="false"
                        style="font-size: 0.95rem; padding: 0.5rem 0.75rem; color: #495057 !important; background-color: rgba(255,255,255,0.5);">รายการสอบ</button>
                </li>
            </ul>

            <div class="tab-content p-3 border rounded-bottom bg-white" id="maintenanceTabContent">

                <div class="tab-pane fade show active" id="pm-content" role="tabpanel" aria-labelledby="pm-tab">
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal"
                            data-bs-target="#addMaintenanceModal">
                            เพิ่มการตรวจบำรุงรักษา
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle mb-0">
                            <thead class="table-warning text-dark border-dark">
                                <tr>
                                    <th style="width: 12%;">วันที่ตรวจ</th>
                                    <th style="width: 10%;">เวลา</th>
                                    <th style="width: 20%;">ผู้ตรวจเช็คอุปกรณ์</th>
                                    <th style="width: 20%;">หัวหน้ารับรอง</th>
                                    <th style="width: 10%;">ผลการตรวจ</th>
                                    <th style="width: 10%;">ประเภท</th>
                                    <th style="width: 10%;">หมายเหตุ</th>
                                    <th style="width: 8%;">ตรวจสอบ</th>
                                    <th style="width: 10%;">คำสั่ง</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1 ก.ค. 2569</td>
                                    <td>10:00:00</td>
                                    <td>นายณัฐวุฒิ วรรณพงษ์</td>
                                    <td>น.ส.ปานจิตร หมอกชัย</td>
                                    <td class="text-danger">ผิดปกติ</td>
                                    <td>ตรวจเช็คอื่นๆ</td>
                                    <td>ทดสอบ</td>
                                    <td>
                                        <button class="btn btn-success btn-sm p-1 px-2">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                                ทำรายการ
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-menu-item dropdown-item" href="#">แก้ไข</a></li>
                                                <li><a class="dropdown-menu-item dropdown-item text-danger"
                                                        href="#">ลบ</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="cal-content" role="tabpanel" aria-labelledby="cal-tab">
                    <div class="p-3 text-center text-muted">ตารางข้อมูลการสอบเทียบคุณภาพ (CAL) ยังไม่มีข้อมูล</div>
                </div>

                <div class="tab-pane fade" id="cm-content" role="tabpanel" aria-labelledby="cm-tab">
                    <div class="p-3 text-center text-muted">ตารางประวัติการซ่อม (CM) ยังไม่มีข้อมูล</div>
                </div>

                <div class="tab-pane fade" id="plan-pm-content" role="tabpanel" aria-labelledby="plan-pm-tab">
                    <div class="p-3 text-center text-muted">ตารางแผนบำรุงรักษา ยังไม่มีข้อมูล</div>
                </div>

                <div class="tab-pane fade" id="plan-cal-content" role="tabpanel" aria-labelledby="plan-cal-tab">
                    <div class="p-3 text-center text-muted">ตารางแผนสอบเทียบ ยังไม่มีข้อมูล</div>
                </div>

                <div class="tab-pane fade" id="list-pm-content" role="tabpanel" aria-labelledby="list-pm-tab">
                    <div class="p-3 text-center text-muted">ตารางรายการบำรุงรักษา ยังไม่มีข้อมูล</div>
                </div>

                <div class="tab-pane fade" id="list-cal-content" role="tabpanel" aria-labelledby="list-cal-tab">
                    <div class="p-3 text-center text-muted">ตารางรายการสอบเทียบ ยังไม่มีข้อมูล</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addMaintenanceModal" tabindex="-1" aria-labelledby="addMaintenanceModalLabel"
        aria-modal="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="addMaintenanceModalLabel">
                        เพิ่มข้อมูลการตรวจบำรุงรักษา
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">

                    <div class="row g-3">

                        <!-- วันที่ -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold">
                                วันที่ทำรายการ
                            </label>
                        </div>

                        <div class="col-md-3">
                            <input type="date" class="form-control">
                        </div>

                        <!-- เวลา -->
                        <div class="col-md-1">
                            <label class="form-label fw-bold">
                                เวลา
                            </label>
                        </div>

                        <div class="col-md-2">
                            <input type="time" class="form-control">
                        </div>

                        <!-- ถึงเวลา -->
                        <div class="col-md-1">
                            <label class="form-label fw-bold">
                                ถึงเวลา
                            </label>
                        </div>

                        <div class="col-md-3">
                            <input type="time" class="form-control">
                        </div>

                        <!-- หัวหน้ารับรอง -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold">
                                หัวหน้ารับรอง
                            </label>
                        </div>

                        <div class="col-md-3">
                            <select class="form-select">
                                <option>--กรุณาเลือกหัวหน้ารับรอง--</option>
                            </select>
                        </div>

                        <!-- ประเภท -->
                        <div class="col-md-1">
                            <label class="form-label fw-bold">
                                ประเภท
                            </label>
                        </div>

                        <div class="col-md-2 d-flex align-items-center">
                            ตรวจเช็คอื่นๆ
                        </div>

                        <!-- หมายเหตุ -->
                        <div class="col-md-1">
                            <label class="form-label fw-bold">
                                หมายเหตุ
                            </label>
                        </div>

                        <div class="col-md-3">
                            <input type="text" class="form-control">
                        </div>

                    </div>

                    <!-- Table -->
                    <div class="table-responsive mt-4">

                        <table class="table table-bordered align-middle text-center">

                            <thead class="table-warning">

                                <tr>

                                    <th width="40%">
                                        รายการปฏิบัติ
                                    </th>

                                    <th width="15%">
                                        ผลการตรวจเช็ค
                                    </th>

                                    <th>
                                        หมายเหตุ
                                    </th>

                                    <th width="80">
                                        <button class="btn btn-success">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </th>

                                </tr>

                            </thead>

                            <tbody id="tbodyCheck">

                                <tr>

                                    <td>
                                        <select class="form-select">
                                            <option>
                                                --กรุณาเลือกรายการ--
                                            </option>
                                        </select>
                                    </td>

                                    <td>
                                        <select class="form-select">
                                            <option>ปกติ</option>
                                            <option>ผิดปกติ</option>
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" class="form-control">
                                    </td>

                                    <td>
                                        <button class="btn btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">

                    <button class="btn btn-primary px-4">
                        บันทึกข้อมูล
                    </button>

                    <button class="btn btn-danger" data-bs-dismiss="modal">
                        ยกเลิก
                    </button>

                </div>

            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/medical.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('#maintenanceTab .nav-link');

            tabButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    // ให้ Bootstrap plugin ทำงานก่อน
                    setTimeout(() => {
                        tabButtons.forEach(btn => {
                            btn.classList.remove('active');
                        });

                        document.querySelectorAll('.tab-pane').forEach(pane => {
                            pane.classList.remove('active', 'show');
                        });

                        this.classList.add('active');

                        const targetId = this.getAttribute('data-bs-target');
                        if (targetId) {
                            const targetPane = document.querySelector(targetId);
                            if (targetPane) {
                                targetPane.classList.add('active', 'show');
                            }
                        }
                    }, 0);
                });
            });

            // Handle add row button inside modal
            const addRowBtn = document.querySelector("#addMaintenanceModal .btn-success");
            if (addRowBtn) {
                addRowBtn.addEventListener("click", function (e) {
                    e.preventDefault();
                    const tbody = document.getElementById("tbodyCheck");

                    tbody.insertAdjacentHTML("beforeend", `
                        <tr>
                            <td>
                                <select class="form-select">
                                    <option>--กรุณาเลือกรายการ--</option>
                                </select>
                            </td>

                            <td>
                                <select class="form-select">
                                    <option>ปกติ</option>
                                    <option>ผิดปกติ</option>
                                </select>
                            </td>

                            <td>
                                <input type="text" class="form-control">
                            </td>

                            <td class="text-center">
                                <button class="btn btn-danger btn-delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }

            // Handle delete row buttons
            document.addEventListener("click", function (e) {
                if (e.target.closest(".btn-delete")) {
                    e.target.closest("tr").remove();
                }
            });
        });
    </script>
</body>

</html>