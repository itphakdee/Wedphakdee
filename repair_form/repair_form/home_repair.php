<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Tahoma, sans-serif;
            background: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #0051ffff;
            padding: 15px 20px;
            color: white;
            font-size: 22px;
            font-weight: bold;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .container-wrapper {
            display: flex;
            width: 100%;
            margin-top: 60px;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #003d99, #0051ff);
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            left: 0;
            top: 60px;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #48cae4;
            padding-left: 25px;
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: #48cae4;
        }

        .menu-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
            margin: 10px 0;
        }

        .sidebar-title {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 15px 20px 8px;
            letter-spacing: 1px;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px 20px;
        }

        .wrapper {
            width: 100%;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .menu-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #2a9d8f;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            margin: 5px 5px 5px 0;
        }

        .msg {
            background: #d4edda;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        input,
        select,
        textarea,
        button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: Tahoma, sans-serif;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button {
            background: #2a9d8f;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #1e7566;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background: #2a9d8f;
            color: white;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }
        }

        @media (max-width: 600px) {
            .container-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <div class="top-bar">🏢 ระบบแจ้งซ่อมและบริหารงาน</div>

    <div class="container-wrapper">
        <?php
        $activePage = "vehicle";
        $basePath = "../";
        require __DIR__ . "/../components/sidebar.php";
        ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="row">
                <style>
                    .menu-grid {

                        display: grid;

                        grid-template-columns:
                            repeat(auto-fit, minmax(220px, 1fr));

                        gap: 25px;

                        padding: 25px;
                    }

                    .menu-card {

                        background: #f7f7f7;

                        min-height: 180px;

                        border-radius: 10px;

                        text-decoration: none;

                        display: flex;

                        flex-direction: column;

                        justify-content: center;

                        align-items: center;

                        transition: .3s;

                        box-shadow:
                            0 2px 5px rgba(0, 0, 0, .08);

                        border: 1px solid #eee;
                    }

                    .menu-card i {

                        font-size: 50px;

                        margin-bottom: 20px;
                    }

                    .menu-card span {

                        color: #222;

                        font-size: 24px;

                        font-weight: 600;
                    }

                    .menu-card:hover {

                        transform: translateY(-8px);

                        box-shadow:
                            0 10px 25px rgba(0, 0, 0, .15);

                        background: white;
                    }
                </style>
                <div class="menu-grid">

                    <a href="personnel/index.php" class="menu-card">
                        <i class="fas fa-users text-primary"></i>
                        <span>บุคลากร</span>
                    </a>

                    <a href="attendance/index.php" class="menu-card">
                        <i class="fas fa-history text-dark"></i>
                        <span>ระบบลงเวลา</span>
                    </a>

                    <a href="leave/index.php" class="menu-card">
                        <i class="fas fa-calendar-alt text-primary"></i>
                        <span>ระบบการลา</span>
                    </a>

                    <a href="documents/index.php" class="menu-card">
                        <i class="fas fa-book text-warning"></i>
                        <span>สารบรรณ</span>
                    </a>

                    <a href="meeting_final/index.php" class="menu-card">
                        <i class="fas fa-tablet-alt text-success"></i>
                        <span>ระบบห้องประชุม</span>
                    </a>

                    <a href="../vehicle/index.php" class="menu-card">
                        <i class="fas fa-ambulance text-info"></i>
                        <span>ยานพาหนะ</span>
                    </a>
                    <a href="maintenance/index.php" class="menu-card">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span>แจ้งซ่อม</span>
                    </a>
                    <a href="computer/indexrepairlist.php" class="menu-card">
                        <i class="fa-solid fa-desktop"></i>
                        <span>ศูนย์คอมพิวเตอร์</span>
                    </a>
                    <a href="medical/index.php" class="menu-card">
                        <i class="fa fa-3x fa-stethoscope text-danger"></i>
                        <span>ศูนย์เครื่องมือแพทย์</span>
                    </a>
                    <a href="propertywork/index.php" class="menu-card">
                        <i class="fa-solid fa-house "></i>
                        <span>งานทรัพย์สิน</span>
                    </a>



                </div>

            </div>
        </main>
    </div>




    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php require __DIR__ . "/../components/dialog.php"; ?>
    <?php $show_login_success = $_SESSION["show_login_success"] ?? false; ?>
    <script>
        <?php if ($show_login_success) { ?>
            showMessageDialog(
                <?php echo json_encode("สวัสดี " . ($_SESSION["fullname"] ?? "ผู้ใช้") . "\nยินดีต้อนรับเข้าสู่ระบบแจ้งซ่อมและบริหารงาน", JSON_UNESCAPED_UNICODE); ?>,
                <?php echo json_encode("✅ ยินดีต้อนรับ", JSON_UNESCAPED_UNICODE); ?>
            );
        <?php } ?>
    </script>
</body>

</html>