<?php
include("../../../config.php");

header("Content-Type: application/json; charset=utf-8");

$sql = "
SELECT
technician_name,
COUNT(*) total
FROM repair_receive_jobs
GROUP BY technician_name
ORDER BY total DESC
LIMIT 10
";

$result = $conn->query($sql);

$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {

    $labels[] = $row['technician_name'];
    $data[] = (int)$row['total'];
}

echo json_encode([
    "labels" => $labels,
    "data" => $data
]);
