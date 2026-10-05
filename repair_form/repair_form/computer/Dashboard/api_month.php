<?php
include("../../../config.php");

header("Content-Type: application/json; charset=utf-8");

$sql = "
SELECT
MONTH(created_at) m,
COUNT(*) total
FROM repair_receive_jobs
GROUP BY MONTH(created_at)
ORDER BY MONTH(created_at)
";

$result = $conn->query($sql);

$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {

    $labels[] = "เดือน " . $row['m'];
    $data[] = (int)$row['total'];
}

echo json_encode([
    "labels" => $labels,
    "data" => $data
]);
