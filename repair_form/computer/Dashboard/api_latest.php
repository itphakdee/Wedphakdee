<?php
include("../../../config.php");
$sql = "
SELECT *
FROM repair_receive_jobs
ORDER BY id DESC
LIMIT 10
";

$result = $conn->query($sql);
?>

<table class="table table-bordered table-hover">

    <thead class="table-primary">

        <tr>

            <th>ID</th>
            <th>ช่าง</th>
            <th>สถานะ</th>
            <th>ประเภท</th>
            <th>อุปกรณ์</th>
            <th>ค่าใช้จ่าย</th>
            <th>วันที่</th>

        </tr>

    </thead>

    <tbody>

        <?php while ($row = $result->fetch_assoc()) { ?>

            <tr>

                <td><?= $row['repair_job_id']; ?></td>

                <td><?= htmlspecialchars($row['technician_name']); ?></td>

                <td>

                    <?php

                    if ($row['status'] == "เสร็จสิ้น") {

                        echo '<span class="badge bg-success">เสร็จสิ้น</span>';
                    } elseif ($row['status'] == "กำลังดำเนินการ") {

                        echo '<span class="badge bg-warning text-dark">กำลังดำเนินการ</span>';
                    } else {

                        echo '<span class="badge bg-secondary">' . $row['status'] . '</span>';
                    }

                    ?>

                </td>

                <td><?= htmlspecialchars($row['repair_type']); ?></td>

                <td><?= htmlspecialchars($row['device_name']); ?></td>

                <td class="text-end">

                    <?= number_format($row['total_price'], 2); ?>

                </td>

                <td><?= $row['created_at']; ?></td>

            </tr>

        <?php } ?>

    </tbody>

</table>