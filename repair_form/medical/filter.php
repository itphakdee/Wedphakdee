<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบข้อมูลการซ่อมเครื่องมือแพทย์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Sarabun', sans-serif;
            /* แนะนำให้ต่อยอดใช้ฟอนต์สารบรรณเพื่อความสวยงาม */
        }

        .navbar-custom {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .nav-link-custom {
            color: #495057;
            font-weight: 500;
        }

        .nav-link-custom:hover,
        .nav-link-custom.active {
            color: #5c6bc0;
        }

        .bg-dashboard-active {
            background-color: #e8eaf6;
            color: #3f51b5 !important;
            border-radius: 4px;
        }

        .card-header-custom {
            background-color: #f1f3f5;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <div class="card-header-custom text-dark mb-4">
        ข้อมูลการซ่อมเครื่องมือแพทย์
    </div>
    <nav class="navbar navbar-expand-lg navbar-custom py-2 mb-4">
        <div class="">
            <div class="collapse navbar-collapse " id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item ms-3">
                        <a class="nav-link nav-link-custom bg-dashboard-active px-3 fw-bold" href="index.php">
                            <i class="fa-solid fa-chart-pie me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link nav-link-custom" href="#">
                            <i class="fa-solid fa-calendar-days text-primary me-1"></i> ปฏิทินบำรุงรักษา
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link nav-link-custom" href="#">
                            <i class="fa-solid fa-calendar-check text-primary me-1"></i> ทะเบียนแจ้งซ่อมเครื่องมือแพทย์
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link nav-link-custom" href="medical_list.php">
                            <i class="fa-solid fa-calendar-plus text-primary me-1"></i> ทะเบียนครุภัณฑ์เครื่องมือแพทย์
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown">
                            <i class="fa-solid fa-gear text-primary me-1"></i> ตั้งค่า
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">ตั้งค่าระบบ</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>