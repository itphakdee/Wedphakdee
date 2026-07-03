<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลครุภัณฑ์และการบำรุงรักษา</title>
    
    <link href="/system_login/Wedphakdee/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/system_login/Wedphakdee/assets/css/medical.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                           <div class="border d-inline-block p-2 bg-white" style="width: 180px; height: 180px;">
                           <img src="/system_login/Wedphakdee/assets/images/1.png" alt="รูปครุภัณฑ์" class="img-fluid h-100 object-fit-cover" 
                               onerror="this.onerror=null; this.src='https://via.placeholder.com/180?text=No+Image';">
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

        <ul class="nav nav-tabs bg-secondary bg-opacity-10 border-bottom-0 rounded-top px-2 pt-2" id="maintenanceTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-dark" id="pm-tab" data-bs-toggle="tab" data-bs-target="#pm-content" type="button" role="tab">ตรวจเช็คบำรุงรักษา(PM)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary" id="cal-tab" data-bs-toggle="tab" data-bs-target="#cal-content" type="button" role="tab">สอบเทียบคุณภาพ(CAL)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary" id="cm-tab" data-bs-toggle="tab" data-bs-target="#cm-content" type="button" role="tab">ประวัติการซ่อม(CM)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary" id="plan-pm-tab" data-bs-toggle="tab" data-bs-target="#plan-pm-content" type="button" role="tab">แผนบำรุงรักษา</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary" id="plan-cal-tab" data-bs-toggle="tab" data-bs-target="#plan-cal-content" type="button" role="tab">แผนสอบเทียบ</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary" id="list-pm-tab" data-bs-toggle="tab" data-bs-target="#list-pm-content" type="button" role="tab">รายการบำรุงรักษา</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary" id="list-cal-tab" data-bs-toggle="tab" data-bs-target="#list-cal-content" type="button" role="tab">รายการสอบเทียบ</button>
            </li>
        </ul>

        <div class="tab-content p-3 border rounded-bottom bg-white" id="maintenanceTabContent">
            <div class="tab-pane fade show active" id="pm-content" role="tabpanel">
                
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-plus me-1"></i> เพิ่มการตรวจบำรุงรักษา
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
                                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            ทำรายการ
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-menu-item dropdown-item" href="#">แก้ไข</a></li>
                                            <li><a class="dropdown-menu-item dropdown-item text-danger" href="#">ลบ</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="tab-pane fade" id="cal-content" role="tabpanel">...</div>
            <div class="tab-pane fade" id="cm-content" role="tabpanel">...</div>
            <div class="tab-pane fade" id="plan-pm-content" role="tabpanel">...</div>
            <div class="tab-pane fade" id="plan-cal-content" role="tabpanel">...</div>
            <div class="tab-pane fade" id="list-pm-content" role="tabpanel">...</div>
            <div class="tab-pane fade" id="list-cal-content" role="tabpanel">...</div>
        </div>

    </div>
</div>

<script src="/system_login/Wedphakdee/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/system_login/Wedphakdee/assets/js/medical.js"></script>
</body>
</html>