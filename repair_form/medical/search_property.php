<?php
include("../../config.php");

if (!isset($_SESSION["user_id"])) {
    exit;
}

$keyword = trim($_GET['keyword'] ?? '');
$sql = "SELECT * FROM properties";
$params = [];
$types = "";

if ($keyword != "") {
    $sql .= " WHERE asset_no LIKE ? OR property_type LIKE ?";
    $search = "%{$keyword}%";
    $params[] = $search;
    $params[] = $search;
    $types = "ss";
}

$sql .= " ORDER BY id DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$no = 1;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['budget_year']) ?></td>
            <td><?= htmlspecialchars($row['asset_no']) ?></td>
            <td class="text-center"><?= htmlspecialchars($row['purchase_date']) ?></td>
            <td><?= htmlspecialchars($row['property_name']) ?></td>
            <td><?= htmlspecialchars($row['property_type']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['department_unit']) ?></td>
            <td><?= htmlspecialchars($row['borrow_department']) ?></td>
            <td><?= htmlspecialchars($row['withdraw_status']) ?></td>
            <td><?= htmlspecialchars($row['withdraw_status']) ?></td>
            <td class="text-center">
                <button type="button" class="btn btn-primary btn-sm btn-view-detail" 
                    data-bs-toggle="modal"
                    data-bs-target="#equipmentDetailModal" 
                    data-id="<?= $row['id'] ?>"
                    data-asset-no="<?= htmlspecialchars($row['asset_no'] ?? '') ?>"
                    data-receive-date="<?= htmlspecialchars($row['purchase_date'] ?? '') ?>"
                    data-property-name="<?= htmlspecialchars($row['property_name'] ?? '') ?>"
                    data-property-type="<?= htmlspecialchars($row['property_type'] ?? '') ?>"
                    data-budget-year="<?= htmlspecialchars($row['budget_year'] ?? '') ?>"
                    data-price="<?= number_format($row['price'] ?? 0, 2) ?>"
                    data-department="<?= htmlspecialchars($row['department'] ?? '') ?>"
                    data-unit="<?= htmlspecialchars($row['department_unit'] ?? '') ?>"
                    data-building="<?= htmlspecialchars($row['building'] ?? '') ?>"
                    data-model="<?= htmlspecialchars($row['model'] ?? '') ?>"
                    data-brand="<?= htmlspecialchars($row['brand'] ?? '') ?>"
                    data-detail="<?= htmlspecialchars($row['detail'] ?? '') ?>"
                    data-floor="<?= htmlspecialchars($row['floor'] ?? '') ?>"
                    data-room="<?= htmlspecialchars($row['room'] ?? '') ?>"
                    data-size="<?= htmlspecialchars($row['size'] ?? '') ?>"
                    data-color="<?= htmlspecialchars($row['color'] ?? '') ?>">
                    <i class="fas fa-eye"></i>
                </button>
                <a href="maintenance_detail.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="แก้ไข">
                    <i class="fas fa-pen"></i>
                </a>
            </td>
        </tr>
        <?php
    }
} else {
    ?>
    <tr>
        <td colspan="12" class="text-center">ไม่พบข้อมูล</td>
    </tr>
    <?php
}
?>