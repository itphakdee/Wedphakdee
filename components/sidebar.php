<?php
$activePage = $activePage ?? "";
$basePath = $basePath ?? "";

$menuItems = [
    "dashboard" => ["label" => "🏠 หน้าแรก", "href" => "dashboard.php"],
    "leave" => ["label" => "📅 วันลา", "href" => "leave.php"],
    "e_document" => ["label" => "📄 หนังสือราชการ", "href" => "e_document.php"],
    "vehicle" => ["label" => "🚗 ยานพาหนะ", "href" => "vehicle/index.php"],
    "repair" => ["label" => "🔧 แจ้งซ่อม", "href" => "repair_form.php"],
];
?>
<!-- Sidebar Navigation -->
<aside class="sidebar">
    <ul class="sidebar-menu">
        <li class="sidebar-title">📋 เมนูหลัก</li>

        <?php foreach ($menuItems as $page => $item): ?>
            <li>
                <a href="<?php echo htmlspecialchars($basePath . $item["href"]); ?>" class="<?php echo $activePage === $page ? "active" : ""; ?>">
                    <?php echo $item["label"]; ?>
                </a>
            </li>
        <?php endforeach; ?>

        <li class="menu-divider"></li>
        <li class="sidebar-title">⚙️ ตั้งค่า</li>

        <?php if ($activePage === "dashboard"): ?>
            <li><a href="">โปรไฟล์</a></li>
        <?php endif; ?>

        <li><a href="<?php echo htmlspecialchars($basePath . "logout.php"); ?>" class="logout-link">🚪 ออกจากระบบ</a></li>
    </ul>
</aside>
