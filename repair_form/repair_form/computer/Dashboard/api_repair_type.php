<?php
include("../../../config.php");

header("Content-Type: application/json; charset=utf-8");

$sql = "
SELECT repair_type,COUNT(*) total
FROM repair_receive_jobs
GROUP BY repair_type
";

$result = $conn->query($sql);

$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {

    $labels[] = $row['repair_type'];
    $data[] = (int)$row['total'];
}

echo json_encode([
    "labels" => $labels,
    "data" => $data
]);
