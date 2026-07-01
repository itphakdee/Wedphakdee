<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login.php");
    exit();
}


$sql = "
SELECT
r.*,
t.name AS technician_name
FROM repair_jobs r
LEFT JOIN technicians t
ON r.technician_id = t.id
ORDER BY r.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <title>ระบบวันลา</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>ข้อมูลการแจ้งซ่อมคอมพิวเตอร์</title>
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

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
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
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #1f7d35;
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
    <div class="top-bar">
        🏢 ระบบแจ้งซ่อมและบริหารงาน
    </div>
    <div class="container mt-4">
        <?php
        $activePage = "repair";
        $basePath = "../../";
        require __DIR__ . "/../../components/sidebar.php";
        ?>
        <div class="card shadow">

            <div class="card-header bg-primary text-white">

                ข้อมูลการแจ้งซ่อมคอมพิวเตอร์

            </div>


            <div class="card-body">
                <a href="repair_form.php" class="btn btn-success ">
                    เพิ่มข้อมูลแจ้งซ่อม
                </a>

                <a href="savejob/receive_job_list.php" class="btn btn-primary">
                    📋 รายละเอียดงานที่รับซ่อม
                </a>
                <a href="Dashboard/dashboard.php" class="btn btn-info text-white">
                    📊 Dashboard สรุปงาน
                </a>
                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>ชื่อผู้ส่ง</th>
                            <th>แผนก</th>
                            <th>ระบบที่ต้องการแจ้งซ่อม</th>
                            <th>สถานที่พบเจอปัญหา</th>
                            <th>ช่าง</th>
                            <th>รายละเอียด</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?= $row['id'] ?>
                                </td>

                                <td>
                                    <?= $row['sender_name'] ?>
                                </td>

                                <td>
                                    <?= $row['department'] ?>
                                </td>

                                <td>
                                    <?= $row['repair_system'] ?>
                                </td>

                                <td>
                                    <?= $row['location'] ?>
                                </td>

                                <td>
                                    <?= $row['technician_name'] ?>
                                </td>

                                <td>
                                    <?= $row['details'] ?>
                                </td>

                                <td>
                                    <?php
                                    switch ($row['priority']) {

                                        case 'normal':
                                            echo '<span class="badge bg-success">ปกติ</span>';
                                            break;

                                        case 'urgent':
                                            echo '<span class="badge bg-warning text-dark">ด่วน</span>';
                                            break;

                                        case 'emergency':
                                            echo '<span class="badge bg-danger">ด่วนมาก</span>';
                                            break;

                                        default:
                                            echo '<span class="badge bg-secondary">ไม่ระบุ</span>';
                                    }
                                    ?>
                                </td>

                                <td>

                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                        แก้ไข
                                    </a>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('ยืนยันการลบ?')">
                                        ลบ
                                    </a>

                                    <a href="receive_job.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">
                                        รับงาน
                                    </a>


                                </td>

                            </tr>

                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>#003d99
    <?php require __DIR__ . "/../../components/dialog.php"; ?>
    <script>
        <?php if ($show_login_success) { ?>
            showMessageDialog(
                <?php echo json_encode("สวัสดี " . $_SESSION["fullname"] . "\nยินดีต้อนรับเข้าสู่ระบบแจ้งซ่อมและบริหารงาน", JSON_UNESCAPED_UNICODE); ?>,
                <?php echo json_encode("✅ ยินดีต้อนรับ", JSON_UNESCAPED_UNICODE); ?>
            );
            z
        <?php } ?>
    </script>
</body>

</html>