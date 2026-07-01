<?php
include("../../../config.php");

header("Content-Type: application/json; charset=utf-8");

$sql = "
SELECT status,COUNT(*) total
FROM repair_receive_jobs
GROUP BY status
";

$result = $conn->query($sql);

$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {

    $labels[] = $row['status'];
    $data[] = (int)$row['total'];
}

echo json_encode([
    "labels" => $labels,
    "data" => $data
]);
